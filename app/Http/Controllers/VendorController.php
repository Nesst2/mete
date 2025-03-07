<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Daerah;
use App\Models\Wilayah;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    // Menampilkan daftar vendor
    public function index(Request $request)
{
    $status  = $request->get('status');
    $search  = $request->get('search');

    if (Auth::user()->role == 'admin') {
        // Ambil parameter filter khusus admin
        $kota    = $request->get('kota');
        $wilayah = $request->get('wilayah');

        $vendors = Vendor::with(['wilayah'])
            ->when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })
            // Filter berdasarkan kota, jika diisi
            ->when($kota, function ($query) use ($kota) {
                return $query->whereHas('wilayah', function ($query) use ($kota) {
                    $query->whereHas('daerah', function ($subQuery) use ($kota) {
                        $subQuery->where('kota', $kota);
                    });
                });
            })
            // Filter berdasarkan wilayah hanya jika kota sudah dipilih
            ->when($wilayah, function ($query) use ($wilayah, $kota) {
                if ($kota) {
                    return $query->whereHas('wilayah', function ($query) use ($wilayah, $kota) {
                        $query->where('nama', $wilayah)
                              ->whereHas('daerah', function ($subQuery) use ($kota) {
                                  $subQuery->where('kota', $kota);
                              });
                    });
                }
                return $query;
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_vendor', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%")
                      ->orWhere('keterangan', 'LIKE', "%{$search}%")
                      ->orWhere('jam_operasional', 'LIKE', "%{$search}%")
                      ->orWhere('nomor_hp', 'LIKE', "%{$search}%");
                });
            })
            ->paginate(10);
        $vendors->appends($request->query());

        // Siapkan data dropdown untuk admin
        // Dropdown kota: ambil semua kota dari relasi wilayah -> daerah
        $kotaList = Wilayah::with('daerah')
            ->get()
            ->pluck('daerah.kota', 'daerah.kota')
            ->unique();

        // Dropdown wilayah: jika kota dipilih, tampilkan wilayah sesuai kota tersebut, jika tidak tampilkan semua wilayah
        if ($kota) {
            $wilayahList = Wilayah::whereHas('daerah', function ($q) use ($kota) {
                $q->where('kota', $kota);
            })->pluck('nama')->unique();
        } else {
            $wilayahList = Wilayah::pluck('nama')->unique();
        }
    } else {
        // Untuk sales: data kota didapat dari profil, sehingga hanya filter wilayah (yang otomatis terikat dengan kota sales)
        $wilayah = $request->get('wilayah');
        $salesKota = optional(Auth::user()->daerah)->kota;

        if ($salesKota) {
            $vendors = Vendor::with(['wilayah'])
                ->whereHas('wilayah', function ($query) use ($salesKota) {
                    $query->whereHas('daerah', function ($subQuery) use ($salesKota) {
                        $subQuery->where('kota', $salesKota);
                    });
                })
                ->when($wilayah, function ($query) use ($wilayah, $salesKota) {
                    return $query->whereHas('wilayah', function ($query) use ($wilayah, $salesKota) {
                        $query->where('nama', $wilayah)
                              ->whereHas('daerah', function ($q) use ($salesKota) {
                                  $q->where('kota', $salesKota);
                              });
                    });
                })
                ->when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('kode_vendor', 'LIKE', "%{$search}%")
                          ->orWhere('nama', 'LIKE', "%{$search}%")
                          ->orWhere('keterangan', 'LIKE', "%{$search}%")
                          ->orWhere('jam_operasional', 'LIKE', "%{$search}%")
                          ->orWhere('nomor_hp', 'LIKE', "%{$search}%");
                    });
                })
                ->paginate(10);
            $vendors->appends($request->query());

            // Untuk sales, dropdown wilayah hanya berdasarkan kota sales
            $wilayahList = Wilayah::whereHas('daerah', function ($q) use ($salesKota) {
                $q->where('kota', $salesKota);
            })->pluck('nama')->unique();
            // Tidak ada dropdown kota untuk sales
            $kotaList = collect();
        } else {
            $vendors = collect();
            $wilayahList = collect();
            $kotaList = collect();
        }
    }

    return view('master_data.vendor.index', compact('vendors', 'kotaList', 'wilayahList'));
}

    // Menampilkan form untuk membuat vendor baru
    public function create()
    {
        // Ambil semua daerah (kota)
        $daerahs = Daerah::all();  
        // Ambil semua wilayah untuk dropdown
        $wilayahs = Wilayah::all(); 

        return view('master_data.vendor.create', compact('daerahs', 'wilayahs'));
    }

    // Menyimpan vendor baru
    public function store(Request $request)
    {
        $request->validate([
            'nama'             => 'required|string|max:255',
            'keterangan'       => 'nullable|string',
            'jam_operasional'  => 'required|string|max:50',
            'nomor_hp'         => 'required|string|max:20|unique:vendors,nomor_hp',
            'location_link'    => 'nullable|url',
            'gambar_vendor'    => 'nullable|image',
            'wilayah_id'       => 'required|exists:wilayah,id',
        ]);

        // Ambil data wilayah untuk kode vendor
        $wilayah = Wilayah::findOrFail($request->wilayah_id);
        $kode_huruf = strtoupper(substr($wilayah->kota, 0, 1));
        $prefix = 'V' . $kode_huruf;

        // Cari vendor terakhir dengan kode yang dimulai dengan prefix tersebut (tanpa filter wilayah)
        $lastVendor = Vendor::where('kode_vendor', 'like', $prefix . '%')
            ->orderBy('kode_vendor', 'desc')
            ->first();

        $lastNumber = $lastVendor ? (int) substr($lastVendor->kode_vendor, strlen($prefix)) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        $kode_vendor = $prefix . $newNumber;


        // Simpan vendor baru
        $vendor = new Vendor();
        $vendor->kode_vendor    = $kode_vendor;
        $vendor->nama           = $request->nama;
        $vendor->keterangan     = $request->keterangan;
        $vendor->jam_operasional= $request->jam_operasional;
        $vendor->nomor_hp       = $request->nomor_hp;
        $vendor->location_link  = $request->location_link;
        if ($request->hasFile('gambar_vendor')) {
            $vendor->gambar_vendor = $request->gambar_vendor->store('vendors', 'public');
        }
        $vendor->status         = 'aktif'; // default status
        $vendor->wilayah_id     = $request->wilayah_id;
        $vendor->save();

        // Log aktivitas: Insert vendor baru
        ActivityLog::log(
            'insert',
            'vendors',
            $vendor->id,
            null,
            $vendor->toArray(),
            'Vendor berhasil ditambahkan'
        );

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil ditambahkan');
    }


    // Menampilkan form untuk mengedit vendor
    public function edit(Vendor $vendor)
    {
        // Ambil semua data daerah (kota)
        $daerahs = Daerah::all();
        // Ambil wilayah berdasarkan kota vendor (sesuaikan jika ada relasi yang tepat)
        $wilayahs = Wilayah::where('kota', $vendor->kota)->get(); 

        return view('master_data.vendor.edit', compact('vendor', 'daerahs', 'wilayahs'));
    }

    // Mengupdate data vendor

