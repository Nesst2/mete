<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use App\Models\Daerah;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WilayahController extends Controller
{
    // Menampilkan daftar wilayah
    public function index(Request $request)
{
    $kotaFilter = $request->get('kota'); // Parameter filter kota dari form
    $search     = $request->get('search'); // Parameter pencarian

    // Mulai query dasar
    $query = Wilayah::with('daerah');

    // Jika user adalah sales, batasi data pada kota sales
    if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;
        if ($salesKota) {
            $query->whereHas('daerah', function ($q) use ($salesKota) {
                $q->where('kota', 'LIKE', "%{$salesKota}%");
            });
        }
    }

    // Terapkan pencarian jika ada keyword search
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('nama', 'LIKE', "%{$search}%")
              ->orWhereHas('daerah', function ($q2) use ($search) {
                  $q2->where('kota', 'LIKE', "%{$search}%")
                     ->orWhere('provinsi', 'LIKE', "%{$search}%");
              });
        });
    }

    // Terapkan filter berdasarkan kota jika diisi
    if ($kotaFilter) {
        $query->whereHas('daerah', function ($q) use ($kotaFilter) {
            $q->where('kota', 'LIKE', "%{$kotaFilter}%");
        });
    }

    $wilayahs = $query->paginate(10); // Menampilkan 10 data per halaman

    // Ambil daftar kota untuk filter dropdown
    $kotaList = Wilayah::with('daerah')
        ->get()
        ->pluck('daerah.kota', 'daerah.kota')
        ->unique();

    return view('master_data.daerah.wilayah.index', compact('wilayahs', 'kotaList'));
}


    // Menampilkan form untuk membuat wilayah baru
    public function create()
{
    // Cek jika user adalah sales
    if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;
        // Sales hanya bisa menambah wilayah di kotanya sendiri
        $daerahs = Daerah::where('kota', $salesKota)->get();
    } else {
        // Admin bisa memilih semua kota
        $daerahs = Daerah::all();
    }

    return view('master_data.daerah.wilayah.create', compact('daerahs'));
}

    // Menyimpan data wilayah baru
    public function store(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'kota' => 'required|string|max:255',
    ]);

    // Cek jika user adalah sales
    if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;
        // Pastikan sales hanya bisa menambah wilayah di kotanya sendiri
        if ($request->kota !== $salesKota) {
            abort(403, 'Anda tidak memiliki akses untuk menambah wilayah di kota ini.');
        }
    }

    // Simpan data wilayah dan ambil data yang baru dibuat
    $wilayah = Wilayah::create([
        'nama' => $request->nama, // Nama wilayah
        'kota' => $request->kota, // Kota yang dipilih
    ]);

    // Simpan log aktivitas untuk aksi insert
    ActivityLog::log(
        'insert',
        'wilayahs',
        $wilayah->id,
        null,
        $wilayah->toArray(),
        'Wilayah berhasil dibuat.'
    );

    return redirect()->route('wilayah.index')->with('success', 'Wilayah berhasil dibuat.');
}

    // Menampilkan form untuk mengedit wilayah
    public function edit($id)
{
    $wilayah = Wilayah::with('daerah')->findOrFail($id);

    // Cek jika user adalah sales
    if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;
        // Cek apakah wilayah ini sesuai dengan kota sales
        if ($wilayah->daerah->kota !== $salesKota) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit wilayah ini.');
        }
    }

    $daerahs = Daerah::all(); // Ambil semua data daerah untuk dropdown
    return view('master_data.daerah.wilayah.edit', compact('wilayah', 'daerahs'));
}

    // Menyimpan perubahan data wilayah
    public function update(Request $request, $id)
{
    $wilayah = Wilayah::findOrFail($id);

    // Cek jika user adalah sales
    if (Auth::user()->role == 'sales') {
        $salesKota = optional(Auth::user()->daerah)->kota;
        // Pastikan sales hanya bisa edit wilayah sesuai kotanya
        if ($wilayah->daerah->kota !== $salesKota) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate wilayah ini.');
        }
        // Pastikan daerah_id tidak bisa diubah oleh sales
        $request->merge(['daerah_id' => $wilayah->daerah_id]);
    }

    $request->validate([
        'nama' => 'required|string|max:255',
        'daerah_id' => 'required|exists:daerah,id',
    ]);

    $oldData = $wilayah->toArray();

    $wilayah->update([
        'nama' => $request->nama,
        'daerah_id' => $request->daerah_id, // Update berdasarkan daerah_id
    ]);

    $newData = $wilayah->toArray();

    ActivityLog::log(
        'update',
        'wilayahs',
        $wilayah->id,
        $oldData,
        $newData,
        'Wilayah berhasil diperbarui.'
    );

    return redirect()->route('wilayah.index')->with('success', 'Wilayah berhasil diperbarui.');
}

    // Menghapus data wilayah
    public function destroy($id)
    {
        $wilayah = Wilayah::findOrFail($id);
        // Simpan data lama untuk log
        $oldData = $wilayah->toArray();

        $wilayah->delete();

        // Simpan log aktivitas untuk aksi delete
        ActivityLog::log(
            'delete',
            'wilayahs',
            $id,
            $oldData,
            null,
            'Wilayah berhasil dihapus.'
        );

        return redirect()->route('wilayah.index')->with('success', 'Wilayah berhasil dihapus.');
    }

    // Mengambil data wilayah berdasarkan kota
    public function show($kota)
    {
        // Mengambil data wilayah berdasarkan nama kota
        $wilayah = Wilayah::where('kota', $kota)->get();

        // Mengembalikan data wilayah dalam format JSON
        return response()->json($wilayah);
    }

    // Ambil wilayah berdasarkan kota
    public function getWilayahByKota($kota)
    {
        $wilayahs = Wilayah::where('kota', $kota)->get();
        return response()->json($wilayahs);
    }
}
