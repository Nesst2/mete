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
use Illuminate\Support\Facades\Auth;

// Redirect ke dashboard saat mengakses root URL
Route::get('/', function () {
    return redirect()->route('login');
});

// === 🟢 AUTHENTICATION ROUTES === //
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate(); // Hapus sesi
    request()->session()->regenerateToken(); // Regenerasi CSRF token
    return redirect('/login'); // Redirect ke halaman login
})->name('logout');

Route::get('/tagihan/history', [TagihanController::class, 'history'])->name('tagihan.history');
Route::get('/tagihan/export', [TagihanController::class, 'export'])->name('tagihan.export');
// === 🟢 RESOURCE ROUTES (CRUD) === //
Route::resources([
    'user' => UserController::class,
    'daerah' => DaerahController::class,
    'wilayah' => WilayahController::class,
    'vendor' => VendorController::class,
    'tagihan' => TagihanController::class,
    'retur' => ReturController::class,
]);

// === 🟢 DASHBOARD ROUTES === //
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', fn() => view('dashboard.admin'))
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/sales/dashboard', fn() => view('dashboard.sales'))
        ->middleware('role:sales')
        ->name('sales.dashboard');
});

// === 🟢 MASTER DATA ROUTES === //
Route::prefix('master_data')->group(function () {
    // Admin hanya bisa CRUD semua master data
    Route::middleware('role:admin')->group(function () {
        Route::resource('user', UserController::class);          // Admin bisa CRUD User
        Route::resource('daerah', DaerahController::class);      // Admin bisa CRUD Daerah
        // Route::resource('requests', RequestController::class);    // Admin bisa CRUD request
    });

    // Sales hanya bisa melihat daftar request & mengakses wilayah
    // Route::middleware('role:sales')->group(function () {
    //     Route::get('requests', [RequestController::class, 'index'])->name('request.index');  // Sales hanya bisa lihat request
    // });

    // Vendor & Vendor Non-Aktif bisa diakses oleh Sales & Admin
    Route::get('/vendor-nonaktif', [VendorController::class, 'vendorNonaktif'])->name('vendor_nonaktif.index');
});

// Wilayah bisa diakses oleh kedua role
Route::resource('wilayah', WilayahController::class);    // Admin bisa CRUD Wilayah
// === 🟢 TAGIHAN & RETUR (Admin & Sales) === //
Route::resource('tagihan', TagihanController::class);  // Admin & Sales
Route::resource('retur', ReturController::class);      // Admin & Sales

// === 🟢 LOG ACTIVITY (Admin Only) === //
Route::get('/log-activity', fn() => view('log_activity.index'))
    ->middleware('role:admin')
    ->name('log_activity.index');

// === 🟢 VENDOR ROUTES === //
Route::prefix('vendor')->group(function () {
    Route::get('{vendor}', [VendorController::class, 'show'])->name('vendor.show');
    Route::get('/get-vendor/{id}', [VendorController::class, 'getVendorData']);
});

// === 🟢 DAERAH ROUTES === //
Route::get('daerah/{id}/kota', [VendorController::class, 'getKotaByDaerah'])->name('daerah.kota');

// === 🟢 WILAYAH ROUTES === //
Route::prefix('wilayah')->group(function () {
    Route::get('{id}', [WilayahController::class, 'show'])->name('wilayah.show');
    Route::get('{id}/daerah', [WilayahController::class, 'getWilayahByDaerah'])->name('wilayah.byDaerah');
    Route::get('{kota}', [WilayahController::class, 'getWilayahByKota'])->name('wilayah.getByKota');
});


// Route::middleware('auth')->group(function () {
//     Route::post('/vendor/{id}/request-deactivation', [VendorDeactivationRequestController::class, 'store']);
//     Route::post('/deactivation-request/{id}/approve', [VendorDeactivationRequestController::class, 'approve']);
//     Route::post('/deactivation-request/{id}/reject', [VendorDeactivationRequestController::class, 'reject']);
//     Route::post('/deactivation-request/{id}/cancel', [VendorDeactivationRequestController::class, 'cancel']);
// });


use App\Http\Controllers\VendorDeactivationRequestController;

// Route untuk menampilkan daftar request penonaktifan vendor
Route::get('/request', [VendorDeactivationRequestController::class, 'index'])
    ->middleware('auth') // Hanya memastikan pengguna sudah login
    ->name('request.index');


// Route untuk request nonaktif oleh sales
Route::post('/vendor/{vendor_id}/request-deactivation', [VendorDeactivationRequestController::class, 'store'])
    ->middleware('auth', 'role:sales')
    ->name('request.store');

// Route untuk admin menyetujui request
Route::post('/request/{id}/approve', [VendorDeactivationRequestController::class, 'approve'])
    ->middleware('auth', 'role:admin')
    ->name('request.approve');

// Route untuk admin menolak request
Route::post('/request/{id}/reject', [VendorDeactivationRequestController::class, 'reject'])
    ->middleware('auth', 'role:admin')
    ->name('request.reject');

// Route untuk sales membatalkan request
Route::post('/request/{id}/cancel', [VendorDeactivationRequestController::class, 'cancel'])
    ->middleware('auth', 'role:sales')
    ->name('request.cancel');


// Tampilkan semua vendor pada halaman index tagihan
Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');

// Membuat tagihan untuk vendor tertentu
Route::get('/tagihan/create/{vendor_id}', [TagihanController::class, 'create'])->name('tagihan.create');
Route::post('/tagihan', [TagihanController::class, 'store'])->name('tagihan.store');

Route::get('/tagihan/{id}/edit', [TagihanController::class, 'edit'])->name('tagihan.edit');
Route::put('/tagihan/{id}', [TagihanController::class, 'update'])->name('tagihan.update');

Route::get('/tagihan/{vendor_id}/edit', [TagihanController::class, 'edit'])->name('tagihan.edit');
Route::post('/tagihan/{vendor_id}/update', [TagihanController::class, 'update'])->name('tagihan.update');

Route::put('/tagihan/{id}/update', [TagihanController::class, 'update'])->name('tagihan.update');


Route::post('/vendor/{vendor_id}/deactivate', [VendorController::class, 'deactivate'])
    ->middleware('auth', 'role:admin')
    ->name('vendor.deactivate');

Route::get('/vendor/search', [App\Http\Controllers\VendorController::class, 'search'])->name('vendor.search');

Route::get('/test-dd', function() {
    dd('Test dd berhasil');
});

use App\Http\Controllers\ActivityLogController;

Route::get('/log-activity', [ActivityLogController::class, 'index'])->name('log_activity.index');

Route::post('/vendor/{vendor_id}/deactivate', [VendorDeactivationRequestController::class, 'store'])->name('vendor.deactivate');
Route::post('/vendor/{vendor_id}/request-deactivation', [VendorDeactivationRequestController::class, 'store'])->name('vendor.request-deactivation');

Route::post('/vendor/{id}/activate', [VendorController::class, 'activate'])
    ->name('vendor.activate')
    ->middleware('auth');

    use App\Http\Controllers\DashboardController;

Route::middleware(['auth'])->group(function () {
    // Dashboard untuk Admin
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard')
        ->middleware('role:admin');

    // Dashboard untuk Sales
    Route::get('/sales/dashboard', [DashboardController::class, 'index'])
        ->name('sales.dashboard')
        ->middleware('role:sales');
});
