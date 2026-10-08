<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminKaryawanController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AdminSettingController;
// Rute Halaman Awal & Auth
Route::get('/', function () { return redirect('/login'); }); // Mengganti tampilan awal "Laravel"
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Group route yang mewajibkan user login
Route::middleware(['auth'])->group(function () {

    // Pengajuan Izin (Bisa diakses oleh karyawan kantor, supir, dan gudang)
    Route::post('/izin/store', [LeaveController::class, 'store'])->name('izin.store');

    // 1. Rute Khusus Karyawan Kantor
    Route::middleware(['role:karyawan_kantor'])->group(function () {
        Route::get('/kantor/dashboard', function() { return view('kantor.dashboard'); })->name('kantor.dashboard');
        Route::post('/kantor/clock-in', [AttendanceController::class, 'clockInKantor'])->name('kantor.clockin');
        Route::post('/kantor/clock-out', [AttendanceController::class, 'clockOutKantor'])->name('kantor.clockout');
    });

    // 2. Rute Khusus Supir
    Route::middleware(['role:supir'])->group(function () {
        Route::get('/supir/dashboard', function() { return view('supir.dashboard'); })->name('supir.dashboard');
        Route::post('/supir/clock-in', [AttendanceController::class, 'clockInSupir'])->name('supir.clockin');
        Route::post('/supir/clock-out', [AttendanceController::class, 'clockOutSupir'])->name('supir.clockout');
    });

    // 3. Rute Khusus Karyawan Gudang
    Route::middleware(['role:karyawan_gudang'])->group(function () {
        Route::get('/gudang/dashboard', function() { return view('gudang.dashboard'); })->name('gudang.dashboard');
        Route::post('/gudang/clock-in', [AttendanceController::class, 'clockInGudang'])->name('gudang.clockin');
        Route::post('/gudang/clock-out', [AttendanceController::class, 'clockOutGudang'])->name('gudang.clockout');
    });

    // 4. Rute Khusus Admin/HR
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::resource('karyawan', AdminKaryawanController::class)->except(['show']);
        Route::get('/izin', [AdminController::class, 'approvalIzin'])->name('izin.index');
        Route::put('/izin/{id}', [AdminController::class, 'updateIzin'])->name('izin.update');
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan.index');
        Route::get('/laporan/export-excel', [AdminController::class, 'exportExcel'])->name('laporan.excel');
        Route::get('/laporan/export-pdf', [AdminController::class, 'exportPdf'])->name('laporan.pdf');
    });

});

// 4. Rute Kiosk Karyawan Gudang (Bisa diakses tanpa login personal, standby di device gudang)
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/absen', [KioskController::class, 'processAbsen'])->name('kiosk.process');