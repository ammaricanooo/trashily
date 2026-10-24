<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$filter_date   = trim($_GET['tanggal'] ?? '');
$filter_status = trim($_GET['status'] ?? '');
$filter_device = trim($_GET['device'] ?? 'PIRU-DEV-001');

// Query Device Metadata
$stmtDev = $conn->prepare("SELECT * FROM trashsmart_devices WHERE kode_perangkat = ? LIMIT 1");
$stmtDev->bind_param('s', $filter_device);
$stmtDev->execute();
$device = $stmtDev->get_result()->fetch_assoc();

if (!$device) {
    $device = [
        'id' => 7,
        'kode_perangkat' => 'PIRU-DEV-001',
        'nama_perangkat' => 'PIRU Smart Bin #001',
        'lokasi' => 'Area Kantin Utama / EcoHub',
        'status' => 'online',
        'kapasitas_organik' => 25,
        'kapasitas_anorganik' => 30,
        'baterai' => 100,
        'suhu_celsius' => 28.5,
        'firmware_version' => 'v2.0.0-YOLO',
        'last_ping' => date('Y-m-d H:i:s')
    ];
}

// Build Filter Query
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

$where_str = implode(" AND ", $where);

// 1. Executive Summary KPIs
$kpi_sql = "
    SELECT 
        COUNT(*) as total_sesi,
        COALESCE(SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END), 0) as sesi_selesai,
        COALESCE(SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END), 0) as sesi_aktif,
        COALESCE(SUM(CASE WHEN s.status = 'cancelled' THEN 1 ELSE 0 END), 0) as sesi_batal,
        COALESCE(SUM(s.item_count), 0) as total_item,
        COALESCE(SUM(s.total_poin), 0) as total_poin,
        COALESCE(SUM(s.total_berat_gr), 0) as total_berat_gr,
        COALESCE(SUM(s.total_organik_gr), 0) as total_organik_gr,
        COALESCE(SUM(s.total_anorganik_gr), 0) as total_anorganik_gr
    FROM piru_sessions s
    WHERE $where_str
";
$stmtK = $conn->prepare($kpi_sql);
if (!empty($types)) {
    $stmtK->bind_param($types, ...$params);
}
$stmtK->execute();
$kpi = $stmtK->get_result()->fetch_assoc();

$total_sesi       = intval($kpi['total_sesi'] ?? 0);
$sesi_selesai     = intval($kpi['sesi_selesai'] ?? 0);
$total_item       = intval($kpi['total_item'] ?? 0);
$total_poin       = intval($kpi['total_poin'] ?? 0);
$total_berat_kg   = floatval(($kpi['total_berat_gr'] ?? 0) / 1000);
$berat_organik_kg = floatval(($kpi['total_organik_gr'] ?? 0) / 1000);
$berat_anorg_kg   = floatval(($kpi['total_anorganik_gr'] ?? 0) / 1000);
$success_rate     = ($total_sesi > 0) ? round(($sesi_selesai / $total_sesi) * 100, 1) : 100;

// 2. Query Detail Sesi Pemilahan
$data_sql = "
    SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
           t.kode_transaksi
    FROM piru_sessions s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN transaksi t ON s.transaksi_id = t.id
    WHERE $where_str
    ORDER BY s.created_at DESC
";
$stmtD = $conn->prepare($data_sql);
if (!empty($types)) {
    $stmtD->bind_param($types, ...$params);
}
$stmtD->execute();
$sessions = $stmtD->get_result();

// 3. Log Telemetri IoT & Suara AI PIRU
$devIdInt = intval($device['id']);
$log_sql = "
    SELECT * FROM trashsmart_logs 
    WHERE device_id = $devIdInt 
    " . ($filter_date !== '' ? "AND tanggal = '$filter_date'" : "") . "
    ORDER BY tanggal DESC, waktu DESC 
    LIMIT 15
";
$logs = $conn->query($log_sql);

