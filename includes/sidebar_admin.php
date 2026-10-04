<?php
$current = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

if (!isset($conn)) {
    @include_once __DIR__ . '/../config/database.php';
}

$nav_jemput_count = 0;
$nav_penukaran_count = 0;

if (isset($conn)) {
    $r_j = $conn->query("SELECT COUNT(*) as c FROM jemput_sampah WHERE status NOT IN ('selesai', 'batal')");
    if ($r_j) $nav_jemput_count = intval($r_j->fetch_assoc()['c'] ?? 0);

    $r_p = $conn->query("SELECT COUNT(*) as c FROM penukaran WHERE status = 'pending'");
    if ($r_p) $nav_penukaran_count = intval($r_p->fetch_assoc()['c'] ?? 0);
}
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box" style="background:transparent;padding:0;overflow:hidden"><img src="<?= BASE_URL ?>/assets/brand.png" alt="Trashily" style="width:100%;height:100%;object-fit:contain"></div>
        <div class="logo-text">
            <h2>Trashily</h2>
            <span>Panel Admin</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-label">Utama</div>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-item <?= ($current === 'dashboard.php' && $currentDir === 'admin') ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/transaksi.php" class="nav-item <?= ($current === 'transaksi.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Transaksi Setor
            </a>
            <a href="<?= BASE_URL ?>/admin/jemput-sampah.php" class="nav-item <?= ($current === 'jemput-sampah.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i>
                <span>Jemput Sampah</span>
                <?php if ($nav_jemput_count > 0): ?>
                <span class="nav-badge-pill"><?= $nav_jemput_count ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/admin/penukaran.php" class="nav-item <?= ($current === 'penukaran.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-gift"></i>
                <span>Penukaran Poin</span>
                <?php if ($nav_penukaran_count > 0): ?>
                <span class="nav-badge-pill orange"><?= $nav_penukaran_count ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Kelola</div>
            <a href="<?= BASE_URL ?>/admin/kelola-customer.php" class="nav-item <?= ($current === 'kelola-customer.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Data Customer
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola-sampah.php" class="nav-item <?= ($current === 'kelola-sampah.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-recycle"></i> Jenis Sampah
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola-hadiah.php" class="nav-item <?= ($current === 'kelola-hadiah.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-box-open"></i> Kelola Hadiah
            </a>
            <a href="<?= BASE_URL ?>/admin/kelola-ulasan.php" class="nav-item <?= ($current === 'kelola-ulasan.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-star"></i> Kelola Ulasan
            </a>
            <a href="<?= BASE_URL ?>/admin/lokasi.php" class="nav-item <?= ($current === 'lokasi.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i> Lokasi Trashily
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Akun</div>
            <a href="<?= BASE_URL ?>/profile.php" class="nav-item <?= ($current === 'profile.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-pen"></i> Edit Profil
            </a>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item" style="color:var(--danger)">
                <i class="fa-solid fa-right-from-bracket" style="color:var(--danger)"></i> Keluar
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/profile.php" class="user-card" style="text-decoration:none;display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--bg-light);border-radius:12px;transition:background .15s" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='var(--bg-light)'">
            <div class="avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
            <div class="user-info" style="flex:1;min-width:0">
                <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                <div class="user-role">Administrator</div>
            </div>
            <i class="fa-solid fa-chevron-right" style="font-size:.7rem;color:var(--text-muted)"></i>
        </a>
    </div>
</aside>
