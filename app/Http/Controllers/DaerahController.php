<?php

namespace App\Http\Controllers;

use App\Models\Daerah;
use App\Models\Wilayah;
use App\Models\ActivityLog; // Import model ActivityLog
use Illuminate\Http\Request;

class DaerahController extends Controller
{
    // Menampilkan daftar daerah
    public function index()
    {
        // Mengambil semua data daerah
        $daerahs = Daerah::all();
        return view('master_data.daerah.index', compact('daerahs'));
    }

    // Menampilkan form untuk membuat daerah baru
    public function create()
    {
        return view('master_data.daerah.create');
    }

    // Menyimpan data daerah baru
    public function store(Request $request)
    {
        // Validasi dan simpan data daerah
        $request->validate([
            'kota' => 'required|string|max:255', // Hanya kolom kota yang diperlukan sekarang
            'provinsi' => 'required|string|max:255', // Kolom provinsi masih diperlukan
        ]);

        // Simpan data daerah yang baru
        $daerah = Daerah::create([
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
        ]);

        // Log aktivitas: Insert daerah baru
        ActivityLog::log(
            'insert',
            'daerahs',
            $daerah->id,
            null,
            $daerah->toArray(),
            'Daerah berhasil ditambahkan.'
        );

        return redirect()->route('daerah.index')->with('success', 'Daerah berhasil ditambahkan.');
    }

    // Menampilkan form untuk mengedit daerah
    public function edit(Daerah $daerah)
    {
        return view('master_data.daerah.edit', compact('daerah'));
    }

    // Mengupdate data daerah
    public function update(Request $request, Daerah $daerah)
    {
        // Validasi dan update data daerah
        $request->validate([
            'kota' => 'required|string|max:255',
            'provinsi' => 'required|string|max:255',
        ]);

        $oldData = $daerah->toArray(); // Simpan data lama untuk log

        $daerah->update([
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
        ]);

        // Log aktivitas: Update daerah
        ActivityLog::log(
            'update',
            'daerahs',
            $daerah->id,
            $oldData,
            $daerah->toArray(),
            'Daerah berhasil diupdate.'
        );

        return redirect()->route('daerah.index')->with('success', 'Daerah berhasil diupdate.');
    }

    // Menghapus data daerah
    public function destroy(Daerah $daerah)
    {
        $oldData = $daerah->toArray(); // Simpan data lama untuk log

        // Hapus daerah
        $daerah->delete();

        // Log aktivitas: Delete daerah
        ActivityLog::log(
            'delete',
            'daerahs',
            $daerah->id,
            $oldData,
            null,
            'Daerah berhasil dihapus.'
        );

        return redirect()->route('daerah.index')->with('success', 'Daerah berhasil dihapus.');
    }

    public function show($id)
    {
        // Ambil data wilayah yang terkait dengan daerah_id (di sini diasumsikan $id merupakan nilai kota)
        $wilayahs = Wilayah::where('kota', $id)->get();

        // Kembalikan data wilayah dalam format JSON
        return response()->json($wilayahs);
    }
}
