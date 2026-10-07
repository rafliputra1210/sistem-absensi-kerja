@extends('layouts.admin')

@section('content')
@php
    $isEdit = isset($karyawan);
    $actionUrl = $isEdit ? route('admin.karyawan.update', $karyawan->id) : route('admin.karyawan.store');
@endphp

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ $isEdit ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru' }}</h1>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <form action="{{ $actionUrl }}" method="POST">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Nama Lengkap (Digunakan untuk Login)</label>
            <input type="text" name="name" value="{{ old('name', $karyawan->name ?? '') }}" class="w-full px-3 py-2 border rounded-lg focus:ring focus:border-blue-300" placeholder="Contoh: Budi Santoso" required>
            <p class="text-xs text-gray-500 mt-1">Karyawan, supir, dan staf gudang akan menggunakan nama ini saat masuk ke sistem absensi.</p>
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Password Login {{ $isEdit ? '(Isi jika ingin diubah)' : '' }}</label>
            <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:ring focus:border-blue-300" {{ !$isEdit ? 'required' : '' }}>
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Divisi / Role Absensi</label>
            <select name="role" id="roleSelect" class="w-full px-3 py-2 border rounded-lg focus:ring focus:border-blue-300" required onchange="togglePinInput()">
                <option value="" disabled {{ !$isEdit ? 'selected' : '' }}>-- Pilih Role --</option>
                <option value="karyawan_kantor" {{ old('role', $karyawan->role ?? '') == 'karyawan_kantor' ? 'selected' : '' }}>Karyawan Kantor (WFO)</option>
                <option value="supir" {{ old('role', $karyawan->role ?? '') == 'supir' ? 'selected' : '' }}>Supir (Mobilitas)</option>
                <option value="karyawan_gudang" {{ old('role', $karyawan->role ?? '') == 'karyawan_gudang' ? 'selected' : '' }}>Karyawan Gudang (Kiosk Shift)</option>
            </select>
            @error('role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Input khusus untuk Role Gudang -->
        <div class="mb-6 hidden bg-gray-50 p-4 border border-gray-200 rounded-lg" id="pinInputContainer">
            <label class="block text-gray-700 font-bold mb-2">PIN Absensi Kiosk (Wajib 6 Angka)</label>
            <input type="text" name="pin_gudang" value="{{ old('pin_gudang', $karyawan->pin_gudang ?? '') }}" maxlength="6" class="w-full px-3 py-2 border rounded-lg focus:ring focus:border-blue-300 font-mono tracking-widest text-lg">
            <p class="text-sm text-gray-500 mt-1">Gunakan PIN ini untuk melakukan absensi Tap & Go di layar masuk Gudang.</p>
            @error('pin_gudang') <span class="text-red-500 text-sm font-bold">{{ $message }}</span> @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition">Simpan Data</button>
            <a href="{{ route('admin.karyawan.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-6 rounded-lg transition">Batal</a>
        </div>
    </form>
</div>

<script>
    function togglePinInput() {
        const role = document.getElementById('roleSelect').value;
        const pinContainer = document.getElementById('pinInputContainer');
        if (role === 'karyawan_gudang') {
            pinContainer.classList.remove('hidden');
        } else {
            pinContainer.classList.add('hidden');
        }
    }
    // Panggil saat halaman diload untuk mode edit
    document.addEventListener('DOMContentLoaded', togglePinInput);
</script>
@endsection