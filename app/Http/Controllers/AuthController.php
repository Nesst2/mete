<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
{
    // Validasi input
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string'
    ]);

    // Jika Auth berhasil
    if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
        $user = Auth::user();

        // Catat log aktivitas login
        ActivityLog::log(
            'login',
            'users',
            $user->id,
            null,
            ['username' => $user->username, 'role' => $user->role],
            'User berhasil login'
        );

        // Redirect berdasarkan role
        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('sales.dashboard');
    }

    // Jika login gagal, kembalikan input dan pesan error
    return back()->withInput()->with('login_error', 'Username atau password salah!');
}

    // Logout
    public function logout()
{
    $user = Auth::user();

    // Log aktivitas logout
    ActivityLog::log(
        'logout',
        'users',
        $user->id,
        ['username' => $user->username, 'role' => $user->role],
        null,
        'User berhasil logout'
    );

    Auth::logout();
    return redirect()->route('login');
}

}
