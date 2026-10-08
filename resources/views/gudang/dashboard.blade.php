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

                <!-- Input Kamera Live & Bukti Foto -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-gray-700 text-sm font-bold">
                            Foto Bukti (Wajah / Area Gudang)
                        </label>
                        <span id="cameraStatusBadge" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 flex items-center gap-1">
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
                                <i class="fas fa-camera text-2xl text-emerald-400"></i>
                            </div>
                            <p class="text-sm font-bold text-gray-100">Kamera Belum Aktif</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-[220px]">Aktifkan kamera untuk selfie atau foto area gudang</p>
                            <button type="button" id="btnStartCamera" onclick="startCamera()" class="mt-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition flex items-center gap-2">
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
                                <button type="button" onclick="takeSnapshot()" class="bg-white hover:bg-gray-100 text-emerald-600 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center border-4 border-emerald-500/40 transition transform active:scale-90" title="Jepret Foto">
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
                        <input type="file" name="{{ !$sudahMasuk ? 'photo_in' : 'photo_out' }}" id="photoFileInput" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-gray-200 rounded-xl p-1 bg-gray-50" onchange="handleFileSelect(event)">
                    </div>
                </div>

                @if($sudahMasuk)
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Catatan Tugas / Serah Terima Shift (Opsional)</label>
                    <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-xl shadow-sm py-2 px-3 border focus:ring-emerald-500 text-sm" placeholder="Contoh: Stok opname rak C selesai, shift diserahkan..."></textarea>
                </div>
                @endif

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
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }

                currentStream = stream;
                cameraVideo.srcObject = stream;
                cameraVideo.classList.remove('hidden');
                imagePreview.classList.add('hidden');
                cameraPlaceholder.classList.add('hidden');
                cameraActiveControls.classList.remove('hidden');
                cameraRetakeControls.classList.add('hidden');

                cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center gap-1';
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

                    photoBase64.value = '';
                    hasPhoto = true;

                    cameraStatusBadge.className = 'text-[11px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 flex items-center gap-1';
                    cameraStatusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Foto Terpilih';

                    updateSubmitButton();
                };
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

                    hasGps = true;
                    updateSubmitButton();
                }, 
                function(error) {
                    document.getElementById('gpsStatus').innerHTML = `<span class="text-red-600"><i class="fas fa-times-circle"></i> Gagal GPS</span>`;
                    hasGps = false;
                    updateSubmitButton();
                }, 
                { enableHighAccuracy: true, timeout: 5000 }
            );
        }
        @endif
    </script>
</body>
</html>