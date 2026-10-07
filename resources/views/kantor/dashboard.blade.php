<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Absensi Karyawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-200 min-h-screen flex items-center justify-center sm:p-4">

    <!-- Mobile Container -->
    <div class="bg-white w-full max-w-md h-screen sm:h-auto sm:rounded-3xl shadow-2xl flex flex-col relative overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-600 text-white p-6 rounded-b-[2.5rem] shadow-md relative z-10">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-xl font-bold">Halo, {{ Auth::user()->name ?? 'Karyawan' }}!</h2>
                    <p class="text-blue-200 text-sm">Divisi Kantor (WFO)</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-700 p-2 rounded-full hover:bg-blue-800 transition">
                        <i class="fas fa-sign-out-alt text-white"></i>
                    </button>
                </form>
            </div>
            <!-- Jam Digital -->
            <div class="text-center mt-2">
                <div id="jamDigital" class="text-5xl font-black tracking-wider drop-shadow-md">00:00:00</div>
                <div class="text-blue-100 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 flex flex-col pt-8 overflow-y-auto">
            
            <!-- Notifikasi Session -->
            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-3 rounded-xl mb-4 text-sm text-center font-bold">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-3 rounded-xl mb-4 text-sm text-center font-bold">{{ session('error') }}</div>
            @endif

            @php
                $todayAttendance = \App\Models\Attendance::where('user_id', Auth::id())->where('date', now()->toDateString())->first();
            @endphp

            @if($todayAttendance)
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-4 text-center">
                    <p class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-2">Status Absensi Hari Ini ({{ now()->translatedFormat('d M Y') }})</p>
                    <div class="flex justify-around items-center">
                        <div>
                            <span class="text-xs text-gray-500 block">Jam Masuk</span>
                            <span class="text-base font-bold text-gray-800 font-mono">{{ $todayAttendance->clock_in ? \Carbon\Carbon::parse($todayAttendance->clock_in)->format('H:i:s') : '--:--' }}</span>
                        </div>
                        <div class="h-8 w-px bg-blue-200"></div>
                        <div>
                            <span class="text-xs text-gray-500 block">Jam Keluar</span>
                            <span class="text-base font-bold text-gray-800 font-mono">{{ $todayAttendance->clock_out ? \Carbon\Carbon::parse($todayAttendance->clock_out)->format('H:i:s') : '--:--' }}</span>
                        </div>
                        <div class="h-8 w-px bg-blue-200"></div>
                        <div>
                            <span class="text-xs text-gray-500 block">Status</span>
                            @if($todayAttendance->status === 'hadir')
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Hadir</span>
                            @elseif($todayAttendance->status === 'telat')
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700">Telat</span>
                            @else
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 capitalize">{{ $todayAttendance->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <p class="text-center text-gray-500 text-sm mb-4">Validasi IP Wi-Fi & Selfie Kamera</p>

            <!-- Area Kamera -->
            <div class="relative bg-gray-100 rounded-2xl overflow-hidden mb-6 h-64 flex flex-col items-center justify-center shadow-inner border-2 border-dashed border-gray-300">
                <video id="webcam" autoplay playsinline class="w-full h-full object-cover hidden absolute inset-0 z-10"></video>
                <button id="btnStartCamera" class="bg-white text-blue-600 border border-blue-200 px-5 py-3 rounded-xl font-bold shadow-sm hover:bg-blue-50 transition z-20 flex items-center gap-2">
                    <i class="fas fa-camera"></i> Buka Kamera Selfie
                </button>
                <canvas id="canvas" class="hidden"></canvas>
            </div>

            <!-- Form Absensi -->
            <form action="{{ route('kantor.clockin') }}" method="POST" id="formAbsen" class="mb-4">
                @csrf
                <input type="hidden" name="photo_base64" id="photoData">
                <div class="grid grid-cols-2 gap-4">
                    @if($todayAttendance)
                        <button type="button" class="bg-gray-100 text-gray-400 font-bold py-4 px-2 rounded-2xl border border-gray-200 shadow-sm transition text-sm sm:text-base flex flex-col items-center gap-1 cursor-not-allowed" disabled>
                            <i class="fas fa-check-circle text-xl text-green-500"></i> Sudah Masuk
                        </button>
                    @else
                        <button type="button" id="btnClockIn" class="bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-2 rounded-2xl shadow-lg opacity-50 cursor-not-allowed transition text-sm sm:text-base flex flex-col items-center gap-1" disabled>
                            <i class="fas fa-sign-in-alt text-xl"></i> Absen Masuk
                        </button>
                    @endif

                    @if($todayAttendance && $todayAttendance->clock_out)
                        <button type="button" class="bg-gray-100 text-gray-400 font-bold py-4 px-2 rounded-2xl border border-gray-200 shadow-sm transition text-sm sm:text-base flex flex-col items-center gap-1 cursor-not-allowed" disabled>
                            <i class="fas fa-check-circle text-xl text-blue-500"></i> Sudah Keluar
                        </button>
                    @elseif($todayAttendance)
                        <button type="button" onclick="document.getElementById('formAbsenOut')?.scrollIntoView({behavior: 'smooth'})" class="bg-red-500 hover:bg-red-600 text-white font-bold py-4 px-2 rounded-2xl shadow-lg transition text-sm sm:text-base flex flex-col items-center gap-1">
                            <i class="fas fa-sign-out-alt text-xl"></i> Absen Keluar
                        </button>
                    @else
                        <button type="button" class="bg-gray-100 text-gray-400 font-bold py-4 px-2 rounded-2xl border border-gray-200 shadow-sm transition text-sm sm:text-base flex flex-col items-center gap-1 cursor-not-allowed" title="Lakukan absen masuk terlebih dahulu" disabled>
                            <i class="fas fa-sign-out-alt text-xl"></i> Absen Keluar
                        </button>
                    @endif
                </div>
            </form>
            
            @if($todayAttendance && !$todayAttendance->clock_out)
            <form action="{{ route('kantor.clockout') }}" method="POST" id="formAbsenOut" class="mt-3 bg-gray-50 p-4 rounded-2xl border border-gray-200 text-left shadow-sm">
                @csrf
                <label class="flex items-center gap-2 text-sm font-bold text-gray-700 cursor-pointer">
                    <input type="checkbox" name="claim_overtime" value="1" onchange="document.getElementById('boxAlasanLembur').classList.toggle('hidden')" class="w-4 h-4 rounded text-blue-600">
                    <span>Klaim Lembur (Pulang lewat jam kerja)</span>
                </label>

                <div id="boxAlasanLembur" class="hidden mt-2">
                    <input type="text" name="overtime_reason" placeholder="Tuliskan tugas lembur yang dikerjakan..." class="w-full text-sm border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin melakukan absensi keluar sekarang?')" class="w-full mt-3 bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded-xl shadow transition flex items-center justify-center gap-2">
                    <i class="fas fa-sign-out-alt"></i> Absen Keluar Sekarang
                </button>
            </form>
            @endif
            <hr class="my-4 border-gray-200">

            <!-- Tombol Buka Modal Izin -->
            <button onclick="toggleModal('izinModal')" class="w-full bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 font-bold py-3 rounded-xl transition flex justify-center items-center gap-2">
                <i class="fas fa-file-medical"></i> Pengajuan Izin / Cuti
            </button>
        </div>
    </div>

    <!-- Modal Pengajuan Izin (Bottom Sheet style on Mobile) -->
    <div id="izinModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden flex flex-col justify-end sm:justify-center items-center transition-opacity">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl p-6 transform transition-transform translate-y-0 relative">
            
            <!-- Close Button -->
            <button onclick="toggleModal('izinModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>

            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Form Pengajuan Izin</h3>
            
            <form action="{{ route('izin.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Izin</label>
                    <select name="type" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border focus:ring-blue-500" required>
                        <option value="sakit">Sakit</option>
                        <option value="cuti">Cuti</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mulai</label>
                        <input type="date" name="start_date" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Sampai</label>
                        <input type="date" name="end_date" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Alasan</label>
                    <textarea name="reason" rows="2" class="w-full border-gray-300 rounded-lg shadow-sm py-2 px-3 border" required placeholder="Jelaskan alasan Anda..."></textarea>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Lampiran (Surat Dokter/Bukti)</label>
                    <input type="file" name="attachment" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-gray-200 rounded-lg p-1">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition">
                    Kirim Pengajuan
                </button>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // 1. Jam Digital
        setInterval(() => {
            document.getElementById('jamDigital').innerText = new Date().toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);

        // 2. Kamera Selfie
        const video = document.getElementById('webcam');
        const btnStart = document.getElementById('btnStartCamera');
        const btnClockIn = document.getElementById('btnClockIn');
        const canvas = document.getElementById('canvas');
        const photoData = document.getElementById('photoData');

        btnStart.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
                video.srcObject = stream;
                video.classList.remove('hidden');
                btnStart.classList.add('hidden');
                
                // Aktifkan tombol masuk
                btnClockIn.classList.remove('opacity-50', 'cursor-not-allowed');
                btnClockIn.disabled = false;
            } catch (err) {
                alert("Kamera tidak dapat diakses. Pastikan izin browser diberikan.");
            }
        });

        btnClockIn.addEventListener('click', () => {
            if(!video.srcObject) return;
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            photoData.value = canvas.toDataURL('image/jpeg');
            document.getElementById('formAbsen').submit();
        });

        // 3. Toggle Modal Izin
        function toggleModal(modalID) {
            const modal = document.getElementById(modalID);
            modal.classList.toggle('hidden');
        }
    </script>
</body>
</html>