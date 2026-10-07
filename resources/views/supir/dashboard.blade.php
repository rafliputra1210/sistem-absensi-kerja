<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Supir - Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Peta Leaflet JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100 min-h-screen relative shadow-2xl overflow-x-hidden sm:max-w-md mx-auto sm:border-x sm:border-gray-300 sm:bg-white">

    @php
        // Cek status absensi hari ini
        $today = now()->toDateString();
        $absenHariIni = \App\Models\Attendance::where('user_id', Auth::id())->where('date',$today)->first();
        
        $sudahMasuk =$absenHariIni !== null;
        $sudahKeluar = $sudahMasuk &&$absenHariIni->clock_out !== null;
    @endphp

    <!-- App Header -->
    <header class="bg-blue-700 text-white p-5 rounded-b-[2rem] shadow-lg relative z-10">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold">Halo, {{ Auth::user()->name }}!</h1>
                <p class="text-blue-200 text-xs mt-1"><i class="fas fa-truck"></i> Divisi Supir Lapangan</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-blue-800 p-2 rounded-full hover:bg-red-500 transition text-sm">
                    <i class="fas fa-power-off text-white"></i>
                </button>
            </form>
        </div>
    </header>

    <main class="p-5 -mt-4 pt-8">

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
            <!-- Tampilan Jika Sudah Selesai Tugas -->
            <div class="bg-green-50 border-2 border-green-500 rounded-2xl p-6 text-center mt-10 shadow-md">
                <i class="fas fa-flag-checkered text-5xl text-green-500 mb-4"></i>
                <h2 class="text-xl font-bold text-gray-800">Tugas Selesai!</h2>
                <p class="text-gray-600 text-sm mt-2">Anda telah menyelesaikan absensi masuk dan keluar untuk hari ini. Silakan beristirahat.</p>
            </div>
        @else
            <!-- Live Map Preview GPS -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-2 mb-6">
                <div class="flex justify-between items-center px-2 py-1 mb-2">
                    <span class="text-xs font-bold text-gray-600">Status Lokasi (GPS)</span>
                    <span id="gpsStatus" class="text-xs font-bold text-red-500"><i class="fas fa-spinner fa-spin"></i> Mencari Lokasi...</span>
                </div>
                <div id="map" class="h-40 w-full rounded-xl z-0 bg-gray-200 flex items-center justify-center relative"></div>
            </div>

            <!-- Form Dinamis: Jika belum masuk, tampilkan Clock IN. Jika sudah, tampilkan Clock OUT -->
            @php
                $actionUrl = !$sudahMasuk ? route('supir.clockin') : route('supir.clockout');
                $buttonText = !$sudahMasuk ? 'ABSEN MASUK (START)' : 'ABSEN KELUAR & LAPORAN';
                $buttonClass = !$sudahMasuk ? 'from-blue-500 to-blue-700' : 'from-red-500 to-red-700';
            @endphp

            <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" id="formSupir">
                @csrf
                <!-- Input Koordinat Tersembunyi -->
                <input type="hidden" name="{{ !$sudahMasuk ? 'lat_in' : 'lat_out' }}" id="lat">
                <input type="hidden" name="{{ !$sudahMasuk ? 'lng_in' : 'lng_out' }}" id="lng">

                <!-- Fitur Kamera Langsung & Unggah Foto -->
                <div class="mb-5 bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-gray-800 text-sm font-bold">
                            Foto Bukti {{ !$sudahMasuk ? 'Berangkat' : 'Selesai' }}
                        </label>
                        <span id="cameraStatusBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                            <i class="fas fa-camera"></i> Belum ada foto
                        </span>
                    </div>

                    <!-- Viewport Kamera / Preview -->
                    <div class="relative w-full h-64 bg-slate-900 rounded-xl overflow-hidden shadow-inner flex flex-col items-center justify-center border border-gray-300">
                        <!-- Video Stream Langsung -->
                        <video id="cameraVideo" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>

                        <!-- Gambar Hasil Jepretan -->
                        <img id="imagePreview" class="w-full h-full object-cover hidden" alt="Preview Foto">

                        <!-- Canvas Hidden untuk capture snapshot -->
                        <canvas id="cameraCanvas" class="hidden"></canvas>

                        <!-- Placeholder / Tombol Start Kamera -->
                        <div id="cameraPlaceholder" class="flex flex-col items-center justify-center p-4 text-center z-10 text-white">
                            <div class="w-14 h-14 rounded-full bg-white/10 flex items-center justify-center mb-3">
                                <i class="fas fa-camera text-2xl text-blue-400"></i>
                            </div>
                            <p class="text-sm font-bold text-gray-100">Kamera Belum Aktif</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-[220px]">Aktifkan kamera untuk selfie atau foto area armada Anda</p>
                            <button type="button" id="btnStartCamera" onclick="startCamera()" class="mt-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-video"></i> Aktifkan Kamera
                            </button>
                        </div>

                        <!-- Kontrol Kamera saat Aktif (Overlay) -->
                        <div id="cameraActiveControls" class="absolute inset-0 flex flex-col justify-between p-3 hidden pointer-events-none z-20">
                            <div class="flex justify-end pointer-events-auto">
                                <button type="button" onclick="switchCamera()" class="bg-black/60 hover:bg-black/80 text-white text-xs px-3 py-1.5 rounded-full backdrop-blur-sm transition flex items-center gap-1.5 shadow">
                                    <i class="fas fa-sync-alt"></i> Ganti Kamera
                                </button>
                            </div>
                            <div class="flex justify-center pb-1 pointer-events-auto">
                                <button type="button" onclick="takeSnapshot()" class="bg-white hover:bg-gray-100 text-blue-600 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center border-4 border-blue-500/40 transition transform active:scale-90" title="Jepret Foto">
                                    <i class="fas fa-circle text-2xl"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Kontrol Setelah Foto Diambil (Overlay) -->
                        <div id="cameraRetakeControls" class="absolute bottom-3 right-3 hidden z-20">
                            <button type="button" onclick="retakePhoto()" class="bg-black/75 hover:bg-black/90 text-white text-xs font-bold py-2 px-3.5 rounded-xl backdrop-blur-sm shadow transition flex items-center gap-1.5">
                                <i class="fas fa-redo"></i> Foto Ulang
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Input Base64 -->
                    <input type="hidden" name="photo_base64" id="photoBase64">

                    <!-- Fallback / Opsi Alternatif: Unggah File / Galeri -->
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <label class="block text-[11px] font-semibold text-gray-500 mb-1">
                            Atau pilih foto dari galeri / file perangkat:
                        </label>
                        <input type="file" name="{{ !$sudahMasuk ? 'photo_in' : 'photo_out' }}" id="photoFileInput" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-xl p-1 bg-gray-50" onchange="handleFileSelect(event)">
                    </div>
                </div>

                <!-- Input Catatan Khusus Saat Clock Out -->
                @if($sudahMasuk)
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Logbook / Laporan Tugas</label>
                    <textarea name="notes" rows="3" class="w-full border-gray-300 rounded-xl shadow-sm py-2 px-3 border focus:ring-blue-500 text-sm" placeholder="Contoh: Bongkar muat selesai di Gudang B, kondisi aman..." required></textarea>
                </div>
                @endif

                <!-- Tombol Submit -->
                <button type="submit" id="btnAbsen" class="w-full bg-gradient-to-r {{ $buttonClass }} text-white font-bold text-lg py-4 rounded-xl shadow-lg hover:scale-[1.02] transition transform opacity-50 cursor-not-allowed" disabled>
                    {{ $buttonText }} <i class="fas fa-paper-plane ml-1"></i>
                </button>
            </form>
        @endif
        
    </main>

    <script>
        let currentFacingMode = 'user'; // 'user' (depan) atau 'environment' (belakang)
        let currentStream = null;
        let hasGps = false;
        let hasPhoto = false;

        const cameraVideo = document.getElementById('cameraVideo');
        const imagePreview = document.getElementById('imagePreview');
        const cameraCanvas = document.getElementById('cameraCanvas');
        const cameraPlaceholder = document.getElementById('cameraPlaceholder');
        const cameraActiveControls = document.getElementById('cameraActiveControls');
        const cameraRetakeControls = document.getElementById('cameraRetakeControls');
        const photoBase64 = document.getElementById('photoBase64');
        const photoFileInput = document.getElementById('photoFileInput');
        const cameraStatusBadge = document.getElementById('cameraStatusBadge');
        const btnAbsen = document.getElementById('btnAbsen');

        // Update status tombol submit absensi
        function updateSubmitButton() {
            if (!btnAbsen) return;
            if (hasGps && hasPhoto) {
                btnAbsen.disabled = false;
                btnAbsen.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnAbsen.disabled = true;
                btnAbsen.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // --- 1. FITUR KAMERA LIVE ---
        async function startCamera(facing = currentFacingMode) {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }

            try {
                let constraints = {
                    video: {
                        facingMode: facing,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    },
                    audio: false
                };

                let stream;
                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                } catch (e) {
                    // Fallback jika facingMode spesifik tidak didukung (misal webcam laptop)
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }

                currentStream = stream;
                cameraVideo.srcObject = stream;
                cameraVideo.classList.remove('hidden');
                imagePreview.classList.add('hidden');
                cameraPlaceholder.classList.add('hidden');
                cameraActiveControls.classList.remove('hidden');
                cameraRetakeControls.classList.add('hidden');

                cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 flex items-center gap-1';
                cameraStatusBadge.innerHTML = '<i class="fas fa-video"></i> Kamera Aktif';

            } catch (err) {
                console.error('Kamera gagal diakses:', err);
                alert('Kamera tidak dapat diakses. Pastikan izin kamera browser telah diberikan.');
                cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700 flex items-center gap-1';
                cameraStatusBadge.innerHTML = '<i class="fas fa-exclamation-circle"></i> Izin Ditolak';
            }
        }

        async function switchCamera() {
            currentFacingMode = (currentFacingMode === 'user') ? 'environment' : 'user';
            await startCamera(currentFacingMode);
        }

        function takeSnapshot() {
            if (!cameraVideo || !cameraVideo.videoWidth) return;

            cameraCanvas.width = cameraVideo.videoWidth;
            cameraCanvas.height = cameraVideo.videoHeight;
            const ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);

            const dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.85);
            photoBase64.value = dataUrl;

            imagePreview.src = dataUrl;
            imagePreview.classList.remove('hidden');
            cameraVideo.classList.add('hidden');
            cameraActiveControls.classList.add('hidden');
            cameraRetakeControls.classList.remove('hidden');

            // Hentikan stream kamera untuk menghemat daya
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }

            hasPhoto = true;
            cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1';
            cameraStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Foto Siap';

            updateSubmitButton();
        }

        function retakePhoto() {
            photoBase64.value = '';
            imagePreview.classList.add('hidden');
            cameraRetakeControls.classList.add('hidden');
            hasPhoto = false;

            cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 flex items-center gap-1';
            cameraStatusBadge.innerHTML = '<i class="fas fa-camera"></i> Belum ada foto';

            updateSubmitButton();
            startCamera(currentFacingMode);
        }

        function handleFileSelect(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (currentStream) {
                        currentStream.getTracks().forEach(track => track.stop());
                        currentStream = null;
                    }

                    cameraPlaceholder.classList.add('hidden');
                    cameraVideo.classList.add('hidden');
                    cameraActiveControls.classList.add('hidden');

                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                    cameraRetakeControls.classList.remove('hidden');

                    photoBase64.value = ''; // Gunakan file dari input file
                    hasPhoto = true;

                    cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1';
                    cameraStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Foto Terpilih';

                    updateSubmitButton();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- 2. GPS & PETA LEAFLET ---
        @if(!$sudahKeluar)
        // Inisialisasi Peta (Default koordinat Kepanjen, Malang)
        var map = L.map('map').setView([-8.1325, 112.5694], 14); 
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker;

        // Minta akses GPS
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    
                    // Isi input hidden form
                    document.getElementById('lat').value = lat;
                    document.getElementById('lng').value = lng;
                    
                    // Update Status Teks
                    document.getElementById('gpsStatus').innerHTML = `<span class="text-green-600 font-bold"><i class="fas fa-check-circle"></i> Lokasi Terkunci</span>`;

                    // Pindahkan Peta ke koordinat saat ini
                    map.setView([lat, lng], 16);
                    if(marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map).bindPopup("Lokasi Anda Saat Ini").openPopup();

                    hasGps = true;
                    updateSubmitButton();
                }, 
                function(error) {
                    let msg = "Gagal. Izinkan Akses Lokasi!";
                    if(error.code === 1) msg = "Akses Lokasi Ditolak Pengguna.";
                    document.getElementById('gpsStatus').innerHTML = `<span class="text-red-600 font-bold"><i class="fas fa-times-circle"></i> ${msg}</span>`;
                    alert("Aplikasi memerlukan akses lokasi (GPS) untuk absensi.");
                }, 
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            alert("Browser Anda tidak mendukung Geolocation.");
        }

        // Validasi saat form dikirim
        const formSupir = document.getElementById('formSupir');
        if (formSupir) {
            formSupir.addEventListener('submit', function(e) {
                const lat = document.getElementById('lat').value;
                const lng = document.getElementById('lng').value;
                const pBase64 = photoBase64.value;
                const pFile = photoFileInput && photoFileInput.files.length > 0;

                if (!lat || !lng) {
                    e.preventDefault();
                    alert("Lokasi GPS belum terkunci. Pastikan izin lokasi aktif.");
                    return false;
                }

                if (!pBase64 && !pFile) {
                    e.preventDefault();
                    alert("Foto bukti absensi wajib diambil atau diunggah.");
                    return false;
                }
            });
        }
        @endif
    </script>
</body>
</html>