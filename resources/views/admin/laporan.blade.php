@extends('layouts.admin')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Laporan & Rekapitulasi</h1>
        <p class="text-gray-500 mt-1">Filter dan tinjau data absensi seluruh karyawan.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.laporan.excel', request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-lg flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-file-excel text-lg"></i>
            <span>Export Excel</span>
        </a>
        <a href="{{ route('admin.laporan.pdf', request()->all()) }}" target="_blank" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 px-4 rounded-lg flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-file-pdf text-lg"></i>
            <span>Export PDF</span>
        </a>
    </div>
</div>

<!-- Filter Panel -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex flex-wrap md:flex-nowrap gap-4 items-end">
        <div class="w-full md:w-1/4">
            <label class="block text-sm font-bold text-gray-700 mb-1">Mulai Tanggal</label>
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm">
        </div>
        <div class="w-full md:w-1/4">
            <label class="block text-sm font-bold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm">
        </div>
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-bold text-gray-700 mb-1">Divisi / Peran</label>
            <select name="divisi" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring focus:ring-blue-200 focus:border-blue-500 text-sm">
                <option value="all" {{ request('divisi') == 'all' ? 'selected' : '' }}>Semua Divisi</option>
                <option value="karyawan_kantor" {{ request('divisi') == 'karyawan_kantor' ? 'selected' : '' }}>Karyawan Kantor</option>
                <option value="supir" {{ request('divisi') == 'supir' ? 'selected' : '' }}>Supir</option>
                <option value="karyawan_gudang" {{ request('divisi') == 'karyawan_gudang' ? 'selected' : '' }}>Karyawan Gudang</option>
            </select>
        </div>
        <div class="w-full md:w-auto flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-sm transition">
                Filter
            </button>
            @if(request()->has('start_date') || request()->has('end_date') || (request()->has('divisi') && request('divisi') !== 'all'))
                <a href="{{ route('admin.laporan.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg shadow-sm transition">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabel Data Absensi -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse whitespace-nowrap">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-sm text-gray-600">
                    <th class="p-4 font-bold">Tanggal</th>
                    <th class="p-4 font-bold">Nama Karyawan</th>
                    <th class="p-4 font-bold">Divisi</th>
                    <th class="p-4 font-bold">Jam Masuk</th>
                    <th class="p-4 font-bold">Jam Keluar</th>
                    <th class="p-4 font-bold text-center">Status</th>
                    <th class="p-4 font-bold text-center">Validasi</th>
                    <th class="p-4 font-bold text-center">Foto Bukti</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $absen)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                    <td class="p-4 font-medium text-gray-700">
                        {{ \Carbon\Carbon::parse($absen->date)->format('d M Y') }}
                    </td>
                    <td class="p-4">
                        <p class="font-bold text-gray-800">{{ $absen->user->name ?? 'User Terhapus' }}</p>
                    </td>
                    <td class="p-4">
                        @if($absen->user)
                            @if($absen->user->role === 'karyawan_kantor')
                                <span class="text-blue-600 font-semibold bg-blue-50 px-2 py-1 rounded text-xs">Kantor</span>
                            @elseif($absen->user->role === 'supir')
                                <span class="text-yellow-600 font-semibold bg-yellow-50 px-2 py-1 rounded text-xs">Supir</span>
                            @else
                                <span class="text-emerald-600 font-semibold bg-emerald-50 px-2 py-1 rounded text-xs">Gudang</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-4 font-mono text-gray-600">
                        {{ $absen->clock_in ? \Carbon\Carbon::parse($absen->clock_in)->format('H:i') : '--:--' }}
                    </td>
                    <td class="p-4 font-mono text-gray-600">
                        {{ $absen->clock_out ? \Carbon\Carbon::parse($absen->clock_out)->format('H:i') : '--:--' }}
                    </td>
                    <td class="p-4 text-center">
                        @if($absen->status === 'hadir')
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold w-20 inline-block">Hadir</span>
                        @elseif($absen->status === 'telat')
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold w-20 inline-block">Terlambat</span>
                        @elseif($absen->status === 'izin')
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold w-20 inline-block">Izin/Cuti</span>
                        @else
                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold w-20 inline-block">Alpha</span>
                        @endif
                    </td>
                    <td class="p-4 text-center text-xs text-gray-500">
                        @if($absen->ip_address_in)
                            <i class="fas fa-wifi text-blue-500" title="IP: {{ $absen->ip_address_in }}"></i>
                        @endif
                        @if($absen->lat_in && $absen->lng_in)
                            <i class="fas fa-map-marker-alt text-red-500 ml-1" title="GPS Tersedia"></i>
                        @endif
                        @if($absen->photo_in)
                            <i class="fas fa-camera text-gray-700 ml-1" title="Foto Tersedia"></i>
                        @endif
                        @if(!$absen->ip_address_in && !$absen->lat_in && !$absen->photo_in)
                            <span title="Kiosk PIN / Otomatis">PIN/Tap</span>
                        @endif
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            @php
                                $photoIn = $absen->photo_in ? (str_starts_with($absen->photo_in, 'data:image') || str_starts_with($absen->photo_in, 'http') ? $absen->photo_in : asset('storage/' . $absen->photo_in)) : null;
                                $photoOut = $absen->photo_out ? (str_starts_with($absen->photo_out, 'data:image') || str_starts_with($absen->photo_out, 'http') ? $absen->photo_out : asset('storage/' . $absen->photo_out)) : null;
                            @endphp

                            @if($photoIn)
                                <button type="button" onclick="bukaFoto('{{ $photoIn }}', 'Foto Masuk - {{ addslashes($absen->user->name ?? 'User') }}')" class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm" title="Lihat Foto Masuk">
                                    <i class="fas fa-sign-in-alt"></i> Masuk
                                </button>
                            @endif

                            @if($photoOut)
                                <button type="button" onclick="bukaFoto('{{ $photoOut }}', 'Foto Keluar - {{ addslashes($absen->user->name ?? 'User') }}')" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm" title="Lihat Foto Keluar">
                                    <i class="fas fa-sign-out-alt"></i> Keluar
                                </button>
                            @endif

                            @if(!$photoIn && !$photoOut)
                                <span class="text-gray-400 text-xs italic">-</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                            <p>Tidak ada data absensi yang ditemukan pada rentang/filter ini.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Section -->
    <div class="p-4 border-t border-gray-100 bg-gray-50">
        {{ $attendances->links() }}
    </div>
