<!-- Meta Tag PWA -->
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#2563eb">
<link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<!-- Banner Install Aplikasi (Muncul otomatis di HP jika belum diinstal) -->
<div id="pwaInstallBanner" class="hidden fixed bottom-4 left-4 right-4 max-w-md mx-auto bg-slate-900 text-white p-4 rounded-2xl shadow-2xl z-50 flex items-center justify-between border border-slate-700">
    <div class="flex items-center gap-3">
        <div class="bg-blue-600 p-2.5 rounded-xl text-xl">📲</div>
        <div class="text-left">
            <p class="font-bold text-sm">Instal Aplikasi Absensi</p>
            <p class="text-xs text-slate-300">Akses lebih cepat dari layar utama HP</p>
        </div>
    </div>
    <button id="btnPwaInstall" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition">
        Instal
    </button>
</div>

<script>
    // 1. Registrasi Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('PWA Service Worker terdaftar'))
                .catch(err => console.error('Gagal registrasi SW:', err));
        });
    }

    // 2. Logika Custom Tombol Install PWA
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        const banner = document.getElementById('pwaInstallBanner');
        if (banner) banner.classList.remove('hidden');
    });

    document.addEventListener('DOMContentLoaded', () => {
        const btnInstall = document.getElementById('btnPwaInstall');
        if (btnInstall) {
            btnInstall.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        document.getElementById('pwaInstallBanner').classList.add('hidden');
                    }
                    deferredPrompt = null;
                }
            });
        }
    });
</script>