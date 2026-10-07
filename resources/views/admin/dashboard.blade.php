@extends('layouts.admin')

@push('styles')
    <!-- Map API untuk Tracking Supir -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
    </style>
@endpush

@section('content')
<!-- Header & Overview -->
<header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Dashboard Statistik</h1>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Data Real-Time
            </span>
        </div>
        <p class="text-gray-500 text-sm mt-1">
            Pantauan kehadiran hari ini: <span class="font-semibold text-gray-700">{{ now()->translatedFormat('l, d F Y') }}</span>
        </p>
    </div>
    
    <div class="flex flex-wrap items-center gap-4">
        <!-- Info Ringkasan Karyawan -->
        <div class="bg-white p-3.5 px-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-6">
            <div>
                <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Karyawan</p>
                <p class="text-2xl font-black text-gray-800">{{ $stats['total_karyawan'] }} <span class="text-xs font-medium text-gray-400">Orang</span></p>
            </div>
            <div class="h-9 w-px bg-gray-200"></div>
            <div class="flex gap-2 text-xs font-bold">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg border border-blue-100 flex items-center gap-1">
                    <i class="fas fa-building text-[10px]"></i> {{ $stats['count_kantor'] }} Kantor
                </span>
                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg border border-amber-100 flex items-center gap-1">
                    <i class="fas fa-truck text-[10px]"></i> {{ $stats['count_supir'] }} Supir
                </span>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100 flex items-center gap-1">
                    <i class="fas fa-boxes text-[10px]"></i> {{ $stats['count_gudang'] }} Gudang
                </span>
            </div>
        </div>

        <!-- Widget Pengaturan Jam Kerja -->
        <a href="{{ route('admin.settings.index') }}" class="bg-white hover:bg-slate-50 hover:border-blue-300 p-3.5 px-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3.5 transition group" title="Klik untuk mengubah jam kerja">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg group-hover:bg-blue-600 group-hover:text-white transition">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Jam Masuk (Batas Telat)</p>
                <p class="text-sm font-extrabold text-gray-800 flex items-center gap-1.5">
                    {{ substr($setting->start_time ?? '08:00', 0, 5) }} - {{ substr($setting->end_time ?? '17:00', 0, 5) }}
                    <span class="text-xs text-blue-600 font-semibold group-hover:translate-x-0.5 transition"><i class="fas fa-cog"></i> Atur</span>
                </p>
            </div>
        </a>
    </div>
</header>

@if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl mb-6 text-sm font-semibold flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600 text-lg"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><i class="fas fa-times"></i></button>
    </div>
@endif

<!-- 1. Statistik Kehadiran Hari Ini (4 Kartu Utama) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <!-- Hadir Tepat Waktu -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">Hadir Tepat Waktu</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        <h3 class="text-3xl font-black text-gray-800">{{ $stats['hadir'] }}</h3>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fas fa-clock text-emerald-500"></i> Absen sebelum batas terlambat
        </p>
    </div>

    <!-- Terlambat -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg">Terlambat</span>
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
        <h3 class="text-3xl font-black text-gray-800">{{ $stats['telat'] }}</h3>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fas fa-exclamation-triangle text-amber-500"></i> Masuk setelah jam toleransi
        </p>
    </div>

    <!-- Izin / Cuti -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg">Izin / Cuti</span>
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                <i class="fas fa-file-medical"></i>
            </div>
        </div>
        <h3 class="text-3xl font-black text-gray-800">{{ $stats['izin'] }}</h3>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fas fa-check-double text-blue-500"></i> Status disetujui HR
        </p>
    </div>

    <!-- Belum Absen / Alpha -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">Belum Absen / Alpha</span>
            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg">
                <i class="fas fa-user-times"></i>
            </div>
        </div>
        <h3 class="text-3xl font-black text-gray-800">{{ $stats['alpha'] }}</h3>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
            <i class="fas fa-info-circle text-rose-500"></i> Tidak / belum melakukan absensi
        </p>
    </div>
</div>

