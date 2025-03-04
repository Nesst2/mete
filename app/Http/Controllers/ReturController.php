<?php

namespace App\Http\Controllers;

use App\Models\Retur;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    public function index()
    {
        $returs = Retur::with('vendor')->get();
        return view('retur.index', compact('returs'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        return view('retur.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'nominal_debet' => 'required|numeric',
            'jumlah_retur' => 'required|integer',
            'keterangan' => 'nullable|string',
        ]);

        Retur::create([
            'vendor_id' => $request->vendor_id,
            'nominal_debet' => $request->nominal_debet,
            'jumlah_retur' => $request->jumlah_retur,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('retur.index')->with('success', 'Retur berhasil ditambahkan!');
    }

    public function edit(Retur $retur)
    {
        $vendors = Vendor::all();
        return view('retur.edit', compact('retur', 'vendors'));
    }

    public function update(Request $request, Retur $retur)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'nominal_debet' => 'required|numeric',
            'jumlah_retur' => 'required|integer',
            'keterangan' => 'nullable|string',
        ]);

        $retur->update([
            'vendor_id' => $request->vendor_id,
            'nominal_debet' => $request->nominal_debet,
            'jumlah_retur' => $request->jumlah_retur,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('retur.index')->with('success', 'Retur berhasil diperbarui!');
    }

    public function destroy(Retur $retur)
    {
        $retur->delete();
        return redirect()->route('retur.index')->with('success', 'Retur berhasil dihapus!');
    }
}
