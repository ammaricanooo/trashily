<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/seo_helper.php';

$deviceId = trim($_GET['device'] ?? 'PIRU-DEV-001');
$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';

// Jika customer sudah login dan bukan sedang mode preview, alihkan langsung ke ruang kontrol sesi
if (isset($_SESSION['user_id']) && !$isPreview) {
    header("Location: customer/piru.php?device=" . urlencode($deviceId));
    exit;
}

// Cek metadata perangkat di database
$device = null;
if (isset($conn)) {
    $stmt = $conn->prepare("SELECT nama_perangkat, lokasi, status, kapasitas_organik, kapasitas_anorganik, baterai, firmware_version FROM trashsmart_devices WHERE kode_perangkat = ?");
    $stmt->bind_param('s', $deviceId);
    $stmt->execute();
    $device = $stmt->get_result()->fetch_assoc();
}

$deviceName       = $device['nama_perangkat'] ?? 'PIRU Smart Bin #001';
$deviceLocation   = $device['lokasi'] ?? 'Area Utama / EcoHub Trashily';
$deviceStatus     = $device['status'] ?? 'online';
$kapasitasOrganik = intval($device['kapasitas_organik'] ?? 25);
$kapasitasAnorg   = intval($device['kapasitas_anorganik'] ?? 30);
$firmwareVersion  = $device['firmware_version'] ?? 'v2.0.0-YOLO';

