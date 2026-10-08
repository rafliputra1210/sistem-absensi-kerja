<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


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

        // Simpan Foto Selfie jika ada
        $photoPath = null;
        if ($request->filled('photo_base64')) {
            $image = $request->photo_base64;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'kantor_selfies/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, base64_decode($image));
            $photoPath = $imageName;
        }

        // Simpan Absen
        $batasTelat = $setting && $setting->start_time ? substr($setting->start_time, 0, 5) : '08:00';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now()->toTimeString(),
            'photo_in' => $photoPath,
            'ip_address_in' => $clientIp,
            'status' => now()->format('H:i') > $batasTelat ? 'telat' : 'hadir'
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
            'photo_in' => 'nullable|image|max:5120', // Maks 5MB untuk foto HP
            'photo_base64' => 'nullable|string',
        ]);

        if (!$request->hasFile('photo_in') && !$request->filled('photo_base64')) {
            return back()->with('error', 'Foto bukti absensi wajib diambil.');
        }

        $user = Auth::user();
        $today = now()->toDateString();

        if (Attendance::where('user_id', $user->id)->where('date', $today)->exists()) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }

        $photoPath = null;
        if ($request->hasFile('photo_in')) {
            $photoPath = $request->file('photo_in')->store('absensi_supir', 'public');
        } elseif ($request->filled('photo_base64')) {
            $image = $request->photo_base64;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_supir/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, base64_decode($image));
            $photoPath = $imageName;
        }

        $setting = OfficeSetting::first();
        $batasTelat = $setting && $setting->start_time ? substr($setting->start_time, 0, 5) : '08:00';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now()->toTimeString(),
            'lat_in' => $request->lat_in,
            'lng_in' => $request->lng_in,
            'photo_in' => $photoPath,
            'status' => now()->format('H:i') > $batasTelat ? 'telat' : 'hadir'
        ]);

        return back()->with('success', 'Absen Masuk berhasil! Hati-hati di jalan.');
    }

    /**
     * Proses Absen Keluar & Laporan Tugas Supir
     */
    public function clockOutSupir(Request $request)
    {
        $request->validate([
            'lat_out' => 'required|numeric',
            'lng_out' => 'required|numeric',
            'photo_out' => 'nullable|image|max:5120',
            'photo_base64' => 'nullable|string',
            'notes' => 'nullable|string' // Logbook Laporan Tugas
        ]);

        if (!$request->hasFile('photo_out') && !$request->filled('photo_base64')) {
            return back()->with('error', 'Foto bukti absensi keluar wajib diambil.');
        }

        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }
        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah menyelesaikan absen hari ini.');
        }

        $photoPath = null;
        if ($request->hasFile('photo_out')) {
            $photoPath = $request->file('photo_out')->store('absensi_supir', 'public');
        } elseif ($request->filled('photo_base64')) {
            $image = $request->photo_base64;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_supir/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, base64_decode($image));
            $photoPath = $imageName;
        }

        $attendance->update([
            'clock_out' => now()->toTimeString(),
            'lat_out' => $request->lat_out,
            'lng_out' => $request->lng_out,
            'photo_out' => $photoPath,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Absen Keluar dan Laporan Tugas berhasil disimpan. Selamat istirahat!');
    }

    /**
     * Proses Absen Keluar untuk Karyawan Kantor
     */
    public function clockOutKantor(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }
        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah melakukan absen keluar hari ini.');
        }

        $updateData = array_merge(
            ['clock_out' => now()->toTimeString()],
            $this->hitungLembur($request, $today)
        );

        $attendance->update($updateData);

        return back()->with('success', 'Absen Keluar (Kantor) Berhasil!');
    }

    /**
     * Fallback clockOut untuk rute umum
     */
    public function clockOut(Request $request)
    {
        if (Auth::user()->role === 'supir') {
            return $this->clockOutSupir($request);
        }
        return $this->clockOutKantor($request);
    }
    public function clockInGudang(Request $request)
    {
        $request->validate([
            'lat_in' => 'required|numeric',
            'lng_in' => 'required|numeric',
            'photo_in' => 'nullable|image|max:5120', // Foto wajah/sekitar gudang
            'photo_base64' => 'nullable|string',
        ]);

        if (!$request->hasFile('photo_in') && !$request->filled('photo_base64')) {
            return back()->with('error', 'Foto bukti absensi masuk gudang wajib diambil.');
        }

        $user = Auth::user();
        $today = now()->toDateString();

        if (Attendance::where('user_id', $user->id)->where('date', $today)->exists()) {
            return back()->with('error', 'Anda sudah melakukan absen masuk hari ini.');
        }

        $photoPath = null;
        if ($request->hasFile('photo_in')) {
            $photoPath = $request->file('photo_in')->store('absensi_gudang', 'public');
        } elseif ($request->filled('photo_base64')) {
            $image = $request->photo_base64;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_gudang/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, base64_decode($image));
            $photoPath = $imageName;
        }

        $setting = OfficeSetting::first();
        $batasTelat = $setting && $setting->start_time ? substr($setting->start_time, 0, 5) : '08:00';

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => now()->toTimeString(),
            'lat_in' => $request->lat_in,
            'lng_in' => $request->lng_in,
            'photo_in' => $photoPath,
            'status' => now()->format('H:i') > $batasTelat ? 'telat' : 'hadir'
        ]);

        return back()->with('success', 'Absen Masuk (Gudang) berhasil! Selamat bekerja.');
    }

    /**
     * Proses Absen Keluar Karyawan Gudang (Mobile)
     */
    public function clockOutGudang(Request $request)
    {
        $request->validate([
            'lat_out' => 'required|numeric',
            'lng_out' => 'required|numeric',
            'photo_out' => 'nullable|image|max:5120',
            'photo_base64' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if (!$request->hasFile('photo_out') && !$request->filled('photo_base64')) {
            return back()->with('error', 'Foto bukti absensi keluar gudang wajib diambil.');
        }

        $user = Auth::user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum melakukan absen masuk hari ini.');
        }
        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah menyelesaikan absen hari ini.');
        }

        $photoPath = null;
        if ($request->hasFile('photo_out')) {
            $photoPath = $request->file('photo_out')->store('absensi_gudang', 'public');
        } elseif ($request->filled('photo_base64')) {
            $image = $request->photo_base64;
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'absensi_gudang/' . uniqid() . '.jpg';
            Storage::disk('public')->put($imageName, base64_decode($image));
            $photoPath = $imageName;
        }

        $updateData = array_merge([
            'clock_out' => now()->toTimeString(),
            'lat_out' => $request->lat_out,
            'lng_out' => $request->lng_out,
            'photo_out' => $photoPath,
            'notes' => $request->notes
        ], $this->hitungLembur($request, $today));

        $attendance->update($updateData);

        return back()->with('success', 'Absen Keluar (Gudang) berhasil. Hati-hati di jalan!');
    }
    private function hitungLembur(Request $request, $today)
    {
        $setting = OfficeSetting::first();
        $jamPulangStandar = Carbon::parse($today . ' ' . ($setting->end_time ?? '17:00:00'));
        $waktuSekarang = Carbon::now();

        $dataLembur = [
            'is_overtime' => false,
            'overtime_minutes' => 0,
            'overtime_reason' => null,
            'overtime_status' => 'none',
        ];

        // Jika waktu pulang melewati jam pulang standar & user mencentang klaim lembur
        if ($waktuSekarang->greaterThan($jamPulangStandar) && $request->has('claim_overtime')) {
            $selisihMenit = $jamPulangStandar->diffInMinutes($waktuSekarang);
            $minLembur = $setting->min_overtime_minutes ?? 60;

            if ($selisihMenit >= $minLembur) {
                $dataLembur = [
                    'is_overtime' => true,
                    'overtime_minutes' => $selisihMenit,
                    'overtime_reason' => $request->overtime_reason,
                    'overtime_status' => 'pending', // Menunggu disetujui Admin
                ];
            }
        }

        return $dataLembur;
    }
}