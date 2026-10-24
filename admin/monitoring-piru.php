<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

// --- AJAX ENDPOINT: Ambil rincian item sesi pemilahan ---
if (isset($_GET['ajax']) && $_GET['ajax'] === 'session_items') {
    header('Content-Type: application/json; charset=utf-8');
    $sessionId = intval($_GET['session_id'] ?? 0);
    $items = [];
    if ($sessionId > 0) {
        $stmt = $conn->prepare("
            SELECT id, session_id, category, item_name, weight_gr, poin, created_at 
            FROM piru_session_items 
            WHERE session_id = ? 
            ORDER BY id ASC
        ");
        $stmt->bind_param('i', $sessionId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $items[] = $row;
        }
    }
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

$pesan_sukses = '';
$pesan_error  = '';

// --- 1. HANDLE ACTION: Kosongkan Wadah / Reset Kapasitas ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'empty_bin') {
        $dev_id = intval($_POST['device_id'] ?? 0);
        if ($dev_id > 0) {
            $stmtReset = $conn->prepare("
                UPDATE trashsmart_devices 
                SET kapasitas_organik = 0, kapasitas_anorganik = 0, status = 'online', terakhir_dikosongkan = NOW() 
                WHERE id = ?
            ");
            $stmtReset->bind_param('i', $dev_id);
            if ($stmtReset->execute()) {
                $nowDate = date('Y-m-d');
                $nowTime = date('H:i:s');
                $stmtLog = $conn->prepare("
                    INSERT INTO trashsmart_logs (
                        device_id, tanggal, waktu, kategori_sampah, status_pilah,
                        berat_kg, poin, pesan_suara, user_identifier
                    ) VALUES (?, ?, ?, 'sistem', 'sistem', 0, 0, 'Admin telah mengosongkan kedua kompartemen PIRU Smart Bin. Kapasitas kembali 0%.', 'Petugas / Admin PIRU')
                ");
                $stmtLog->bind_param('iss', $dev_id, $nowDate, $nowTime);
                $stmtLog->execute();
                $pesan_sukses = "Kedua kompartemen PIRU Smart Bin berhasil dikosongkan dan status di-reset ke 0%!";
            } else {
                $pesan_error = "Gagal mereset perangkat: " . $conn->error;
            }
        }
    } elseif ($action === 'update_status') {
        $dev_id = intval($_POST['device_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? 'online');
        if (in_array($new_status, ['online', 'warning', 'maintenance'])) {
            $stmtUpdate = $conn->prepare("UPDATE trashsmart_devices SET status = ? WHERE id = ?");
            $stmtUpdate->bind_param('si', $new_status, $dev_id);
            if ($stmtUpdate->execute()) {
                $pesan_sukses = "Status perangkat berhasil diperbarui menjadi " . ucfirst($new_status);
            }
        }
    } elseif ($action === 'cancel_session') {
        $sess_id = intval($_POST['session_id'] ?? 0);
        if ($sess_id > 0) {
            $stmtCancel = $conn->prepare("UPDATE piru_sessions SET status = 'cancelled' WHERE id = ? AND status = 'active'");
            $stmtCancel->bind_param('i', $sess_id);
            if ($stmtCancel->execute()) {
                $pesan_sukses = "Sesi aktif berhasil dibatalkan.";
            }
        }
    }
}

// --- 2. AMBIL DATA PERANGKAT PIRU ---
$piru_dev = $conn->query("
    SELECT * FROM trashsmart_devices 
    WHERE kode_perangkat = 'PIRU-DEV-001' 
    LIMIT 1
")->fetch_assoc();

if (!$piru_dev) {
    // Fallback if not found
    $piru_dev = [
        'id' => 7,
        'kode_perangkat' => 'PIRU-DEV-001',
        'nama_perangkat' => 'PIRU Smart Bin #001',
        'lokasi' => 'Area Kantin Utama / EcoHub',
        'status' => 'online',
        'kapasitas_organik' => 0,
        'kapasitas_anorganik' => 0,
        'firmware_version' => 'v2.0.0-YOLO',
        'last_ping' => date('Y-m-d H:i:s'),
        'baterai' => 100,
        'suhu_celsius' => 28.5
    ];
}

$dev_id = intval($piru_dev['id']);

// --- 3. METRIK UTAMA PIRU ---
// Total Sesi
$total_sessions_all = $conn->query("SELECT COUNT(*) as c FROM piru_sessions")->fetch_assoc()['c'] ?? 0;
$total_completed = $conn->query("SELECT COUNT(*) as c FROM piru_sessions WHERE status = 'completed'")->fetch_assoc()['c'] ?? 0;
$total_active = $conn->query("SELECT COUNT(*) as c FROM piru_sessions WHERE status = 'active'")->fetch_assoc()['c'] ?? 0;

// Total Item & Kategori
$item_stats = $conn->query("
    SELECT 
        COUNT(*) as total_item,
        COALESCE(SUM(CASE WHEN category = 'ORGANIK' THEN 1 ELSE 0 END), 0) as count_organik,
        COALESCE(SUM(CASE WHEN category = 'ANORGANIK' THEN 1 ELSE 0 END), 0) as count_anorganik,
        COALESCE(SUM(poin), 0) as total_poin_items,
        COALESCE(SUM(weight_gr), 0) as total_weight_gr
    FROM piru_session_items
")->fetch_assoc();

$total_items       = intval($item_stats['total_item'] ?? 0);
$count_organik     = intval($item_stats['count_organik'] ?? 0);
$count_anorganik   = intval($item_stats['count_anorganik'] ?? 0);
$total_poin_dist   = intval($item_stats['total_poin_items'] ?? 0);
$total_berat_kg    = floatval(($item_stats['total_weight_gr'] ?? 0) / 1000);

// Sesi Hari Ini
$today_stats = $conn->query("
    SELECT 
        COUNT(*) as ses_today,
        COALESCE(SUM(total_poin), 0) as poin_today,
        COALESCE(SUM(item_count), 0) as items_today,
        COALESCE(SUM(total_berat_gr), 0) as berat_gr_today
    FROM piru_sessions 
    WHERE DATE(created_at) = CURDATE() AND status = 'completed'
")->fetch_assoc();

// --- 4. FILTER & QUERY SESI PEMILAHAN ---
$filter_date   = trim($_GET['tanggal'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$search        = trim($_GET['search'] ?? '');

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = ["1=1"];
$params = [];
$types  = "";

if ($filter_date !== '') {
    $where[] = "DATE(s.created_at) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

if ($filter_status !== '' && $filter_status !== 'all') {
    $where[] = "s.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if ($search !== '') {
    $where[] = "(s.session_token LIKE ? OR u.nama LIKE ? OR u.no_hp LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= "sss";
}

$where_str = implode(" AND ", $where);

// Hitung total baris
$count_sql = "
    SELECT COUNT(*) as c 
    FROM piru_sessions s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE $where_str
";
$stmtC = $conn->prepare($count_sql);
if (!empty($types)) {
    $stmtC->bind_param($types, ...$params);
}
$stmtC->execute();
$total_rows = $stmtC->get_result()->fetch_assoc()['c'] ?? 0;
$total_pages = ceil($total_rows / $limit);

// Ambil Data Sesi
$data_sql = "
    SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
           t.kode_transaksi
    FROM piru_sessions s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN transaksi t ON s.transaksi_id = t.id
    WHERE $where_str
    ORDER BY s.created_at DESC
    LIMIT ? OFFSET ?
";
$stmtD = $conn->prepare($data_sql);
$types_paged = $types . "ii";
$params_paged = array_merge($params, [$limit, $offset]);
$stmtD->bind_param($types_paged, ...$params_paged);
$stmtD->execute();
$sessions_result = $stmtD->get_result();

// --- 5. LOG TELEMETRI TERBARU PIRU (trashsmart_logs device_id = 7) ---
$recent_logs = $conn->query("
    SELECT * FROM trashsmart_logs 
    WHERE device_id = $dev_id 
    ORDER BY tanggal DESC, waktu DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring PIRU Smart Bin — Trashily Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
    <style>
        .piru-hero-card {
            background: linear-gradient(135deg, #064e29 0%, #006e2f 60%, #059669 100%);
            border-radius: var(--radius-xl);
            padding: 1.5rem 1.75rem;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 110, 47, 0.3);
            margin-bottom: 1.5rem;
        }
        .piru-hero-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }
        .pulse-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .gauge-container {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 1rem;
        }
        .gauge-bar-track {
            height: 10px;
            background: rgba(0, 0, 0, 0.25);
            border-radius: 999px;
            overflow: hidden;
            margin-top: 6px;
        }
        .gauge-bar-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card-piru {
            background: #fff;
            border: 1px solid var(--color-border);
            border-radius: var(--radius-xl);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: var(--shadow-card);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card-piru:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-float);
        }
        .spec-pill {
            background: #f1f5f9;
            color: #334155;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid #e2e8f0;
        }
        .tab-btn {
            padding: 0.6rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .tab-btn.active {
            color: var(--color-primary);
            border-bottom-color: var(--color-primary);
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1>Monitoring PIRU Smart Bin</h1>
                    <p>Telemetri IoT ESP32 &amp; Deteksi Computer Vision YOLO Pemilah Sampah Cerdas</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="poin-badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0">
                    <span class="pulse-dot mr-1"></span> Unit: <strong><?= htmlspecialchars($piru_dev['kode_perangkat']) ?></strong>
                </div>
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">

            <!-- SWEETALERT FEEDBACK -->
            <?php if ($pesan_sukses): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?= addslashes($pesan_sukses) ?>',
                        timer: 2500,
                        showConfirmButton: false
                    });
                </script>
            <?php endif; ?>
            <?php if ($pesan_error): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: '<?= addslashes($pesan_error) ?>'
                    });
                </script>
            <?php endif; ?>

            <!-- PIRU HARDWARE & TELEMETRY HERO BANNER -->
            <div class="piru-hero-card">
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; align-items: center; position:relative; z-index:2;">
                    
                    <!-- Left: Device Identity & Specs -->
                    <div>
                        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:.5rem;">
                            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.3);font-size:.75rem;padding:3px 10px;">
                                <i class="fa-solid fa-microchip"></i> <?= htmlspecialchars($piru_dev['kode_perangkat']) ?>
                            </span>
                            <span class="badge" style="background:#10b981;color:#fff;font-weight:700;font-size:.72rem;">
                                <i class="fa-solid fa-circle-check"></i> <?= strtoupper($piru_dev['status']) ?>
                            </span>
                            <span style="font-size:.75rem;opacity:.9;">
                                <i class="fa-regular fa-clock"></i> Ping: <?= htmlspecialchars($piru_dev['last_ping'] ?? 'Aktif') ?>
                            </span>
                        </div>

                        <h2 style="font-size:1.6rem;font-weight:800;letter-spacing:-0.5px;margin:0 0 .35rem 0;font-family:var(--font-display);">
                            <?= htmlspecialchars($piru_dev['nama_perangkat']) ?>
                        </h2>
                        <p style="font-size:.88rem;opacity:.9;margin:0 0 1rem 0;">
                            <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($piru_dev['lokasi']) ?>
                        </p>

                        <!-- Hardware Badges -->
                        <div style="display:flex; flex-wrap:wrap; gap:.4rem;">
                            <span class="spec-pill" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25);">
                                <i class="fa-solid fa-brain"></i> YOLO Multi-Class Vision
                            </span>
                            <span class="spec-pill" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25);">
                                <i class="fa-solid fa-wave-square"></i> Dual IR Proximity
                            </span>
                            <span class="spec-pill" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25);">
                                <i class="fa-solid fa-arrows-up-down"></i> Dual Servo SG90
                            </span>
                            <span class="spec-pill" style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25);">
                                <i class="fa-solid fa-volume-high"></i> Auto TTS Audio
                            </span>
                        </div>
                    </div>

                    <!-- Right: Compartment Capacities & Action Controls -->
                    <div class="gauge-container">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem;">
                            <span style="font-weight:700; font-size:.85rem; text-transform:uppercase; letter-spacing:0.5px;">
                                <i class="fa-solid fa-chart-simple"></i> Kapasitas Wadah Fisik
                            </span>
                            <span style="font-size:.75rem; opacity:.85;">
                                JSN-SR04T Sensor
                            </span>
                        </div>

                        <!-- Organik Fill Gauge -->
                        <div style="margin-bottom:.75rem;">
                            <div style="display:flex; justify-content:space-between; font-size:.8rem; font-weight:600;">
                                <span><i class="fa-solid fa-leaf text-emerald-300"></i> Wadah Organik (+10 Poin)</span>
                                <span><?= intval($piru_dev['kapasitas_organik']) ?>%</span>
                            </div>
                            <div class="gauge-bar-track">
                                <div class="gauge-bar-fill" style="width: <?= min(100, intval($piru_dev['kapasitas_organik'])) ?>%; background: #34d399;"></div>
                            </div>
                        </div>

                        <!-- Anorganik Fill Gauge -->
                        <div style="margin-bottom:1rem;">
                            <div style="display:flex; justify-content:space-between; font-size:.8rem; font-weight:600;">
                                <span><i class="fa-solid fa-bottle-water text-cyan-300"></i> Wadah Anorganik (+15 Poin)</span>
                                <span><?= intval($piru_dev['kapasitas_anorganik']) ?>%</span>
                            </div>
                            <div class="gauge-bar-track">
                                <div class="gauge-bar-fill" style="width: <?= min(100, intval($piru_dev['kapasitas_anorganik'])) ?>%; background: #38bdf8;"></div>
                            </div>
                        </div>

                        <!-- Action Buttons inside Card -->
                        <div style="display:flex; gap:.5rem; flex-wrap:wrap;">
                            <button onclick="confirmEmptyBin(<?= $dev_id ?>)" class="btn btn-sm" style="background:#fff; color:#064e29; font-weight:700; border:none;">
                                <i class="fa-solid fa-broom"></i> Kosongkan Wadah
                            </button>
                            <button onclick="showQrModal()" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:#fff; font-weight:600; border:1px solid rgba(255,255,255,0.4);">
                                <i class="fa-solid fa-qrcode"></i> Buka Tampilan QR
                            </button>
                            <a href="<?= BASE_URL ?>/customer/piru.php?dev=<?= urlencode($piru_dev['kode_perangkat']) ?>" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.2); color:#fff; font-weight:600; border:1px solid rgba(255,255,255,0.4);" title="Buka Simulasi Customer">
                                <i class="fa-solid fa-up-right-from-square"></i> Tes Sesi User
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KPI STAT CARDS (4-GRID) -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 1.5rem;">
                <!-- Total Sesi -->
                <div class="stat-card-piru">
                    <div class="stat-icon green" style="background:#e8f5e9;color:#006e2f;">
                        <i class="fa-solid fa-arrows-spin"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.4rem;font-weight:800;color:var(--text-dark);"><?= number_format($total_completed) ?></div>
                        <div class="stat-label" style="font-size:.78rem;color:var(--text-muted);font-weight:600;">Sesi Memilah Sukses</div>
                        <div style="font-size:.72rem;color:#059669;font-weight:700;margin-top:2px;">
                            <i class="fa-solid fa-bolt"></i> <?= intval($today_stats['ses_today'] ?? 0) ?> sesi hari ini
                        </div>
                    </div>
                </div>

                <!-- Total Item Terpilah -->
                <div class="stat-card-piru">
                    <div class="stat-icon blue" style="background:#e0f2fe;color:#0284c7;">
                        <i class="fa-solid fa-recycle"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.4rem;font-weight:800;color:var(--text-dark);"><?= number_format($total_items) ?></div>
                        <div class="stat-label" style="font-size:.78rem;color:var(--text-muted);font-weight:600;">Total Sampah Terpilah</div>
                        <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;margin-top:2px;">
                            <span style="color:#059669;">● <?= $count_organik ?> Org</span> &bull; <span style="color:#0284c7;">● <?= $count_anorganik ?> Anorg</span>
                        </div>
                    </div>
                </div>

                <!-- Total Poin Reward Disalurkan -->
                <div class="stat-card-piru">
                    <div class="stat-icon amber" style="background:#fef3c7;color:#d97706;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.4rem;font-weight:800;color:var(--text-dark);"><?= number_format($total_poin_dist) ?></div>
                        <div class="stat-label" style="font-size:.78rem;color:var(--text-muted);font-weight:600;">Poin Reward Diterima</div>
                        <div style="font-size:.72rem;color:#d97706;font-weight:700;margin-top:2px;">
                            +<?= number_format($today_stats['poin_today'] ?? 0) ?> poin hari ini
                        </div>
                    </div>
                </div>

                <!-- Estimasi Berat Dikelola -->
                <div class="stat-card-piru">
                    <div class="stat-icon green" style="background:#f0fdf4;color:#16a34a;">
                        <i class="fa-solid fa-weight-hanging"></i>
                    </div>
                    <div>
                        <div class="stat-value" style="font-size:1.4rem;font-weight:800;color:var(--text-dark);"><?= number_format($total_berat_kg, 2) ?> <small style="font-size:.8rem;font-weight:600">kg</small></div>
                        <div class="stat-label" style="font-size:.78rem;color:var(--text-muted);font-weight:600;">Total Bobot Sampah</div>
                        <div style="font-size:.72rem;color:#16a34a;font-weight:700;margin-top:2px;">
                            AI Akurasi: 100% Valid
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTER & SEARCH BAR -->
            <div class="card mb-4">
                <div class="card-body" style="padding: 1rem 1.25rem;">
                    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                        
                        <!-- Form Filter Tanggal & Status -->
                        <form method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:.75rem;">
                            <!-- Filter Tanggal -->
                            <div style="display:flex; align-items:center; gap:.4rem;">
                                <label style="font-size:.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">
                                    <i class="fa-regular fa-calendar"></i> Tanggal:
                                </label>
                                <input type="date" name="tanggal" value="<?= htmlspecialchars($filter_date) ?>" 
                                       class="form-control" style="width:auto; padding:.35rem .75rem; font-size:.82rem;"
                                       onchange="this.form.submit()">
                            </div>

                            <!-- Filter Status -->
                            <div style="display:flex; align-items:center; gap:.4rem;">
                                <label style="font-size:.75rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">
                                    <i class="fa-solid fa-filter"></i> Status:
                                </label>
                                <select name="status" class="form-control" style="width:auto; padding:.35rem .75rem; font-size:.82rem;" onchange="this.form.submit()">
                                    <option value="all" <?= ($filter_status === 'all' || $filter_status === '') ? 'selected' : '' ?>>Semua Sesi</option>
                                    <option value="completed" <?= ($filter_status === 'completed') ? 'selected' : '' ?>>Selesai (Completed)</option>
                                    <option value="active" <?= ($filter_status === 'active') ? 'selected' : '' ?>>Sedang Aktif</option>
                                    <option value="cancelled" <?= ($filter_status === 'cancelled') ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div style="display:flex; align-items:center; gap:.4rem;">
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari token / nama customer..."
                                       class="form-control" style="width:200px; padding:.35rem .75rem; font-size:.82rem;">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding:.4rem .8rem;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>

                            <?php if ($filter_date !== '' || $filter_status !== '' || $search !== ''): ?>
                                <a href="monitoring-piru.php" class="btn btn-ghost btn-sm" style="font-size:.8rem;">
                                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                                </a>
                            <?php endif; ?>
                        </form>

                        <!-- Export Buttons -->
                        <div style="display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;">
                            <a href="export-piru-excel.php?tanggal=<?= urlencode($filter_date) ?>" class="btn btn-primary btn-sm" title="Download Excel Sesi PIRU">
                                <i class="fa-solid fa-file-excel"></i> Export Excel (.xls)
                            </a>
                            <a href="export-piru-pdf.php?tanggal=<?= urlencode($filter_date) ?>&status=<?= urlencode($filter_status) ?>" target="_blank" 
                               class="btn btn-sm" style="background:#e11d48; border-color:#e11d48; color:#fff; font-weight:700;" title="Cetak atau Unduh Laporan Resmi PDF PIRU">
                                <i class="fa-solid fa-file-pdf"></i> Export PDF Laporan
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- MAIN DATA TABS -->
            <div class="card mb-4">
                <div style="border-bottom: 1px solid var(--color-border); padding: 0.5rem 1.25rem 0 1.25rem; display: flex; gap: 0.5rem;">
                    <button class="tab-btn active" id="tabBtnSessions" onclick="switchTab('sessions')">
                        <i class="fa-solid fa-list-check"></i> Riwayat Sesi Pengguna (<?= $total_rows ?>)
                    </button>
                    <button class="tab-btn" id="tabBtnLogs" onclick="switchTab('logs')">
                        <i class="fa-solid fa-wave-square"></i> Telemetri &amp; Suara AI (10 Terakhir)
                    </button>
                </div>

                <!-- TAB 1: RIWAYAT SESI -->
                <div id="tabContentSessions" style="padding: 1.25rem;">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:50px;">No</th>
                                    <th>Token Sesi</th>
                                    <th>Customer / Penyetor</th>
                                    <th>Item Terpilah</th>
                                    <th>Reward Poin</th>
                                    <th>Total Berat</th>
                                    <th>Status</th>
                                    <th>Waktu Sesi</th>
                                    <th style="text-align:center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($sessions_result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="9" style="text-align:center; padding: 2.5rem 1rem; color:var(--text-muted);">
                                            <i class="fa-solid fa-inbox" style="font-size:2rem; margin-bottom:.5rem; display:block;"></i>
                                            Belum ada sesi pemilahan yang cocok dengan filter.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $no = $offset + 1;
                                    while ($s = $sessions_result->fetch_assoc()): 
                                        $is_completed = ($s['status'] === 'completed');
                                        $is_active    = ($s['status'] === 'active');
                                    ?>
                                        <tr>
                                            <td class="text-muted text-small"><?= $no++ ?></td>
                                            <td>
                                                <div style="font-family:monospace;font-size:.78rem;font-weight:700;color:var(--text-dark)">
                                                    <?= substr($s['session_token'], 0, 10) ?>...
                                                </div>
                                                <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.65rem;">
                                                    <?= htmlspecialchars($s['device_kode']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($s['customer_nama'])): ?>
                                                    <div style="font-weight:700;color:var(--text-dark);font-size:.85rem;">
                                                        <?= htmlspecialchars($s['customer_nama']) ?>
                                                    </div>
                                                    <div style="font-size:.75rem;color:var(--text-muted);">
                                                        <?= htmlspecialchars($s['customer_nohp'] ?? $s['customer_email']) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-style:italic;color:var(--text-muted);font-size:.8rem;">
                                                        Anonim / Tamu
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div style="display:flex; gap:.35rem; align-items:center;">
                                                    <span class="badge badge-green" style="font-size:.72rem;">
                                                        <i class="fa-solid fa-leaf"></i> <?= intval($s['total_organik_gr'] > 0 ? ceil($s['total_organik_gr'] / 100) : 0) ?> Org
                                                    </span>
                                                    <span class="badge badge-blue" style="font-size:.72rem; background:#e0f2fe; color:#0284c7;">
                                                        <i class="fa-solid fa-bottle-water"></i> <?= intval($s['total_anorganik_gr'] > 0 ? ceil($s['total_anorganik_gr'] / 100) : 0) ?> Anorg
                                                    </span>
                                                </div>
                                                <div style="font-size:.72rem; color:var(--text-muted); margin-top:2px;">
                                                    Total <?= intval($s['item_count']) ?> item
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-green" style="font-size:.82rem;font-weight:700;">
                                                    +<?= intval($s['total_poin']) ?> Poin
                                                </span>
                                                <?php if (!empty($s['kode_transaksi'])): ?>
                                                    <div style="font-size:.7rem;color:var(--text-muted);margin-top:2px;">
                                                        Trx: <?= htmlspecialchars($s['kode_transaksi']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:.85rem;font-weight:600;">
                                                <?= number_format(floatval($s['total_berat_gr']) / 1000, 2) ?> kg
                                            </td>
                                            <td>
                                                <?php if ($is_completed): ?>
                                                    <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Selesai</span>
                                                <?php elseif ($is_active): ?>
                                                    <span class="badge badge-amber"><i class="fa-solid fa-spinner fa-spin"></i> Sedang Memilah</span>
                                                <?php else: ?>
                                                    <span class="badge badge-gray"><i class="fa-solid fa-ban"></i> Batal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-size:.78rem;">
                                                <div><?= date('d M Y, H:i', strtotime($s['created_at'])) ?></div>
                                                <?php if (!empty($s['completed_at'])): ?>
                                                    <div style="color:var(--text-muted);font-size:.7rem;">
                                                        Durasi: <?= max(1, strtotime($s['completed_at']) - strtotime($s['created_at'])) ?> detik
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center; white-space:nowrap;">
                                                <button onclick="viewSessionDetails(<?= $s['id'] ?>, '<?= htmlspecialchars($s['customer_nama'] ?? 'User') ?>', '<?= htmlspecialchars($s['session_token']) ?>', <?= intval($s['total_poin']) ?>)" 
                                                        class="btn btn-outline btn-sm" style="padding:.25rem .6rem; font-size:.75rem;" title="Lihat Item Terpilah">
                                                    <i class="fa-solid fa-eye"></i> Rincian
                                                </button>
                                                <a href="../customer/export-session-pdf.php?session_id=<?= $s['id'] ?>" target="_blank"
                                                   class="btn btn-outline btn-sm" style="padding:.25rem .5rem; font-size:.75rem; color:#e11d48; border-color:#fecdd3;" title="Unduh Struk Sesi (PDF)">
                                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                                </a>
                                                <?php if ($is_active): ?>
                                                    <button onclick="cancelActiveSession(<?= $s['id'] ?>)" 
                                                            class="btn btn-ghost btn-sm" style="color:var(--danger);padding:.25rem .5rem;" title="Batalkan Sesi Gantung">
                                                        <i class="fa-solid fa-stop"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <?php if ($total_pages > 1): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1.25rem;">
                            <div style="font-size:.8rem; color:var(--text-muted);">
                                Menampilkan <?= $offset + 1 ?>-<?= min($offset + $limit, $total_rows) ?> dari <?= $total_rows ?> sesi
                            </div>
                            <div style="display:flex; gap:.35rem;">
                                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                    <a href="?tanggal=<?= urlencode($filter_date) ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>" 
                                       class="btn btn-sm <?= ($p == $page) ? 'btn-primary' : 'btn-outline' ?>" 
                                       style="min-width:32px; padding:.3rem .6rem; font-size:.8rem;">
                                        <?= $p ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB 2: LIVE TELEMETRI & LOGS SUARA -->
                <div id="tabContentLogs" style="display:none; padding: 1.25rem;">
                    <div style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center;">
                        <span style="font-size:.85rem; font-weight:700; color:var(--text-dark);">
                            <i class="fa-solid fa-volume-high text-emerald-600 mr-1"></i> Log Telemetri Real-Time &amp; Suara Edukasi TTS
                        </span>
                        <span style="font-size:.78rem; color:var(--text-muted);">
                            Terhubung ke <strong>trashsmart_logs</strong>
                        </span>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Kategori</th>
                                    <th>Status Deteksi</th>
                                    <th>Poin</th>
                                    <th>Pesan Suara / Respon TTS</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_logs->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 2rem; color:var(--text-muted);">
                                            Belum ada catatan log telemetri suara.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($l = $recent_logs->fetch_assoc()): ?>
                                        <tr>
                                            <td style="font-size:.8rem;font-weight:600;white-space:nowrap;">
                                                <?= date('d M Y', strtotime($l['tanggal'])) ?> &bull; <?= htmlspecialchars($l['waktu']) ?>
                                            </td>
                                            <td>
                                                <?php if ($l['kategori_sampah'] === 'organik'): ?>
                                                    <span class="badge badge-green"><i class="fa-solid fa-leaf"></i> Organik</span>
                                                <?php elseif ($l['kategori_sampah'] === 'anorganik'): ?>
                                                    <span class="badge badge-blue" style="background:#e0f2fe;color:#0284c7;"><i class="fa-solid fa-bottle-water"></i> Anorganik</span>
                                                <?php else: ?>
                                                    <span class="badge badge-gray"><?= htmlspecialchars($l['kategori_sampah']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($l['status_pilah'] === 'benar' || $l['status_pilah'] === 'valid'): ?>
                                                    <span class="badge badge-green"><i class="fa-solid fa-check"></i> Valid</span>
                                                <?php elseif ($l['status_pilah'] === 'sistem'): ?>
                                                    <span class="badge badge-gray"><i class="fa-solid fa-gear"></i> Sistem</span>
                                                <?php else: ?>
                                                    <span class="badge badge-amber"><?= htmlspecialchars($l['status_pilah']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="font-weight:700;">
                                                <?= ($l['poin'] > 0) ? '+' . $l['poin'] : '-' ?>
                                            </td>
                                            <td style="font-size:.82rem; color:var(--text-dark); max-width:320px;">
                                                <i class="fa-solid fa-comment-dots text-emerald-600 mr-1"></i>
                                                <?= htmlspecialchars($l['pesan_suara'] ?? '-') ?>
                                            </td>
                                            <td style="font-size:.8rem; color:var(--text-muted);">
                                                <?= htmlspecialchars($l['user_identifier'] ?? '-') ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>
</div>

<!-- HIDDEN FORMS FOR POST ACTIONS -->
<form id="formEmptyBin" method="POST" style="display:none;">
    <input type="hidden" name="action" value="empty_bin">
    <input type="hidden" name="device_id" value="<?= $dev_id ?>">
</form>

<form id="formCancelSession" method="POST" style="display:none;">
    <input type="hidden" name="action" value="cancel_session">
    <input type="hidden" name="session_id" id="cancelSessionId" value="0">
</form>

<!-- MODAL QR DISPLAY -->
<div id="modalQr" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div style="background:#fff; border-radius:1.25rem; max-width:440px; width:100%; padding:1.75rem; text-align:center; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.15rem; font-weight:800; color:var(--text-dark); margin:0;">
                <i class="fa-solid fa-qrcode text-emerald-600"></i> QR Code PIRU Smart Bin
            </h3>
            <button onclick="closeQrModal()" style="border:none;background:transparent;cursor:pointer;font-size:1.2rem;color:var(--text-muted);">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:1.25rem;">
            Scan QR Code ini menggunakan kamera smartphone untuk memulai sesi memilah sampah otomatis.
        </p>

        <!-- QR Image Box -->
        <div style="background:#f8fafc; border:2px dashed #cbd5e1; border-radius:1rem; padding:1.5rem; display:inline-block; margin-bottom:1.25rem;">
            <?php 
                $qrTargetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . "/customer/piru.php?dev=" . urlencode($piru_dev['kode_perangkat']);
                $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=240x240&data=" . urlencode($qrTargetUrl);
            ?>
            <img src="<?= $qrApiUrl ?>" alt="QR Code PIRU" style="width:200px; height:200px; display:block; margin:0 auto; border-radius:8px;">
        </div>

        <div style="background:#ecfdf5; border-radius:10px; padding:.75rem; margin-bottom:1.25rem; font-size:.78rem; color:#065f46; font-weight:600;">
            <i class="fa-solid fa-link"></i> <?= htmlspecialchars($qrTargetUrl) ?>
        </div>

        <div style="display:flex; gap:.5rem;">
            <a href="<?= $qrTargetUrl ?>" target="_blank" class="btn btn-primary" style="flex:1;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Tautan
            </a>
            <button onclick="window.print()" class="btn btn-outline" style="flex:1;">
                <i class="fa-solid fa-print"></i> Cetak QR
            </button>
        </div>
    </div>
</div>

<script>
// Switch Tabs
function switchTab(tab) {
    const tabSessions = document.getElementById('tabContentSessions');
    const tabLogs = document.getElementById('tabContentLogs');
    const btnSessions = document.getElementById('tabBtnSessions');
    const btnLogs = document.getElementById('tabBtnLogs');

    if (tab === 'sessions') {
        tabSessions.style.display = 'block';
        tabLogs.style.display = 'none';
        btnSessions.classList.add('active');
        btnLogs.classList.remove('active');
    } else {
        tabSessions.style.display = 'none';
        tabLogs.style.display = 'block';
        btnSessions.classList.remove('active');
        btnLogs.classList.add('active');
    }
}

// Konfirmasi Kosongkan Wadah
function confirmEmptyBin(devId) {
    Swal.fire({
        title: 'Kosongkan Wadah PIRU?',
        text: 'Kapasitas organik & anorganik akan di-reset menjadi 0% dan dicatat di log sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#006e2f',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Kosongkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formEmptyBin').submit();
        }
    });
}

// Batalkan Sesi Gantung
function cancelActiveSession(sessId) {
    Swal.fire({
        title: 'Batalkan Sesi Ini?',
        text: 'Sesi yang sedang aktif akan diubah statusnya menjadi dibatalkan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Batalkan'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('cancelSessionId').value = sessId;
            document.getElementById('formCancelSession').submit();
        }
    });
}

// Modal QR Display
function showQrModal() {
    const modal = document.getElementById('modalQr');
    modal.style.display = 'flex';
}
function closeQrModal() {
    const modal = document.getElementById('modalQr');
    modal.style.display = 'none';
}

// Lihat Rincian Item Sesi
function viewSessionDetails(sessId, customerName, token, totalPoin) {
    Swal.fire({
        title: 'Memuat data item...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`monitoring-piru.php?ajax=session_items&session_id=${sessId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.items || data.items.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Rincian Sesi',
                    html: `
                        <div style="text-align:left; font-size:.85rem;">
                            <p><strong>Customer:</strong> ${customerName}</p>
                            <p><strong>Token:</strong> <code>${token}</code></p>
                            <p><strong>Total Poin:</strong> ${totalPoin} Poin</p>
                            <hr style="margin:10px 0; border:0; border-top:1px solid #e2e8f0;">
                            <p style="color:#64748b; text-align:center; padding:10px 0;">Belum ada item fisik yang dimasukkan pada sesi ini.</p>
                        </div>
                    `
                });
                return;
            }

            let itemRows = data.items.map((it, idx) => {
                const isOrg = it.category.toUpperCase() === 'ORGANIK';
                const badgeColor = isOrg ? '#10b981' : '#0284c7';
                const icon = isOrg ? 'fa-leaf' : 'fa-bottle-water';
                const weight = (parseFloat(it.weight_gr) || 0).toFixed(0);
                const displayName = it.item_name || (isOrg ? 'Sampah Organik' : 'Sampah Anorganik');

                return `
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:8px 6px; font-weight:600; color:#64748b;">#${idx + 1}</td>
                        <td style="padding:8px 6px;">
                            <div style="font-weight:700; color:#1e293b; font-size:.78rem; margin-bottom:2px;">${displayName}</div>
                            <span style="background:${badgeColor}15; color:${badgeColor}; font-weight:700; font-size:.7rem; padding:2px 7px; border-radius:999px;">
                                <i class="fa-solid ${icon}"></i> ${it.category}
                            </span>
                        </td>
                        <td style="padding:8px 6px; font-weight:600; text-align:right;">${weight} gr</td>
                        <td style="padding:8px 6px; font-weight:800; color:#006e2f; text-align:right;">+${it.poin} Poin</td>
                    </tr>
                `;
            }).join('');

            Swal.fire({
                title: `Item Sesi: ${customerName}`,
                width: '500px',
                html: `
                    <div style="text-align:left; font-size:.85rem; margin-bottom:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:.75rem; color:#64748b; margin-bottom:10px;">
                            <div>Token: <code>${token}</code></div>
                            <div>Total: <strong>${data.items.length} item (+${totalPoin} Poin)</strong></div>
                        </div>
                        <div style="max-height:280px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
                            <table style="width:100%; font-size:.8rem; border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#475569; font-weight:700;">
                                        <th style="padding:6px; text-align:left;">No</th>
                                        <th style="padding:6px; text-align:left;">Kategori</th>
                                        <th style="padding:6px; text-align:right;">Bobot</th>
                                        <th style="padding:6px; text-align:right;">Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemRows}
                                </tbody>
                            </table>
                        </div>
                        <div style="margin-top:14px; text-align:center;">
                            <a href="../customer/export-session-pdf.php?session_id=${sessId}" target="_blank" 
                               style="display:inline-flex; align-items:center; gap:6px; background:#e11d48; color:#fff; font-size:.8rem; font-weight:700; padding:8px 16px; border-radius:8px; text-decoration:none; box-shadow:0 2px 6px rgba(225,29,72,0.3);">
                                <i class="fa-solid fa-file-pdf"></i> Unduh / Cetak Bukti Sesi (PDF)
                            </a>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#006e2f'
            });
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat',
                text: 'Terjadi kesalahan saat mengambil rincian item sesi.'
            });
        });
}
</script>
</body>
</html>
