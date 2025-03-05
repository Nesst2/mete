<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\Vendor;
use App\Models\Daerah;
use App\Models\Retur;
use App\Models\Wilayah;
use App\Models\ActivityLog; // Pastikan ActivityLog di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\TagihanExport;
use Maatwebsite\Excel\Facades\Excel;

class TagihanController extends Controller
{

    public function index(Request $request)
{
    $currentDate = Carbon::now();
    $defaultMin = $currentDate->copy()->startOfMonth()->format('Y-m-d');
    $defaultMax = $currentDate->copy()->endOfMonth()->format('Y-m-d');

    $query = Vendor::query();

    // Pencarian Vendor (Berlaku untuk Admin dan Sales)
    if ($request->filled('search')) {
        $search = $request->input('search');
        $query->where('nama', 'LIKE', '%' . $search . '%');
    }

    // Filter berdasarkan peran
    if (Auth::user()->role == 'admin') {
        if ($request->filled('kota')) {
            $kota = $request->input('kota');
            $query->whereHas('wilayah.daerah', function ($q) use ($kota) {
                $q->where('kota', $kota);
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('status', $status);
        }
    } else if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;

        if ($salesKota) {
            $query->where('status', '!=', 'nonaktif')
                  ->whereHas('wilayah.daerah', function ($q) use ($salesKota) {
                      $q->where('kota', $salesKota);
                  });
        } else {
            $query->whereRaw('1=0'); // Jika sales tidak memiliki kota, tampilkan data kosong
        }
    }

    // Filter berdasarkan tanggal
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        // Pastikan kedua tanggal berada dalam bulan yang sama; jika tidak, gunakan default
        if (Carbon::parse($startDate)->format('Y-m') !== Carbon::parse($endDate)->format('Y-m')) {
            $startDate = $defaultMin;
            $endDate   = $defaultMax;
        }

        if ($startDate === $endDate) {
            // Jika tanggal awal dan akhir sama, cari data pada tanggal tersebut
            $query->whereHas('tagihan', function ($q) use ($startDate) {
                $q->whereDate('tanggal_masuk', $startDate);
            });

            // Eager load tagihan dengan filter tanggal
            $query->with(['tagihan' => function ($q) use ($startDate) {
                $q->whereDate('tanggal_masuk', $startDate);
            }]);
        } else {
            // Jika tanggal berbeda, cari data di antara tanggal tersebut
            $query->whereHas('tagihan', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            });

            // Eager load tagihan dengan filter tanggal
            $query->with(['tagihan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_masuk', [$startDate, $endDate]);
            }]);
        }
    } else {
        // Jika filter tanggal tidak diisi, tampilkan semua vendor dan eager load semua tagihan (jika diperlukan)
        $query->with('tagihan');
    }

    $vendors = $query->paginate(10)->appends($request->query());

    // Ambil daftar kota untuk filter (hanya untuk Admin)
    $kotaList = [];
    if (Auth::user()->role == 'admin') {
        $kotaList = Wilayah::with('daerah')->get()
                    ->pluck('daerah.kota')
                    ->unique();
    }

    return view('tagihan.index', compact('vendors', 'kotaList'));
}

