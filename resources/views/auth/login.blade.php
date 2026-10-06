<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi Terpadu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen items-center justify-center">
    
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Sistem Absensi Terpadu</h1>
            <p class="text-gray-500 text-sm mt-2">Login untuk mengakses Dashboard Karyawan, Supir, atau Admin</p>
        </div>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-center">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Alamat Email</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300" placeholder="nama@perusahaan.com" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300" placeholder="******" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded hover:bg-blue-700 transition">
                MASUK
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Untuk akses Gudang, silakan menuju <a href="{{ route('kiosk.index') }}" class="text-blue-600 font-bold hover:underline">Kiosk Absensi</a>.</p>
        </div>
    </div>

</body>
</html>