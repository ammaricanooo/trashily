<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

$total_customer     = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$total_transaksi    = $conn->query("SELECT COUNT(*) as c FROM transaksi")->fetch_assoc()['c'];
$total_poin_beredar = $conn->query("SELECT COALESCE(SUM(poin),0) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$total_uang_keluar  = $conn->query("SELECT COALESCE(SUM(total_uang),0) as c FROM transaksi WHERE status='selesai'")->fetch_assoc()['c'];
$total_berat        = $conn->query("SELECT COALESCE(SUM(total_berat),0) as c FROM transaksi WHERE status='selesai'")->fetch_assoc()['c'];
$pending_jemput     = $conn->query("SELECT COUNT(*) as c FROM jemput_sampah WHERE status NOT IN ('selesai','batal')")->fetch_assoc()['c'];

$recent_trx = $conn->query("
    SELECT t.kode_transaksi, t.customer_id, t.nama_non_member,
           t.total_poin, t.total_uang, t.total_berat, t.created_at,
           u.nama as customer_nama
    FROM transaksi t
    LEFT JOIN users u ON t.customer_id = u.id
    ORDER BY t.created_at DESC LIMIT 6
");

$top_customer = $conn->query("
    SELECT nama, poin FROM users WHERE role='customer' ORDER BY poin DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
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
                    <h1>Dashboard</h1>
                    <p><?= date('l, d F Y') ?></p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">

            <!-- WELCOME BANNER -->
            <div class="relative overflow-hidden rounded-2xl mb-6"
                 style="background: linear-gradient(135deg, #006e2f 0%, #004b1e 100%);">
                <div class="absolute -right-8 -top-8 w-40 h-40 rounded-full"
                     style="background:rgba(74,225,118,.12)"></div>
                <div class="absolute right-16 -bottom-10 w-28 h-28 rounded-full"
                     style="background:rgba(255,255,255,.06)"></div>
                <div class="relative z-10 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold mb-1" style="color:rgba(255,255,255,.75)">
                            <?= date('l, d F Y') ?>
                        </p>
                        <h2 class="text-2xl font-extrabold text-white mb-1"
                            style="font-family:var(--font-display)">
                            Selamat datang, Admin 👋
                        </h2>
                        <p class="text-sm" style="color:rgba(255,255,255,.8)">
                            <?php if ($pending_jemput > 0): ?>
                                Ada <strong class="text-white"><?= $pending_jemput ?> permintaan jemput</strong> yang menunggu konfirmasi.
                            <?php else: ?>
                                Semua permintaan jemput sudah tertangani.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        <a href="transaksi.php"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-sm"
                           style="background:#fff;color:var(--color-primary-dark)">
                            <i class="fa-solid fa-plus text-xs"></i> Transaksi Baru
                        </a>
                        <a href="jemput-sampah.php"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl font-semibold text-sm"
                           style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
                            <i class="fa-solid fa-truck text-xs"></i> Jemput Sampah
                        </a>
                    </div>
                </div>
            </div>

            <!-- STAT CARDS -->
            <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(185px,1fr))">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_customer) ?></div>
                        <div class="stat-label">Total Customer</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_transaksi) ?></div>
                        <div class="stat-label">Total Transaksi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <div class="stat-value" style="font-size:1.2rem"><?= formatRupiah($total_uang_keluar) ?></div>
                        <div class="stat-label">Uang Disalurkan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fa-solid fa-coins"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_poin_beredar) ?></div>
                        <div class="stat-label">Poin Beredar</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-weight-scale"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= number_format($total_berat, 1) ?> <small style="font-size:.85rem">kg</small></div>
                        <div class="stat-label">Sampah Terkelola</div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="dashboard-grid">

                <!-- Transaksi Terbaru -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-clock-rotate-left text-xs mr-2" style="color:var(--color-primary)"></i>Transaksi Terbaru</h3>
                        <a href="transaksi.php" class="btn btn-ghost btn-sm">Lihat Semua <i class="fa-solid fa-arrow-right text-xs"></i></a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Penyetor</th>
                                    <th>Uang</th>
                                    <th>Poin</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($recent_trx->num_rows === 0): ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fa-solid fa-receipt"></i></div>
                                            <p>Belum ada transaksi</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $recent_trx->fetch_assoc()):
                                    $is_member = !empty($row['customer_id']);
                                    $label = $is_member ? $row['customer_nama'] : ($row['nama_non_member'] ?: 'Tamu Offline');
                                ?>
                                <tr>
                                    <td><span class="badge badge-green"><?= $row['kode_transaksi'] ?></span></td>
                                    <td>
                                        <div class="font-semibold text-sm"><?= htmlspecialchars($label) ?></div>
                                        <div class="text-muted text-small"><?= $is_member ? 'Member' : 'Tamu' ?> · <?= number_format($row['total_berat'], 2) ?> kg</div>
                                    </td>
                                    <td><span class="font-bold" style="color:var(--color-primary-dark)"><?= formatRupiah($row['total_uang']) ?></span></td>
                                    <td>
                                        <?php if ($is_member): ?>
                                            <span class="badge badge-green">+<?= number_format($row['total_poin']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted text-small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted text-small"><?= date('d/m H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Customer -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-trophy text-xs mr-2" style="color:var(--color-amber)"></i>Top Customer</h3>
                    </div>
                    <div class="card-body" style="padding:.75rem 1.25rem">
                        <?php
                        $rank = 1;
                        $rank_colors = ['1'=>'#f59e0b','2'=>'#94a3b8','3'=>'#b45309'];
                        while ($c = $top_customer->fetch_assoc()):
                            $color = $rank_colors[$rank] ?? 'var(--color-ink-subtle)';
                        ?>
                        <div class="flex items-center gap-3 py-3 border-b" style="border-color:var(--color-border)">
                            <div class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-extrabold"
                                 style="background:<?= $rank <= 3 ? 'rgba('.($rank==1?'245,158,11':'148,163,184').',.15)' : 'var(--color-surface)' ?>;color:<?= $color ?>">
                                <?= $rank ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold truncate"><?= htmlspecialchars($c['nama']) ?></div>
                            </div>
                            <div class="text-sm font-bold flex-shrink-0" style="color:var(--color-primary-dark)">
                                <?= number_format($c['poin']) ?>
                                <span class="font-normal text-xs" style="color:var(--color-ink-muted)">poin</span>
                            </div>
                        </div>
                        <?php $rank++; endwhile; ?>
                        <?php if ($rank === 1): ?>
                            <div class="empty-state" style="padding:1.5rem 0"><p>Belum ada data</p></div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- end dashboard-grid -->
        </div><!-- end content -->
    </main>
</div>

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
<?php include_once '../includes/notif_scripts.php'; ?>
</body>
</html>