$nomor_laporan = "PIRU-RPT-" . ($filter_date ? date('Ymd', strtotime($filter_date)) : date('Ymd')) . "-" . strtoupper(substr(md5($where_str), 0, 4));
$tgl_cetak = date('d F Y, H:i') . " WIB";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_PIRU_SmartBin_<?= $filter_date ? htmlspecialchars($filter_date) : 'Semua' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar (Hanya tampil di browser, otomatis hilang saat Print / PDF) */
        .no-print-bar {
            background: #064e29;
            background: linear-gradient(135deg, #064e29 0%, #006e2f 100%);
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.2);
        }
        .btn-print {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 20px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
        }
        .btn-print:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        .btn-back {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
        }
        .btn-back:hover { 
            color: #fff; 
            background: rgba(255,255,255,0.18);
        }

        /* A4 Page Container */
        .page {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 18mm 20mm;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            border-radius: 6px;
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #006e2f;
            padding-bottom: 12px;
            margin-bottom: 16px;
            position: relative;
        }
        .kop-header::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 1px;
            background: #a7f3d0;
        }
        .kop-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .kop-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .kop-title h1 {
            font-size: 19px;
            font-weight: 800;
            color: #006e2f;
            letter-spacing: -0.02em;
            line-height: 1.1;
        }
        .kop-title h1 span {
            color: #10b981;
        }
        .kop-title p {
            font-size: 9.5px;
            color: #475569;
            font-weight: 500;
            margin-top: 2px;
        }
        .kop-meta {
            text-align: right;
            font-size: 9.5px;
            color: #64748b;
        }
        .kop-meta strong {
            color: #0f172a;
            font-size: 10.5px;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin: 14px 0 16px 0;
        }
        .doc-title-box h2 {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .doc-title-box p {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Info Ringkas Meta Grid */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 10px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .meta-label { color: #64748b; }
        .meta-val { font-weight: 700; color: #0f172a; }

        /* KPI Matrix Cards */
        .kpi-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        .kpi-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 9px 8px;
            text-align: center;
        }
        .kpi-box.blue { background: #f0f9ff; border-color: #bae6fd; }
        .kpi-box.amber { background: #fffbeb; border-color: #fde68a; }
        .kpi-box.purple { background: #faf5ff; border-color: #e9d5ff; }
        .kpi-val {
            font-size: 15px;
            font-weight: 800;
            color: #166534;
            line-height: 1.2;
        }
        .kpi-box.blue .kpi-val { color: #0369a1; }
        .kpi-box.amber .kpi-val { color: #b45309; }
        .kpi-box.purple .kpi-val { color: #6b21a8; }
        .kpi-lbl {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 3px;
        }

        /* Section Headings */
        .section-header {
            font-size: 10.5px;
            font-weight: 800;
            color: #006e2f;
            border-left: 3px solid #006e2f;
            padding-left: 8px;
            margin: 16px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Report Tables */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5px;
        }
        .report-table th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .report-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #1e293b;
        }
        .report-table tr:nth-child(even) td {
            background: #fcfdfe;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .font-bold { font-weight: 700; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #e0f2fe; color: #075985; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Signatures Grid */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
            page-break-inside: avoid;
        }
        .sig-col {
            text-align: center;
            font-size: 10px;
            color: #334155;
        }
        .sig-space {
            height: 52px;
        }
        .sig-name {
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
        }

        /* Watermark & Footer */
        .doc-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* PRINT MEDIA RULES */
        @media print {
            body { background: #fff; font-size: 9pt; }
            .no-print-bar { display: none !important; }
            .page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .page-break { page-break-before: always; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- TOP ACTION BAR -->
    <div class="no-print-bar">
        <div style="display:flex; align-items:center; gap:16px;">
            <a href="monitoring-piru.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Monitoring
            </a>
            <div>
                <strong style="font-size:13px; letter-spacing:-0.2px;">Dokumen Laporan PIRU Smart Bin (IoT AI)</strong>
                <span style="font-size:11px; color:#a7f3d0; margin-left:8px;">
                    Filter: <?= $filter_date ? htmlspecialchars($filter_date) : 'Semua Tanggal' ?>
                    <?= $filter_status ? ' &bull; Status: ' . htmlspecialchars($filter_status) : '' ?>
                </span>
            </div>
        </div>

        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Cetak / Simpan PDF
        </button>
    </div>

    <!-- MAIN A4 PAGE -->
    <div class="page">
        
        <!-- KOP SURAT RESMI -->
        <div class="kop-header">
            <div class="kop-logo">
                <img src="../assets/brand.png" alt="Logo Trashily">
                <div class="kop-title">
                    <h1>Trashily<span>.id</span></h1>
                    <p>Platform Digital Bank Sampah Modern &amp; Inovasi Edukasi Ekologis</p>
                    <p style="font-size:8.5px; color:#64748b;">DIVISI RISET &amp; TEKNOLOGI IOT: SISTEM PIRU (PILAH RUNTAH CERDAS)</p>
                </div>
            </div>
            <div class="kop-meta">
                <p>No. Laporan: <strong class="font-mono"><?= $nomor_laporan ?></strong></p>
                <p>Dicetak: <?= $tgl_cetak ?></p>
                <p>Otoritas: <strong>Administrator Trashily</strong></p>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="doc-title-box">
            <h2>LAPORAN RESMI MONITORING PIRU SMART BIN</h2>
            <p>Rekapitulasi Pemilahan Sampah Berbasis Computer Vision YOLO &amp; Telemetri IoT</p>
        </div>

        <!-- METADATA DOKUMEN -->
        <div class="meta-grid">
            <div>
                <div class="meta-row">
                    <span class="meta-label">Kode Perangkat:</span>
                    <span class="meta-val font-mono"><?= htmlspecialchars($device['kode_perangkat']) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Nama Perangkat:</span>
                    <span class="meta-val"><?= htmlspecialchars($device['nama_perangkat']) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Lokasi Penempatan:</span>
                    <span class="meta-val"><?= htmlspecialchars($device['lokasi']) ?></span>
                </div>
            </div>
            <div>
                <div class="meta-row">
                    <span class="meta-label">Periode Data:</span>
                    <span class="meta-val"><?= $filter_date ? date('d F Y', strtotime($filter_date)) : 'Semua Rekam Historis' ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Filter Status:</span>
                    <span class="meta-val"><?= $filter_status ? ucfirst($filter_status) : 'Semua Status Sesi' ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Versi Firmware / AI:</span>
                    <span class="meta-val"><?= htmlspecialchars($device['firmware_version'] ?? 'v2.0.0-YOLO') ?></span>
                </div>
            </div>
        </div>

        <!-- EXECUTIVE KPI SUMMARY -->
        <div class="kpi-container">
            <div class="kpi-box">
                <div class="kpi-val"><?= number_format($total_sesi) ?></div>
                <div class="kpi-lbl">Total Sesi Pemilahan</div>
            </div>
            <div class="kpi-box blue">
                <div class="kpi-val"><?= number_format($total_item) ?> <small style="font-size:10px;">Item</small></div>
                <div class="kpi-lbl">Sampah Terpilah AI</div>
            </div>
            <div class="kpi-box amber">
                <div class="kpi-val">+<?= number_format($total_poin) ?></div>
                <div class="kpi-lbl">Poin Reward Diterbitkan</div>
            </div>
            <div class="kpi-box purple">
                <div class="kpi-val"><?= number_format($total_berat_kg, 2) ?> <small style="font-size:10px;">kg</small></div>
                <div class="kpi-lbl">Total Bobot Sampah</div>
            </div>
        </div>

        <!-- BAGIAN I: STATUS UNIT & KOMPARTEMEN -->
        <div class="section-header">
            <span>I. STATUS PERANGKAT PIRU &amp; KAPASITAS WADAH FISIK</span>
            <span style="font-size:8.5px; font-weight:normal; color:#64748b;">Dual JSN-SR04T Sensor</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Unit Smart Bin</th>
                    <th>Status Sistem</th>
                    <th class="text-center">Wadah Organik</th>
                    <th class="text-center">Wadah Anorganik</th>
                    <th class="text-center">Daya Baterai</th>
                    <th class="text-center">Suhu Unit</th>
                    <th class="text-right">Rasio Berat (Org / Anorg)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="font-bold">
                        <?= htmlspecialchars($device['nama_perangkat']) ?>
                        <div style="font-size:8px; color:#64748b; font-family:monospace;"><?= htmlspecialchars($device['kode_perangkat']) ?></div>
                    </td>
                    <td>
                        <span class="badge badge-green"><i class="fa-solid fa-circle-check"></i> ONLINE</span>
                    </td>
                    <td class="text-center font-bold" style="color: <?= intval($device['kapasitas_organik']) >= 80 ? '#dc2626' : '#15803d' ?>;">
                        <?= intval($device['kapasitas_organik']) ?>%
                    </td>
                    <td class="text-center font-bold" style="color: <?= intval($device['kapasitas_anorganik']) >= 80 ? '#dc2626' : '#0369a1' ?>;">
                        <?= intval($device['kapasitas_anorganik']) ?>%
                    </td>
                    <td class="text-center font-mono"><?= intval($device['baterai'] ?? 100) ?>%</td>
                    <td class="text-center font-mono"><?= floatval($device['suhu_celsius'] ?? 28.5) ?> &deg;C</td>
                    <td class="text-right font-bold">
                        <?= number_format($berat_organik_kg, 2) ?> kg / <?= number_format($berat_anorg_kg, 2) ?> kg
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- BAGIAN II: RINCIAN SESI PEMILAHAN PENGGUNA -->
        <div class="section-header">
            <span>II. LOG DETAIL SESI PEMILAHAN &amp; TRANSAKSI POIN</span>
            <span style="font-size:8.5px; font-weight:normal; color:#64748b;">Total: <?= $sessions->num_rows ?> Sesi</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:20px;" class="text-center">No</th>
                    <th style="width:75px;">Waktu Sesi</th>
                    <th style="width:95px;">Token / Unit</th>
                    <th>Customer / Penyetor</th>
                    <th class="text-center" style="width:65px;">Kategori</th>
                    <th class="text-right" style="width:50px;">Bobot</th>
                    <th class="text-right" style="width:55px;">Poin</th>
                    <th style="width:85px;">Kode Trx</th>
                    <th class="text-center" style="width:55px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sessions->num_rows === 0): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding:15px; color:#64748b;">
                            Tidak ada sesi pemilahan PIRU yang tercatat dalam periode filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    while ($s = $sessions->fetch_assoc()):
                        $is_completed = ($s['status'] === 'completed');
                        $is_active    = ($s['status'] === 'active');
                        $orgCount = $s['total_organik_gr'] > 0 ? ceil($s['total_organik_gr'] / 100) : 0;
                        $anorgCount = $s['total_anorganik_gr'] > 0 ? ceil($s['total_anorganik_gr'] / 100) : 0;
                    ?>
                    <tr>
                        <td class="text-center font-mono" style="color:#64748b;"><?= $no++ ?></td>
                        <td style="font-size:8.5px;">
                            <?= date('d/m/y H:i', strtotime($s['created_at'])) ?>
                        </td>
                        <td>
                            <div class="font-mono font-bold" style="font-size:8.5px;"><?= substr($s['session_token'], 0, 8) ?>...</div>
                            <span style="font-size:7.5px; color:#64748b;"><?= htmlspecialchars($s['device_kode']) ?></span>
                        </td>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($s['customer_nama'] ?? 'Pengguna Tamu') ?></div>
                            <div style="font-size:8px; color:#64748b;"><?= htmlspecialchars($s['customer_nohp'] ?? $s['customer_email'] ?? '-') ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-green" style="font-size:7.5px;"><?= $orgCount ?> Org</span>
                            <span class="badge badge-blue" style="font-size:7.5px;"><?= $anorgCount ?> Anorg</span>
                        </td>
                        <td class="text-right font-bold">
                            <?= number_format(floatval($s['total_berat_gr']) / 1000, 2) ?> kg
                        </td>
                        <td class="text-right font-bold" style="color:#006e2f;">
                            +<?= intval($s['total_poin']) ?>
                        </td>
                        <td class="font-mono font-bold" style="font-size:8px; color:#334155;">
                            <?= htmlspecialchars($s['kode_transaksi'] ?? '-') ?>
                        </td>
                        <td class="text-center">
                            <?php if ($is_completed): ?>
                                <span class="badge badge-green">SELESAI</span>
                            <?php elseif ($is_active): ?>
                                <span class="badge badge-amber">AKTIF</span>
                            <?php else: ?>
                                <span class="badge badge-gray">BATAL</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- BAGIAN III: LOG TELEMETRI & EDUKASI SUARA TERBARU -->
        <?php if ($logs && $logs->num_rows > 0): ?>
        <div class="section-header">
            <span>III. LOG TELEMETRI &amp; RESPON SUARA EDUKASI AI (TTS NUDGE)</span>
            <span style="font-size:8.5px; font-weight:normal; color:#64748b;">Sampel Log Terakhir</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width:75px;">Waktu</th>
                    <th style="width:65px;" class="text-center">Kategori</th>
                    <th style="width:55px;" class="text-center">Status</th>
                    <th style="width:40px;" class="text-right">Poin</th>
                    <th>Respon Suara Edukasi (Voice Nudge TTS)</th>
                    <th style="width:85px;">Pengguna</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($l = $logs->fetch_assoc()): ?>
                <tr>
                    <td class="font-mono" style="font-size:8px;">
                        <?= date('d/m/Y', strtotime($l['tanggal'])) ?> <?= substr($l['waktu'], 0, 5) ?>
                    </td>
                    <td class="text-center">
                        <?php if ($l['kategori_sampah'] === 'organik'): ?>
                            <span class="badge badge-green">ORGANIK</span>
                        <?php elseif ($l['kategori_sampah'] === 'anorganik'): ?>
                            <span class="badge badge-blue">ANORGANIK</span>
                        <?php else: ?>
                            <span class="badge badge-gray"><?= strtoupper($l['kategori_sampah']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-green"><?= strtoupper($l['status_pilah']) ?></span>
                    </td>
                    <td class="text-right font-bold"><?= ($l['poin'] > 0) ? '+' . $l['poin'] : '-' ?></td>
                    <td style="font-style:italic; font-size:8.5px; color:#475569;">
                        &ldquo;<?= htmlspecialchars($l['pesan_suara'] ?? '-') ?>&rdquo;
                    </td>
                    <td style="font-size:8px;"><?= htmlspecialchars($l['user_identifier'] ?? 'Guest') ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- LEMBAR PENGESAHAN & TANDA TANGAN -->
        <div class="signature-grid">
            <div class="sig-col">
                <p>Mengetahui &amp; Memvalidasi,</p>
                <p style="font-weight:700; color:#0f172a; margin-top:2px;">Supervisor IoT &amp; Ekosistem Digital</p>
                <div class="sig-space"></div>
                <p class="sig-name">Ammar Abdul Malik, S.Kom.</p>
                <p style="font-size:8.5px; color:#64748b;">NIP: 19880921 201201 1 002</p>
            </div>
            <div class="sig-col">
                <p>Bogor, <?= date('d F Y') ?></p>
                <p style="font-weight:700; color:#0f172a; margin-top:2px;">Petugas Admin Monitoring PIRU</p>
                <div class="sig-space"></div>
                <p class="sig-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Petugas Validasi') ?></p>
                <p style="font-size:8.5px; color:#64748b;">ID Petugas: TRASH-ADM-<?= str_pad($_SESSION['user_id'] ?? 1, 4, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

        <!-- DOKUMEN FOOTER -->
        <div class="doc-footer">
            <span>Dicetak secara otomatis dari Sistem Bank Sampah Digital Trashily &bull; Dokumen Sah Tanpa Stempel Fisik</span>
            <span>Halaman 1 dari 1 &bull; Dokumen Kode: <?= $nomor_laporan ?></span>
        </div>

    </div>

</body>
</html>
