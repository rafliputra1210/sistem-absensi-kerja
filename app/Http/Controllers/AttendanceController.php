<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    /**
     * Proses Absen Masuk untuk Karyawan Kantor
     */
    public function clockInKantor(Request $request)
    {
        $user = Auth::user();
        $clientIp = $request->ip();
        $setting = OfficeSetting::first();

        // Validasi IP Address Wi-Fi Kantor
        if ($setting && $clientIp !== $setting->office_ip) {
            return back()->with('error', 'Anda harus terhubung ke Wi-Fi kantor untuk melakukan absen!');
        }

        // Cek apakah sudah absen hari ini
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }

        // Simpan Absen
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now()->toTimeString(),
            'ip_address_in' => $clientIp,
            'status' => now()->format('H:i') > '08:00' ? 'telat' : 'hadir' // Misal batas telat jam 08:00
        ]);

        return back()->with('success', 'Absen Masuk (Kantor) Berhasil!');
    }

    /**
     * Proses Absen Masuk untuk Supir (GPS & Foto)
     */
    public function clockInSupir(Request $request)
    {
        $request->validate([
            'lat_in' => 'required|numeric',
            'lng_in' => 'required|numeric',
            'photo_in' => 'required|image|max:2048' // Max 2MB
        ]);

        $user = Auth::user();
        $today = now()->toDateString();

        if (Attendance::where('user_id', $user->id)->where('date', $today)->first()) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }

        // Simpan foto selfie
        $photoPath = $request->file('photo_in')->store('supir_selfies', 'public');

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now()->toTimeString(),
            'lat_in' => $request->lat_in,
            'lng_in' => $request->lng_in,
            'photo_in' => $photoPath,
            'status' => 'hadir'
        ]);

        return back()->with('success', 'Absen Masuk (Supir) Berhasil!');
    }

    /**
     * Proses Absen Keluar (Bisa digunakan bersama untuk Kantor & Supir)
     */
    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum melakukan absen masuk.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan absen keluar hari ini.');
        }

        // Update data absen keluar
        $updateData = ['clock_out' => now()->toTimeString()];

        // Jika user adalah supir, simpan titik akhir (opsional jika dikirim dari frontend)
        if ($user->role === 'supir' && $request->has('lat_out') && $request->has('lng_out')) {
            $updateData['lat_out'] = $request->lat_out;
            $updateData['lng_out'] = $request->lng_out;
        }

        $attendance->update($updateData);

        return back()->with('success', 'Absen Keluar Berhasil!');
    }
}