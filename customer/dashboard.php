<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid  = $_SESSION['user_id'];
$user = $conn->query("SELECT nama, poin FROM users WHERE id = $uid")->fetch_assoc();
$_SESSION['poin'] = $user['poin'];

$total_setor     = $conn->query("SELECT COUNT(*) as c FROM transaksi WHERE customer_id=$uid AND status='selesai'")->fetch_assoc()['c'];
$total_poin_all  = $conn->query("SELECT COALESCE(SUM(total_poin),0) as c FROM transaksi WHERE customer_id=$uid AND status='selesai'")->fetch_assoc()['c'];
$total_berat     = $conn->query("SELECT COALESCE(SUM(total_berat),0) as c FROM transaksi WHERE customer_id=$uid AND status='selesai'")->fetch_assoc()['c'];
$total_penukaran = $conn->query("SELECT COUNT(*) as c FROM penukaran WHERE customer_id=$uid AND status!='batal'")->fetch_assoc()['c'];

$riwayat = $conn->query("
    SELECT kode_transaksi, total_poin, total_berat, created_at
    FROM transaksi
    WHERE customer_id = $uid
    ORDER BY created_at DESC LIMIT 5
");

$hadiah = $conn->query("
    SELECT id, nama, poin_dibutuhkan, stok
    FROM hadiah WHERE is_active=1
    ORDER BY poin_dibutuhkan ASC LIMIT 5
");

$poin = $user['poin'];
// Estimasi progress ke hadiah berikutnya
$next_hadiah = $conn->query("SELECT poin_dibutuhkan FROM hadiah WHERE is_active=1 AND poin_dibutuhkan > $poin ORDER BY poin_dibutuhkan ASC LIMIT 1")->fetch_assoc();
$progress_pct = 0;
if ($next_hadiah) {
    $prev = $conn->query("SELECT COALESCE(MAX(poin_dibutuhkan),0) as p FROM hadiah WHERE is_active=1 AND poin_dibutuhkan <= $poin")->fetch_assoc()['p'];
    $range = $next_hadiah['poin_dibutuhkan'] - $prev;
    $progress_pct = $range > 0 ? min(100, round(($poin - $prev) / $range * 100)) : 100;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda — Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_customer.php'; ?>

    <main class="main">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1>Beranda</h1>
                    <p><?= date('l, d F Y') ?></p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-coins text-xs"></i>
                    <?= number_format($poin, 0, ',', '.') ?> Poin
                </div>
            </div>
        </div>

        <div class="content">

            <!-- HERO POIN CARD -->
            <div class="relative overflow-hidden rounded-2xl mb-6"
                 style="background: linear-gradient(135deg, #006e2f 0%, #004b1e 100%)">
                <!-- Orbs -->
                <div class="absolute rounded-full pointer-events-none"
                     style="width:240px;height:240px;top:-80px;right:-60px;background:radial-gradient(circle,rgba(74,225,118,.18) 0%,transparent 70%)"></div>
                <div class="absolute rounded-full pointer-events-none"
                     style="width:160px;height:160px;bottom:-50px;right:25%;background:radial-gradient(circle,rgba(255,255,255,.07) 0%,transparent 70%)"></div>

                <div class="relative z-10 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <!-- Poin info -->
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest mb-1" style="color:rgba(255,255,255,.65)">
                                Saldo Poin Anda
                            </p>
                            <div class="flex items-end gap-2 mb-1">
                                <span class="font-extrabold text-white leading-none"
                                      style="font-family:var(--font-display);font-size:2.75rem">
                                    <?= number_format($poin, 0, ',', '.') ?>
                                </span>
                                <span class="text-base font-semibold mb-1" style="color:rgba(255,255,255,.7)">poin</span>
                            </div>
                            <p class="text-xs mb-4" style="color:rgba(255,255,255,.75)">
                                Halo, <strong class="text-white"><?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?></strong>! Terus setor sampah &amp; kumpulkan poin.
                            </p>

                            <!-- Progress bar ke hadiah berikutnya -->
                            <?php if ($next_hadiah): ?>
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-semibold" style="color:rgba(255,255,255,.75)">
                                        Menuju hadiah <?= htmlspecialchars($next_hadiah['poin_dibutuhkan']) ?> poin
                                    </span>
                                    <span class="text-xs font-bold text-white"><?= $progress_pct ?>%</span>
                                </div>
                                <div class="rounded-full h-2" style="background:rgba(255,255,255,.2)">
                                    <div class="h-2 rounded-full transition-all"
                                         style="width:<?= $progress_pct ?>%;background:var(--color-inverse-primary)"></div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Action buttons -->
                            <div class="flex gap-3 flex-wrap">
                                <a href="setor.php"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold"
                                   style="background:#fff;color:var(--color-primary-dark)">
                                    <i class="fa-solid fa-person-walking-luggage text-xs"></i> Setor Sendiri
                                </a>
                                <a href="jemput.php"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold"
                                   style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
                                    <i class="fa-solid fa-truck text-xs"></i> Jemput Sampah
                                </a>
                                <a href="penukaran.php"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold"
                                   style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25)">
                                    <i class="fa-solid fa-gift text-xs"></i> Tukar Poin
                                </a>
                            </div>
                        </div>

                        <!-- Mini stats -->
                        <div class="grid grid-cols-2 gap-3 sm:w-52 flex-shrink-0">
                            <div class="rounded-xl p-3 text-center" style="background:rgba(255,255,255,.12)">
                                <div class="text-lg font-extrabold text-white" style="font-family:var(--font-display)"><?= $total_setor ?></div>
                                <div class="text-xs" style="color:rgba(255,255,255,.7)">Kali Setor</div>
                            </div>
                            <div class="rounded-xl p-3 text-center" style="background:rgba(255,255,255,.12)">
                                <div class="text-lg font-extrabold text-white" style="font-family:var(--font-display)"><?= number_format($total_berat, 1) ?></div>
                                <div class="text-xs" style="color:rgba(255,255,255,.7)">kg Sampah</div>
                            </div>
                            <div class="rounded-xl p-3 text-center" style="background:rgba(255,255,255,.12)">
                                <div class="text-lg font-extrabold text-white" style="font-family:var(--font-display)"><?= number_format($total_poin_all) ?></div>
                                <div class="text-xs" style="color:rgba(255,255,255,.7)">Poin Diperoleh</div>
                            </div>
                            <div class="rounded-xl p-3 text-center" style="background:rgba(255,255,255,.12)">
                                <div class="text-lg font-extrabold text-white" style="font-family:var(--font-display)"><?= $total_penukaran ?></div>
                                <div class="text-xs" style="color:rgba(255,255,255,.7)">Penukaran</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="dashboard-grid">

                <!-- Riwayat Setor -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-clock-rotate-left text-xs mr-2" style="color:var(--color-primary)"></i>Setor Terbaru</h3>
                        <a href="riwayat.php" class="btn btn-ghost btn-sm">Semua <i class="fa-solid fa-arrow-right text-xs"></i></a>
                    </div>
                    <?php if ($riwayat->num_rows === 0): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fa-solid fa-recycle"></i></div>
                        <p>Belum ada riwayat setor.<br>Yuk mulai setor sampahmu!</p>
                        <a href="setor.php" class="btn btn-primary mt-3">Setor Sekarang</a>
                    </div>
                    <?php else: ?>
                    <div style="padding:.25rem 0">
                        <?php while ($r = $riwayat->fetch_assoc()): ?>
                        <div class="flex items-center gap-3 px-5 py-4 border-b" style="border-color:var(--color-border)">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:var(--color-primary-light)">
                                <i class="fa-solid fa-recycle text-sm" style="color:var(--color-primary)"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm"><?= $r['kode_transaksi'] ?></div>
                                <div class="text-muted text-small"><?= number_format($r['total_berat'], 2) ?> kg · <?= date('d M Y', strtotime($r['created_at'])) ?></div>
                            </div>
                            <span class="badge badge-green flex-shrink-0">+<?= number_format($r['total_poin']) ?> poin</span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Hadiah Tersedia -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fa-solid fa-gift text-xs mr-2" style="color:var(--color-amber)"></i>Hadiah Tersedia</h3>
                        <a href="penukaran.php" class="btn btn-ghost btn-sm">Semua</a>
                    </div>
                    <div style="padding:.25rem 0">
                        <?php while ($h = $hadiah->fetch_assoc()):
                            $can = $poin >= $h['poin_dibutuhkan'];
                        ?>
                        <div class="flex items-center gap-3 px-4 py-3 border-b" style="border-color:var(--color-border)">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                                 style="background:<?= $can ? 'var(--color-primary-light)' : 'var(--color-surface)' ?>">
                                <i class="fa-solid fa-gift text-sm" style="color:<?= $can ? 'var(--color-primary)' : 'var(--color-ink-subtle)' ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold truncate"><?= htmlspecialchars($h['nama']) ?></div>
                                <div class="text-xs font-bold" style="color:var(--color-amber)"><?= number_format($h['poin_dibutuhkan']) ?> poin</div>
                            </div>
                            <?php if ($can): ?>
                                <a href="penukaran.php" class="badge badge-green flex-shrink-0 cursor-pointer">Tukar</a>
                            <?php else: ?>
                                <span class="badge badge-gray flex-shrink-0">
                                    <?= number_format($h['poin_dibutuhkan'] - $poin) ?> lagi
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
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
