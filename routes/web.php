<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminKaryawanController;

// Rute Halaman Awal & Auth
Route::get('/', function () { return redirect('/login'); }); // Mengganti tampilan awal "Laravel"
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Group route yang mewajibkan user login
Route::middleware(['auth'])->group(function () {

    // 1. Rute Khusus Karyawan Kantor
    Route::middleware(['role:karyawan_kantor'])->group(function () {
        Route::get('/kantor/dashboard', function() { return view('kantor.dashboard'); })->name('kantor.dashboard');
        Route::post('/kantor/clock-in', [AttendanceController::class, 'clockInKantor'])->name('kantor.clockin');
        Route::post('/kantor/clock-out', [AttendanceController::class, 'clockOut'])->name('kantor.clockout');
    });

    // 2. Rute Khusus Supir
    Route::middleware(['role:supir'])->group(function () {
        Route::get('/supir/dashboard', function() { return view('supir.dashboard'); })->name('supir.dashboard');
        Route::post('/supir/clock-in', [AttendanceController::class, 'clockInSupir'])->name('supir.clockin');
        Route::post('/supir/clock-out', [AttendanceController::class, 'clockOut'])->name('supir.clockout');
    });

    // 3. Rute Khusus Admin/HR
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    
    // CRUD Data Karyawan
    Route::resource('karyawan', AdminKaryawanController::class)->except(['show']);
});

});

// 4. Rute Kiosk Karyawan Gudang (Bisa diakses tanpa login personal, standby di device gudang)
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk.index');
Route::post('/kiosk/absen', [KioskController::class, 'processAbsen'])->name('kiosk.process');