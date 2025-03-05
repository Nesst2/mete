<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DaerahController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VendorDeactivationRequestController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;

// Redirect ke halaman login saat mengakses root URL
Route::get('/', function () {
    return redirect()->route('login');
});

// === 🟢 AUTHENTICATION ROUTES === //
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// === 🟢 RESOURCE ROUTES (CRUD) === //
Route::resources([
    'user'    => UserController::class,
    'daerah'  => DaerahController::class,
    'wilayah' => WilayahController::class,  // Hanya satu definisi resource wilayah
    'vendor'  => VendorController::class,
    //'tagihan' => TagihanController::class,
    'retur'   => ReturController::class,
]);

// === 🟢 CUSTOM ROUTES UNTUK WILAYAH === //
// Hanya definisikan route custom yang tidak duplikasi dengan resource routes.
// Misalnya, jika Anda membutuhkan route tambahan selain show, buatlah route dengan nama berbeda.
Route::prefix('wilayah')->group(function () {
    // Route show diabaikan karena sudah dibuat oleh Route::resource('wilayah', ...)
    Route::get('{id}/daerah', [WilayahController::class, 'getWilayahByDaerah'])
        ->name('wilayah.byDaerah');
    // Ubah parameter agar tidak bentrok, misalnya menggunakan prefix 'kota'
    Route::get('kota/{kota}', [WilayahController::class, 'getWilayahByKota'])
        ->name('wilayah.getByKota');
});

// === 🟢 DASHBOARD ROUTES === //
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/sales/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:sales')
        ->name('sales.dashboard');
});

// === 🟢 MASTER DATA ROUTES === //
Route::prefix('master_data')->name('master_data.')->group(function () {
    Route::middleware('role:admin')->group(function () {
        Route::resource('user', UserController::class);
        Route::resource('daerah', DaerahController::class);
    });
    Route::get('/vendor-nonaktif', [VendorController::class, 'vendorNonaktif'])
         ->name('vendor_nonaktif.index');
});

// === 🟢 VENDOR ROUTES === //
Route::prefix('vendor')->group(function () {
    Route::get('{vendor}', [VendorController::class, 'show'])->name('vendor.show');
    Route::get('/get-vendor/{id}', [VendorController::class, 'getVendorData']);
});

// === 🟢 DAERAH ROUTES === //
Route::get('daerah/{id}/kota', [VendorController::class, 'getKotaByDaerah'])
    ->name('daerah.kota');

// === 🟢 VENDOR DEACTIVATION REQUEST ROUTES === //
Route::get('/request', [VendorDeactivationRequestController::class, 'index'])
    ->middleware('auth')
    ->name('request.index');

Route::post('/vendor/{vendor_id}/request-deactivation', [VendorDeactivationRequestController::class, 'store'])
    ->middleware('auth', 'role:sales')
    ->name('request.store');

Route::post('/request/{id}/approve', [VendorDeactivationRequestController::class, 'approve'])
    ->middleware('auth', 'role:admin')
    ->name('request.approve');

Route::post('/request/{id}/reject', [VendorDeactivationRequestController::class, 'reject'])
    ->middleware('auth', 'role:admin')
    ->name('request.reject');

Route::post('/request/{id}/cancel', [VendorDeactivationRequestController::class, 'cancel'])
    ->middleware('auth', 'role:sales')
    ->name('request.cancel');

// === 🟢 TAGIHAN CUSTOM ROUTES === //
Route::get('/tagihan/history', [TagihanController::class, 'history'])
    ->name('tagihan.history');

Route::get('/tagihan/export', [TagihanController::class, 'export'])
    ->name('tagihan.export');

    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');

    // Membuat tagihan untuk vendor tertentu
    Route::get('/tagihan/create/{vendor_id}', [TagihanController::class, 'create'])->name('tagihan.create');
    Route::post('/tagihan', [TagihanController::class, 'store'])->name('tagihan.store');
    
    Route::get('/tagihan/{id}/edit', [TagihanController::class, 'edit'])->name('tagihan.edit');
    Route::put('/tagihan/{id}', [TagihanController::class, 'update'])->name('tagihan.update');
    
    Route::get('/tagihan/{vendor_id}/edit', [TagihanController::class, 'edit'])->name('tagihan.edit');
    Route::post('/tagihan/{vendor_id}/update', [TagihanController::class, 'update'])->name('tagihan.update');
    
    Route::put('/tagihan/{id}/update', [TagihanController::class, 'update'])->name('tagihan.update');

// === 🟢 VENDOR ACTIVATION/DEACTIVATION ROUTES === //
Route::post('/vendor/{vendor_id}/deactivate', [VendorController::class, 'deactivate'])
    ->middleware('auth', 'role:admin')
    ->name('vendor.deactivate');

Route::get('/vendor/search', [VendorController::class, 'search'])
    ->name('vendor.search');

Route::post('/vendor/{id}/activate', [VendorController::class, 'activate'])
    ->middleware('auth')
    ->name('vendor.activate');

Route::get('/test-dd', function () {
    dd('Test dd berhasil');
});

Route::get('/log-activity', [ActivityLogController::class, 'index'])
    ->name('log_activity.index');
