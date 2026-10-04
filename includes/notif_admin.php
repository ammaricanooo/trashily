<?php
if (!isset($conn)) {
    @include_once __DIR__ . '/../config/database.php';
}

$notif_jemput_count = 0;
$notif_penukaran_count = 0;
$notif_items = [];

if (isset($conn)) {
    // 1. Permintaan Jemput Sampah baru / belum selesai
    $res_j = $conn->query("SELECT js.id, js.kode_jemput, js.created_at, js.status, u.nama 
                           FROM jemput_sampah js 
                           JOIN users u ON js.customer_id = u.id 
                           WHERE js.status IN ('menunggu', 'dikonfirmasi', 'dijemput') 
                           ORDER BY js.created_at DESC LIMIT 5");
    if ($res_j) {
        while ($r = $res_j->fetch_assoc()) {
            if ($r['status'] === 'menunggu') $notif_jemput_count++;
            $status_lbl = $r['status'] === 'menunggu' ? 'Baru' : ($r['status'] === 'dikonfirmasi' ? 'Dikonfirmasi' : 'Sedang Dijemput');
            $notif_items[] = [
                'type'     => 'jemput',
                'id'       => $r['id'],
                'kode'     => $r['kode_jemput'],
                'nama'     => $r['nama'],
                'status'   => $r['status'],
                'time'     => $r['created_at'],
                'url'      => '/admin/jemput-sampah.php?search=' . urlencode($r['kode_jemput']),
                'icon'     => 'fa-truck',
                'title'    => 'Jemput Sampah ' . $status_lbl,
                'subtitle' => $r['nama'] . ' (' . $r['kode_jemput'] . ')'
            ];
        }
    }

    // 2. Penukaran Hadiah Pending
    $res_p = $conn->query("SELECT p.id, p.kode_penukaran, p.created_at, u.nama, h.nama as hadiah 
                           FROM penukaran p 
                           JOIN users u ON p.customer_id = u.id 
                           JOIN hadiah h ON p.hadiah_id = h.id 
                           WHERE p.status = 'pending' 
                           ORDER BY p.created_at DESC LIMIT 5");
    if ($res_p) {
        while ($r = $res_p->fetch_assoc()) {
            $notif_penukaran_count++;
            $notif_items[] = [
                'type'     => 'penukaran',
                'id'       => $r['id'],
                'kode'     => $r['kode_penukaran'],
                'nama'     => $r['nama'],
                'status'   => 'pending',
                'time'     => $r['created_at'],
                'url'      => '/admin/penukaran.php?search=' . urlencode($r['kode_penukaran']),
                'icon'     => 'fa-gift',
                'title'    => 'Penukaran Hadiah Pending',
                'subtitle' => $r['nama'] . ' tukar ' . $r['hadiah']
            ];
        }
    }

    usort($notif_items, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
}

$total_notif_badge = $notif_jemput_count + $notif_penukaran_count;
?>

<div class="admin-notif-wrap">
    <button class="btn-notif-bell" id="btnAdminNotif" onclick="toggleAdminNotifDropdown(event)" title="Notifikasi Admin">
        <i class="fa-solid fa-bell"></i>
        <?php if ($total_notif_badge > 0): ?>
        <span class="notif-badge-count"><?= $total_notif_badge > 99 ? '99+' : $total_notif_badge ?></span>
        <?php endif; ?>
    </button>

    <div class="notif-dropdown-menu" id="adminNotifDropdown">
        <div class="notif-dd-header">
            <div class="notif-dd-title">
                <i class="fa-solid fa-bell" style="color:var(--primary)"></i> Notifikasi
            </div>
            <?php if ($total_notif_badge > 0): ?>
            <span class="badge badge-orange"><?= $total_notif_badge ?> Baru</span>
            <?php endif; ?>
        </div>

        <div class="notif-dd-body">
            <?php if (empty($notif_items)): ?>
            <div class="notif-empty-state">
                <i class="fa-regular fa-bell-slash" style="font-size:1.6rem;color:var(--text-muted);margin-bottom:.4rem"></i>
                <div>Belum ada notifikasi baru</div>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($notif_items, 0, 6) as $item): ?>
            <a href="<?= $item['url'] ?>" class="notif-dd-item <?= $item['status'] === 'menunggu' || $item['status'] === 'pending' ? 'unread' : '' ?>">
                <div class="notif-item-icon <?= $item['type'] === 'jemput' ? 'green' : 'orange' ?>">
                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                </div>
                <div class="notif-item-content">
                    <div class="notif-item-title"><?= htmlspecialchars($item['title']) ?></div>
                    <div class="notif-item-sub"><?= htmlspecialchars($item['subtitle']) ?></div>
                    <div class="notif-item-time"><i class="fa-regular fa-clock" style="margin-right:.2rem"></i><?= date('d M Y, H:i', strtotime($item['time'])) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="notif-dd-footer">
            <a href="<?= BASE_URL ?>/admin/jemput-sampah.php">Jemput Sampah</a>
            <span style="color:var(--border)">•</span>
            <a href="<?= BASE_URL ?>/admin/penukaran.php">Penukaran Hadiah</a>
        </div>
    </div>
</div>