public function create($vendor_id)
{
    $vendor = Vendor::findOrFail($vendor_id);
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;

    // Ambil hanya tagihan untuk bulan ini
    $tagihanSebelumnya = Tagihan::where('vendor_id', $vendor_id)
        ->whereYear('tanggal_masuk', $currentYear)
        ->whereMonth('tanggal_masuk', $currentMonth)
        ->orderBy('id', 'asc')
        ->select(['id', 'kunjungan_ke', 'status_kunjungan', 'uang_masuk', 'tanggal_masuk'])
        ->get();

    // Inisialisasi status form untuk 15 form (semua terkunci)
    $formStatus = [];
    for ($i = 1; $i <= 15; $i++) {
        $formStatus["form{$i}"] = 'locked';
    }

    // Otomatis buka form pertama jika data bulan ini kosong
    if ($tagihanSebelumnya->isEmpty()) {
        $formStatus['form1'] = 'open';
    } else {
        $nextForm = $tagihanSebelumnya->count() + 1;
        if ($nextForm <= 15) {
            $formStatus["form{$nextForm}"] = 'open';
        }
    }

    // Ambil Data Retur yang terkait dengan tagihan bulan ini
    $returData = [];
    foreach ($tagihanSebelumnya as $tagihan) {
        $retur = Retur::where('tagihan_id', $tagihan->id)->first();
        if ($retur) {
            $returData[$tagihan->id] = $retur;
        }
    }

    return view('tagihan.create', compact('vendor', 'tagihanSebelumnya', 'formStatus', 'returData'));
}



    public function store(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $vendor = Vendor::findOrFail($vendorId);

        // Konstanta nilai maksimal dan nilai retur per unit
        $maxNominal = 40000;
        $returValue = 2000;

        for ($i = 1; $i <= 15; $i++) {
            $statusKunjungan = $request->input("status_kunjungan{$i}");
            $nominalInput = $request->input("nominal{$i}", 0);

            // Cek apakah data tagihan untuk kunjungan_ke sudah ada
            $existingTagihan = Tagihan::where('vendor_id', $vendorId)
                ->where('kunjungan_ke', $i)
                ->first();

            $action = $existingTagihan ? 'update' : 'insert';

            if ($existingTagihan) {
                $nominal = $existingTagihan->uang_masuk;
            } else {
                $nominal = in_array($statusKunjungan, ['ada orang', 'tertunda']) ? $nominalInput : 0;
            }

            // Hitung jumlah retur jika status 'ada orang' atau 'tertunda'
            $jumlahRetur = 0;
            if (in_array($statusKunjungan, ['ada orang', 'tertunda']) && $nominal >= 0) {
                if ($nominal < $maxNominal) {
                    $jumlahRetur = floor(($maxNominal - $nominal) / $returValue);
                } else {
                    $jumlahRetur = 0;
                }
            }

            // Simpan data tagihan jika status kunjungan diisi
            if ($statusKunjungan) {
                $oldTagihanData = $existingTagihan ? $existingTagihan->toArray() : null;
                $tagihan = Tagihan::updateOrCreate(
                    [
                        'vendor_id'    => $vendorId,
                        'kunjungan_ke' => $i
                    ],
                    [
                        'status_kunjungan' => $statusKunjungan,
                        'uang_masuk'       => in_array($statusKunjungan, ['ada orang', 'tertunda']) ? $nominal : 0,
                        'retur'            => $jumlahRetur,
                        'tanggal_masuk'    => now(),
                        'daerah_id'        => $vendor->daerah_id,
                    ]
                );

                ActivityLog::log(
                    $action,
                    'tagihans',
                    $tagihan->id,
                    $oldTagihanData,
                    $tagihan->toArray(),
                    "Tagihan untuk kunjungan ke-$i berhasil dibuat atau diperbarui"
                );

                // Buat data retur jika diperlukan
                if ($jumlahRetur > 0) {
                    $retur = Retur::create([
                        'tagihan_id'    => $tagihan->id,
                        'vendor_id'     => $vendorId,
                        'nominal_debet' => $nominal,
                        'jumlah_retur'  => $jumlahRetur,
                        'keterangan'    => "Retur untuk kunjungan ke-$i"
                    ]);

                    ActivityLog::log(
                        'insert',
                        'returs',
                        $retur->id,
                        null,
                        $retur->toArray(),
                        "Retur untuk tagihan kunjungan ke-$i berhasil dibuat"
                    );
                }
            }
        }

        return redirect()->route('tagihan.index', ['vendor' => $vendorId]);
    }

    public function edit($id)
{
    $vendor = Vendor::findOrFail($id);
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;

    // Hanya ambil tagihan dari bulan ini untuk diedit
    $tagihanSebelumnya = Tagihan::where('vendor_id', $vendor->id)
        ->whereYear('tanggal_masuk', $currentYear)
        ->whereMonth('tanggal_masuk', $currentMonth)
        ->get();

    $returData = Retur::whereIn('tagihan_id', $tagihanSebelumnya->pluck('id'))
        ->get()
        ->keyBy('tagihan_id');

    return view('tagihan.edit', compact('vendor', 'tagihanSebelumnya', 'returData'));
}


    public function update(Request $request, $id)
    {
        // Cari vendor berdasarkan ID
        $vendor = Vendor::findOrFail($id);
        $oldVendor = $vendor->toArray();

        // Update nama vendor
        $vendor->update([
            'nama' => $request->input('nama'),
        ]);

        // Log aktivitas untuk update vendor
        ActivityLog::log(
            'update',
            'vendors',
            $vendor->id,
            $oldVendor,
            $vendor->toArray(),
            'Nama vendor berhasil diperbarui melalui Tagihan update'
        );

        // Loop untuk update masing-masing retur
        foreach ($request->input('retur') as $tagihanId => $jumlahRetur) {
            $retur = Retur::where('tagihan_id', $tagihanId)->first();
            if ($retur) {
                $oldRetur = $retur->toArray();
                $retur->update([
                    'jumlah_retur' => $jumlahRetur,
                ]);
                ActivityLog::log(
                    'update',
                    'returs',
                    $retur->id,
                    $oldRetur,
                    $retur->toArray(),
                    "Retur untuk tagihan ID $tagihanId berhasil diperbarui"
                );
            } else {
                if ($jumlahRetur > 0) {
                    $retur = Retur::create([
                        'tagihan_id'   => $tagihanId,
                        'jumlah_retur' => $jumlahRetur,
                    ]);
                    ActivityLog::log(
                        'insert',
                        'returs',
                        $retur->id,
                        null,
                        $retur->toArray(),
                        "Retur untuk tagihan ID $tagihanId berhasil dibuat"
                    );
                }
            }
        }

        return redirect()->route('tagihan.index')->with('success', 'Data retur berhasil diupdate!');
    }

    public function destroy($id)
    {
        $tagihan = Tagihan::findOrFail($id);
        $oldData = $tagihan->toArray();
        $tagihan->delete();

        ActivityLog::log(
            'delete',
            'tagihans',
            $tagihan->id,
            $oldData,
            null,
            'Tagihan berhasil dihapus'
        );

        return redirect()->route('tagihan.index')->with('success', 'Tagihan berhasil dihapus!');
    }

    public function show($id)
    {
        $tagihan = Tagihan::with('vendor', 'daerah')->findOrFail($id);
        $tagihan->tanggal_masuk = Carbon::parse($tagihan->tanggal_masuk);

        return view('tagihan.show', compact('tagihan'));
    }

    public function history(Request $request)
    {
        if (Auth::user()->role == 'admin') {
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->input('start_date');
                $endDate   = Carbon::parse($request->input('end_date'))->endOfDay();

                $tagihanQuery = Tagihan::with(['vendor', 'retur'])
                    ->whereBetween('tanggal_masuk', [$startDate, $endDate]);

                $year = $month = '';
            } elseif ($request->filled('month')) {
                [$year, $month] = explode('-', $request->input('month'));

                $tagihanQuery = Tagihan::with(['vendor', 'retur'])
                    ->whereYear('tanggal_masuk', $year)
                    ->whereMonth('tanggal_masuk', $month);
            } else {
                $year = now()->year;
                $month = now()->month;

                $tagihanQuery = Tagihan::with(['vendor', 'retur'])
                    ->whereYear('tanggal_masuk', $year)
                    ->whereMonth('tanggal_masuk', $month);
            }

            // Filter berdasarkan kota jika ada input 'kota'
            if ($request->filled('kota')) {
                $kota = $request->input('kota');
                $tagihanQuery->whereHas('vendor', function($q) use ($kota) {
                    $q->whereHas('wilayah.daerah', function($q2) use ($kota) {
                        $q2->where('kota', $kota);
                    });
                });
            }

            // Gantikan get() dengan paginate(10)
            $tagihan = $tagihanQuery->paginate(10);

            // Ambil daftar kota untuk filter dari model Wilayah
            $kotaList = Wilayah::with('daerah')->get()
                            ->pluck('daerah.kota')
                            ->unique();
        } else if (Auth::user()->role == 'sales') {
            $lastMonth = now()->subMonth();
            $year = $lastMonth->year;
            $month = $lastMonth->month;

            $tagihan = Tagihan::with(['vendor', 'retur'])
                ->whereYear('tanggal_masuk', $year)
                ->whereMonth('tanggal_masuk', $month)
                ->paginate(5);

            $kotaList = collect();
        }

        return view('tagihan.history', compact('tagihan', 'year', 'month', 'kotaList'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['month', 'kota']);
        $month   = $filters['month'] ?? now()->format('Y-m');
        $kota    = $filters['kota'] ?? 'all-cities';

        try {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
            $formattedMonth = $date->format('F_Y');
        } catch (\Exception $e) {
            $formattedMonth = now()->format('F_Y');
        }

        $fileName = 'tagihan_' . $formattedMonth . '_' . $kota . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TagihanMultipleSheetExport($filters), $fileName);
    }

    
}