<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen Kantor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full text-center">
        <h2 class="text-2xl font-bold text-gray-800">Dashboard Karyawan</h2>
        <p class="text-gray-500 mb-6">Validasi IP WiFi & Selfie Kamera</p>

        <!-- Jam Digital -->
        <div class="text-5xl font-mono font-bold text-indigo-600 mb-6" id="jamDigital">00:00:00</div>

        <!-- Area Kamera -->
        <div class="relative bg-gray-200 rounded-lg overflow-hidden mb-6 h-64 flex items-center justify-center shadow-inner">
            <video id="webcam" autoplay playsinline class="w-full h-full object-cover hidden"></video>
            <button id="btnStartCamera" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg font-semibold hover:bg-indigo-200 transition">
                📸 Buka Kamera Selfie
            </button>
            <canvas id="canvas" class="hidden"></canvas>
        </div>

        <form action="{{ route('kantor.clockin') }}" method="POST" id="formAbsen">
            @csrf
            <input type="hidden" name="photo_base64" id="photoData">
            
            <div class="grid grid-cols-2 gap-4">
                <button type="button" id="btnClockIn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg opacity-50 cursor-not-allowed transition" disabled>
                    Absen Masuk
                </button>
                <button type="button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition">
                    Absen Keluar
                </button>
            </div>
        </form>
    </div>

    <script>
        // Jam Digital Realtime
        setInterval(() => {
            document.getElementById('jamDigital').innerText = new Date().toLocaleTimeString('id-ID');
        }, 1000);

        // Akses Webcam WebRTC
        const video = document.getElementById('webcam');
        const btnStart = document.getElementById('btnStartCamera');
        const btnClockIn = document.getElementById('btnClockIn');
        const canvas = document.getElementById('canvas');
        const photoData = document.getElementById('photoData');

        btnStart.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.classList.remove('hidden');
                btnStart.classList.add('hidden');
                
                // Aktifkan tombol absen setelah kamera menyala
                btnClockIn.classList.remove('opacity-50', 'cursor-not-allowed');
                btnClockIn.disabled = false;
            } catch (err) {
                alert("Gagal mengakses kamera: " + err.message);
            }
        });

        // Tangkap gambar saat submit
        btnClockIn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            photoData.value = canvas.toDataURL('image/jpeg'); // Konversi ke Base64
            
            // Submit form
            document.getElementById('formAbsen').submit();
        });
    </script>
</body>
</html>