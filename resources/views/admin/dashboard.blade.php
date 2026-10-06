@extends('layouts.admin')

@push('styles')
    <!-- Map API untuk Tracking Supir -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')
<header class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Monitor</h1>
        <p class="text-gray-500">Statistik kehadiran hari ini: {{ now()->translatedFormat('d F Y') }}</p>
    </div>
    <div class="bg-white px-4 py-2 rounded-lg shadow-sm font-semibold border-l-4 border-blue-500">
        Total Karyawan Aktif: 120
    </div>
</header>

<!-- 1. Statistik Real-Time -->
<div class="grid grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
        <p class="text-sm text-gray-500 font-semibold mb-1">Total Hadir</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ $stats['hadir'] ?? 0 }}</h3>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
        <p class="text-sm text-gray-500 font-semibold mb-1">Terlambat</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ $stats['telat'] ?? 0 }}</h3>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
        <p class="text-sm text-gray-500 font-semibold mb-1">Izin / Cuti</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ $stats['izin'] ?? 0 }}</h3>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 border-l-4 border-l-red-500">
        <p class="text-sm text-gray-500 font-semibold mb-1">Alpha</p>
        <h3 class="text-3xl font-bold text-gray-800">{{ $stats['alpha'] ?? 0 }}</h3>
    </div>
</div>

<!-- 2. Grafik Kehadiran Terbagi Per Divisi -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Grafik Kehadiran per Divisi</h2>
    <div id="chartDivisi" class="h-80"></div>
</div>

<!-- Baris Bawah: Map Supir & Approval Izin Cepat -->
<div class="grid grid-cols-2 gap-8 mb-8">
    
    <!-- 3. Mini Tracker Supir (Leaflet API) -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Tracking Supir Aktif</h2>
            <a href="#" class="text-sm text-blue-600 hover:underline">Lihat Penuh & Validasi Foto &rarr;</a>
        </div>
        <div id="mapSupir" class="h-64 w-full bg-gray-200 rounded-lg z-0"></div>
    </div>

    <!-- 4. Antrean Approval Izin -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Pengajuan Izin Pending</h2>
        <div class="space-y-4">
            <div class="flex justify-between items-center p-4 border border-gray-100 rounded-lg bg-gray-50">
                <div>
                    <p class="font-bold text-gray-800">Siti (Karyawan Kantor)</p>
                    <p class="text-sm text-gray-500">Sakit - 2 Hari (Lampiran Tersedia)</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 text-sm font-bold">Approve</button>
                    <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm font-bold">Reject</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- 5. Form Rekapitulasi & Export -->
<div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Tarik Laporan & Rekapitulasi (Payroll)</h2>
    <form action="{{ route('admin.laporan.index') }}" method="GET" class="flex gap-4 items-end">
        <div class="flex-1">
            <label class="block text-sm font-bold text-gray-700 mb-1">Mulai Tanggal</label>
            <input type="date" name="start_date" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex-1">
            <label class="block text-sm font-bold text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="flex-1">
            <label class="block text-sm font-bold text-gray-700 mb-1">Divisi</label>
            <select name="divisi" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring-blue-500 focus:border-blue-500">
                <option value="all">Semua Divisi</option>
                <option value="karyawan_kantor">Kantor</option>
                <option value="supir">Supir</option>
                <option value="karyawan_gudang">Gudang</option>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow flex items-center gap-2">
                <span>📥</span> Export Excel
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // 1. Inisialisasi Grafik Kehadiran Per Divisi (ApexCharts)
    var optionsDivisi = {
        series: [{
            name: 'Kantor', data: [44, 55, 41, 67, 22]
        }, {
            name: 'Supir', data: [13, 23, 20, 8, 13]
        }, {
            name: 'Gudang', data: [11, 17, 15, 15, 21]
        }],
        chart: { type: 'bar', height: 320, stacked: true, toolbar: { show: false } },
        plotOptions: { bar: { horizontal: false, borderRadius: 5, columnWidth: '40%' } },
        xaxis: { categories: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] },
        colors: ['#3B82F6', '#10B981', '#F59E0B'],
        fill: { opacity: 1 },
        legend: { position: 'top', horizontalAlign: 'right' }
    };
    new ApexCharts(document.querySelector("#chartDivisi"), optionsDivisi).render();

    // 2. Inisialisasi Mini Map Tracker Supir (Leaflet JS)
    var map = L.map('mapSupir').setView([-7.250445, 112.768845], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([-7.250445, 112.768845]).addTo(map).bindPopup("<b>Budi</b><br>Supir Truk A");
    L.marker([-7.300445, 112.718845]).addTo(map).bindPopup("<b>Anton</b><br>Supir Pickup");
</script>
@endpush