<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pesan_sukses = '';
$pesan_error  = '';

// 1. Handle Action: Kosongkan Wadah / Reset Kapasitas Perangkat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'empty_bin') {
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
                ) VALUES (?, ?, ?, 'sistem', 'sistem', 0, 0, 'Petugas kebersihan telah mengosongkan wadah sampah. Kapasitas kembali 0%.', 'Petugas Kebersihan (Admin)')
            ");
            $stmtLog->bind_param('iss', $dev_id, $nowDate, $nowTime);
            $stmtLog->execute();

            $pesan_sukses = "Wadah perangkat berhasil dikosongkan dan status di-reset ke Online!";
        } else {
            $pesan_error = "Gagal mereset perangkat: " . $conn->error;
        }
    }
}

// 2. Filter & Date selection (Default Hari ini)
$tanggal_terpilih = $_GET['tanggal'] ?? date('Y-m-d');
$device_filter    = intval($_GET['device_id'] ?? 0);
$status_filter    = trim($_GET['status_pilah'] ?? '');

// 3. Query All Devices
$devices_query = $conn->query("SELECT * FROM trashsmart_devices ORDER BY status = 'warning' DESC, id ASC");
$devices = [];
$total_online = 0;
$total_warning = 0;
$total_maintenance = 0;

while ($d = $devices_query->fetch_assoc()) {
    $devices[] = $d;
    if ($d['status'] === 'online') $total_online++;
    elseif ($d['status'] === 'warning') $total_warning++;
    elseif ($d['status'] === 'maintenance') $total_maintenance++;
}

// 4. Query Daily Summary Metrics for Selected Date
$sum_sql = "
    SELECT 
        COUNT(*) as total_interaksi,
        COALESCE(SUM(berat_kg), 0) as total_berat,
        COALESCE(SUM(CASE WHEN kategori_sampah = 'organik' THEN berat_kg ELSE 0 END), 0) as berat_organik,
        COALESCE(SUM(CASE WHEN kategori_sampah = 'anorganik' THEN berat_kg ELSE 0 END), 0) as berat_anorganik,
        COALESCE(SUM(CASE WHEN status_pilah = 'benar' THEN 1 ELSE 0 END), 0) as count_benar,
        COALESCE(SUM(CASE WHEN status_pilah = 'keliru' THEN 1 ELSE 0 END), 0) as count_keliru,
        COALESCE(SUM(poin), 0) as total_poin_reward
    FROM trashsmart_logs
    WHERE tanggal = ? " . ($device_filter > 0 ? "AND device_id = $device_filter" : "");
$stmtSum = $conn->prepare($sum_sql);
$stmtSum->bind_param('s', $tanggal_terpilih);
$stmtSum->execute();
$daily_metrics = $stmtSum->get_result()->fetch_assoc();

$total_interaksi_hari_ini = $daily_metrics['total_interaksi'] ?? 0;
$total_berat_hari_ini     = floatval($daily_metrics['total_berat'] ?? 0);
$berat_organik_hari_ini   = floatval($daily_metrics['berat_organik'] ?? 0);
$berat_anorganik_hari_ini = floatval($daily_metrics['berat_anorganik'] ?? 0);
$count_benar              = intval($daily_metrics['count_benar'] ?? 0);
$count_keliru             = intval($daily_metrics['count_keliru'] ?? 0);
$total_pilah_aktif        = $count_benar + $count_keliru;
$akurasi_persen           = ($total_pilah_aktif > 0) ? round(($count_benar / $total_pilah_aktif) * 100, 1) : 100;

// 5. Query Logs Table with Pagination
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 12;
$offset = ($page - 1) * $limit;

$where_clauses = ["l.tanggal = ?"];
$bind_types    = "s";
$bind_params   = [$tanggal_terpilih];

if ($device_filter > 0) {
    $where_clauses[] = "l.device_id = ?";
    $bind_types     .= "i";
    $bind_params[]   = $device_filter;
}

if ($status_filter !== '') {
    $where_clauses[] = "l.status_pilah = ?";
    $bind_types     .= "s";
    $bind_params[]   = $status_filter;
}

