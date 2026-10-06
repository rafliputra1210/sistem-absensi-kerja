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

        $stats = [
            'hadir' => Attendance::where('date', $today)->where('status', 'hadir')->count(),
            'telat' => Attendance::where('date', $today)->where('status', 'telat')->count(),
            'izin'  => Attendance::where('date', $today)->where('status', 'izin')->count(),
            'alpha' => Attendance::where('date', $today)->where('status', 'alpha')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
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