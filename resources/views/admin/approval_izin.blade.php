@extends('layouts.admin')

@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Izin & Cuti</h1>
        <p class="text-gray-500 mt-1">Tinjau dan setujui pengajuan ketidakhadiran karyawan.</p>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm mb-6 font-medium">
        {{ session('success') }}
    </div>
@endif

<!-- Tabel Menunggu Persetujuan (Pending) -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-10">
    <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100 flex justify-between items-center">
        <h2 class="font-bold text-yellow-800 text-lg flex items-center gap-2">
            <i class="fas fa-clock"></i> Menunggu Persetujuan
        </h2>
        <span class="bg-yellow-200 text-yellow-800 text-xs px-3 py-1 rounded-full font-bold">{{ $pendingLeaves->count() }} Pengajuan</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                    <th class="p-4 font-semibold">Nama Karyawan</th>
                    <th class="p-4 font-semibold">Tipe & Tanggal</th>
                    <th class="p-4 font-semibold w-1/3">Alasan</th>
                    <th class="p-4 font-semibold text-center">Lampiran</th>
                    <th class="p-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingLeaves as $leave)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="p-4">
                        <p class="font-bold text-gray-800">{{ $leave->user->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ str_replace('_', ' ', $leave->user->role ?? '-') }}</p>
                    </td>
                    <td class="p-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-bold uppercase inline-block mb-1">{{ $leave->type }}</span>
                        <p class="text-sm font-medium text-gray-700">
                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                        </p>
                    </td>
                    <td class="p-4 text-sm text-gray-600">
                        {{ $leave->reason }}
                    </td>
                    <td class="p-4 text-center">
                        @if($leave->attachment)
                            <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline text-sm font-semibold inline-flex items-center gap-1 justify-center">
                                <i class="fas fa-paperclip"></i> Lihat
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">-</span>
                        @endif
                    </td>
                    <td class="p-4 flex justify-center gap-2">
                        <form action="{{ route('admin.izin.update', $leave->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-bold shadow-sm transition" onclick="return confirm('Setujui izin ini?');">Approve</button>
                        </form>
                        <form action="{{ route('admin.izin.update', $leave->id) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 text-sm font-bold shadow-sm transition" onclick="return confirm('Tolak pengajuan izin ini?');">Reject</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500 font-medium">
                        <i class="fas fa-check-circle text-4xl text-green-200 mb-3 block"></i>
                        Tidak ada pengajuan izin baru.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Riwayat Persetujuan -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-700 text-lg">Riwayat Izin & Cuti</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-sm text-gray-500">
                    <th class="p-4 font-semibold">Nama Karyawan</th>
                    <th class="p-4 font-semibold">Tanggal Izin</th>
                    <th class="p-4 font-semibold w-1/3">Alasan</th>
                    <th class="p-4 font-semibold text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historyLeaves as $history)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="p-4">
                        <p class="font-bold text-gray-800">{{ $history->user->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">{{ str_replace('_', ' ', $history->user->role ?? '-') }}</p>
                    </td>
                    <td class="p-4 text-sm font-medium text-gray-700">
                        {{ \Carbon\Carbon::parse($history->start_date)->format('d M Y') }} s/d <br>
                        {{ \Carbon\Carbon::parse($history->end_date)->format('d M Y') }}
                    </td>
                    <td class="p-4 text-sm text-gray-600">
                        <span class="font-bold text-gray-800 capitalize">{{ $history->type }}:</span> {{ $history->reason }}
                    </td>
                    <td class="p-4 text-center">
                        @if($history->status === 'approved')
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold inline-flex items-center justify-center gap-1 w-24 mx-auto">
                                <i class="fas fa-check"></i> Disetujui
                            </span>
                        @else
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold inline-flex items-center justify-center gap-1 w-24 mx-auto">
                                <i class="fas fa-times"></i> Ditolak
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-6 text-center text-gray-400">Belum ada riwayat izin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection