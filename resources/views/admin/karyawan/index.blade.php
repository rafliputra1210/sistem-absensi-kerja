<!-- Asumsi Anda menggunakan layout utama yang mencakup Sidebar Admin -->
@extends('layouts.admin') 

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Data Master Karyawan</h1>
    <a href="{{ route('admin.karyawan.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2">
        <span>+</span> Tambah Karyawan Baru
    </a>
</div>

@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
    {{ session('success') }}
</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
                <th class="p-4 font-bold text-gray-600">Nama</th>
                <th class="p-4 font-bold text-gray-600">Email</th>
                <th class="p-4 font-bold text-gray-600">Role / Divisi</th>
                <th class="p-4 font-bold text-gray-600">PIN Gudang</th>
                <th class="p-4 font-bold text-gray-600 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawans as $k)
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="p-4 text-gray-800 font-medium">{{ $k->name }}</td>
                <td class="p-4 text-gray-600">{{ $k->email }}</td>
                <td class="p-4">
                    @if($k->role === 'karyawan_kantor')
                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">Kantor</span>
                    @elseif($k->role === 'supir')
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">Supir</span>
                    @else
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">Gudang</span>
                    @endif
                </td>
                <td class="p-4 text-gray-600 font-mono">{{ $k->pin_gudang ?? '-' }}</td>
                <td class="p-4 flex justify-center gap-2">
                    <a href="{{ route('admin.karyawan.edit', $k->id) }}" class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm font-bold">Edit</a>
                    <form action="{{ route('admin.karyawan.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Hapus karyawan ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm font-bold">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-gray-500">Belum ada data karyawan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection