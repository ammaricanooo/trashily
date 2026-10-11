<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$tanggal_terpilih = $_GET['tanggal'] ?? date('Y-m-d');
$device_filter    = intval($_GET['device_id'] ?? 0);

// Ambil info perangkat terpilih jika ada filter
$filter_device_name = "Semua Unit";
if ($device_filter > 0) {
    $rDev = $conn->query("SELECT kode_perangkat, nama_perangkat FROM trashsmart_devices WHERE id = $device_filter")->fetch_assoc();
    if ($rDev) {
        $filter_device_name = $rDev['kode_perangkat'] . ' - ' . $rDev['nama_perangkat'];
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

// Set Header untuk Download File Excel (.xls)
$filename = "Laporan_TrashSmart_" . $tanggal_terpilih . ($device_filter > 0 ? "_Unit" . $device_filter : "") . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM agar aksen dan karakter Indonesia tidak rusak di Excel
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Laporan Harian TrashSmart</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
    body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; color: #1e293b; }
    .title { font-size: 16pt; font-weight: bold; color: #15803d; text-align: left; }
    .subtitle { font-size: 11pt; color: #475569; }
    .meta-table td { padding: 4px 8px; font-size: 10pt; }
    .kpi-table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    .kpi-table th { background-color: #166534; color: #ffffff; padding: 8px; font-size: 10pt; border: 1px solid #166534; text-align: center; }
    .kpi-table td { background-color: #f0fdf4; color: #166534; font-weight: bold; padding: 10px; font-size: 12pt; border: 1px solid #bbf7d0; text-align: center; }
    .data-table { border-collapse: collapse; width: 100%; margin-bottom: 25px; }
    .data-table th { background-color: #1e293b; color: #ffffff; font-weight: bold; padding: 8px 10px; font-size: 10pt; border: 1px solid #cbd5e1; text-align: left; }
    .data-table td { padding: 6px 10px; font-size: 10pt; border: 1px solid #cbd5e1; vertical-align: top; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .badge-organik { background-color: #dcfce7; color: #166534; font-weight: bold; }
    .badge-anorganik { background-color: #fef3c7; color: #92400e; font-weight: bold; }
    .badge-benar { background-color: #dcfce7; color: #15803d; }
    .badge-keliru { background-color: #fee2e2; color: #991b1b; }
</style>
</head>
<body>

    <!-- KOP LAPORAN -->
    <table>
        <tr>
            <td colspan="7" class="title">TRASHILY — LAPORAN HARIAN MONITORING PERANGKAT TRASHSMART IOT</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Sistem Pemilahan Hulu Otomatis &bull; Baki Inspeksi Pintar &bull; Behavioral Nudge Audio</td>
        </tr>
        <tr><td></td></tr>
    </table>

    <!-- METADATA LAPORAN -->
    <table class="meta-table">
        <tr>
            <td style="font-weight:bold; width: 140px;">Tanggal Laporan:</td>
            <td><?= date('d F Y', strtotime($tanggal_terpilih)) ?></td>
            <td style="width: 40px;"></td>
            <td style="font-weight:bold; width: 140px;">Dicetak Oleh:</td>
            <td><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Filter Unit:</td>
            <td><?= htmlspecialchars($filter_device_name) ?></td>
            <td></td>
            <td style="font-weight:bold;">Waktu Ekspor:</td>
            <td><?= date('d/m/Y H:i:s') ?> WIB</td>
        </tr>
        <tr><td></td></tr>
    </table>

    <!-- BAGIAN 1: RINGKASAN EKSEKUTIF & KPI -->
    <h3 style="color:#0f172a; margin-bottom: 5px;">I. RINGKASAN EKSEKUTIF &amp; INDIKATOR KINERJA (KPI)</h3>
    <table class="kpi-table">
        <tr>
            <th>Total Interaksi</th>
            <th>Total Terpilah (kg)</th>
            <th>Sampah Organik</th>
            <th>Sampah Anorganik</th>
            <th>Akurasi Pemilahan</th>
            <th>Edukasi Suara (Nudge)</th>
            <th>Poin Reward</th>
        </tr>
        <tr>
            <td><?= number_format($total_interaksi) ?> kali</td>
            <td><?= number_format($total_berat, 3) ?> kg</td>
            <td><?= number_format($berat_organik, 3) ?> kg</td>
            <td><?= number_format($berat_anorganik, 3) ?> kg</td>
            <td><?= $akurasi_persen ?>%</td>
            <td><?= number_format($count_keliru) ?> kali</td>
            <td>+<?= number_format($total_poin) ?> Poin</td>
        </tr>
    </table>

    <!-- BAGIAN 2: STATUS PERANGKAT LAPANGAN -->
    <h3 style="color:#0f172a; margin-top:20px; margin-bottom: 5px;">II. STATUS &amp; KAPASITAS PERANGKAT LAPANGAN</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">No</th>
                <th style="width: 120px;">Kode Unit</th>
                <th style="width: 220px;">Nama Perangkat</th>
                <th style="width: 260px;">Lokasi Penempatan</th>
                <th style="width: 90px;" class="text-center">Status</th>
                <th style="width: 110px;" class="text-center">Kapasitas Org</th>
                <th style="width: 110px;" class="text-center">Kapasitas Anorg</th>
                <th style="width: 80px;" class="text-center">Baterai</th>
                <th style="width: 80px;" class="text-center">Suhu</th>
                <th style="width: 110px;" class="text-right">Total Pilah (kg)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $noDev = 1;
            while ($dev = $dev_result->fetch_assoc()): 
            ?>
            <tr>
                <td class="text-center"><?= $noDev++ ?></td>
                <td><strong><?= htmlspecialchars($dev['kode_perangkat']) ?></strong></td>
                <td><?= htmlspecialchars($dev['nama_perangkat']) ?></td>
                <td><?= htmlspecialchars($dev['lokasi']) ?></td>
                <td class="text-center" style="font-weight:bold; <?= ($dev['status'] === 'warning') ? 'color:#dc2626;' : (($dev['status'] === 'online') ? 'color:#16a34a;' : 'color:#d97706;') ?>">
                    <?= strtoupper($dev['status']) ?>
                </td>
                <td class="text-center" style="<?= ($dev['kapasitas_organik'] >= 85) ? 'color:#dc2626; font-weight:bold;' : '' ?>">
                    <?= $dev['kapasitas_organik'] ?>%
                </td>
                <td class="text-center" style="<?= ($dev['kapasitas_anorganik'] >= 85) ? 'color:#dc2626; font-weight:bold;' : '' ?>">
                    <?= $dev['kapasitas_anorganik'] ?>%
                </td>
                <td class="text-center"><?= $dev['baterai'] ?>%</td>
                <td class="text-center"><?= $dev['suhu_celsius'] ?>°C</td>
                <td class="text-right"><?= number_format($dev['total_berat_kg'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- BAGIAN 3: LOG DETAIL TELEMETRI HARIAN -->
    <h3 style="color:#0f172a; margin-top:20px; margin-bottom: 5px;">III. RINCIAN LOG TELEMETRI &amp; AKTIVITAS PEMILAHAN HARIAN</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">No</th>
                <th style="width: 70px;" class="text-center">Waktu</th>
                <th style="width: 100px;">Kode Unit</th>
                <th style="width: 160px;">Nama Perangkat</th>
                <th style="width: 90px;" class="text-center">Kategori</th>
                <th style="width: 100px;" class="text-center">Status Pilah</th>
                <th style="width: 80px;" class="text-right">Berat (kg)</th>
                <th style="width: 70px;" class="text-center">Poin</th>
                <th style="width: 300px;">Respon Edukasi Suara (Voice Nudge)</th>
                <th style="width: 150px;">Pengguna / ID</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($logs_result->num_rows === 0): ?>
            <tr>
                <td colspan="10" class="text-center" style="padding: 15px; color:#64748b;">
                    Tidak ada data aktivitas telemetri yang tercatat pada tanggal ini.
                </td>
            </tr>
            <?php else:
                $noLog = 1;
                while ($l = $logs_result->fetch_assoc()):
            ?>
            <tr>
                <td class="text-center"><?= $noLog++ ?></td>
                <td class="text-center"><?= substr($l['waktu'], 0, 8) ?></td>
                <td><strong><?= htmlspecialchars($l['kode_perangkat']) ?></strong></td>
                <td><?= htmlspecialchars($l['nama_perangkat']) ?></td>
                <td class="text-center <?= ($l['kategori_sampah'] === 'organik') ? 'badge-organik' : (($l['kategori_sampah'] === 'anorganik') ? 'badge-anorganik' : '') ?>">
                    <?= ucfirst($l['kategori_sampah']) ?>
                </td>
                <td class="text-center <?= ($l['status_pilah'] === 'benar') ? 'badge-benar' : (($l['status_pilah'] === 'keliru') ? 'badge-keliru' : '') ?>">
                    <?= ($l['status_pilah'] === 'benar') ? 'Tepat' : (($l['status_pilah'] === 'keliru') ? 'Salah Kamar' : ucfirst($l['status_pilah'])) ?>
                </td>
                <td class="text-right"><?= ($l['berat_kg'] > 0) ? number_format($l['berat_kg'], 3) : '-' ?></td>
                <td class="text-center"><?= ($l['poin'] > 0) ? '+' . $l['poin'] : '0' ?></td>
                <td style="font-style: italic; color: #475569;"><?= htmlspecialchars($l['pesan_suara'] ?? '-') ?></td>
                <td><?= htmlspecialchars($l['user_identifier'] ?? '-') ?></td>
            </tr>
            <?php 
                endwhile;
            endif; 
            ?>
        </tbody>
    </table>

    <br>
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td colspan="4" style="text-align: center;">
                Mengetahui,<br><strong>Supervisor Operasional Trashily</strong><br><br><br><br>
                ( __________________________ )
            </td>
            <td></td>
            <td colspan="2" style="text-align: center;">
                Bogor, <?= date('d F Y') ?><br><strong>Petugas Monitoring IoT</strong><br><br><br><br>
                ( <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Administrator') ?></strong> )
            </td>
        </tr>
    </table>

</body>
</html>