$where_str = implode(" AND ", $where_clauses);

// Count Total Logs
$stmtCount = $conn->prepare("SELECT COUNT(*) as c FROM trashsmart_logs l WHERE $where_str");
$stmtCount->bind_param($bind_types, ...$bind_params);
$stmtCount->execute();
$total_logs = $stmtCount->get_result()->fetch_assoc()['c'] ?? 0;
$total_pages = ceil($total_logs / $limit);

// Fetch Logs Rows
$log_sql = "
    SELECT l.*, d.kode_perangkat, d.nama_perangkat, d.lokasi
    FROM trashsmart_logs l
    JOIN trashsmart_devices d ON l.device_id = d.id
    WHERE $where_str
    ORDER BY l.waktu DESC
    LIMIT ? OFFSET ?
";
$stmtLogs = $conn->prepare($log_sql);
$bind_types_paged = $bind_types . "ii";
$bind_params_paged = array_merge($bind_params, [$limit, $offset]);
$stmtLogs->bind_param($bind_types_paged, ...$bind_params_paged);
$stmtLogs->execute();
$logs_result = $stmtLogs->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring TrashSmart — Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
    <style>
        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }
        .device-card {
            background: #fff;
            border-radius: var(--radius-xl);
            border: 1px solid var(--color-border);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow-card);
            transition: box-shadow .2s, transform .2s;
        }
        .device-card:hover {
            box-shadow: var(--shadow-float);
            transform: translateY(-2px);
        }
        .device-card.warning-card {
            border-color: #fca5a5;
            background: #fffafa;
        }
        .gauge-track {
            height: 7px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 4px;
        }
        .gauge-fill {
            height: 100%;
            border-radius: 999px;
            transition: width .4s ease;
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
                    <h1>Monitoring TrashSmart</h1>
                    <p>Telemetri baki inspeksi, sensor timbangan, dan edukasi suara real-time</p>
                </div>
            </div>
            <div class="topbar-right">
                <div class="poin-badge">
                    <i class="fa-solid fa-satellite-dish"></i> IoT Active
                </div>
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">

            <!-- NOTIFIKASI SWEETALERT -->
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

            <!-- ACTION & FILTER BAR -->
            <div class="card mb-4">
                <div class="card-body" style="padding: 1rem 1.25rem;">
                    <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                        <!-- Form Filter Tanggal & Device -->
                        <form method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:.75rem;">
                            <div style="display:flex; align-items:center; gap:.5rem;">
                                <label style="font-size:.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">
                                    <i class="fa-regular fa-calendar"></i> Tanggal:
                                </label>
                                <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_terpilih) ?>" 
                                       class="form-control" style="width:auto; padding:.4rem .8rem; font-size:.85rem; font-weight:600;"
                                       onchange="this.form.submit()">
                            </div>

                            <div style="display:flex; align-items:center; gap:.5rem;">
                                <label style="font-size:.78rem; font-weight:700; color:var(--text-muted); text-transform:uppercase;">
                                    <i class="fa-solid fa-microchip"></i> Unit:
                                </label>
                                <select name="device_id" class="form-control" style="width:auto; padding:.4rem .8rem; font-size:.85rem;" onchange="this.form.submit()">
                                    <option value="0">Semua Unit (<?= count($devices) ?>)</option>
                                    <?php foreach ($devices as $d): ?>
                                        <option value="<?= $d['id'] ?>" <?= ($device_filter == $d['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['kode_perangkat'] . ' — ' . $d['nama_perangkat']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($device_filter > 0 || $tanggal_terpilih !== date('Y-m-d')): ?>
                                <a href="monitoring-trashsmart.php" class="btn btn-ghost btn-sm">
                                    <i class="fa-solid fa-rotate-left"></i> Reset
                                </a>
                            <?php endif; ?>
                        </form>

                        <!-- Tombol Export Excel & PDF -->
                        <div style="display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;">
                            <a href="export-trashsmart-excel.php?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>" 
                               class="btn btn-primary btn-sm"
                               title="Download Laporan Harian dalam Format Microsoft Excel">
                                <i class="fa-solid fa-file-excel"></i> Export Excel (.xls)
                            </a>

                            <a href="export-trashsmart-pdf.php?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>" 
                               target="_blank"
                               class="btn btn-danger btn-sm"
                               title="Cetak atau Unduh Laporan Resmi PDF">
                                <i class="fa-solid fa-file-pdf"></i> Export PDF Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STAT CARDS (METRIK KPI) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-satellite-dish"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $total_online ?> <span style="font-size:.8rem;font-weight:500;color:var(--text-muted)">/ <?= count($devices) ?> Online</span></div>
                        <div class="stat-label"><?= ($total_warning > 0) ? '<strong style="color:var(--danger)">' . $total_warning . ' Unit Kritis</strong>' : 'Seluruh unit operasional normal' ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-weight-scale"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_berat_hari_ini, 2) ?> <span style="font-size:.8rem;font-weight:500;color:var(--text-muted)">kg</span></div>
                        <div class="stat-label"><?= number_format($berat_organik_hari_ini, 1) ?>kg Org &bull; <?= number_format($berat_anorganik_hari_ini, 1) ?>kg Anorg</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $akurasi_persen ?>%</div>
                        <div class="stat-label"><?= $count_benar ?> Tepat &bull; <?= $count_keliru ?> Salah Kamar</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fa-solid fa-volume-high"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $count_keliru ?> <span style="font-size:.8rem;font-weight:500;color:var(--text-muted)">kali</span></div>
                        <div class="stat-label">Suara Nudge Terpicu Hari Ini</div>
                    </div>
                </div>
            </div>

            <!-- DAFTAR UNIT TRASHSMART -->
            <div class="card mb-4">
                <div class="card-header">
                    <div>
                        <h3>Status Unit TrashSmart Lapangan (<?= count($devices) ?> Unit)</h3>
                        <p style="font-size:.75rem; color:var(--text-muted); margin-top:2px;">Pemantauan kapasitas wadah organik/anorganik dan sensor baterai</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="device-grid">
                        <?php foreach ($devices as $dev): 
                            $is_warn = ($dev['status'] === 'warning' || $dev['kapasitas_anorganik'] >= 85 || $dev['kapasitas_organik'] >= 85);
                        ?>
                        <div class="device-card <?= $is_warn ? 'warning-card' : '' ?>">
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:.5rem;">
                                    <span style="font-family:monospace; font-weight:700; font-size:.78rem; background:var(--bg-light); padding:.15rem .5rem; border-radius:4px; color:var(--text-muted);">
                                        <?= htmlspecialchars($dev['kode_perangkat']) ?>
                                    </span>
                                    <?php if ($dev['status'] === 'online'): ?>
                                        <span class="badge badge-green"><i class="fa-solid fa-circle" style="font-size:.45rem"></i> Online</span>
                                    <?php elseif ($dev['status'] === 'warning'): ?>
                                        <span class="badge badge-red"><i class="fa-solid fa-triangle-exclamation" style="font-size:.6rem"></i> Penuh</span>
                                    <?php elseif ($dev['status'] === 'maintenance'): ?>
                                        <span class="badge badge-amber"><i class="fa-solid fa-wrench" style="font-size:.6rem"></i> Perawatan</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">Offline</span>
                                    <?php endif; ?>
                                </div>

                                <h4 style="font-size:.95rem; font-weight:700; color:var(--text-dark); line-height:1.3; margin-bottom:.3rem;">
                                    <?= htmlspecialchars($dev['nama_perangkat']) ?>
                                </h4>
                                <p style="font-size:.75rem; color:var(--text-muted); margin-bottom:.85rem; display:flex; align-items:center; gap:.35rem;">
                                    <i class="fa-solid fa-location-dot" style="font-size:.7rem"></i>
                                    <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($dev['lokasi']) ?></span>
                                </p>

                                <!-- Level Kapasitas Wadah -->
                                <div style="background:var(--bg-light); padding:.75rem; border-radius:10px; margin-bottom:.85rem;">
                                    <!-- Organik -->
                                    <div style="margin-bottom:.5rem;">
                                        <div style="display:flex; justify-content:space-between; font-size:.75rem; font-weight:600;">
                                            <span style="color:var(--color-primary-dark);"><i class="fa-solid fa-leaf" style="font-size:.65rem"></i> Wadah Organik</span>
                                            <span style="<?= ($dev['kapasitas_organik'] >= 85) ? 'color:var(--danger);font-weight:700' : '' ?>"><?= $dev['kapasitas_organik'] ?>%</span>
                                        </div>
                                        <div class="gauge-track">
                                            <div class="gauge-fill" style="width:<?= min(100, $dev['kapasitas_organik']) ?>%; background: <?= ($dev['kapasitas_organik'] >= 85) ? 'var(--color-danger)' : 'var(--color-primary-container)' ?>;"></div>
                                        </div>
                                    </div>

                                    <!-- Anorganik -->
                                    <div>
                                        <div style="display:flex; justify-content:space-between; font-size:.75rem; font-weight:600;">
                                            <span style="color:#92400e;"><i class="fa-solid fa-bottle-water" style="font-size:.65rem"></i> Wadah Anorganik</span>
                                            <span style="<?= ($dev['kapasitas_anorganik'] >= 85) ? 'color:var(--danger);font-weight:700' : '' ?>"><?= $dev['kapasitas_anorganik'] ?>%</span>
                                        </div>
                                        <div class="gauge-track">
                                            <div class="gauge-fill" style="width:<?= min(100, $dev['kapasitas_anorganik']) ?>%; background: <?= ($dev['kapasitas_anorganik'] >= 85) ? 'var(--color-danger)' : 'var(--color-amber)' ?>;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div style="display:flex; justify-content:space-between; font-size:.75rem; color:var(--text-muted); margin-bottom:.85rem;">
                                    <span><i class="fa-solid fa-battery-three-quarters" style="color:var(--color-primary)"></i> <?= $dev['baterai'] ?>%</span>
                                    <span><i class="fa-solid fa-temperature-half" style="color:var(--color-amber)"></i> <?= $dev['suhu_celsius'] ?>°C</span>
                                    <span>Total: <strong><?= number_format($dev['total_berat_kg'], 1) ?> kg</strong></span>
                                </div>
                            </div>

                            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:.75rem; gap:.5rem;">
                                <button type="button" class="btn btn-ghost btn-sm" onclick='showDeviceDetail(<?= json_encode($dev) ?>)'>
                                    <i class="fa-solid fa-circle-info"></i> Detail
                                </button>

                                <form method="POST" onsubmit="return confirmEmptyBin(event, '<?= addslashes($dev['nama_perangkat']) ?>')">
                                    <input type="hidden" name="action" value="empty_bin">
                                    <input type="hidden" name="device_id" value="<?= $dev['id'] ?>">
                                    <button type="submit" class="btn <?= $is_warn ? 'btn-danger' : 'btn-ghost' ?> btn-sm">
                                        <i class="fa-solid fa-broom"></i> Kosongkan
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- LOG AKTIVITAS & TELEMETRI HARIAN -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Log Telemetri &amp; Aktivitas Pemilahan</h3>
                        <p style="font-size:.75rem; color:var(--text-muted); margin-top:2px;">
                            Menampilkan <?= number_format($total_logs) ?> log data pada tanggal <?= date('d F Y', strtotime($tanggal_terpilih)) ?>
                        </p>
                    </div>

                    <!-- Filter Tabs -->
                    <div style="display:flex; gap:.4rem; flex-wrap:wrap;">
                        <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>" 
                           class="filter-tab <?= ($status_filter === '') ? 'active' : '' ?>">
                            Semua
                        </a>
                        <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>&status_pilah=benar" 
                           class="filter-tab <?= ($status_filter === 'benar') ? 'active' : '' ?>">
                            <i class="fa-solid fa-check"></i> Tepat
                        </a>
                        <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>&status_pilah=keliru" 
                           class="filter-tab <?= ($status_filter === 'keliru') ? 'active' : '' ?>">
                            <i class="fa-solid fa-volume-high"></i> Salah Kamar
                        </a>
                        <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>&status_pilah=peringatan" 
                           class="filter-tab <?= ($status_filter === 'peringatan') ? 'active' : '' ?>">
                            <i class="fa-solid fa-triangle-exclamation"></i> Peringatan
                        </a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Unit Perangkat</th>
                                <th>Kategori</th>
                                <th>Status Pilah</th>
                                <th>Berat</th>
                                <th>Reward</th>
                                <th>Respon Edukasi Suara (Voice Nudge)</th>
                                <th>Pengguna</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs_result->num_rows === 0): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fa-solid fa-inbox"></i></div>
                                            <p>Tidak ada data telemetri untuk kriteria yang dipilih</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($log = $logs_result->fetch_assoc()): ?>
                                <tr>
                                    <td style="font-family:monospace; font-size:.8rem; color:var(--text-muted); white-space:nowrap;">
                                        <?= substr($log['waktu'], 0, 8) ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['kode_perangkat']) ?></strong>
                                        <div style="font-size:.72rem; color:var(--text-muted);"><?= htmlspecialchars($log['nama_perangkat']) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($log['kategori_sampah'] === 'organik'): ?>
                                            <span class="badge badge-green"><i class="fa-solid fa-leaf"></i> Organik</span>
                                        <?php elseif ($log['kategori_sampah'] === 'anorganik'): ?>
                                            <span class="badge badge-amber"><i class="fa-solid fa-bottle-water"></i> Anorganik</span>
                                        <?php else: ?>
                                            <span class="badge badge-gray"><i class="fa-solid fa-gear"></i> Sistem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($log['status_pilah'] === 'benar'): ?>
                                            <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> Tepat</span>
                                        <?php elseif ($log['status_pilah'] === 'keliru'): ?>
                                            <span class="badge badge-amber"><i class="fa-solid fa-triangle-exclamation"></i> Salah Kamar</span>
                                        <?php elseif ($log['status_pilah'] === 'peringatan'): ?>
                                            <span class="badge badge-red"><i class="fa-solid fa-triangle-exclamation"></i> Kritis</span>
                                        <?php else: ?>
                                            <span class="badge badge-gray">Sistem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= ($log['berat_kg'] > 0) ? number_format($log['berat_kg'], 3) . ' kg' : '-' ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($log['poin'] > 0): ?>
                                            <span style="font-weight:700; color:var(--color-primary-dark); background:var(--color-primary-light); padding:.15rem .45rem; border-radius:6px;">
                                                +<?= $log['poin'] ?> poin
                                            </span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:.78rem; max-width:280px; color:var(--text-body); font-style:italic;">
                                        <?= htmlspecialchars($log['pesan_suara'] ?? '-') ?>
                                    </td>
                                    <td style="font-size:.78rem; color:var(--text-muted); white-space:nowrap;">
                                        <i class="fa-regular fa-user" style="font-size:.7rem"></i> <?= htmlspecialchars($log['user_identifier']) ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div style="padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border);">
                    <span style="font-size:.75rem; color:var(--text-muted);">
                        Halaman <strong><?= $page ?></strong> dari <strong><?= $total_pages ?></strong>
                    </span>
                    <div style="display:flex; gap:.4rem;">
                        <?php if ($page > 1): ?>
                            <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>&status_pilah=<?= urlencode($status_filter) ?>&page=<?= $page - 1 ?>" 
                               class="btn btn-ghost btn-sm">
                                <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                            </a>
                        <?php endif; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?tanggal=<?= urlencode($tanggal_terpilih) ?>&device_id=<?= $device_filter ?>&status_pilah=<?= urlencode($status_filter) ?>&page=<?= $page + 1 ?>" 
                               class="btn btn-ghost btn-sm">
                                Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<!-- MODAL DETAIL SPESIFIKASI PERANGKAT (STANDARD ADMIN MODAL) -->
