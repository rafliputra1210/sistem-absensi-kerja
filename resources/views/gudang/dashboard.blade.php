<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Gudang - Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100 min-h-screen relative shadow-2xl overflow-x-hidden sm:max-w-md mx-auto sm:border-x sm:border-gray-300 sm:bg-white flex flex-col">

    @php
        $today = now()->toDateString();
        $absenHariIni = \App\Models\Attendance::where('user_id', Auth::id())->where('date',$today)->first();
        
        $sudahMasuk =$absenHariIni !== null;
        $sudahKeluar = $sudahMasuk &&$absenHariIni->clock_out !== null;
    @endphp

    <!-- App Header (Tema Hijau/Emerald untuk Gudang) -->
    <header class="bg-emerald-600 text-white p-5 rounded-b-[2rem] shadow-lg relative z-10 shrink-0">
        <div class="flex justify-between items-center mb-2">
            <div>
                <h1 class="text-xl font-bold">Halo, {{ Auth::user()->name }}!</h1>
                <p class="text-emerald-100 text-xs mt-1"><i class="fas fa-boxes"></i> Divisi Gudang / Warehouse</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-emerald-700 p-2 rounded-full hover:bg-emerald-800 transition text-sm">
                    <i class="fas fa-sign-out-alt text-white"></i>
                </button>
            </form>
        </div>
    </header>

    <main class="flex-1 p-5 -mt-4 pt-8 overflow-y-auto">

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded-xl mb-4 text-sm font-bold text-center shadow-sm">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm font-bold text-center shadow-sm">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            </div>
        @endif

        @if($sudahKeluar)
            <!-- Selesai Shift -->
            <div class="bg-green-50 border-2 border-emerald-500 rounded-2xl p-6 text-center mt-6 shadow-md">
                <i class="fas fa-check-double text-5xl text-emerald-500 mb-4"></i>
                <h2 class="text-xl font-bold text-gray-800">Shift Selesai!</h2>
                <p class="text-gray-600 text-sm mt-2">Data absensi masuk dan keluar Anda telah tercatat. Selamat beristirahat.</p>
            </div>
        @else
            <!-- Peta Verifikasi Lokasi Gudang -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-2 mb-6">
                <div class="flex justify-between items-center px-2 py-1 mb-2">
                    <span class="text-xs font-bold text-gray-600">Verifikasi Lokasi Gudang</span>
                    <span id="gpsStatus" class="text-xs font-bold text-emerald-600"><i class="fas fa-spinner fa-spin"></i> Memeriksa...</span>
                </div>
                <div id="map" class="h-32 w-full rounded-xl z-0 bg-gray-200"></div>
            </div>

            @php
                $actionUrl = !$sudahMasuk ? route('gudang.clockin') : route('gudang.clockout');
                $buttonText = !$sudahMasuk ? 'ABSEN MASUK (SHIFT MULAI)' : 'ABSEN KELUAR (SHIFT SELESAI)';
                $buttonClass = !$sudahMasuk ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-orange-500 hover:bg-orange-600';
            @endphp

            <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" id="formGudang">
                @csrf
                <input type="hidden" name="{{ !$sudahMasuk ? 'lat_in' : 'lat_out' }}" id="lat">
                <input type="hidden" name="{{ !$sudahMasuk ? 'lng_in' : 'lng_out' }}" id="lng">

                <!-- Input Foto (Sama dengan Supir) -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Foto Wajah / Area Gudang
                    </label>
                    <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-emerald-300 rounded-xl cursor-pointer bg-emerald-50 hover:bg-emerald-100 overflow-hidden relative" id="photoArea">
                        <div class="flex flex-col items-center justify-center text-emerald-700 z-10" id="photoPlaceholder">
                            <i class="fas fa-camera text-4xl mb-2"></i>
                            <p class="text-sm font-semibold">Buka Kamera</p>
                        </div>
                        <img id="imagePreview" class="hidden absolute inset-0 w-full h-full object-cover z-20">
                        <input type="file" name="{{ !$sudahMasuk ? 'photo_in' : 'photo_out' }}" accept="image/*" capture="user" class="hidden" id="photoInput" required onchange="previewImage(event)" />
                    </label>
                </div>

                <button type="submit" id="btnAbsen" class="w-full text-white font-bold text-lg py-4 rounded-xl shadow-lg transition transform opacity-50 cursor-not-allowed {{ $buttonClass }}" disabled>
                    {{ $buttonText }}
                </button>
            </form>
        @endif

        <hr class="my-6 border-gray-200">

        <!-- Tombol Pengajuan Izin -->
        <button onclick="toggleModal('izinModal')" class="w-full bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 font-bold py-3 rounded-xl transition flex justify-center items-center gap-2 mb-4">
            <i class="fas fa-file-medical"></i> Pengajuan Izin / Cuti
        </button>
        
    </main>

    <!-- Modal Pengajuan Izin (Sama persis dengan Kantor) -->
    <div id="izinModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex flex-col justify-end sm:justify-center items-center">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 relative">
            <button onclick="toggleModal('izinModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Form Pengajuan Izin</h3>
            
            <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- (Field Izin sama dengan file dashboard kantor) -->
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Izin</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border" required>
                        <option value="sakit">Sakit</option>
                        <option value="cuti">Cuti</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mulai</label>
                        <input type="date" name="start_date" class="w-full border-gray-300 rounded-lg py-2 px-3 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sampai</label>
                        <input type="date" name="end_date" class="w-full border-gray-300 rounded-lg py-2 px-3 border" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Alasan</label>
                    <textarea name="reason" rows="2" class="w-full border-gray-300 rounded-lg py-2 px-3 border" required></textarea>
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Lampiran Bukti</label>
                    <input type="file" name="attachment" class="w-full text-sm border p-1 rounded-lg">
                </div>
                <button type="submit" class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-emerald-700 transition">Kirim Pengajuan</button>
            </form>
        </div>
    </div>

    <!-- Script Kamera & GPS -->
    <script>
        function toggleModal(modalID) { document.getElementById(modalID).classList.toggle('hidden'); }

        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('photoPlaceholder').classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        @if(!$sudahKeluar)
        var map = L.map('map').setView([-8.1325, 112.5694], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;
                    document.getElementById('gpsStatus').innerHTML = `<span class="text-emerald-700 font-bold"><i class="fas fa-check-circle"></i> Selesai</span>`;

                    map.setView([lat, lng], 17);
                    if(marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map).bindPopup("Lokasi Gudang Terdeteksi").openPopup();

                    let btn = document.getElementById('btnAbsen');
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                }, 
                function(error) {
                    document.getElementById('gpsStatus').innerHTML = `<span class="text-red-600"><i class="fas fa-times-circle"></i> Gagal GPS</span>`;
                }, 
                { enableHighAccuracy: true, timeout: 5000 }
            );
        }
        @endif
    </script>
</body>
</html>