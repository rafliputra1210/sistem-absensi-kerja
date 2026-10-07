@extends('layouts.admin') <!-- Sesuaikan jika Anda menggunakan layout blade -->

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Pengaturan Sistem & Jam Kerja</h1>
    <p class="text-gray-500 mt-1">Atur jam batas keterlambatan dan konfigurasi IP absensi kantor.</p>
</div>

@if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm mb-6 font-medium">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-6 pb-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-blue-500"></i> Pengaturan Jam Operasional
            </h2>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jam Masuk (Batas Telat)</label>
                    <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($setting->start_time)->format('H:i') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:border-blue-300 text-lg font-mono" required>
                    <p class="text-xs text-gray-500 mt-1">Karyawan yang absen lewat jam ini akan berstatus "Telat".</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jam Pulang / Selesai</label>
                    <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($setting->end_time)->format('H:i') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:border-blue-300 text-lg font-mono" required>
                </div>
            </div>
        </div>

        <div class="mb-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-network-wired text-blue-500"></i> Konfigurasi Jaringan (Kantor)
            </h2>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Alamat IP Wi-Fi Kantor</label>
                <input type="text" name="office_ip" value="{{ old('office_ip', $setting->office_ip) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:border-blue-300 font-mono" placeholder="Contoh: 192.168.1.1">
                <p class="text-xs text-gray-500 mt-1">Digunakan untuk validasi bahwa karyawan kantor terhubung dengan Wi-Fi perusahaan.</p>
                @error('office_ip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition">
            <i class="fas fa-save mr-2"></i> Simpan Pengaturan
        </button>
    </form>
</div>
@endsection