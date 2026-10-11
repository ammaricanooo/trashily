<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$tanggal_terpilih = $_GET['tanggal'] ?? date('Y-m-d');
$device_filter    = intval($_GET['device_id'] ?? 0);

// Info Perangkat jika difilter
$filter_device_name = "Semua Unit";
if ($device_filter > 0) {
    $rDev = $conn->query("SELECT kode_perangkat, nama_perangkat FROM trashsmart_devices WHERE id = $device_filter")->fetch_assoc();
    if ($rDev) {
        $filter_device_name = $rDev['kode_perangkat'] . ' — ' . $rDev['nama_perangkat'];
    }
}

// 1. Query KPI Harian
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
$kpi = $stmtSum->get_result()->fetch_assoc();

$total_interaksi = $kpi['total_interaksi'] ?? 0;
$total_berat     = floatval($kpi['total_berat'] ?? 0);
$berat_organik   = floatval($kpi['berat_organik'] ?? 0);
$berat_anorganik = floatval($kpi['berat_anorganik'] ?? 0);
$count_benar     = intval($kpi['count_benar'] ?? 0);
$count_keliru    = intval($kpi['count_keliru'] ?? 0);
$total_pilah     = $count_benar + $count_keliru;
$akurasi_persen  = ($total_pilah > 0) ? round(($count_benar / $total_pilah) * 100, 1) : 100;
$total_poin      = intval($kpi['total_poin_reward'] ?? 0);

// 2. Query Devices List
$dev_sql = "SELECT * FROM trashsmart_devices " . ($device_filter > 0 ? "WHERE id = $device_filter " : "") . "ORDER BY id ASC";
$dev_result = $conn->query($dev_sql);

// 3. Query Logs Detail
$log_sql = "
    SELECT l.*, d.kode_perangkat, d.nama_perangkat, d.lokasi
    FROM trashsmart_logs l
    JOIN trashsmart_devices d ON l.device_id = d.id
    WHERE l.tanggal = ? " . ($device_filter > 0 ? "AND l.device_id = $device_filter " : "") . "
    ORDER BY l.waktu ASC
";
$stmtLog = $conn->prepare($log_sql);
$stmtLog->bind_param('s', $tanggal_terpilih);
$stmtLog->execute();
$logs_result = $stmtLog->get_result();

