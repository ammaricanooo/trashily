<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireCustomer();
require_once __DIR__ . '/../config/database.php';

$uid = intval($_SESSION['user_id']);
$user = $conn->query("SELECT nama, email, poin FROM users WHERE id = $uid")->fetch_assoc();
$_SESSION['poin'] = $user['poin'];

$deviceId = trim($_GET['device'] ?? 'PIRU-DEV-001');

// Cek metadata perangkat di database
$stmtDev = $conn->prepare("SELECT id, nama_perangkat, lokasi, status, kapasitas_organik, kapasitas_anorganik FROM trashsmart_devices WHERE kode_perangkat = ?");
$stmtDev->bind_param('s', $deviceId);
$stmtDev->execute();
$device = $stmtDev->get_result()->fetch_assoc();

$deviceName   = $device['nama_perangkat'] ?? 'PIRU Smart Bin #001';
$deviceLokasi = $device['lokasi'] ?? 'Area Utama / EcoHub Trashily';
$deviceStatus = $device['status'] ?? 'online';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PIRU Smart Bin — Trashily</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/output.css">

    <style>
        /* Pulse Radar Wave Animations */
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.22); opacity: 0.15; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }
        .animate-pulse-ring {
            animation: pulse-ring 2.8s infinite ease-in-out;
        }

        /* Scanning Line Beam */
        @keyframes scan-line {
            0% { top: 10%; opacity: 0.2; }
            50% { opacity: 0.85; }
            100% { top: 90%; opacity: 0.2; }
        }
        .scanner-beam {
            position: absolute;
            left: 8%;
            right: 8%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #22c55e, #10b981, transparent);
            box-shadow: 0 0 10px #22c55e;
            animation: scan-line 2.2s ease-in-out infinite alternate;
        }

        /* Audio Waveform Sound Bars */
        @keyframes soundBounce {
            0% { height: 4px; }
            50% { height: 16px; }
            100% { height: 6px; }
        }
        .sound-bar {
            width: 3px;
            height: 6px;
            background: #10b981;
            border-radius: 2px;
            animation: soundBounce 1s ease-in-out infinite alternate;
        }
        .sound-bar:nth-child(2) { animation-delay: 0.15s; }
        .sound-bar:nth-child(3) { animation-delay: 0.3s; }
        .sound-bar:nth-child(4) { animation-delay: 0.45s; }
        .sound-bar:nth-child(5) { animation-delay: 0.2s; }

        /* Card Slide-in Animation */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .item-card-enter {
            animation: slideInUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Responsive Grid for PIRU */
        .piru-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }
        @media (min-width: 992px) {
            .piru-grid {
                grid-template-columns: 1.1fr 1fr;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include __DIR__ . '/../includes/sidebar_customer.php'; ?>

    <main class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1>PIRU Smart Bin</h1>
                    <p>Sesi Pemilahan Sampah Berbasis AI YOLO &amp; IoT</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include __DIR__ . '/../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-coins text-xs"></i>
                    <span id="headerUserPoints"><?= number_format($user['poin'], 0, ',', '.') ?></span> Poin
                </div>
            </div>
        </div>

        <div class="content">

            <!-- HERO CARD PERANGKAT & TELEMETRI FISIK -->
            <div class="relative overflow-hidden rounded-2xl mb-5 shadow-sm"
                 style="background: linear-gradient(135deg, #006e2f 0%, #004b1e 100%)">
                
                <!-- Ambient Glow Orbs -->
                <div class="absolute rounded-full pointer-events-none"
                     style="width:240px;height:240px;top:-80px;right:-40px;background:radial-gradient(circle,rgba(74,225,118,.2) 0%,transparent 70%)"></div>
                <div class="absolute rounded-full pointer-events-none"
                     style="width:160px;height:160px;bottom:-60px;right:30%;background:radial-gradient(circle,rgba(255,255,255,.08) 0%,transparent 70%)"></div>

                <div class="relative z-10 p-5 md:p-6 text-white">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        
                        <!-- Metadata Perangkat -->
                        <div class="flex items-start gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-md flex items-center justify-center text-xl shrink-0 border border-white/20">
                                <i class="fa-solid fa-microchip text-emerald-300"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-lg md:text-xl font-extrabold tracking-tight" style="font-family:var(--font-display);">
                                        <?= htmlspecialchars($deviceName) ?>
                                    </h2>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-white/15 border border-white/20 text-emerald-200">
                                        <?= htmlspecialchars($deviceId) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-emerald-100/80 flex items-center gap-1.5 mt-1">
                                    <i class="fa-solid fa-location-dot text-emerald-300"></i>
                                    <?= htmlspecialchars($deviceLokasi) ?>
                                </p>
                            </div>
                        </div>

                        <!-- Status Koneksi IoT Pill -->
                        <div class="flex items-center gap-2">
                            <span id="hardwarePill" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/40 shadow-sm">
                                <span class="inline-block h-2 w-2 rounded-full bg-emerald-400" style="box-shadow: 0 0 6px #34d399"></span>
                                Live IoT Online
                            </span>
                        </div>
                    </div>

                    <!-- Telemetri Ketinggian Tong (Dual JSN-SR04T Sensor) -->
                    <div class="mt-4 pt-3.5 border-t border-white/15">
                        <div class="flex items-center justify-between text-xs font-semibold mb-2 text-emerald-100/90">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-solid fa-gauge-high text-emerald-300"></i>
                                Status Kapasitas Kompartemen Fisik (Ultrasonik JSN-SR04T)
                            </span>
                            <span class="text-[11px] font-mono text-emerald-200/70">Batas Jarak: 15 cm</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Baki 1: Organik -->
                            <div class="p-2.5 rounded-xl bg-black/20 border border-white/10">
                                <div class="flex items-center justify-between text-xs mb-1.5 font-semibold">
                                    <span class="text-emerald-200 flex items-center gap-1.5">
                                        <i class="fa-solid fa-leaf text-emerald-400"></i> Baki 1: Organik
                                    </span>
                                    <span id="capOrgVal" class="font-mono font-bold text-white"><?= intval($device['kapasitas_organik'] ?? 25) ?>%</span>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                                    <div id="capOrgBar" class="bg-emerald-400 h-2 rounded-full transition-all duration-500" style="width: <?= intval($device['kapasitas_organik'] ?? 25) ?>%"></div>
                                </div>
                            </div>

                            <!-- Baki 2: Anorganik -->
                            <div class="p-2.5 rounded-xl bg-black/20 border border-white/10">
                                <div class="flex items-center justify-between text-xs mb-1.5 font-semibold">
                                    <span class="text-sky-200 flex items-center gap-1.5">
                                        <i class="fa-solid fa-bottle-water text-sky-400"></i> Baki 2: Anorganik
                                    </span>
                                    <span id="capAnorgVal" class="font-mono font-bold text-white"><?= intval($device['kapasitas_anorganik'] ?? 30) ?>%</span>
                                </div>
                                <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                                    <div id="capAnorgBar" class="bg-sky-400 h-2 rounded-full transition-all duration-500" style="width: <?= intval($device['kapasitas_anorganik'] ?? 30) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- MAIN PIRU 2-COLUMN GRID -->
            <div class="piru-grid">

                <!-- KOLOM KIRI: SCANNER RADAR HUD & SCOREBOARD -->
                <div class="space-y-4">

                    <!-- SCANNER RADAR CARD -->
                    <div class="card relative overflow-hidden text-center p-6" style="background:#fff;border-radius:16px;">
                        <div class="scanner-beam"></div>

                        <!-- Target Reticle & Radar Center -->
                        <div class="relative w-28 h-28 mx-auto mb-3.5 flex items-center justify-center">
                            <div class="absolute inset-0 rounded-full bg-emerald-500/10 animate-pulse-ring"></div>
                            <div class="absolute inset-2 rounded-full border border-emerald-500/30"></div>
                            <div class="absolute inset-5 rounded-full border border-dashed border-emerald-500/40 animate-spin" style="animation-duration: 15s;"></div>
                            
                            <div id="radarCenter" class="relative w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center shadow-lg shadow-emerald-600/30 transition-transform duration-300">
                                <i id="radarIcon" class="fa-solid fa-camera-viewfinder text-2xl animate-pulse"></i>
                            </div>
                        </div>

                        <!-- Audio Waveform Live Visualizer -->
                        <div id="audioWaveBox" class="flex items-center justify-center gap-1.5 mb-2.5 h-4 opacity-75">
                            <div class="sound-bar"></div>
                            <div class="sound-bar"></div>
                            <div class="sound-bar"></div>
                            <div class="sound-bar"></div>
                            <div class="sound-bar"></div>
                        </div>

                        <!-- Live Status Badge -->
                        <div id="statusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            STANDBY MEMILAH
                        </div>

                        <h3 id="statusTitle" class="text-base font-extrabold text-slate-800 mb-1" style="font-family:var(--font-display);">
                            Silakan Letakkan Sampah di Baki PIRU
                        </h3>
                        <p id="statusDesc" class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                            Letakkan pada <b>Baki 1 (Organik)</b> atau <b>Baki 2 (Anorganik)</b>. Sensor IR, kamera YOLOv8 &amp; motor servo akan bekerja otomatis.
                        </p>
                    </div>

                    <!-- SCOREBOARD SUMMARY (3 CARDS) -->
                    <div class="grid grid-cols-3 gap-3">
                        <!-- Organik -->
                        <div class="card p-3.5 text-center relative overflow-hidden" style="border-radius:14px;">
                            <div class="text-[11px] font-bold text-emerald-700 mb-0.5 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-leaf text-xs"></i> Organik
                            </div>
                            <div id="statOrganik" class="text-2xl font-extrabold text-slate-800 font-mono my-0.5">0</div>
                            <span class="text-[10px] text-emerald-600 font-semibold block">+10 Poin/item</span>
                        </div>

                        <!-- Anorganik -->
                        <div class="card p-3.5 text-center relative overflow-hidden" style="border-radius:14px;">
                            <div class="text-[11px] font-bold text-sky-700 mb-0.5 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-bottle-water text-xs"></i> Anorganik
                            </div>
                            <div id="statAnorganik" class="text-2xl font-extrabold text-slate-800 font-mono my-0.5">0</div>
                            <span class="text-[10px] text-sky-600 font-semibold block">+15 Poin/item</span>
                        </div>

                        <!-- Total Poin -->
                        <div class="card p-3.5 text-center relative overflow-hidden" style="border-radius:14px;background:#fefce8;border-color:#fef08a;">
                            <div class="text-[11px] font-bold text-amber-700 mb-0.5 flex items-center justify-center gap-1">
                                <i class="fa-solid fa-coins text-xs"></i> Total Poin
                            </div>
                            <div id="statPoin" class="text-2xl font-extrabold text-amber-600 font-mono my-0.5">+0</div>
                            <span class="text-[10px] text-amber-700/80 font-semibold block">Reward Sesi</span>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="card p-4 space-y-2.5" style="border-radius:14px;">
                        <button id="btnComplete" onclick="completeSession()" class="btn btn-primary w-full py-3 text-sm font-bold flex items-center justify-center gap-2" style="background:#006e2f;border-color:#006e2f;border-radius:10px;">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Selesai &amp; Klaim Poin Reward</span>
                        </button>
                        <div class="flex items-center justify-between px-1">
                            <button id="btnCancel" onclick="cancelSession()" class="text-xs font-semibold text-rose-600 hover:text-rose-700 py-1 transition flex items-center gap-1">
                                <i class="fa-solid fa-circle-xmark"></i> Batalkan Sesi
                            </button>
                            <span class="text-[11px] text-slate-400 font-mono">
                                Unit: <?= htmlspecialchars($deviceId) ?>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN: SIMULATOR UJI CEPAT & LIVE STREAM SAMPAH TERDETEKSI -->
                <div class="space-y-4">

                    <!-- PANEL UJI SENSOR IOT (LIVE TEST TRIGGER) -->
                    <div class="card p-4" style="border-radius:14px;">
                        <div class="flex items-center justify-between mb-3 border-b pb-2">
                            <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="fa-solid fa-vial-virus text-emerald-600"></i>
                                Uji Sensor Baki IoT (Live Testing)
                            </h4>
                            <span class="badge badge-green text-[10px]">ESP32 &bull; YOLO</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-3">
                            Tekan tombol berikut untuk memicu deteksi sensor baki secara langsung dan menguji integrasi:
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="simulateIoT('ORGANIK', 'apple', 120)"
                                    class="btn btn-sm flex items-center justify-center gap-1.5 py-2 px-2 text-xs font-bold"
                                    style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">
                                <span>🍎</span> Baki 1: Apel (+10P)
                            </button>
                            <button type="button" onclick="simulateIoT('ORGANIK', 'banana', 100)"
                                    class="btn btn-sm flex items-center justify-center gap-1.5 py-2 px-2 text-xs font-bold"
                                    style="background:#f0fdf4;color:#166534;border:1px solid #bbf7d0;">
                                <span>🍌</span> Baki 1: Pisang (+10P)
                            </button>
                            <button type="button" onclick="simulateIoT('ANORGANIK', 'bottle', 85)"
                                    class="btn btn-sm flex items-center justify-center gap-1.5 py-2 px-2 text-xs font-bold"
                                    style="background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;">
                                <span>🍾</span> Baki 2: Botol (+15P)
                            </button>
                            <button type="button" onclick="simulateIoT('ANORGANIK', 'cardboard', 150)"
                                    class="btn btn-sm flex items-center justify-center gap-1.5 py-2 px-2 text-xs font-bold"
                                    style="background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd;">
                                <span>📦</span> Baki 2: Kardus (+15P)
                            </button>
                        </div>
                    </div>

                    <!-- DAFTAR ITEM SAMPAH TERDETEKSI (LIVE FEED) -->
                    <div class="card p-4 flex flex-col" style="border-radius:14px; min-height: 260px;">
                        <div class="flex items-center justify-between mb-3 border-b pb-2">
                            <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5 uppercase tracking-wider">
                                <i class="fa-solid fa-list-check text-emerald-600"></i>
                                Sampah Terdeteksi (<span id="itemCount" class="font-mono text-emerald-700 font-bold">0</span>)
                            </h4>
                            <span class="text-[11px] text-slate-500 flex items-center gap-1.5 font-mono" id="syncStatus">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500" style="box-shadow: 0 0 6px #10b981"></span>
                                Sinkron Live
                            </span>
                        </div>

                        <div id="itemsContainer" class="space-y-2 flex-1 overflow-y-auto max-h-80 pr-1">
                            <!-- Placeholder saat belum ada item -->
                            <div id="emptyItemsPlaceholder" class="text-center py-10 text-slate-400 text-xs">
                                <i class="fa-solid fa-recycle text-3xl text-slate-300 mb-2 block animate-pulse"></i>
                                Belum ada sampah yang dimasukkan.<br>
                                <span class="text-slate-500 text-[11px]">Letakkan sampah pada baki tong sampah pintar PIRU.</span>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>
</div>

<!-- SCRIPTS NOTIFIKASI & SIDEBAR -->
<?php include __DIR__ . '/../includes/notif_scripts.php'; ?>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>

<!-- SCRIPT POLLING, AUDIO CHIME & PDF RECEIPT -->
<script>
    const DEVICE_ID = <?= json_encode($deviceId) ?>;
    let isPolling = true;
    let lastItemCount = 0;
    let currentSessionId = 0;
    let renderedItemIds = [];
    let isInitialItemsLoaded = false;

    // Helper: update teks hanya bila nilai berubah (mencegah repaint/reflow berlebih)
    function setTextIfChanged(id, val) {
        const el = document.getElementById(id);
        if (el && el.innerText !== String(val)) {
            el.innerText = val;
        }
    }

    // Inisialisasi Mulai Sesi Saat Halaman Dimuat
    async function initSession() {
        try {
            const res = await fetch(`../api/piru-session.php?action=start&device=${encodeURIComponent(DEVICE_ID)}`);
            const data = await res.json();
            if (data.success) {
                console.log("[PIRU] Sesi aktif terhubung:", data.session);
                if (data.session && data.session.id) {
                    currentSessionId = data.session.id;
                }
                pollStatus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: data.message || 'Tidak dapat memulai sesi PIRU.'
                });
            }
        } catch (err) {
            console.error("[PIRU INIT ERROR]", err);
        }
    }

    // Polling Status Terkini ke Server (Setiap 1.5 Detik)
    async function pollStatus() {
        if (!isPolling) return;

        try {
            const res = await fetch(`../api/piru-session.php?action=status&device=${encodeURIComponent(DEVICE_ID)}`);
            const data = await res.json();

            if (data.success && data.has_active_session) {
                if (data.session && data.session.id) {
                    currentSessionId = data.session.id;
                }
                updateUI(data.session, data.items, data.device);
            }
        } catch (err) {
            console.warn("[PIRU POLL WARNING]", err);
            const syncEl = document.getElementById('syncStatus');
            if (syncEl && syncEl.dataset.state !== 'reconnecting') {
                syncEl.dataset.state = 'reconnecting';
                syncEl.innerHTML = '<span class="text-amber-500 font-bold">Reconnecting...</span>';
            }
        } finally {
            if (isPolling) {
                setTimeout(pollStatus, 1500);
            }
        }
    }

    // Render daftar item secara cerdas & stabil (tanpa kedap-kedip / flicker)
    function renderItems(items) {
        const container = document.getElementById('itemsContainer');
        const placeholder = document.getElementById('emptyItemsPlaceholder');

        if (!items || items.length === 0) {
            if (placeholder) placeholder.style.display = 'block';
            if (container.children.length > 1 || (container.children.length === 1 && container.children[0] !== placeholder)) {
                container.innerHTML = '';
                if (placeholder) container.appendChild(placeholder);
            }
            renderedItemIds = [];
            return;
        }

        if (placeholder) placeholder.style.display = 'none';

        // Daftar ID item saat ini
        const currentIds = items.map(it => it.id);

        // Jika daftar item sama persis dengan yang sudah ter-render, JANGAN sentuh DOM! (Cegah flicker / kedap-kedip)
        const isSameList = currentIds.length === renderedItemIds.length &&
                           currentIds.every((id, idx) => id === renderedItemIds[idx]);

        if (isSameList && container.children.length > 0) {
            return;
        }

        const oldSet = new Set(renderedItemIds);
        renderedItemIds = currentIds;

        const html = items.map(item => {
            const isOrg = item.category === 'ORGANIK';
            const badgeStyle = isOrg 
                ? 'background:#f0fdf4; border:1px solid #bbf7d0; color:#166534;' 
                : 'background:#f0f9ff; border:1px solid #bae6fd; color:#0369a1;';

            let icon = isOrg ? 'fa-apple-whole text-emerald-600' : 'fa-bottle-water text-sky-600';
            const nameLower = (item.item_name || '').toLowerCase();
            if (nameLower.includes('botol') || nameLower.includes('bottle')) icon = 'fa-bottle-water text-sky-600';
            else if (nameLower.includes('kardus') || nameLower.includes('box') || nameLower.includes('cardboard')) icon = 'fa-box-open text-amber-600';
            else if (nameLower.includes('kaleng') || nameLower.includes('can')) icon = 'fa-can-food text-orange-600';
            else if (nameLower.includes('gelas') || nameLower.includes('cup')) icon = 'fa-glass-water text-teal-600';
            else if (nameLower.includes('pisang') || nameLower.includes('banana')) icon = 'fa-lemon text-yellow-600';
            else if (nameLower.includes('apel') || nameLower.includes('apple')) icon = 'fa-apple-whole text-rose-600';
            else if (nameLower.includes('roti') || nameLower.includes('bread') || nameLower.includes('sandwich')) icon = 'fa-bread-slice text-amber-600';

            const displayName = item.item_name || (isOrg ? '🌿 Sampah Organik' : '📦 Sampah Anorganik');
            const weightVal = parseFloat(item.weight_gr) || 0;

            // Animasi slide-in HANYA untuk item yang baru dideteksi saat runtime
            const isNew = isInitialItemsLoaded && !oldSet.has(item.id);
            const animClass = isNew ? 'item-card-enter' : '';

            return `
                <div class="flex items-center justify-between p-3 rounded-xl transition ${animClass} shadow-xs" style="${badgeStyle}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center font-bold text-base shadow-xs border border-slate-200">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 leading-tight">${displayName}</div>
                            <div class="text-[10px] text-slate-500 font-mono mt-0.5">${item.waktu} WIB &bull; ~${Math.round(weightVal)} gr</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="badge ${isOrg ? 'badge-green' : 'badge-blue'}" style="font-size:10px;padding:2px 8px;">
                            ${isOrg ? '+10 Pts' : '+15 Pts'}
                        </span>
                        <div class="text-xs font-extrabold text-emerald-800 font-mono mt-0.5">+${item.poin} Poin</div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    }

    // Perbarui Tampilan Layar secara presisi & stabil
    function updateUI(session, items, device) {
        const orgCount = session.count_organik !== undefined ? session.count_organik : Math.round(session.total_organik_gr / 100);
        const anorgCount = session.count_anorganik !== undefined ? session.count_anorganik : Math.round(session.total_anorganik_gr / 100);

        setTextIfChanged('statOrganik', orgCount);
        setTextIfChanged('statAnorganik', anorgCount);
        setTextIfChanged('statPoin', `+${session.total_poin}`);
        setTextIfChanged('itemCount', session.item_count);

        const syncEl = document.getElementById('syncStatus');
        if (syncEl && syncEl.dataset.state !== 'synced') {
            syncEl.dataset.state = 'synced';
            syncEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1" style="box-shadow: 0 0 6px #10b981"></span> Sinkron Live';
        }

        // Update Telemetri Hardware (Status IoT, Kapasitas Kompartemen Ultrasonik JSN-SR04T)
        if (device) {
            const isOnline = !!device.is_online;
            const hwPill = document.getElementById('hardwarePill');
            if (hwPill && hwPill.dataset.online !== String(isOnline)) {
                hwPill.dataset.online = String(isOnline);
                if (isOnline) {
                    hwPill.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/40 shadow-sm';
                    hwPill.innerHTML = '<span class="inline-block h-2 w-2 rounded-full bg-emerald-400" style="box-shadow: 0 0 6px #34d399"></span> Live IoT Online';
                } else {
                    hwPill.className = 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-950/80 text-amber-300 border border-amber-500/40 shadow-sm';
                    hwPill.innerHTML = '<span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span> Standby Menunggu IoT';
                }
            }

            const capOrg = Math.min(100, Math.max(0, parseInt(device.kapasitas_organik) || 0));
            const capAnorg = Math.min(100, Math.max(0, parseInt(device.kapasitas_anorganik) || 0));

            const capOrgEl = document.getElementById('capOrgVal');
            const barOrgEl = document.getElementById('capOrgBar');
            if (capOrgEl && barOrgEl && capOrgEl.innerText !== `${capOrg}%`) {
                capOrgEl.innerText = `${capOrg}%`;
                barOrgEl.style.width = `${capOrg}%`;
            }

            const capAnorgEl = document.getElementById('capAnorgVal');
            const barAnorgEl = document.getElementById('capAnorgBar');
            if (capAnorgEl && barAnorgEl && capAnorgEl.innerText !== `${capAnorg}%`) {
                capAnorgEl.innerText = `${capAnorg}%`;
                barAnorgEl.style.width = `${capAnorg}%`;
            }
        }

        // Tangani notifikasi suara & efek visual HANYA jika ada item baru saat sesi aktif
        if (items && items.length > 0) {
            if (!isInitialItemsLoaded) {
                lastItemCount = items.length;
                isInitialItemsLoaded = true;
            } else if (items.length > lastItemCount) {
                lastItemCount = items.length;
                playTone();
                triggerScanAnimation(items[0]);
            }
        } else {
            isInitialItemsLoaded = true;
        }

        // Render daftar item
        renderItems(items);
    }

    // Animasi deteksi visual HUD saat item terdeteksi
    function triggerScanAnimation(item) {
        const badge = document.getElementById('statusBadge');
        const title = document.getElementById('statusTitle');
        const radar = document.getElementById('radarCenter');
        const radarIcon = document.getElementById('radarIcon');
        
        const isOrg = item.category === 'ORGANIK';
        const displayName = item.item_name || (isOrg ? 'Sampah Organik' : 'Sampah Anorganik');

        radar.style.transform = 'scale(1.15)';
        radarIcon.className = isOrg ? 'fa-solid fa-leaf text-2xl text-emerald-200 animate-bounce' : 'fa-solid fa-bottle-water text-2xl text-sky-200 animate-bounce';
        badge.innerText = `AI DETEKSI: ${item.category}`;
        badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 mb-2';
        title.innerText = `${displayName} (+${item.poin} Poin)`;
        
        setTimeout(() => {
            radar.style.transform = 'scale(1)';
            radarIcon.className = 'fa-solid fa-camera-viewfinder text-2xl animate-pulse';
            badge.innerText = 'STANDBY MEMILAH';
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-2';
            title.innerText = 'Silakan Letakkan Sampah di Baki PIRU';
        }, 3000);
    }

    // Fungsi Uji Coba Pemicuan Sensor IoT Baki Langsung dari Web
    async function simulateIoT(category, itemName, weight) {
        try {
            const res = await fetch(`../api/piru-session.php?action=simulate&device=${encodeURIComponent(DEVICE_ID)}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `category=${encodeURIComponent(category)}&item_name=${encodeURIComponent(itemName)}&weight_grams=${weight}`
            });
            const data = await res.json();
            if (data.success) {
                pollStatus();
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Info Sesi',
                    text: data.message
                });
            }
        } catch (err) {
            console.error("[SIMULATION ERROR]", err);
        }
    }

    // Nada audio feedback browser (Synthesized Dual Beep)
    function playTone() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
            osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0.18, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.28);
            osc.start();
            osc.stop(ctx.currentTime + 0.28);
        } catch (e) {}
    }

    // Selesaikan Sesi & Klaim Poin
    async function completeSession() {
        const currentPoints = parseInt(document.getElementById('statPoin').innerText.replace(/\D/g, '')) || 0;
        const items = parseInt(document.getElementById('itemCount').innerText) || 0;

        const confirm = await Swal.fire({
            title: 'Selesai Memilah?',
            text: items > 0 
                ? `Anda telah memilah ${items} item sampah dan akan mengklaim +${currentPoints} Poin Reward!`
                : 'Belum ada sampah yang dimasukkan ke baki PIRU.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#006e2f',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Selesaikan & Klaim!',
            cancelButtonText: 'Lanjutkan Memilah'
        });

        if (!confirm.isConfirmed) return;

        isPolling = false;
        Swal.fire({
            title: 'Memproses Poin...',
            text: 'Menyimpan transaksi ke akun Trashily Anda...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const res = await fetch(`../api/piru-session.php?action=complete&device=${encodeURIComponent(DEVICE_ID)}`, {
                method: 'POST'
            });
            const data = await res.json();

            if (data.success) {
                if (typeof confetti === 'function') {
                    confetti({ particleCount: 100, spread: 70, origin: { y: 0.6 } });
                }

                const targetSessionId = data.session_id || currentSessionId;
                const pdfUrl = `export-session-pdf.php?session_id=${targetSessionId}`;

                await Swal.fire({
                    title: '🎉 Pemilahan Sukses!',
                    html: `
                        <div class="text-center py-2">
                            <div class="text-3xl font-extrabold text-emerald-700 mb-1">+${data.points_earned} Poin</div>
                            <p class="text-xs text-slate-600 mb-3">${data.message}</p>
                            <div class="text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-slate-600 font-mono mb-4">
                                Kode Trx: <strong class="text-slate-900">${data.kode_transaksi || '-'}</strong>
                            </div>
                            <div class="space-y-2">
                                <a href="${pdfUrl}" target="_blank" 
                                   class="btn btn-primary w-full py-2.5 text-xs font-bold inline-flex items-center justify-center gap-2"
                                   style="background:#e11d48;border-color:#e11d48;border-radius:8px;">
                                    <i class="fa-solid fa-file-pdf"></i> Unduh / Cetak Bukti Sesi (PDF)
                                </a>
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#006e2f',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Lihat Riwayat',
                    cancelButtonText: 'Tutup'
                });

                window.location.href = 'riwayat.php';
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message || 'Terjadi kesalahan sistem.'
                });
                isPolling = true;
                pollStatus();
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal menghubungi server.'
            });
            isPolling = true;
            pollStatus();
        }
    }

    // Batalkan Sesi
    async function cancelSession() {
        const confirm = await Swal.fire({
            title: 'Batalkan Sesi?',
            text: 'Poin yang belum diklaim pada sesi ini tidak akan tersimpan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Kembali'
        });

        if (!confirm.isConfirmed) return;

        isPolling = false;
        try {
            await fetch(`../api/piru-session.php?action=cancel&device=${encodeURIComponent(DEVICE_ID)}`, { method: 'POST' });
            window.location.href = 'dashboard.php';
        } catch (err) {
            window.location.href = 'dashboard.php';
        }
    }

    // Inisialisasi otomatis
    window.addEventListener('DOMContentLoaded', initSession);
</script>
</body>
</html>