</div>

<!-- Modal Preview Foto -->
<div id="modalFoto" class="fixed inset-0 bg-gray-900 bg-opacity-75 z-50 hidden flex items-center justify-center transition-opacity p-4">
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-lg transform transition-transform scale-95 relative p-4">
        
        <!-- Header Modal -->
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Preview Foto</h3>
            <button onclick="tutupFoto()" class="text-gray-400 hover:text-red-500 transition text-2xl leading-none">
                &times;
            </button>
        </div>

        <!-- Gambar -->
        <div class="flex justify-center bg-gray-100 rounded-xl overflow-hidden border border-gray-200" style="min-height: 300px;">
            <img id="modalImage" src="" alt="Foto Bukti Absensi" class="max-w-full max-h-[70vh] object-contain">
        </div>

        <!-- Tombol Tutup -->
        <div class="mt-4 flex justify-end">
            <button onclick="tutupFoto()" class="bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Script Control Modal -->
<script>
    function bukaFoto(imageUrl, title) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalFoto').classList.remove('hidden');
    }

    function tutupFoto() {
        document.getElementById('modalFoto').classList.add('hidden');
        document.getElementById('modalImage').src = ""; // Clear memori gambar
    }

    // Tutup modal jika mengklik area hitam (background)
    document.getElementById('modalFoto').addEventListener('click', function(e) {
        if (e.target === this) {
            tutupFoto();
        }
    });
</script>
@endsection