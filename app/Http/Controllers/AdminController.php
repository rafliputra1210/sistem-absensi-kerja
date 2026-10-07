<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Leave;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        // 1. Total Karyawan Aktif per divisi
        $totalKaryawan = User::where('role', '!=', 'admin')->count();
        $countKantor = User::where('role', 'karyawan_kantor')->count();
        $countSupir = User::where('role', 'supir')->count();
        $countGudang = User::where('role', 'karyawan_gudang')->count();

        // 2. Statistik Kehadiran Hari Ini
        $hadirCount = Attendance::where('date', $today)->where('status', 'hadir')->count();
        $telatCount = Attendance::where('date', $today)->where('status', 'telat')->count();
        
        // Izin hari ini (dari tabel Leave yang approved, atau dari attendance status izin)
        $izinLeaveCount = Leave::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();
        $izinAttendanceCount = Attendance::where('date', $today)->where('status', 'izin')->count();
        $izinCount = max($izinLeaveCount, $izinAttendanceCount);

        // Alpha / Belum Absen
        $alphaCount = Attendance::where('date', $today)->where('status', 'alpha')->count();
        $belumAbsen = max(0, $totalKaryawan - ($hadirCount + $telatCount + $izinCount));

        $stats = [
            'total_karyawan' => $totalKaryawan,
            'count_kantor' => $countKantor,
            'count_supir' => $countSupir,
            'count_gudang' => $countGudang,
            'hadir' => $hadirCount,
            'telat' => $telatCount,
            'izin'  => $izinCount,
            'alpha' => $alphaCount > 0 ? $alphaCount : $belumAbsen,
            'persentase_kehadiran' => $totalKaryawan > 0 ? round((($hadirCount + $telatCount) / $totalKaryawan) * 100, 1) : 0,
        ];

        // 3. Real Data 7 Hari Terakhir untuk Grafik Kehadiran per Divisi
        $dates = [];
        $categories = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = now()->subDays($i);
            $dates[] = $d->toDateString();
            $categories[] = $d->translatedFormat('D, d M');
        }

        $attendancesWeek = Attendance::with('user')
            ->whereIn('date', $dates)
            ->whereIn('status', ['hadir', 'telat'])
            ->get();

        $dataKantor = [];
        $dataSupir = [];
        $dataGudang = [];

        foreach ($dates as $date) {
            $dataKantor[] = $attendancesWeek->filter(function($att) use ($date) {
                return $att->date == $date && optional($att->user)->role === 'karyawan_kantor';
            })->count();

            $dataSupir[] = $attendancesWeek->filter(function($att) use ($date) {
                return $att->date == $date && optional($att->user)->role === 'supir';
            })->count();

            $dataGudang[] = $attendancesWeek->filter(function($att) use ($date) {
                return $att->date == $date && optional($att->user)->role === 'karyawan_gudang';
            })->count();
        }

        $chartData = [
            'categories' => $categories,
            'kantor' => $dataKantor,
            'supir' => $dataSupir,
            'gudang' => $dataGudang,
        ];

        // 4. Data Supir Aktif Hari Ini (Real GPS & Status)
        $supirLocations = Attendance::with('user')
            ->where('date', $today)
            ->whereHas('user', function($q) {
                $q->where('role', 'supir');
            })
            ->whereNotNull('lat_in')
            ->whereNotNull('lng_in')
            ->latest()
            ->get()
            ->map(function($att) {
                return [
                    'id' => $att->id,
                    'name' => $att->user->name ?? 'Supir',
                    'lat' => (float)$att->lat_in,
                    'lng' => (float)$att->lng_in,
                    'clock_in' => $att->clock_in ? substr($att->clock_in, 0, 5) : '-',
                    'clock_out' => $att->clock_out ? substr($att->clock_out, 0, 5) : null,
                    'status' => $att->clock_out ? 'Selesai Shift' : 'Sedang Bertugas',
                    'notes' => $att->notes ?? 'Tidak ada catatan tugas',
                    'photo' => $att->photo_in ? asset('storage/' . $att->photo_in) : null,
                ];
            });

        // 5. Antrean Approval Izin Real (Pending)
        $pendingLeaves = Leave::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // 6. Aktivitas Kehadiran Terkini Hari Ini (Live Feed)
        $recentAttendances = Attendance::with('user')
            ->where('date', $today)
            ->latest('updated_at')
            ->take(6)
            ->get();

        // 7. Pengaturan Jam Kerja Sistem
        $setting = \App\Models\OfficeSetting::first();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'supirLocations',
            'pendingLeaves',
            'recentAttendances',
            'setting'
        ));
    }
    public function approvalIzin()
    {
        // Mengambil data izin yang masih menunggu persetujuan
        $pendingLeaves = Leave::with('user')
                            ->where('status', 'pending')
                            ->orderBy('created_at', 'desc')
                            ->get();
        
        // Mengambil riwayat izin yang sudah diproses
        $historyLeaves = Leave::with('user')
                            ->whereIn('status', ['approved', 'rejected'])
                            ->orderBy('updated_at', 'desc')
                            ->limit(50) // Batasi 50 riwayat terakhir
                            ->get();

        return view('admin.approval_izin', compact('pendingLeaves', 'historyLeaves'));
    }

    public function updateIzin(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:approved,rejected']);
        
        $leave = Leave::findOrFail($id);
        $leave->update(['status' => $request->status]);
        
        $pesan = $request->status === 'approved' ? 'Izin berhasil disetujui.' : 'Izin telah ditolak.';
        return back()->with('success', $pesan);
    }
    public function laporan(Request $request)
    {
        // Inisialisasi query dengan relasi user
        $query = Attendance::with('user')->orderBy('date', 'desc');

        // Filter Rentang Tanggal
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Filter Divisi
        if ($request->filled('divisi') && $request->divisi !== 'all') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role', $request->divisi);
            });
        }

        // Ambil data dengan pagination (20 data per halaman)
        $attendances = $query->paginate(20)->appends($request->all());

        return view('admin.laporan', compact('attendances'));
    }
    
}