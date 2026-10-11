<?php
require_once '../../includes/auth_check.php';
requireAdmin();
require_once '../../config/database.php';

$total_customer = $conn->query("SELECT COUNT(DISTINCT customer_id) c FROM everwear_transaksi")->fetch_assoc()['c'];
$total_transaksi = $conn->query("SELECT COUNT(*) c FROM everwear_transaksi")->fetch_assoc()['c'];
$total_poin = $conn->query("SELECT COALESCE(SUM(poin_diperoleh),0) c FROM everwear_transaksi WHERE status='selesai'")->fetch_assoc()['c'];
$total_berat = $conn->query("SELECT COALESCE(SUM(berat_kg),0) c FROM everwear_transaksi WHERE status='selesai'")->fetch_assoc()['c'];
$pending_jemput = $conn->query("SELECT COUNT(*) c FROM everwear_jemput WHERE status NOT IN ('selesai','dibatalkan')")->fetch_assoc()['c'];

$recent_trx = $conn->query("SELECT t.kode_transaksi,t.pcs_layak,t.pcs_recycle,t.berat_kg,t.poin_diperoleh,t.status,t.created_at,u.nama customer_nama FROM everwear_transaksi t JOIN users u ON u.id=t.customer_id ORDER BY t.created_at DESC LIMIT 6");
$top_customer = $conn->query("SELECT u.nama, COUNT(t.id) total_donasi, COALESCE(SUM(t.berat_kg),0) total_berat FROM users u JOIN everwear_transaksi t ON t.customer_id=u.id WHERE t.status='selesai' GROUP BY u.id,u.nama ORDER BY total_berat DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Everwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php include '../../includes/sidebar_admin_everwear.php'; ?>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left"><button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button><div><h1>Dashboard</h1><p><?= date('l, d F Y') ?></p></div></div>
            <div class="topbar-right"><?php include '../../includes/notif_admin.php'; ?></div>
        </div>
        <div class="content">
            <div class="relative overflow-hidden rounded-2xl mb-6" style="background:linear-gradient(135deg,#006e2f 0%,#004b1e 100%)">
                <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full" style="background:rgba(74,225,118,.12)"></div><div class="absolute right-16 -bottom-10 w-28 h-28 rounded-full" style="background:rgba(255,255,255,.06)"></div>
                <div class="relative z-10 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div><p class="text-sm font-semibold mb-1" style="color:rgba(255,255,255,.75)"><?= date('l, d F Y') ?></p><h2 class="text-2xl font-extrabold text-white mb-1" style="font-family:var(--font-display)">Selamat datang, Admin Everwear</h2><p class="text-sm" style="color:rgba(255,255,255,.8)"><?= $pending_jemput ? "Ada <strong class='text-white'>$pending_jemput permintaan jemput</strong> yang menunggu konfirmasi." : 'Semua permintaan jemput sudah tertangani.' ?></p></div>
                    <div class="flex gap-3 flex-wrap"><a href="transaksi.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm" style="background:#fff;color:var(--color-primary-dark)"><i class="fa-solid fa-shirt text-xs"></i> Donasi Masuk</a><a href="jemput-sampah.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)"><i class="fa-solid fa-truck text-xs"></i> Jemput Pakaian</a></div>
                </div>
            </div>
            <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(185px,1fr))">
                <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-users"></i></div><div class="stat-info"><div class="stat-value"><?= number_format($total_customer) ?></div><div class="stat-label">Donatur Aktif</div></div></div>
                <div class="stat-card"><div class="stat-icon blue"><i class="fa-solid fa-shirt"></i></div><div class="stat-info"><div class="stat-value"><?= number_format($total_transaksi) ?></div><div class="stat-label">Total Donasi</div></div></div>
                <div class="stat-card"><div class="stat-icon amber"><i class="fa-solid fa-coins"></i></div><div class="stat-info"><div class="stat-value"><?= number_format($total_poin) ?></div><div class="stat-label">Poin Dibagikan</div></div></div>
                <div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-recycle"></i></div><div class="stat-info"><div class="stat-value"><?= number_format($total_berat,1) ?> <small style="font-size:.85rem">kg</small></div><div class="stat-label">Tekstil Terkelola</div></div></div>
            </div>
            <div class="dashboard-grid">
                <div class="card"><div class="card-header"><h3><i class="fa-solid fa-clock-rotate-left text-xs mr-2" style="color:var(--color-primary)"></i>Donasi Terbaru</h3><a href="transaksi.php" class="btn btn-ghost btn-sm">Lihat Semua <i class="fa-solid fa-arrow-right text-xs"></i></a></div><div class="table-wrap"><table><thead><tr><th>Kode</th><th>Donatur</th><th>Jumlah</th><th>Poin</th><th>Waktu</th></tr></thead><tbody><?php if (!$recent_trx->num_rows): ?><tr><td colspan="5"><div class="empty-state"><div class="empty-icon"><i class="fa-solid fa-shirt"></i></div><p>Belum ada donasi</p></div></td></tr><?php else: while ($row=$recent_trx->fetch_assoc()): ?><tr><td><span class="badge badge-green"><?= htmlspecialchars($row['kode_transaksi']) ?></span></td><td><div class="font-semibold text-sm"><?= htmlspecialchars($row['customer_nama']) ?></div></td><td><span class="text-small">Layak: <?= intval($row['pcs_layak']) ?> pcs<br>Recycle: <?= intval($row['pcs_recycle']) ?> pcs<br><?= number_format($row['berat_kg'],2) ?> kg</span></td><td><?= $row['poin_diperoleh']?'<span class="badge badge-green">+'.number_format($row['poin_diperoleh']).'</span>':'<span class="text-muted text-small">—</span>' ?></td><td class="text-muted text-small"><?= date('d/m H:i',strtotime($row['created_at'])) ?></td></tr><?php endwhile; endif; ?></tbody></table></div></div>
                <div class="card"><div class="card-header"><h3><i class="fa-solid fa-trophy text-xs mr-2" style="color:var(--color-amber)"></i>Top Donatur</h3></div><div class="card-body" style="padding:.75rem 1.25rem"><?php $rank=1; $colors=['#f59e0b','#94a3b8','#b45309']; while($c=$top_customer->fetch_assoc()): ?><div class="flex items-center gap-3 py-3 border-b" style="border-color:var(--color-border)"><div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-extrabold" style="background:var(--color-surface);color:<?= $colors[$rank-1]??'var(--color-ink-subtle)' ?>"><?= $rank ?></div><div class="flex-1 min-w-0"><div class="text-sm font-semibold truncate"><?= htmlspecialchars($c['nama']) ?></div><div class="text-muted text-small"><?= $c['total_donasi'] ?> donasi</div></div><div class="text-sm font-bold flex-shrink-0" style="color:var(--color-primary-dark)"><?= number_format($c['total_berat'],1) ?> <span class="font-normal text-xs" style="color:var(--color-ink-muted)">kg</span></div></div><?php $rank++; endwhile; if($rank===1): ?><div class="empty-state" style="padding:1.5rem 0"><p>Belum ada data</p></div><?php endif; ?></div></div>
            </div>
        </div>
    </main>
</div>
<script>function toggleSidebar(){document.getElementById('sidebar').classList.toggle('open');document.getElementById('sidebarOverlay').classList.toggle('open')}function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('open')}</script>
<?php include_once '../../includes/notif_scripts.php'; ?>
</body></html>
