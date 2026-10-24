<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$filter_date = trim($_GET['tanggal'] ?? '');

$where = "1=1";
$params = [];
$types = "";

if ($filter_date !== '') {
    $where .= " AND DATE(s.created_at) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

// 1. KPI Sesi
$kpi_sql = "
    SELECT 
        COUNT(*) as total_sesi,
        COALESCE(SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END), 0) as sesi_selesai,
        COALESCE(SUM(s.item_count), 0) as total_item,
        COALESCE(SUM(s.total_poin), 0) as total_poin,
        COALESCE(SUM(s.total_berat_gr), 0) as total_berat_gr
    FROM piru_sessions s
    WHERE $where
";
$stmtK = $conn->prepare($kpi_sql);
if (!empty($types)) {
    $stmtK->bind_param($types, ...$params);
}
$stmtK->execute();
$kpi = $stmtK->get_result()->fetch_assoc();

// 2. Query Detail Sesi
$data_sql = "
    SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
           t.kode_transaksi
    FROM piru_sessions s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN transaksi t ON s.transaksi_id = t.id
    WHERE $where
    ORDER BY s.created_at DESC
";
$stmtD = $conn->prepare($data_sql);
if (!empty($types)) {
    $stmtD->bind_param($types, ...$params);
}
$stmtD->execute();
$sessions = $stmtD->get_result();

// Set Header untuk Download File Excel (.xls)
$filename = "Laporan_PIRU_SmartBin_" . ($filter_date ? $filter_date : "Semua") . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF"; // UTF-8 BOM
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
    <x:Name>Laporan Sesi PIRU</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
    body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; color: #1e293b; }
    .header-table td { padding: 4px 8px; }
    .title { font-size: 16pt; font-weight: bold; color: #006e2f; }
    .subtitle { font-size: 10pt; color: #64748b; margin-bottom: 15px; }
    .kpi-table { border-collapse: collapse; margin-bottom: 20px; width: 100%; max-width: 800px; }
    .kpi-table td, .kpi-table th { border: 1px solid #cbd5e1; padding: 8px 12px; }
    .kpi-label { background-color: #f1f5f9; font-weight: bold; color: #334155; width: 35%; }
    .kpi-value { font-weight: bold; color: #006e2f; font-size: 12pt; }
    
    .data-table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    .data-table th { background-color: #006e2f; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #005a26; padding: 8px 6px; }
    .data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; font-size: 10pt; vertical-align: middle; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .badge-org { background-color: #dcfce7; color: #166534; font-weight: bold; }
    .badge-anorg { background-color: #e0f2fe; color: #075985; font-weight: bold; }
</style>
</head>
<body>

<table class="header-table" style="width:100%; margin-bottom:10px;">
    <tr>
        <td colspan="9" class="title">LAPORAN MONITORING SESI PEMILAHAN PIRU SMART BIN</td>
    </tr>
    <tr>
        <td colspan="9" class="subtitle">Platform Sirkular Ekonomi &amp; Edukasi Sampah Cerdas — Trashily.id</td>
    </tr>
    <tr>
        <td colspan="9">
            <strong>Tanggal Laporan:</strong> <?= $filter_date ? date('d F Y', strtotime($filter_date)) : 'Semua Periode' ?> &bull; 
            <strong>Dicetak Pada:</strong> <?= date('d/m/Y H:i:s') ?> WIB &bull; 
            <strong>Unit:</strong> PIRU-DEV-001 (PIRU Smart Bin #001)
        </td>
    </tr>
</table>

<h3 style="color:#006e2f; margin-bottom:5px;">1. RINGKASAN REKAPITULASI SESI</h3>
<table class="kpi-table">
    <tr>
        <td class="kpi-label">Total Sesi Terdaftar</td>
        <td class="kpi-value"><?= number_format($kpi['total_sesi'] ?? 0) ?> Sesi</td>
        <td class="kpi-label">Sesi Selesai (Completed)</td>
        <td class="kpi-value"><?= number_format($kpi['sesi_selesai'] ?? 0) ?> Sesi</td>
    </tr>
    <tr>
        <td class="kpi-label">Total Item Fisik Terpilah</td>
        <td class="kpi-value"><?= number_format($kpi['total_item'] ?? 0) ?> Item</td>
        <td class="kpi-label">Total Poin Reward Diterima</td>
        <td class="kpi-value"><?= number_format($kpi['total_poin'] ?? 0) ?> Poin</td>
    </tr>
    <tr>
        <td class="kpi-label">Total Berat Sampah Terkelola</td>
        <td class="kpi-value" colspan="3"><?= number_format(($kpi['total_berat_gr'] ?? 0) / 1000, 2) ?> kg</td>
    </tr>
</table>

<h3 style="color:#006e2f; margin-top:20px; margin-bottom:5px;">2. RINCIAN SESI PEMILAHAN PENGGUNA</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>No</th>
            <th>Waktu Sesi</th>
            <th>Kode Perangkat</th>
            <th>Token Sesi</th>
            <th>Nama Customer</th>
            <th>No. HP / Kontak</th>
            <th>Item Terpilah</th>
            <th>Poin Reward</th>
            <th>Bobot (kg)</th>
            <th>Status</th>
            <th>Kode Transaksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($sessions->num_rows === 0): ?>
            <tr>
                <td colspan="11" class="text-center" style="padding:15px; color:#64748b;">Tidak ada data sesi pemilahan pada filter ini.</td>
            </tr>
        <?php else: ?>
            <?php 
            $no = 1;
            while ($s = $sessions->fetch_assoc()): 
                $orgCount = $s['total_organik_gr'] > 0 ? ceil($s['total_organik_gr'] / 100) : 0;
                $anorgCount = $s['total_anorganik_gr'] > 0 ? ceil($s['total_anorganik_gr'] / 100) : 0;
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= date('d/m/Y H:i:s', strtotime($s['created_at'])) ?></td>
                    <td class="text-center"><?= htmlspecialchars($s['device_kode']) ?></td>
                    <td style="font-family:monospace;"><?= htmlspecialchars($s['session_token']) ?></td>
                    <td><strong><?= htmlspecialchars($s['customer_nama'] ?? 'Anonim / Tamu') ?></strong></td>
                    <td><?= htmlspecialchars($s['customer_nohp'] ?? $s['customer_email'] ?? '-') ?></td>
                    <td class="text-center">
                        <?= $orgCount ?> Org, <?= $anorgCount ?> Anorg (Total: <?= $s['item_count'] ?>)
                    </td>
                    <td class="text-right" style="font-weight:bold; color:#006e2f;">+<?= intval($s['total_poin']) ?></td>
                    <td class="text-right"><?= number_format($s['total_berat_gr'] / 1000, 2) ?></td>
                    <td class="text-center">
                        <?= strtoupper($s['status']) ?>
                    </td>
                    <td class="text-center" style="font-family:monospace;">
                        <?= htmlspecialchars($s['kode_transaksi'] ?? '-') ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
