<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Gudang - Tap & Go</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-900 text-white h-screen flex overflow-hidden">

    <!-- Kolom Kiri: Jam & Info -->
    <div class="w-1/2 flex flex-col justify-center items-center bg-gray-800 border-r border-gray-700 p-10">
        <img src="https://ui-avatars.com/api/?name=Warehouse&background=4F46E5&color=fff" class="w-24 rounded-full mb-6 shadow-lg shadow-indigo-500/50">
        <h1 class="text-4xl font-bold text-gray-300 tracking-wider mb-2">SISTEM ABSENSI GUDANG</h1>
        <div id="kioskClock" class="text-8xl font-black text-indigo-500 drop-shadow-lg font-mono">00:00:00</div>
        <div id="kioskDate" class="text-2xl mt-4 text-gray-400">Senin, 1 Januari 2024</div>
    </div>

    <!-- Kolom Kanan: Virtual Numpad (Touch Friendly) -->
    <div class="w-1/2 flex flex-col justify-center items-center p-10">
        <p class="text-2xl font-semibold mb-6">Masukkan PIN Karyawan</p>
        
        <!-- Input Tampilan (Readonly) -->
        <div class="w-72 bg-gray-700 h-16 rounded-xl flex items-center justify-center text-4xl tracking-[1em] font-black border-2 border-indigo-500 mb-8 shadow-inner" id="pinDisplay">
            <!-- Asterisk pin akan muncul disini -->
        </div>

        <input type="hidden" id="pinInput" value="">

        <!-- CSS Grid Numpad -->
        <div class="grid grid-cols-3 gap-4 w-72">
            <!-- Generate Numpad 1-9 -->
            <script>
                for(let i = 1; i <= 9; i++) {
                    document.write(`<button onclick="addPin(${i})" class="bg-gray-800 hover:bg-indigo-600 active:bg-indigo-700 text-3xl font-bold py-6 rounded-xl shadow transition transform active:scale-95">${i}</button>`);
                }
            </script>
            <button onclick="clearPin()" class="bg-red-600 hover:bg-red-700 text-lg font-bold py-6 rounded-xl shadow transition active:scale-95">CLEAR</button>
            <button onclick="addPin(0)" class="bg-gray-800 hover:bg-indigo-600 text-3xl font-bold py-6 rounded-xl shadow transition active:scale-95">0</button>
            <button onclick="submitPin()" class="bg-green-600 hover:bg-green-700 text-lg font-bold py-6 rounded-xl shadow transition active:scale-95">ENTER</button>
        </div>
    </div>

    <script>
        // Jam Kiosk
        setInterval(() => {
            const now = new Date();
            document.getElementById('kioskClock').innerText = now.toLocaleTimeString('id-ID');
            document.getElementById('kioskDate').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }, 1000);

        // Numpad Logic
        let currentPin = "";
        const pinDisplay = document.getElementById('pinDisplay');

        function addPin(num) {
            if(currentPin.length < 6) { // Asumsi PIN 6 digit
                currentPin += num;
                updateDisplay();
            }
        }

        function clearPin() {
            currentPin = "";
            updateDisplay();
        }

        function updateDisplay() {
            // Tampilkan bullet '•' sebanyak jumlah PIN
            pinDisplay.innerText = "•".repeat(currentPin.length);
        }

        function submitPin() {
            if(currentPin.length === 0) return;

            // Simulasi Proses AJAX dengan SweetAlert2 (Canggih)
            Swal.fire({
                title: 'Memproses Absensi...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            // Ganti URL ini dengan {{ route('kiosk.process') }} saat implementasi nyata
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Absen Berhasil!',
                    text: 'Selamat bekerja, Karyawan Gudang.',
                    timer: 3000,
                    showConfirmButton: false,
                    background: '#1f2937', // Cocok dengan tema dark Kiosk
                    color: '#fff'
                }).then(() => {
                    clearPin(); // Auto clear setelah 3 detik
                });
            }, 1000); // Simulasi delay network
        }
    </script>
</body>
</html>