<!-- Progress Bar Tingkat Kehadiran Keseluruhan -->
<div class="bg-white p-4 px-6 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
    <div class="w-full md:w-auto">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tingkat Kehadiran Karyawan Hari Ini</p>
        <div class="flex items-baseline gap-2 mt-0.5">
            <span class="text-2xl font-black text-blue-600">{{ $stats['persentase_kehadiran'] }}%</span>
            <span class="text-xs text-gray-500">dari total {{ $stats['total_karyawan'] }} karyawan aktif</span>
        </div>
    </div>
    <div class="w-full md:flex-1 max-w-xl">
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden shadow-inner">
            <div class="bg-gradient-to-r from-blue-500 to-emerald-500 h-3 rounded-full transition-all duration-700" style="width: {{ min(100, $stats['persentase_kehadiran']) }}%"></div>
        </div>
    </div>
    <div class="text-xs font-semibold text-gray-500 hidden lg:block text-right">
        <span>{{ $stats['hadir'] + $stats['telat'] }} Hadir</span> &bull; 
        <span>{{ $stats['alpha'] }} Belum Hadir</span>
    </div>
</div>

<!-- 2. Bagian Grafik Statistik (Grid 2 Kolom: Bar Chart 7 Hari & Donut Status Hari Ini) -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Bar Chart 7 Hari Kehadiran per Divisi -->
    <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Tren Kehadiran 7 Hari Terakhir</h2>
                <p class="text-xs text-gray-400">Distribusi jumlah kehadiran karyawan kantor, supir, dan gudang</p>
            </div>
            <span class="text-xs px-3 py-1 bg-gray-50 text-gray-600 rounded-lg border border-gray-200 font-medium">Real Data</span>
        </div>
        <div id="chartDivisi" class="h-72"></div>
    </div>

    <!-- Donut Chart Komposisi Kehadiran Hari Ini -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Komposisi Kehadiran Hari Ini</h2>
            <p class="text-xs text-gray-400 mb-4">Perbandingan status absensi per hari ini</p>
            <div id="chartDonut" class="h-64 flex items-center justify-center"></div>
        </div>
        <div class="grid grid-cols-2 gap-2 pt-4 border-t border-gray-100 text-xs text-gray-600">
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Hadir: {{ $stats['hadir'] }}</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Telat: {{ $stats['telat'] }}</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Izin: {{ $stats['izin'] }}</div>
            <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500"></span> Belum: {{ $stats['alpha'] }}</div>
        </div>
    </div>
</div>