$nomor_laporan = "TRASH-RPT-" . date('Ymd', strtotime($tanggal_terpilih)) . "-" . str_pad($device_filter, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_TrashSmart_<?= htmlspecialchars($tanggal_terpilih) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar (Hanya tampil di browser, otomatis sembunyi saat Print/PDF) */
        .no-print-bar {
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-print {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #15803d;
            transform: translateY(-1px);
        }
        .btn-back {
            color: #94a3b8;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover { color: #fff; }

        /* A4 Page Layout */
        .page {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 20mm 20mm;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        /* Kop Surat */
        .kop-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #006e2f;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }
        .kop-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .kop-logo img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }
        .kop-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #006e2f;
            letter-spacing: -0.02em;
        }
        .kop-title p {
            font-size: 10px;
            color: #475569;
            font-weight: 500;
        }
        .kop-meta {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .kop-meta strong {
            color: #0f172a;
            font-size: 11px;
        }

        /* Judul Dokumen */
        .doc-title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title-box h2 {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .doc-title-box p {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Info Ringkas 2 Kolom */
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10.5px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .meta-label { color: #64748b; }
        .meta-val { font-weight: 700; color: #0f172a; }

        /* KPI Matrix */
        .kpi-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        .kpi-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .kpi-box.blue { background: #eff6ff; border-color: #bfdbfe; }
        .kpi-box.amber { background: #fffbeb; border-color: #fde68a; }
        .kpi-box.rose { background: #fff1f2; border-color: #fecdd3; }
        .kpi-val {
            font-size: 16px;
            font-weight: 800;
            color: #166534;
        }
        .kpi-box.blue .kpi-val { color: #1e40af; }
        .kpi-box.amber .kpi-val { color: #92400e; }
        .kpi-box.rose .kpi-val { color: #9f1239; }
        .kpi-lbl {
            font-size: 9.5px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 2px;
        }

        /* Tabel Gaya Resmi */
        .section-header {
            font-size: 11.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 16px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 10px;
        }
        .report-table th {
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            padding: 6px 8px;
            text-align: left;
            border: 1px solid #0f172a;
        }
        .report-table td {
            padding: 5.5px 8px;
            border: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: top;
        }
        .report-table tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        
        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
        }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }

        /* Tanda Tangan */
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .sig-col { text-align: center; font-size: 10.5px; }
        .sig-space { height: 60px; }
        .sig-name { font-weight: 800; text-decoration: underline; color: #0f172a; }

        /* Print Settings */
        @media print {
            .no-print-bar { display: none !important; }
            body { background: #fff !important; }
            .page {
                margin: 0 !important;
                padding: 10mm 15mm !important;
                box-shadow: none !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- TOP TOOLBAR BROWSER -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="monitoring-trashsmart.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard Monitoring
            </a>
            <span style="color: #64748b;">|</span>
            <span style="font-size: 12px; color: #cbd5e1;">
                Pratinjau Cetak / Ekspor PDF Dokumen Resmi
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn-print">
                <i class="fa-solid fa-print"></i> Cetak / Simpan sebagai PDF
            </button>
        </div>
    </div>

    <!-- HALAMAN A4 LAPORAN RESMI -->
    <div class="page">
        <!-- KOP SURAT RESMI -->
        <div class="kop-header">
            <div class="kop-logo">
                <img src="../assets/brand.png" alt="Trashily Logo">
                <div class="kop-title">
                    <h1>TRASHILY INDONESIA</h1>
                    <p>DIVISI TEKNOLOGI IOT &bull; PLATFORM BANK SAMPAH DIGITAL BERBASIS REWARD</p>
                    <p style="font-size:9px; color:#94a3b8;">Jl. Padjadjaran Eco-Innovation Park No. 12, Kota Bogor | support@trashily.id</p>
                </div>
            </div>
            <div class="kop-meta">
                <div>No. Dokumen: <strong><?= $nomor_laporan ?></strong></div>
                <div>Status: <span class="badge badge-green">TERVERIFIKASI SISTEM</span></div>
                <div>Waktu Cetak: <?= date('d/m/Y H:i') ?> WIB</div>
            </div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="doc-title-box">
            <h2>LAPORAN HARIAN MONITORING &amp; TELEMETRI TRASHSMART IOT</h2>
            <p>Rekapitulasi Pemilahan Sampah Hulu, Efektivitas Behavioral Nudge, dan Status Kapasitas Perangkat</p>
        </div>

        <!-- META DATA -->
        <div class="meta-grid">
            <div>
                <div class="meta-row">
                    <span class="meta-label">Tanggal Pelaporan:</span>
                    <span class="meta-val"><?= date('l, d F Y', strtotime($tanggal_terpilih)) ?></span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Lingkup Monitoring:</span>
                    <span class="meta-val"><?= htmlspecialchars($filter_device_name) ?></span>
                </div>
            </div>
            <div>
                <div class="meta-row">
                    <span class="meta-label">Dicetak Oleh:</span>
                    <span class="meta-val"><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?> (Admin)</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Metode Audit:</span>
                    <span class="meta-val">Sensor Beban HX711 &amp; Inspeksi IR Baki Pintar</span>
                </div>
            </div>
        </div>

        <!-- KPI SUMMARY MATRIX -->
        <div class="kpi-container">
            <div class="kpi-box blue">
                <div class="kpi-val"><?= number_format($total_berat, 2) ?> kg</div>
                <div class="kpi-lbl">Total Terpilah</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-val"><?= $akurasi_persen ?>%</div>
                <div class="kpi-lbl">Akurasi Pemilahan</div>
            </div>
            <div class="kpi-box amber">
                <div class="kpi-val"><?= $count_keliru ?>x</div>
                <div class="kpi-lbl">Voice Nudge Terpicu</div>
            </div>
            <div class="kpi-box rose">
                <div class="kpi-val">+<?= number_format($total_poin) ?></div>
                <div class="kpi-lbl">Poin Reward Diberikan</div>
            </div>
        </div>

        <!-- TABEL 1: STATUS PERANGKAT TRASHSMART -->
        <div class="section-header">
            <span>I. KONDISI OPERASIONAL &amp; KAPASITAS WADAH PERANGKAT</span>
            <span style="font-size:9.5px; font-weight:normal; color:#64748b;">Standar Ambang Batas Penuh: &ge; 85%</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th style="width: 80px;">Kode Unit</th>
                    <th>Nama Unit &amp; Lokasi</th>
                    <th style="width: 65px;" class="text-center">Status</th>
                    <th style="width: 80px;" class="text-center">Kapasitas Org</th>
                    <th style="width: 80px;" class="text-center">Kapasitas Anorg</th>
                    <th style="width: 50px;" class="text-center">Baterai</th>
                    <th style="width: 50px;" class="text-center">Suhu</th>
                    <th style="width: 75px;" class="text-right">Total Pilah</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $n = 1;
                while ($d = $dev_result->fetch_assoc()): 
                    $is_warn = ($d['kapasitas_organik'] >= 85 || $d['kapasitas_anorganik'] >= 85);
                ?>
                <tr>
                    <td class="text-center"><?= $n++ ?></td>
                    <td class="font-bold"><?= htmlspecialchars($d['kode_perangkat']) ?></td>
                    <td>
                        <strong><?= htmlspecialchars($d['nama_perangkat']) ?></strong><br>
                        <span style="color:#64748b; font-size:9px;"><?= htmlspecialchars($d['lokasi']) ?></span>
                    </td>
                    <td class="text-center">
                        <?php if ($d['status'] === 'online'): ?>
                            <span class="badge badge-green">ONLINE</span>
                        <?php elseif ($d['status'] === 'warning'): ?>
                            <span class="badge badge-red">PENUH</span>
                        <?php else: ?>
                            <span class="badge badge-yellow"><?= strtoupper($d['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center <?= ($d['kapasitas_organik'] >= 85) ? 'font-bold' : '' ?>" style="<?= ($d['kapasitas_organik'] >= 85) ? 'color:#b91c1c;' : '' ?>">
                        <?= $d['kapasitas_organik'] ?>%
                    </td>
                    <td class="text-center <?= ($d['kapasitas_anorganik'] >= 85) ? 'font-bold' : '' ?>" style="<?= ($d['kapasitas_anorganik'] >= 85) ? 'color:#b91c1c;' : '' ?>">
                        <?= $d['kapasitas_anorganik'] ?>%
                    </td>
                    <td class="text-center"><?= $d['baterai'] ?>%</td>
                    <td class="text-center"><?= $d['suhu_celsius'] ?>°C</td>
                    <td class="text-right font-bold"><?= number_format($d['total_berat_kg'], 1) ?> kg</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- TABEL 2: LOG DETAIL AKTIVITAS TELEMETRI -->
        <div class="section-header">
            <span>II. LOG TELEMETRI &amp; INTERAKSI EDUKASI SUARA (VOICE NUDGE)</span>
            <span style="font-size:9.5px; font-weight:normal; color:#64748b;">Total: <?= $logs_result->num_rows ?> Entri</span>
        </div>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 25px;" class="text-center">No</th>
                    <th style="width: 50px;" class="text-center">Waktu</th>
                    <th style="width: 70px;">Unit</th>
                    <th style="width: 75px;" class="text-center">Kategori</th>
                    <th style="width: 75px;" class="text-center">Status Pilah</th>
                    <th style="width: 60px;" class="text-right">Berat</th>
                    <th style="width: 45px;" class="text-center">Poin</th>
                    <th>Edukasi Suara (Voice Nudge)</th>
                    <th style="width: 90px;">Pengguna / ID</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($logs_result->num_rows === 0): ?>
                <tr>
                    <td colspan="9" class="text-center" style="padding:15px; color:#64748b;">
                        Tidak ada log telemetri pada tanggal ini.
                    </td>
                </tr>
                <?php else:
                    $m = 1;
                    while ($l = $logs_result->fetch_assoc()):
                ?>
                <tr>
                    <td class="text-center"><?= $m++ ?></td>
                    <td class="text-center" style="font-family:monospace;"><?= substr($l['waktu'], 0, 5) ?></td>
                    <td class="font-bold"><?= htmlspecialchars($l['kode_perangkat']) ?></td>
                    <td class="text-center">
                        <span class="badge <?= ($l['kategori_sampah'] === 'organik') ? 'badge-green' : (($l['kategori_sampah'] === 'anorganik') ? 'badge-yellow' : '') ?>">
                            <?= ucfirst($l['kategori_sampah']) ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <?php if ($l['status_pilah'] === 'benar'): ?>
                            <span style="color:#15803d; font-weight:700;">Tepat</span>
                        <?php elseif ($l['status_pilah'] === 'keliru'): ?>
                            <span style="color:#b91c1c; font-weight:700;">Salah Kamar</span>
                        <?php else: ?>
                            <span style="color:#64748b;"><?= ucfirst($l['status_pilah']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right font-bold"><?= ($l['berat_kg'] > 0) ? number_format($l['berat_kg'], 3) . ' kg' : '-' ?></td>
                    <td class="text-center"><?= ($l['poin'] > 0) ? '+' . $l['poin'] : '0' ?></td>
                    <td style="font-style: italic; color: #475569; font-size: 9.5px;">
                        <?= htmlspecialchars($l['pesan_suara'] ?? '-') ?>
                    </td>
                    <td style="font-size: 9px;"><?= htmlspecialchars($l['user_identifier'] ?? '-') ?></td>
                </tr>
                <?php 
                    endwhile;
                endif; 
                ?>
            </tbody>
        </table>

        <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
        <div class="signature-grid">
            <div class="sig-col">
                <p>Mengetahui &amp; Menyetujui,</p>
                <p style="font-weight:700; color:#0f172a;">Supervisor Operasional &amp; Ekosistem IoT</p>
                <div class="sig-space"></div>
                <p class="sig-name">Ammar Abdul Malik, S. Gpt.</p>
                <p style="font-size:9.5px; color:#64748b;">NIP: 19850412 201001 1 004</p>
            </div>
            <div class="sig-col">
                <p>Bogor, <?= date('d F Y', strtotime($tanggal_terpilih)) ?></p>
                <p style="font-weight:700; color:#0f172a;">Petugas Monitoring Data TrashSmart</p>
                <div class="sig-space"></div>
                <p class="sig-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></p>
                <p style="font-size:9.5px; color:#64748b;">ID Admin: TRASH-ADM-<?= str_pad($_SESSION['user_id'] ?? 1, 4, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

    </div>

</body>
</html>
