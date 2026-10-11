<?php
$current = basename($_SERVER['PHP_SELF']);

if (!isset($conn)) {
    @include_once __DIR__ . '/../config/database.php';
}

$e_active_jemput  = 0;
$e_active_setor   = 0;
$e_active_tukar   = 0;

if (isset($conn) && isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    
    $r_ej = $conn->query("SELECT COUNT(*) as c FROM everwear_jemput WHERE customer_id = $uid AND status NOT IN ('selesai', 'dibatalkan')");
    if ($r_ej) $e_active_jemput = intval($r_ej->fetch_assoc()['c'] ?? 0);

    $r_es = $conn->query("SELECT COUNT(*) as c FROM everwear_transaksi WHERE customer_id = $uid AND status = 'pending'");
    if ($r_es) $e_active_setor = intval($r_es->fetch_assoc()['c'] ?? 0);

    $r_et = $conn->query("SELECT COUNT(*) as c FROM penukaran WHERE customer_id = $uid AND status IN ('pending', 'diproses')");
    if ($r_et) $e_active_tukar = intval($r_et->fetch_assoc()['c'] ?? 0);
}
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-box" style="background:transparent;padding:0;overflow:hidden"><img src="<?= BASE_URL ?>/assets/EVERWEAR.jpeg" alt="Everwear" style="width:100%;height:100%;object-fit:contain"></div>
        <div class="logo-text">
            <h2>Everwear</h2>
            <span>Halo, <?= htmlspecialchars(explode(' ', $_SESSION['nama'] ?? 'Customer')[0]) ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-label">Menu</div>
            <a href="<?= BASE_URL ?>/everwear/dashboard.php" class="nav-item <?= ($current === 'dashboard.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Beranda
            </a>
            <a href="<?= BASE_URL ?>/everwear/setor.php" class="nav-item <?= ($current === 'setor.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-shirt"></i>
                <span style="flex:1">Setor Sendiri</span>
                <?php if ($e_active_setor > 0): ?>
                <span class="nav-badge-pill"><?= $e_active_setor ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/everwear/riwayat.php" class="nav-item <?= ($current === 'riwayat.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Sampah
            </a>
            <a href="<?= BASE_URL ?>/everwear/jemput.php" class="nav-item <?= ($current === 'jemput.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-truck"></i>
                <span style="flex:1">Jemput Sampah</span>
                <?php if ($e_active_jemput > 0): ?>
                <span class="nav-badge-pill"><?= $e_active_jemput ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/customer/dashboard.php" class="nav-item">
                <i class="fa-solid fa-recycle"></i> Ke Trashily
            </a>
            <a href="<?= BASE_URL ?>/everwear/penukaran.php" class="nav-item <?= ($current === 'penukaran.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-gift"></i>
                <span style="flex:1">Tukar Poin</span>
                <?php if ($e_active_tukar > 0): ?>
                <span class="nav-badge-pill orange"><?= $e_active_tukar ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="nav-section">
            <div class="nav-section-label">Akun</div>
            <a href="<?= BASE_URL ?>/everwear/profile.php" class="nav-item <?= ($current === 'profile.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-user-pen"></i> Edit Profil
            </a>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item" style="color:var(--danger)">
                <i class="fa-solid fa-right-from-bracket" style="color:var(--danger)"></i> Keluar
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/everwear/profile.php" class="user-card" style="text-decoration:none;display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--bg-light);border-radius:12px;transition:background .15s" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='var(--bg-light)'">
            <div class="avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'E', 0, 1)) ?></div>
            <div class="user-info" style="flex:1;min-width:0">
                <div class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                <div class="user-role"><?= number_format($_SESSION['poin'] ?? 0, 0, ',', '.') ?> Poin</div>
            </div>
            <i class="fa-solid fa-chevron-right" style="font-size:.7rem;color:var(--text-muted)"></i>
        </a>
    </div>
</aside>
