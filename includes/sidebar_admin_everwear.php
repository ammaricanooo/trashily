<?php
$current = basename($_SERVER['PHP_SELF']);

if (!isset($conn)) {
    @include_once __DIR__ . '/../config/database.php';
}

$ew_pending_transaksi = 0;
$ew_pending_jemput = 0;

if (isset($conn)) {
    $r_t = $conn->query("SELECT COUNT(*) as c FROM everwear_transaksi WHERE status = 'pending'");
    if ($r_t) $ew_pending_transaksi = intval($r_t->fetch_assoc()['c'] ?? 0);

    $r_j = $conn->query("SELECT COUNT(*) as c FROM everwear_jemput WHERE status = 'pending'");
    if ($r_j) $ew_pending_jemput = intval($r_j->fetch_assoc()['c'] ?? 0);
}
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar" style="--primary:#1976D2;--primary-dark:#0D47A1;--primary-light:#E3F2FD;--color-primary:#1976D2;--color-primary-dark:#0D47A1;--color-primary-light:#E3F2FD;--color-primary-container:#BBDEFB;">
    <div class="sidebar-logo">
        <div class="logo-box" style="background:transparent;padding:0;overflow:hidden">
            <img src="<?= BASE_URL ?>/assets/EVERWEAR.jpeg" alt="Everwear" style="width:100%;height:100%;object-fit:contain">
        </div>
        <div class="logo-text">
            <h2>Everwear</h2>
            <span>Admin Panel</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-label">Utama</div>
            <a href="<?= BASE_URL ?>/admin/everwear/dashboard.php" class="nav-item <?= ($current === 'dashboard.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard Everwear
            </a>
            <a href="<?= BASE_URL ?>/admin/everwear/transaksi.php" class="nav-item <?= ($current === 'transaksi.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-shirt"></i>
                <span style="flex:1">Donasi Masuk</span>
                <?php if ($ew_pending_transaksi > 0): ?>
                <span class="nav-badge-pill"><?= $ew_pending_transaksi ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/everwear/jemput-sampah.php" class="nav-item <?= ($current === 'jemput-sampah.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i>
                <span style="flex:1">Jemput Pakaian</span>
                <?php if ($ew_pending_jemput > 0): ?>
                <span class="nav-badge-pill"><?= $ew_pending_jemput ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label">Navigasi Sistem</div>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-item">
                <i class="fa-solid fa-recycle"></i> Ke Admin Trashily
            </a>
            <a href="<?= BASE_URL ?>/everwear.php" class="nav-item" target="_blank">
                <i class="fa-solid fa-globe"></i> Halaman Publik
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-label">Akun</div>
            <a href="<?= BASE_URL ?>/profile.php" class="nav-item">
                <i class="fa-solid fa-user-pen"></i> Edit Profil
            </a>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item" style="color:var(--danger)">
                <i class="fa-solid fa-right-from-bracket" style="color:var(--danger)"></i> Keluar
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/profile.php" class="user-card" style="text-decoration:none;display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--bg-light);border-radius:12px;">
            <div class="avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
            <div class="user-info" style="flex:1;min-width:0">
                <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                <div class="user-role">Admin Everwear</div>
            </div>
            <i class="fa-solid fa-chevron-right" style="font-size:.7rem;color:var(--text-muted)"></i>
        </a>
    </div>
</aside>
