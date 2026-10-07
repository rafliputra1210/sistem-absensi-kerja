<?php

namespace App\Http\Controllers;

use App\Models\OfficeSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        // Ambil data setting, jika belum ada di database, buat instance baru
        $setting = OfficeSetting::firstOrCreate(
            ['id' => 1],
            [
                'start_time' => '08:00',
                'end_time' => '17:00',
                'office_ip' => '192.168.1.1'
            ]
        );

        return view('admin.settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'office_ip' => 'nullable|ip',
        ]);

        $setting = OfficeSetting::first();
        $setting->update([
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'office_ip' => $request->office_ip,
        ]);

        return back()->with('success', 'Pengaturan jam kerja & sistem berhasil diperbarui.');
    }
}