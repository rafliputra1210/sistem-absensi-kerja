<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel - HRIS Absensi' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    <!-- Sidebar Menu Navigasi -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col h-full shrink-0">
        <div class="p-6 font-bold text-2xl border-b border-slate-700 tracking-wider">
            HR PANEL
        </div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="block p-3 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 font-medium' : 'hover:bg-slate-800' }} rounded-lg transition">
                📊 Dashboard Monitor
            </a>
            <a href="{{ route('admin.karyawan.index') }}" class="block p-3 {{ request()->routeIs('admin.karyawan.*') ? 'bg-blue-600 font-medium' : 'hover:bg-slate-800' }} rounded-lg transition">
                👥 Data Master Karyawan
            </a>
            <a href="#" class="block p-3 hover:bg-slate-800 rounded-lg transition">
                📍 Pemeriksaan Supir
            </a>
            <a href="{{ route('admin.izin.index') }}" class="block p-3 {{ request()->routeIs('admin.izin.*') ? 'bg-blue-600 font-medium' : 'hover:bg-slate-800' }} rounded-lg transition flex justify-between items-center">
                <span>📝 Approval Izin</span>
                @php
                    $pendingCount = \App\Models\Leave::where('status', 'pending')->count();
                @endphp
                @if($pendingCount > 0)
                    <span class="bg-red-500 text-xs px-2 py-0.5 rounded-full font-bold">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.laporan.index') }}" class="block p-3 {{ request()->routeIs('admin.laporan.*') ? 'bg-blue-600 font-medium' : 'hover:bg-slate-800' }} rounded-lg transition">
                📑 Rekapitulasi Laporan
            </a>
            <a href="{{ route('admin.settings.index') }}" class="block p-3 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 font-medium' : 'hover:bg-slate-800' }} rounded-lg transition">
                ⚙️ Pengaturan Jam Kerja
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-left p-3 text-red-400 hover:bg-slate-800 rounded-lg transition">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Area Konten Utama -->
    <main class="flex-1 overflow-y-auto p-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