<div class="modal-overlay" id="modalDeviceDetail">
    <div class="modal" style="max-width:500px;">
        <div class="modal-header">
            <h3 style="display:flex; align-items:center; gap:.5rem;">
                <i class="fa-solid fa-trash-can-arrow-up" style="color:var(--color-primary)"></i> Detail Unit TrashSmart
            </h3>
            <button class="modal-close" onclick="closeDeviceModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="padding:1.25rem 1.5rem;">
            <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem;">
                <div style="width:42px; height:42px; background:var(--color-primary-light); border-radius:10px; display:flex; align-items:center; justify-content:center; color:var(--color-primary-dark); font-size:1.2rem;">
                    <i class="fa-solid fa-microchip"></i>
                </div>
                <div>
                    <span id="modalDeviceCode" style="font-family:monospace; font-weight:700; font-size:.78rem; background:var(--bg-light); padding:.1rem .4rem; border-radius:4px; color:var(--text-muted);"></span>
                    <h4 id="modalDeviceName" style="font-size:1rem; font-weight:700; color:var(--text-dark); margin-top:2px;"></h4>
                </div>
            </div>

            <table style="width:100%; font-size:.82rem;">
                <tbody>
                    <tr><td style="color:var(--text-muted); width:40%; padding:.4rem 0;">Lokasi</td><td id="modalDeviceLocation" style="font-weight:600; text-align:right;"></td></tr>
                    <tr><td style="color:var(--text-muted); padding:.4rem 0;">Koordinat GPS</td><td id="modalDeviceCoords" style="font-family:monospace; text-align:right;"></td></tr>
                    <tr><td style="color:var(--text-muted); padding:.4rem 0;">Versi Firmware</td><td id="modalDeviceFirmware" style="font-weight:600; text-align:right;"></td></tr>
                    <tr><td style="color:var(--text-muted); padding:.4rem 0;">Baterai &amp; Suhu</td><td id="modalDeviceBatteryTemp" style="font-weight:600; text-align:right;"></td></tr>
                    <tr><td style="color:var(--text-muted); padding:.4rem 0;">Terakhir Dikosongkan</td><td id="modalDeviceEmptied" style="font-weight:600; text-align:right;"></td></tr>
                    <tr><td style="color:var(--text-muted); padding:.4rem 0;">Last Ping IoT</td><td id="modalDevicePing" style="font-weight:600; text-align:right;"></td></tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer" style="padding:1rem 1.5rem; display:flex; justify-content:flex-end;">
            <button type="button" class="btn btn-ghost btn-sm" onclick="closeDeviceModal()">Tutup</button>
        </div>
    </div>