$active_nav = 'trashsmart';
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    render_seo_tags([
        'title' => 'PIRU (Pilah Runtah) — Tong Sampah Pintar Berbasis Computer Vision & IoT | Trashily',
        'description' => 'PIRU (Pilah Runtah): Inovasi tong sampah pintar berbasis AI YOLO Multi-Class, sensor ultrasonik kompartemen ganda, dan reward poin instan dari Trashily Indonesia.',
        'keywords' => 'piru, pilah runtah, smart bin yolo, tong sampah pintar iot, bank sampah digital bogor, computer vision sampah, trashily piru',
        'canonical' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . '/piru.php',
        'breadcrumbs' => [
            'Beranda' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . '/',
            'PIRU Smart Bin' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . '/piru.php'
        ]
    ]);
    ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#006e2f'
                        },
                        deep: '#062816'
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace']
                    }
                }
            }
        }
    </script>
    <style>
        .hero-pattern {
            background-color: #0b1f13;
            background-image: radial-gradient(#15803d 0.75px, transparent 0.75px), radial-gradient(#15803d 0.75px, #0b1f13 0.75px);
            background-size: 30px 30px;
            background-position: 0 0, 15px 15px;
        }
        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.08); opacity: 1; }
        }
        .animate-pulse-slow {
            animation: pulse-slow 3s infinite ease-in-out;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans selection:bg-brand-500 selection:text-white">

    <!-- Shared Public Navbar -->
    <?php include __DIR__ . '/includes/public_navbar.php'; ?>

    <!-- HERO SECTION -->
    <section class="hero-pattern text-white pt-12 pb-20 md:pt-16 md:pb-28 relative overflow-hidden">
        <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 top-10 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 md:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                
                <!-- Left Column: Copy & Specs -->
                <div class="lg:col-span-7 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 mb-5">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Inovasi IoT &bull; Computer Vision AI
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-4">
                        PIRU <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-green-300">(Pilah Runtah)</span>
                    </h1>
                    
                    <p class="text-emerald-100/90 text-base md:text-lg max-w-2xl mb-8 leading-relaxed mx-auto lg:mx-0">
                        Tong sampah pintar masa depan dengan teknologi <b>Computer Vision YOLO Multi-Class</b>, dual sensor ultrasonik kompartemen fisik, timbangan otomatis, serta konversi poin instan ke akun Trashily Anda.
                    </p>

                    <!-- Hardware Spec Badges -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-2.5 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-xs font-semibold text-emerald-200 border border-white/15">
                            <i class="fa-solid fa-brain text-emerald-400"></i> YOLOv8 Vision
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-xs font-semibold text-emerald-200 border border-white/15">
                            <i class="fa-solid fa-wave-square text-emerald-400"></i> Dual JSN-SR04T Sensor
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-xs font-semibold text-emerald-200 border border-white/15">
                            <i class="fa-solid fa-microchip text-emerald-400"></i> ESP32 IoT Cloud
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/10 backdrop-blur-md text-xs font-semibold text-emerald-200 border border-white/15">
                            <i class="fa-solid fa-file-pdf text-rose-400"></i> Bukti Sesi PDF
                        </span>
                    </div>

                    <!-- Call To Actions -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                        <a href="auth/login.php?from=piru&device=<?= urlencode($deviceId) ?>" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-full bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-sm shadow-xl shadow-emerald-500/25 transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-right-to-bracket text-base"></i>
                            Masuk &amp; Mulai Memilah
                        </a>
                        <a href="#simulasi-piru" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-full border border-white/20 bg-white/5 hover:bg-white/15 text-white font-bold text-sm transition">
                            <i class="fa-solid fa-play text-emerald-400"></i>
                            Coba Simulasi AI
                        </a>
                    </div>
                </div>

                <!-- Right Column: Interactive Unit Card & QR Entry Box -->
                <div class="lg:col-span-5">
                    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-6 shadow-2xl relative overflow-hidden">
                        
                        <!-- Top Header Card -->
                        <div class="flex items-center justify-between pb-4 border-b border-white/15 mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center font-bold text-base shadow">
                                    <i class="fa-solid fa-microchip"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-white"><?= htmlspecialchars($deviceName) ?></h3>
                                    <p class="text-[11px] text-emerald-300 font-mono"><?= htmlspecialchars($deviceId) ?></p>
                                </div>
                            </div>

                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/25 text-emerald-300 border border-emerald-500/30">
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Online
                            </span>
                        </div>

                        <!-- Lokasi & Status Kompartemen -->
                        <div class="bg-slate-900/60 rounded-2xl p-4 border border-white/10 mb-5 space-y-3">
                            <div class="flex items-center justify-between text-xs text-slate-300">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-location-dot text-emerald-400"></i> Lokasi Unit:
                                </span>
                                <span class="font-bold text-white"><?= htmlspecialchars($deviceLocation) ?></span>
                            </div>

                            <!-- Kapasitas Organik -->
                            <div>
                                <div class="flex justify-between text-[11px] font-semibold text-emerald-300 mb-1">
                                    <span><i class="fa-solid fa-leaf"></i> Wadah Organik (+10 Pts)</span>
                                    <span><?= $kapasitasOrganik ?>%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-400 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $kapasitasOrganik) ?>%"></div>
                                </div>
                            </div>

                            <!-- Kapasitas Anorganik -->
                            <div>
                                <div class="flex justify-between text-[11px] font-semibold text-sky-300 mb-1">
                                    <span><i class="fa-solid fa-bottle-water"></i> Wadah Anorganik (+15 Pts)</span>
                                    <span><?= $kapasitasAnorg ?>%</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-sky-400 h-2 rounded-full transition-all duration-500" style="width: <?= min(100, $kapasitasAnorg) ?>%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Scanner Box -->
                        <div class="text-center bg-white rounded-2xl p-4 text-slate-800 shadow-inner">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                                <i class="fa-solid fa-qrcode text-emerald-600 mr-1"></i> Scan di Lokasi / Handphone
                            </div>
                            
                            <?php 
                                $scanUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . "/piru.php?device=" . urlencode($deviceId);
                                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=" . urlencode($scanUrl);
                            ?>
                            <img src="<?= $qrCodeUrl ?>" alt="QR PIRU Smart Bin" class="mx-auto w-36 h-36 rounded-xl border border-slate-200 p-1 mb-2">
                            
                            <p class="text-[11px] text-slate-500">
                                Arahkan kamera HP ke QR Code ini untuk menghubungkan akun ke unit <b><?= htmlspecialchars($deviceId) ?></b>
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CARA KERJA & LANGKAH PEMILAHAN -->
    <section class="py-16 md:py-24 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-brand-600 font-bold text-xs uppercase tracking-widest bg-brand-50 px-3 py-1 rounded-full">
                    Mudah &amp; Otomatis
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight mt-3 mb-4">
                    4 Langkah Mendapatkan Poin dari PIRU
                </h2>
                <p class="text-slate-600 text-sm md:text-base">
                    PIRU dirancang tanpa tombol rumit. Cukup letakkan sampah Anda, kecerdasan buatan dan IoT akan memvalidasi dan memproses reward secara real-time.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Step 1 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 hover:border-brand-500/50 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 font-extrabold text-lg flex items-center justify-center mb-4">
                        1
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Pindai QR Perangkat</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Scan stiker QR code di bodi tong sampah PIRU menggunakan kamera smartphone Anda untuk membuka sesi kendali.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 hover:border-brand-500/50 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 font-extrabold text-lg flex items-center justify-center mb-4">
                        2
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Letakkan di Baki</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pilih <b>Baki 1 (Organik)</b> atau <b>Baki 2 (Anorganik)</b>. Sensor proximity mendeteksi keberadaan objek secara instan.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-brand-200 bg-brand-50/40 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-brand-600 text-white font-extrabold text-lg flex items-center justify-center mb-4 shadow-md shadow-brand-600/30">
                        3
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">AI YOLO &amp; Timbangan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Kamera Computer Vision YOLO memverifikasi jenis sampah, servo membuka wadah, dan load cell menimbang bobot fisik.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80 hover:border-brand-500/50 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 font-extrabold text-lg flex items-center justify-center mb-4">
                        4
                    </div>
                    <h3 class="font-bold text-base text-slate-900 mb-2">Klaim Poin &amp; Cetak PDF</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Tekan "Selesai", poin reward langsung masuk ke akun Trashily Anda, dan struk bukti sesi PDF siap diunduh!
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- SISTEM POIN REWARD & SIMULASI INTERAKTIF -->
    <section id="simulasi-piru" class="py-16 md:py-24 bg-slate-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left: Skema Poin -->
                <div class="lg:col-span-5">
                    <span class="text-emerald-400 font-bold text-xs uppercase tracking-widest bg-emerald-950 px-3 py-1 rounded-full border border-emerald-800">
                        Skema Reward Terbuka
                    </span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight mt-3 mb-4">
                        Tiap Sampah Bernilai Poin
                    </h2>
                    <p class="text-slate-400 text-sm mb-6 leading-relaxed">
                        Trashily menerapkan reward mikro per item untuk memicu pembiasaan memilah sampah sejak sumbernya. Kumpulkan poin dan tukarkan dengan sembako atau saldo kas.
                    </p>

                    <div class="space-y-3.5 mb-8">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800/80 border border-emerald-500/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg">
                                    <i class="fa-solid fa-leaf"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">Sampah Organik</h4>
                                    <p class="text-xs text-slate-400">Sisa makanan, kulit buah, dedaunan</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-base text-emerald-400 font-mono">+10 Poin / item</span>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-800/80 border border-sky-500/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-lg">
                                    <i class="fa-solid fa-bottle-water"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-white">Sampah Anorganik</h4>
                                    <p class="text-xs text-slate-400">Botol PET, kaleng, cup plastik, kemasan</p>
                                </div>
                            </div>
                            <span class="font-extrabold text-base text-sky-400 font-mono">+15 Poin / item</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Interactive Live Simulator -->
                <div class="lg:col-span-7">
                    <div class="bg-slate-800/90 rounded-3xl p-6 border border-slate-700 shadow-2xl">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-slate-700 mb-5">
                            <div>
                                <h3 class="font-bold text-sm text-white flex items-center gap-2">
                                    <i class="fa-solid fa-gamepad text-emerald-400"></i> Simulator Deteksi YOLO PIRU
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Uji coba interaktif cara kerja sensor &amp; audio edukasi TTS</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-mono bg-emerald-950 text-emerald-300 border border-emerald-800">
                                AI Ready
                            </span>
                        </div>

                        <!-- Live Terminal Screen -->
                        <div class="bg-slate-950 rounded-2xl p-5 border border-slate-800 mb-5 font-mono text-xs">
                            <div class="flex items-center justify-between text-slate-500 mb-3 border-b border-slate-800/80 pb-2">
                                <span>STATUS SENSOR PIRU</span>
                                <span id="simStatusPill" class="text-emerald-400 font-bold">&bull; IDLE / STANDBY</span>
                            </div>

                            <div class="space-y-2 text-slate-300 min-h-[90px]" id="simTerminalLog">
                                <p class="text-slate-500">> Kamera YOLO siap mendeteksi objek di baki.</p>
                                <p class="text-slate-500">> Servo kompartemen dalam posisi standby tertutup.</p>
                                <p class="text-emerald-400">> Silakan klik salah satu tombol sampah di bawah untuk simulasi:</p>
                            </div>

                            <!-- Live Counter Box -->
                            <div class="mt-4 pt-3 border-t border-slate-850 flex items-center justify-between">
                                <span class="text-slate-400">Simulasi Total Poin:</span>
                                <span id="simTotalPoin" class="text-amber-400 font-extrabold text-lg">+0 Poin</span>
                            </div>
                        </div>

                        <!-- Simulation Action Buttons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <button onclick="runSimulation('ORGANIK')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg transition active:scale-95">
                                <i class="fa-solid fa-apple-whole"></i> Masukkan Sampah Organik (+10 Pts)
                            </button>
                            <button onclick="runSimulation('ANORGANIK')" class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-lg transition active:scale-95">
                                <i class="fa-solid fa-bottle-water"></i> Masukkan Sampah Anorganik (+15 Pts)
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- PDF REPORT HIGHLIGHT BANNER -->
    <section class="py-16 bg-emerald-50 border-b border-emerald-100">
        <div class="max-w-5xl mx-auto px-4 md:px-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 text-2xl mb-4">
                <i class="fa-solid fa-file-pdf"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-2">
                Transparansi Lengkap dengan Fitur Ekspor PDF
            </h2>
            <p class="text-slate-600 text-sm max-w-2xl mx-auto mb-6">
                Setiap pemilahan sampah di PIRU terdokumentasi rapi. Customer dapat langsung mengunduh struk digital resmi bertanda tangan QR, dan pengelola bank sampah dapat mencetak laporan telemetri rekapitulasi harian berformat A4 standar ISO.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="auth/login.php?from=piru&device=<?= urlencode($deviceId) ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition">
                    <i class="fa-solid fa-circle-play text-emerald-400"></i> Coba Sesi Pemilahan Sekarang
                </a>
            </div>
        </div>
    </section>

    <!-- Shared Public Footer -->
    <?php include __DIR__ . '/includes/public_footer.php'; ?>

    <!-- JAVASCRIPT SIMULATOR -->
    <script>
        let simPoin = 0;

        function runSimulation(kategori) {
            const term = document.getElementById('simTerminalLog');
            const pill = document.getElementById('simStatusPill');
            const poinEl = document.getElementById('simTotalPoin');

            pill.innerHTML = '<span class="text-amber-400 animate-pulse">&bull; DETEKSI OBJEK YOLO...</span>';

            const addPoin = (kategori === 'ORGANIK') ? 10 : 15;
            simPoin += addPoin;

            term.innerHTML = `
                <p class="text-amber-300">> [CAMERA] Frame tertangkap: Objek terklasifikasi sebagai <b>${kategori}</b>.</p>
                <p class="text-sky-300">> [SERVO] Menggerakkan servo kompartemen ${kategori}...</p>
                <p class="text-emerald-400">> [TTS AUDIO] Memutar suara: "Terima kasih telah membuang sampah ${kategori.toLowerCase()} dengan benar!"</p>
                <p class="text-white font-bold">> [API REWARD] Berhasil menerbitkan +${addPoin} Poin ke akun Anda!</p>
            `;

            poinEl.innerText = `+${simPoin} Poin`;

            // Play tone
            playSynthTone();

            setTimeout(() => {
                pill.innerHTML = '<span class="text-emerald-400">&bull; IDLE / STANDBY</span>';
            }, 2500);
        }

        function playSynthTone() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.28);
                osc.start();
                osc.stop(ctx.currentTime + 0.28);
            } catch (e) {}
        }
    </script>
</body>
</html>
