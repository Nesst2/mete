<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Wilayah;
use App\Models\VendorDeactivationRequest;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VendorDeactivationRequestController extends Controller
{
    // Menampilkan daftar request penonaktifan untuk admin & sales
    public function index(Request $request)
    {
        $filterKota = $request->get('kota'); // Opsi filter untuk admin
        $startDate  = $request->get('start_date');
        $endDate    = $request->get('end_date');
    
        if (Auth::user()->role == 'sales') {
            // Ambil kota dari relasi daerah user sales
            $salesKota = optional(Auth::user()->daerah)->kota;
    
            if ($salesKota) {
                $query = VendorDeactivationRequest::with('vendor', 'sales')
                    ->whereHas('vendor.wilayah.daerah', function ($query) use ($salesKota) {
                        $query->where('kota', $salesKota);
                    });
                
                // Jika ada filter tanggal, terapkan pada query
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [
                        $startDate,
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
    
                $requests = $query->orderBy('created_at', 'desc')->get();
            } else {
                $requests = collect(); // Jika tidak ada kota, kembalikan koleksi kosong
            }
        } else {
            // Untuk admin, memungkinkan filter berdasarkan kota dan tanggal
            $requests = VendorDeactivationRequest::with('vendor', 'sales')
                ->when($filterKota, function ($query) use ($filterKota) {
                    $query->whereHas('vendor.wilayah.daerah', function ($query) use ($filterKota) {
                        $query->where('kota', $filterKota);
                    });
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('created_at', [
                        $startDate,
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }
    
        // Ambil daftar kota untuk opsi filter (berdasarkan data wilayah)
        $kotaList = Wilayah::with('daerah')
            ->get()
            ->pluck('daerah.kota', 'daerah.kota')
            ->unique();
    
        return view('master_data.request.index', compact('requests', 'kotaList'));
    }
    
    // Request Nonaktifkan Vendor oleh Sales
    public function store(Request $request, $vendor_id)
{
    $request->validate([
        'reason' => 'required|string|max:255',
    ]);

    $vendor = Vendor::findOrFail($vendor_id);

    if ($vendor->status !== 'aktif') {
        return response()->json(['message' => 'Vendor tidak dalam kondisi aktif untuk dinonaktifkan'], 400);
    }

    // Jika yang login adalah admin
    if (Auth::user()->role === 'admin') {
        // Simpan request penonaktifan langsung disetujui
        $deactivationRequest = VendorDeactivationRequest::create([
            'vendor_id'   => $vendor->id,
            'sales_id'    => null, // Karena admin yang melakukan tindakan
            'reason'      => $request->reason,
            'status'      => 'approved', // Langsung approve
            'admin_id'    => Auth::id(),
            'approved_at' => now(),
        ]);

        // Update status vendor menjadi nonaktif
        $oldVendor = $vendor->toArray();
        $vendor->update(['status' => 'nonaktif']);

        // Log aktivitas (opsional)
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Vendor dinonaktifkan langsung oleh admin melalui request deactivation'
        );

        return response()->json([
            'message' => 'Vendor berhasil dinonaktifkan',
            'data'    => $deactivationRequest
        ], 200);
    } 
    // Jika yang login adalah sales
    else {
        // Pastikan tidak ada request yang masih pending
        $existingRequest = VendorDeactivationRequest::where('vendor_id', $vendor->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'Request penonaktifan sedang diproses'], 400);
        }

        $deactivationRequest = VendorDeactivationRequest::create([
            'vendor_id' => $vendor->id,
            'sales_id'  => Auth::id(),
            'reason'    => $request->reason,
            'status'    => 'pending'
        ]);

        // Update status vendor menjadi menunggu_verifikasi
        $oldVendor = $vendor->toArray();
        $vendor->update(['status' => 'menunggu_verifikasi']);

        // Log aktivitas (opsional)
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Status vendor diubah menjadi menunggu_verifikasi setelah request penonaktifan oleh sales'
        );

        return response()->json([
            'message' => 'Request berhasil dikirim, menunggu verifikasi admin',
            'data'    => $deactivationRequest
        ], 200);
    }
}

    public function approve($id)
    {
        $deactivationRequest = VendorDeactivationRequest::findOrFail($id);
        $oldRequestData = $deactivationRequest->toArray();
        $deactivationRequest->update([
            'status'     => 'approved', 
            'admin_id'   => Auth::id(), 
            'approved_at'=> now()
        ]);
        
        // Log aktivitas: Approve request penonaktifan
        ActivityLog::log(
            'update',
            'vendor_deactivation_requests',
            $deactivationRequest->id,
            $oldRequestData,
            $deactivationRequest->toArray(),
            'Deactivation request disetujui oleh admin'
        );
        
        // Jika disetujui, ubah status vendor menjadi 'nonaktif'
        $vendor = $deactivationRequest->vendor;
        $oldVendor = $vendor->toArray();
        $vendor->update(['status' => 'nonaktif']);
        
        // Log aktivitas: Update status vendor menjadi nonaktif
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Vendor dinonaktifkan karena deactivation request disetujui'
        );
        
        return response()->json(['message' => 'Vendor berhasil dinonaktifkan']);
    }
    
    public function reject($id)
    {
        $deactivationRequest = VendorDeactivationRequest::findOrFail($id);
        $oldRequestData = $deactivationRequest->toArray();
        $deactivationRequest->update([
            'status'   => 'rejected', 
            'admin_id' => Auth::id()
        ]);
        
        // Log aktivitas: Reject request penonaktifan
        ActivityLog::log(
            'update',
            'vendor_deactivation_requests',
            $deactivationRequest->id,
            $oldRequestData,
            $deactivationRequest->toArray(),
            'Deactivation request ditolak oleh admin'
        );
        
        // Jika ditolak, kembalikan status vendor ke 'aktif' apabila masih 'menunggu_verifikasi'
        $vendor = $deactivationRequest->vendor;
        if ($vendor->status == 'menunggu_verifikasi') {
            $oldVendor = $vendor->toArray();
            $vendor->update(['status' => 'aktif']);
            
            // Log aktivitas: Revert status vendor ke aktif
            ActivityLog::log(
                'update',
                'vendors',
                $vendor->id,
                $oldVendor,
                $vendor->toArray(),
                'Status vendor dikembalikan ke aktif karena deactivation request ditolak'
            );
        }
        
        return response()->json(['message' => 'Request penonaktifan ditolak, vendor tetap aktif']);
    }
    
    // Sales membatalkan request sebelum diproses admin
    public function cancel($id)
    {
        $deactivationRequest = VendorDeactivationRequest::findOrFail($id);
    
        if ($deactivationRequest->status !== 'pending' || $deactivationRequest->sales_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak bisa membatalkan request ini'], 400);
        }
    
        $oldRequestData = $deactivationRequest->toArray();
        $deactivationRequest->update(['status' => 'canceled']);
        
        // Log aktivitas: Cancel request penonaktifan
        ActivityLog::log(
            'update',
            'vendor_deactivation_requests',
            $deactivationRequest->id,
            $oldRequestData,
            $deactivationRequest->toArray(),
            'Deactivation request dibatalkan oleh sales'
        );
    
        // Revert status vendor kembali ke 'aktif' jika sebelumnya sudah diubah ke 'menunggu_verifikasi'
        $vendor = $deactivationRequest->vendor;
        if ($vendor->status == 'menunggu_verifikasi') {
            $oldVendor = $vendor->toArray();
            $vendor->update(['status' => 'aktif']);
            
            // Log aktivitas: Update status vendor kembali ke aktif
            ActivityLog::log(
                'update',
                'vendors',
                $vendor->id,
                $oldVendor,
                $vendor->toArray(),
                'Status vendor dikembalikan ke aktif karena request penonaktifan dibatalkan'
            );
        }
    
        return response()->json(['message' => 'Request penonaktifan berhasil dibatalkan']);
    }

    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
    
        $vendor = Vendor::findOrFail($id);
    
        if ($vendor->status == 'nonaktif') {
            return response()->json(['message' => 'Vendor sudah nonaktif'], 400);
        }
    
        // Buat entri deactivation request dengan status langsung 'approved'
        $deactivationRequest = VendorDeactivationRequest::create([
            'vendor_id'   => $vendor->id,
            'sales_id'    => null, // Karena ini dilakukan oleh admin
            'reason'      => $request->reason,
            'status'      => 'approved',
            'admin_id'    => Auth::id(),
            'approved_at' => now(),
        ]);
    
        // Update status vendor menjadi 'nonaktif'
        $oldVendor = $vendor->toArray();
        $vendor->update(['status' => 'nonaktif']);
    
        // Log aktivitas (jika diperlukan)
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Vendor dinonaktifkan oleh admin dengan entri di vendor_deactivation_requests'
        );
    
        return response()->json([
            'message' => 'Vendor berhasil dinonaktifkan',
            'data'    => $deactivationRequest
        ], 200);
    }    


}