</div>

<script>
function showDeviceDetail(dev) {
    document.getElementById('modalDeviceCode').innerText = dev.kode_perangkat;
    document.getElementById('modalDeviceName').innerText = dev.nama_perangkat;
    document.getElementById('modalDeviceLocation').innerText = dev.lokasi;
    document.getElementById('modalDeviceCoords').innerText = (dev.latitude && dev.longitude) ? (dev.latitude + ', ' + dev.longitude) : '-';
    document.getElementById('modalDeviceFirmware').innerText = dev.firmware_version;
    document.getElementById('modalDeviceBatteryTemp').innerText = dev.baterai + '% | ' + dev.suhu_celsius + '°C';
    document.getElementById('modalDeviceEmptied').innerText = dev.terakhir_dikosongkan ? dev.terakhir_dikosongkan : 'Belum pernah';
    document.getElementById('modalDevicePing').innerText = dev.last_ping ? dev.last_ping : '-';

    document.getElementById('modalDeviceDetail').classList.add('open');
}

function closeDeviceModal() {
    document.getElementById('modalDeviceDetail').classList.remove('open');
}

function confirmEmptyBin(e, name) {
    e.preventDefault();
    const form = e.target;
    Swal.fire({
        title: 'Kosongkan Wadah?',
        text: 'Kapasitas wadah organik & anorganik untuk ' + name + ' akan di-reset menjadi 0% dan dicatat di log pemeliharaan.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#006e2f',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Kosongkan Wadah',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>
</body>
</html>
