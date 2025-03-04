<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Daerah;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Menampilkan daftar pengguna
    public function index()
    {
        $users = User::all();
        return view('master_data.user.index', compact('users'));
    }

    // Menampilkan form untuk membuat user baru
    public function create()
    {
        $daerahs = Daerah::all();
        return view('master_data.user.create', compact('daerahs'));
    }

    // Menyimpan data user baru
    public function store(Request $request)
    {
        // Validasi data input
        $validatedData = $request->validate([
            'nama'          => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'nomor_hp'      => 'required|unique:users,nomor_hp',
            'role'          => 'required|in:admin,sales',
            'username'      => 'required|string|max:100|unique:users',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6|confirmed',
            'daerah_id'     => 'nullable|exists:daerah,id',
        ]);

        // Proses penyimpanan data user
        $user = new User();
        $user->nama = $request->nama;
        $user->tanggal_lahir = $request->tanggal_lahir;
        $user->nomor_hp = $request->nomor_hp;
        $user->role = $request->role;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);

        // Jika role adalah 'sales', simpan daerah_id
        if ($request->role === 'sales') {
            $user->daerah_id = $request->daerah_id;
        }

        $user->save();

        // Log aktivitas: Insert user baru
        ActivityLog::log(
            'insert',
            'users',
            $user->id,
            null,
            $user->toArray(),
            'User berhasil ditambahkan'
        );

        return redirect()->route('user.index')->with('success', 'User berhasil ditambahkan');
    }

    // Menampilkan form untuk mengedit user
    public function edit(User $user)
    {
        $daerahs = Daerah::all();
        return view('master_data.user.edit', compact('user', 'daerahs'));
    }

    // Mengupdate data user
    public function update(Request $request, User $user)
    {
        // Validasi input
        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'nomor_hp'      => 'required|string|max:20|unique:users,nomor_hp,' . $user->id,
            'role'          => 'required|in:admin,sales',
            'username'      => 'required|string|max:100|unique:users,username,' . $user->id,
            'password'      => 'nullable|string|min:8|confirmed',
            'daerah_id'     => 'nullable|exists:daerah,id',
        ]);

        $oldData = $user->toArray();

        // Update data user
        $user->update([
            'nama'      => $request->nama,
            'email'     => $request->email,
            'nomor_hp'  => $request->nomor_hp,
            'role'      => $request->role,
            'username'  => $request->username,
            'password'  => $request->password ? bcrypt($request->password) : $user->password,
        ]);

        // Jika role adalah 'sales', update daerah_id
        if ($request->role === 'sales') {
            $user->daerah_id = $request->daerah_id;
            $user->save();
        }

        // Log aktivitas: Update user
        ActivityLog::log(
            'update',
            'users',
            $user->id,
            $oldData,
            $user->toArray(),
            'User berhasil diperbarui'
        );

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui');
    }

    // Menghapus user
    public function destroy(User $user)
    {
        $oldData = $user->toArray();
        $user->delete();

        // Log aktivitas: Delete user
        ActivityLog::log(
            'delete',
            'users',
            $user->id,
            $oldData,
            null,
            'User berhasil dihapus'
        );

        return redirect()->route('user.index')->with('success', 'User berhasil dihapus');
    }
}