<!-- 3. Monitoring Operasional: Map Supir GPS & Approval Izin Cepat -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    
    <!-- Mini Tracker Supir (Leaflet API dengan Real Coordinates) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-map-marked-alt text-blue-600"></i> Lokasi GPS Supir Hari Ini
                </h2>
                <p class="text-xs text-gray-400">Titik koordinat saat absen masuk/keluar armada</p>
            </div>
            <span class="text-xs font-bold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">
                {{ count($supirLocations) }} Armada Terpantau
            </span>
        </div>
        
        <div id="mapSupir" class="h-72 w-full bg-gray-100 rounded-xl z-0 border border-gray-200"></div>

        @if(count($supirLocations) === 0)
            <div class="mt-3 p-3 bg-gray-50 border border-gray-100 rounded-xl text-center text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1 text-gray-400"></i> Belum ada supir yang melakukan absen dengan koordinat GPS hari ini.
            </div>
        @else
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                @foreach($supirLocations as $loc)
                    <span class="px-2 py-1 bg-gray-50 text-gray-700 border border-gray-200 rounded-lg font-medium flex items-center gap-1">
                        <i class="fas fa-truck text-blue-500 text-[10px]"></i> {{ $loc['name'] }} ({{ $loc['clock_in'] }})
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Antrean Approval Izin Real-Time -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-yellow-600"></i> Antrean Persetujuan Izin
                </h2>
                <p class="text-xs text-gray-400">Pengajuan izin & cuti yang membutuhkan respon HR</p>
            </div>
            <a href="{{ route('admin.izin.index') }}" class="text-xs text-blue-600 font-bold hover:underline">
                Kelola Semua &rarr;
            </a>
        </div>

        <div class="space-y-3 flex-1 overflow-y-auto max-h-80">
            @forelse($pendingLeaves as $leave)
                <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 hover:bg-white hover:border-blue-200 transition">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-bold text-gray-800 text-sm">{{ $leave->user->name ?? 'Karyawan' }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs px-2 py-0.5 rounded font-bold uppercase {{ $leave->type === 'sakit' ? 'bg-red-100 text-red-700' : ($leave->type === 'cuti' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ $leave->type }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} s/d {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Tombol Aksi Cepat Setujui / Tolak -->
                        <div class="flex gap-1.5">
                            <form action="{{ route('admin.izin.update', $leave->id) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Setujui Izin">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.izin.update', $leave->id) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow-sm" title="Tolak Izin">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 line-clamp-2 italic bg-white p-2 rounded-lg border border-gray-100">
                        "{{ $leave->reason }}"
                    </p>
                </div>
            @empty
                <div class="h-48 flex flex-col items-center justify-center text-center p-6 border-2 border-dashed border-gray-200 rounded-2xl text-gray-400">
                    <i class="fas fa-calendar-check text-4xl mb-2 text-emerald-400"></i>
                    <p class="text-sm font-bold text-gray-700">Semua Izin Telah Diproses</p>
                    <p class="text-xs text-gray-400 mt-0.5">Tidak ada antrean pengajuan izin yang menunggu persetujuan.</p>
                </div>
            @endforelse
        </div>
    </div>

</div>

<!-- 4. Log Aktivitas Absensi Terbaru Hari Ini -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history text-indigo-600"></i> Aktivitas Absensi Terbaru (Hari Ini)
            </h2>
            <p class="text-xs text-gray-400">Riwayat clock-in dan clock-out pegawai secara langsung</p>
        </div>
        <span class="text-xs text-gray-400">Menampilkan 6 aktivitas terakhir</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                    <th class="p-3 font-bold">Nama Karyawan</th>
                    <th class="p-3 font-bold">Divisi</th>
                    <th class="p-3 font-bold">Jam Masuk</th>
                    <th class="p-3 font-bold">Jam Keluar</th>
                    <th class="p-3 font-bold">Status</th>
                    <th class="p-3 font-bold">Keterangan / Foto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recentAttendances as $att)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-3 font-semibold text-gray-800">{{ $att->user->name ?? 'User' }}</td>
                        <td class="p-3">
                            @if(optional($att->user)->role === 'karyawan_kantor')
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-bold">Kantor</span>
                            @elseif(optional($att->user)->role === 'supir')
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded text-xs font-bold">Supir</span>
                            @else
                                <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded text-xs font-bold">Gudang</span>
                            @endif
                        </td>
                        <td class="p-3 font-mono text-gray-700 font-medium">
                            {{ $att->clock_in ? substr($att->clock_in, 0, 5) : '-' }}
                        </td>
                        <td class="p-3 font-mono text-gray-700 font-medium">
                            {{ $att->clock_out ? substr($att->clock_out, 0, 5) : '-' }}
                        </td>
                        <td class="p-3">
                            @if($att->status === 'hadir')
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">Hadir</span>
                            @elseif($att->status === 'telat')
                                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">Terlambat</span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ ucfirst($att->status) }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-xs text-gray-500">
                            @if($att->photo_in)
                                <a href="{{ asset('storage/' . $att->photo_in) }}" target="_blank" class="text-blue-600 font-semibold hover:underline flex items-center gap-1">
                                    <i class="fas fa-camera"></i> Lihat Foto
                                </a>
                            @elseif($att->notes)
                                <span class="truncate block max-w-xs text-gray-600" title="{{ $att->notes }}">{{ $att->notes }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-400 text-xs">
                            Belum ada aktivitas absensi tercatat hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 5. Form Rekapitulasi & Export -->
<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-lg bg-green-100 text-green-700 flex items-center justify-center font-bold">
            <i class="fas fa-file-excel"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Tarik Laporan & Rekapitulasi (Payroll)</h2>
            <p class="text-xs text-gray-400">Filter dan export data absensi karyawan</p>
        </div>
    </div>
    <form action="{{ route('admin.laporan.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">Mulai Tanggal</label>
            <input type="date" name="start_date" value="{{ now()->startOfMonth()->toDateString() }}" class="w-full border-gray-300 rounded-xl shadow-sm py-2 px-3 border focus:ring-2 focus:ring-blue-500 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" value="{{ now()->toDateString() }}" class="w-full border-gray-300 rounded-xl shadow-sm py-2 px-3 border focus:ring-2 focus:ring-blue-500 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-600 mb-1">Divisi</label>
            <select name="divisi" class="w-full border-gray-300 rounded-xl shadow-sm py-2 px-3 border focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="all">Semua Divisi</option>
                <option value="karyawan_kantor">Karyawan Kantor</option>
                <option value="supir">Supir</option>
                <option value="karyawan_gudang">Karyawan Gudang</option>
            </select>
        </div>
        <div>
            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-filter"></i> Tampilkan Laporan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Data Riil untuk Grafik 7 Hari Terakhir (ApexCharts Stacked Bar)
    var chartDataCategories = @json($chartData['categories']);
    var dataKantor = @json($chartData['kantor']);
    var dataSupir = @json($chartData['supir']);
    var dataGudang = @json($chartData['gudang']);

    var optionsDivisi = {
        series: [{
            name: 'Karyawan Kantor',
            data: dataKantor
        }, {
            name: 'Supir',
            data: dataSupir
        }, {
            name: 'Gudang',
            data: dataGudang
        }],
        chart: {
            type: 'bar',
            height: 280,
            stacked: true,
            fontFamily: 'Plus Jakarta Sans, sans-serif',
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 6,
                columnWidth: '35%'
            }
        },
        xaxis: {
            categories: chartDataCategories,
            labels: {
                style: { colors: '#64748B', fontSize: '11px', fontWeight: 600 }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: '#64748B', fontSize: '11px' },
                formatter: function (val) { return Math.floor(val); }
            }
        },
        colors: ['#3B82F6', '#F59E0B', '#10B981'],
        fill: { opacity: 1 },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '12px',
            markers: { radius: 12 }
        },
        grid: {
            borderColor: '#F1F5F9',
            strokeDashArray: 4
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " orang";
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#chartDivisi"), optionsDivisi).render();

    // 2. Data Riil Donut Chart Komposisi Kehadiran Hari Ini
    var donutHadir = {{ (int)$stats['hadir'] }};
    var donutTelat = {{ (int)$stats['telat'] }};
    var donutIzin = {{ (int)$stats['izin'] }};
    var donutAlpha = {{ (int)$stats['alpha'] }};

    var totalToday = donutHadir + donutTelat + donutIzin + donutAlpha;

    var optionsDonut = {
        series: totalToday > 0 ? [donutHadir, donutTelat, donutIzin, donutAlpha] : [1],
        labels: totalToday > 0 ? ['Hadir Tepat Waktu', 'Terlambat', 'Izin / Cuti', 'Belum Absen / Alpha'] : ['Belum Ada Data'],
        chart: {
            type: 'donut',
            height: 250,
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        colors: totalToday > 0 ? ['#10B981', '#F59E0B', '#3B82F6', '#EF4444'] : ['#E2E8F0'],
        legend: { show: false },
        dataLabels: { enabled: totalToday > 0 },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', fontWeight: 600, color: '#64748B' },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 800,
                            color: '#1E293B',
                            formatter: function (val) { return totalToday > 0 ? val + " Org" : "0"; }
                        },
                        total: {
                            show: true,
                            label: 'Total Karyawan',
                            color: '#64748B',
                            formatter: function (w) { return {{ (int)$stats['total_karyawan'] }} + " Org"; }
                        }
                    }
                }
            }
        },
        tooltip: {
            enabled: totalToday > 0
        }
    };
    new ApexCharts(document.querySelector("#chartDonut"), optionsDonut).render();

    // 3. Inisialisasi Peta Supir dengan Marker Riil dari Database
    var supirLocations = @json($supirLocations);
    
    // Default koordinat (Surabaya/Indonesia atau lokasi supir pertama jika ada)
    var defaultLat = supirLocations.length > 0 ? supirLocations[0].lat : -7.250445;
    var defaultLng = supirLocations.length > 0 ? supirLocations[0].lng : 112.768845;
    
    var map = L.map('mapSupir').setView([defaultLat, defaultLng], supirLocations.length > 0 ? 12 : 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    if (supirLocations.length > 0) {
        var bounds = [];
        supirLocations.forEach(function(supir) {
            var marker = L.marker([supir.lat, supir.lng]).addTo(map);
            var popupHtml = `
                <div class="p-1 text-xs">
                    <p class="font-bold text-gray-800 text-sm mb-1">${supir.name}</p>
                    <p class="text-blue-600 font-semibold mb-1"><i class="fas fa-truck"></i> ${supir.status}</p>
                    <p class="text-gray-600"><b>Masuk:</b> ${supir.clock_in} ${supir.clock_out ? '| <b>Keluar:</b> ' + supir.clock_out : ''}</p>
                    ${supir.notes && supir.notes !== 'Tidak ada catatan tugas' ? '<p class="text-gray-500 mt-1 italic bg-gray-50 p-1 rounded">"' + supir.notes + '"</p>' : ''}
                </div>
            `;
            marker.bindPopup(popupHtml);
            bounds.push([supir.lat, supir.lng]);
        });

        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    }
</script>
@endpush