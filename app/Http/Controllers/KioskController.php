<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    // Halaman standby Kiosk
    public function index()
    {
        return view('kiosk.index'); // Tampilan layar utama kiosk
    }

    public function processAbsen(Request $request)
    {
        $request->validate(['pin_gudang' => 'required|string']);

        // Cari user berdasarkan PIN dan role gudang
        $user = User::where('pin_gudang', $request->pin_gudang)
                    ->where('role', 'karyawan_gudang')
                    ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'PIN tidak valid atau pengguna tidak ditemukan!'], 404);
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            // Belum absen masuk
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => now()->toTimeString(),
                'status' => 'hadir'
            ]);
            return response()->json([
                'status' => 'success', 
                'message' => "✅ Absen Masuk Berhasil. Selamat Bekerja, {$user->name}!"
            ]);
        } elseif (!$attendance->clock_out) {
            // Sudah absen masuk, saatnya absen keluar
            $attendance->update(['clock_out' => now()->toTimeString()]);
            return response()->json([
                'status' => 'success', 
                'message' => "✅ Absen Keluar Berhasil. Hati-hati di jalan, {$user->name}!"
            ]);
        }

        // Sudah absen masuk dan keluar
        return response()->json(['status' => 'error', 'message' => 'Anda sudah menyelesaikan absen (masuk & keluar) hari ini.'], 400);
    }
}