public function update(Request $request, $id)
{
    $request->validate([
        'nama'            => 'required|string|max:255',
        'jam_operasional' => 'required|string|max:50',
        'nomor_hp'        => 'required|string|max:20|unique:vendors,nomor_hp,' . $id,
        'kota'            => 'required|string|max:255',
        'gambar_vendor'   => 'nullable|image', // Validasi gambar
    ]);

    $vendor = Vendor::findOrFail($id);
    $oldVendor = $vendor->toArray(); // Data lama untuk log

    // Jika ada gambar baru yang diunggah
    if ($request->hasFile('gambar_vendor')) {
        // Hapus gambar lama jika ada
        if ($vendor->gambar_vendor) {
            Storage::disk('public')->delete($vendor->gambar_vendor);
        }

        // Simpan gambar baru
        $newImagePath = $request->file('gambar_vendor')->store('vendors', 'public');
        $vendor->gambar_vendor = $newImagePath;
    }

    // Update data lainnya
    $vendor->update([
        'nama'            => $request->nama,
        'keterangan'      => $request->keterangan,
        'jam_operasional' => $request->jam_operasional,
        'nomor_hp'        => $request->nomor_hp,
        'location_link'   => $request->location_link,
        'kota'            => $request->kota,
        'wilayah_id'      => $vendor->wilayah_id,
        'status'          => $request->status ?? $vendor->status,
    ]);

    // Log aktivitas: Update vendor
    ActivityLog::log(
        'update',
        'vendors',
        $vendor->id,
        $oldVendor,
        $vendor->toArray(),
        'Vendor berhasil diperbarui'
    );

    return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diperbarui!');
}


    // Menghapus vendor
    public function destroy(Vendor $vendor)
    {
        $oldVendor = $vendor->toArray(); // Simpan data lama untuk log

        // Menghapus gambar vendor jika ada
        if ($vendor->gambar_vendor) {
            unlink(storage_path('app/public/' . $vendor->gambar_vendor));
        }

        $vendor->delete();

        // Log aktivitas: Delete vendor
        ActivityLog::log(
            'delete',
            'vendors',
            $vendor->id,
            $oldVendor,
            null,
            'Vendor berhasil dihapus'
        );

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus');
    }

    // Mengambil data vendor beserta wilayahnya (untuk keperluan AJAX)
    public function getVendorData($id)
    {
        $vendor = Vendor::with('wilayah')->find($id);
        if ($vendor) {
            return response()->json([
                'wilayah' => [
                    'nama' => $vendor->wilayah->nama,
                    'kota' => $vendor->wilayah->kota
                ]
            ]);
        }
        return response()->json(null);
    }

    // Menampilkan detail vendor
    public function show($id)
    {
        $vendor = Vendor::with(['wilayah', 'wilayah.daerah', 'deactivationRequests'])->find($id);
        if (!$vendor) {
            return redirect()->route('vendor.index')->with('error', 'Vendor tidak ditemukan!');
        }
        return view('master_data.vendor.show', compact('vendor'));
    }


    // Menonaktifkan vendor
    public function deactivate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $vendor = Vendor::findOrFail($id);

        if ($vendor->status == 'nonaktif') {
            return response()->json(['message' => 'Vendor sudah nonaktif'], 400);
        }

        $oldVendor = $vendor->toArray(); // Simpan data lama untuk log

        $vendor->update(['status' => 'nonaktif']);

        // Log aktivitas: Update status vendor menjadi nonaktif
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Vendor berhasil dinonaktifkan'
        );

        return response()->json(['message' => 'Vendor berhasil dinonaktifkan'], 200);
    }

    public function activate(Request $request, $id)
{
    // Pastikan hanya admin yang dapat mengakses fitur ini
    if (Auth::user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $vendor = Vendor::findOrFail($id);

    // Hanya aktifkan jika vendor sedang nonaktif
    if ($vendor->status !== 'nonaktif') {
        return response()->json(['message' => 'Vendor tidak dalam status nonaktif'], 400);
    }

    $oldVendor = $vendor->toArray();

    // Update status vendor menjadi 'aktif'
    $vendor->update(['status' => 'aktif']);

    // Log aktivitas aktivasi vendor (opsional)
    ActivityLog::log(
        'update',
        'vendors',
        $vendor->id,
        $oldVendor,
        $vendor->toArray(),
        'Vendor diaktifkan kembali oleh admin'
    );

    return response()->json(['message' => 'Vendor berhasil diaktifkan'], 200);
}


    // Fitur live search untuk vendor
    public function search(Request $request)
    {
        $search = $request->get('search');

        $vendors = Vendor::with('wilayah')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_vendor', 'LIKE', "%{$search}%")
                      ->orWhere('nama', 'LIKE', "%{$search}%")
                      ->orWhere('keterangan', 'LIKE', "%{$search}%")
                      ->orWhere('jam_operasional', 'LIKE', "%{$search}%")
                      ->orWhere('nomor_hp', 'LIKE', "%{$search}%");
                });
            })
            ->get();

        return response()->json(['vendors' => $vendors]);
    }
}
