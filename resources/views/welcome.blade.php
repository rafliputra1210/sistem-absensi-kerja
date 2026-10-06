<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Absensi Terpadu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-center items-center relative overflow-hidden">

    <!-- Background Decoration -->
    <div class="absolute top-0 left-0 w-full h-96 bg-blue-600 rounded-b-[4rem] shadow-lg z-0"></div>

    <div class="relative z-10 w-full max-w-6xl px-6">
        
        <!-- Header Text -->
        <div class="text-center mb-12 text-white">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">Sistem Absensi Terpadu</h1>
            <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto">
                Pilih portal masuk sesuai dengan divisi dan peran Anda untuk melakukan absensi hari ini.
            </p>
        </div>

        <!-- Grid Menu Portal -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- 1. Portal Karyawan Kantor -->
            <a href="{{ route('login') }}" class="group bg-white p-8 rounded-2xl shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 border-t-4 border-blue-500 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center text-4xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
                    💻
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Karyawan Kantor</h2>
                <p class="text-sm text-slate-500">Masuk untuk absensi dengan deteksi IP Wi-Fi & Kamera Selfie.</p>
            </a>

            <!-- 2. Portal Supir -->
            <a href="{{ route('login') }}" class="group bg-white p-8 rounded-2xl shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 border-t-4 border-yellow-500 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center text-4xl mb-6 group-hover:bg-yellow-500 group-hover:text-white transition">
                    🚚
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Supir Lapangan</h2>
                <p class="text-sm text-slate-500">Masuk untuk absensi berbasis Geolocation (GPS) dan Foto Kendaraan.</p>
            </a>

            <!-- 3. Portal Kiosk Gudang -->
            <a href="{{ route('kiosk.index') }}" class="group bg-white p-8 rounded-2xl shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 border-t-4 border-emerald-500 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center text-4xl mb-6 group-hover:bg-emerald-500 group-hover:text-white transition">
                    📦
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Kiosk Gudang</h2>
                <p class="text-sm text-slate-500">Portal Stand-by untuk absensi Tap & Go menggunakan PIN Karyawan.</p>
            </a>

            <!-- 4. Portal Admin / HR -->
            <a href="{{ route('login') }}" class="group bg-white p-8 rounded-2xl shadow-md hover:shadow-2xl transition transform hover:-translate-y-2 border-t-4 border-slate-800 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-slate-100 text-slate-800 rounded-full flex items-center justify-center text-4xl mb-6 group-hover:bg-slate-800 group-hover:text-white transition">
                    🏢
                </div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Admin / HRD</h2>
                <p class="text-sm text-slate-500">Masuk ke Panel Manajemen untuk pantau absensi, izin, & rekap gaji.</p>
            </a>

        </div>

        <!-- Footer -->
        <div class="text-center mt-12 text-slate-500 text-sm">
            &copy; {{ date('Y') }} Sistem Absensi Terpadu HRIS. Hak cipta dilindungi.
        </div>
    </div>

</body>
</html>