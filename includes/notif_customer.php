<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($conn)) {
    @include_once __DIR__ . '/../config/database.php';
}

$c_uid = $_SESSION['user_id'] ?? 0;
$c_notif_items = [];
$c_unread_count = 0;

if ($c_uid && isset($conn)) {
    // 1. Status Permintaan Jemput Sampah Customer
    $res_j = $conn->query("SELECT id, kode_jemput, status, jadwal_jemput, created_at, catatan_admin 
                           FROM jemput_sampah 
                           WHERE customer_id = $c_uid 
                           ORDER BY created_at DESC LIMIT 5");
    if ($res_j) {
        while ($r = $res_j->fetch_assoc()) {
            $st = $r['status'];
            if ($st !== 'selesai' && $st !== 'batal') {
                $c_unread_count++;
            }
            
            $icon = 'fa-truck';
            $badge_color = 'green';
            $title = 'Jemput Sampah ' . $r['kode_jemput'];
            $desc = '';

            if ($st === 'menunggu') {
                $desc = 'Permintaan jemput sedang menunggu konfirmasi admin.';
                $badge_color = 'orange';
            } elseif ($st === 'dikonfirmasi') {
                $desc = 'Jadwal penjemputan telah dikonfirmasi petugas.';
                $badge_color = 'blue';
            } elseif ($st === 'dijemput') {
                $desc = 'Petugas sedang dalam perjalanan ke lokasi Anda.';
                $badge_color = 'blue';
            } elseif ($st === 'selesai') {
                $desc = 'Penjemputan selesai & poin telah dikreditkan!';
                $badge_color = 'green';
            } elseif ($st === 'batal') {
                $desc = 'Permintaan penjemputan dibatalkan.';
                $badge_color = 'red';
            }

            $c_notif_items[] = [
                'type'   => 'jemput',
                'id'     => $r['id'],
                'kode'   => $r['kode_jemput'],
                'status' => $st,
                'title'  => $title,
                'desc'   => $desc,
                'time'   => $r['created_at'],
                'url'    => '/customer/jemput.php',
                'icon'   => $icon,
                'color'  => $badge_color
            ];
        }
    }

    // 2. Status Penukaran Hadiah Customer
    $res_p = $conn->query("SELECT p.id, p.kode_penukaran, p.status, p.created_at, h.nama as hadiah_nama 
                           FROM penukaran p 
                           JOIN hadiah h ON p.hadiah_id = h.id 
                           WHERE p.customer_id = $c_uid 
                           ORDER BY p.created_at DESC LIMIT 5");
    if ($res_p) {
        while ($r = $res_p->fetch_assoc()) {
            $st = $r['status'];
            if ($st === 'pending' || $st === 'diproses') {
                $c_unread_count++;
            }

            $desc = '';
            $badge_color = 'orange';
            if ($st === 'pending') {
                $desc = 'Tukar ' . $r['hadiah_nama'] . ' sedang menunggu persetujuan.';
            } elseif ($st === 'diproses') {
                $desc = 'Hadiah ' . $r['hadiah_nama'] . ' sedang disiapkan admin.';
                $badge_color = 'blue';
            } elseif ($st === 'selesai') {
                $desc = 'Penukaran ' . $r['hadiah_nama'] . ' selesai!';
                $badge_color = 'green';
            } elseif ($st === 'batal') {
                $desc = 'Penukaran ' . $r['hadiah_nama'] . ' dibatalkan.';
                $badge_color = 'red';
            }

            $c_notif_items[] = [
                'type'   => 'penukaran',
                'id'     => $r['id'],
                'kode'   => $r['kode_penukaran'],
                'status' => $st,
                'title'  => 'Tukar Hadiah ' . $r['kode_penukaran'],
                'desc'   => $desc,
                'time'   => $r['created_at'],
                'url'    => '/customer/penukaran.php',
                'icon'   => 'fa-gift',
                'color'  => $badge_color
            ];
        }
    }

    usort($c_notif_items, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });
}
?>
<div class="admin-notif-wrap" style="margin-right:.25rem">
    <button class="btn-notif-bell" id="btnCustomerNotif" onclick="toggleCustomerNotifDropdown(event)" title="Notifikasi Saya">
        <i class="fa-solid fa-bell"></i>
        <?php if ($c_unread_count > 0): ?>
        <span class="notif-badge-count"><?= $c_unread_count > 99 ? '99+' : $c_unread_count ?></span>
        <?php endif; ?>
    </button>

    <div class="notif-dropdown-menu" id="customerNotifDropdown">
        <div class="notif-dd-header">
            <div class="notif-dd-title">
                <i class="fa-solid fa-bell" style="color:var(--primary)"></i> Status & Notifikasi
            </div>
            <?php if ($c_unread_count > 0): ?>
            <span class="badge badge-blue"><?= $c_unread_count ?> Aktif</span>
            <?php endif; ?>
        </div>

        <div class="notif-dd-body">
            <?php if (empty($c_notif_items)): ?>
            <div class="notif-empty-state">
                <i class="fa-regular fa-bell-slash" style="font-size:1.6rem;color:var(--text-muted);margin-bottom:.4rem"></i>
                <div>Belum ada aktivitas baru</div>
            </div>
            <?php else: ?>
            <?php foreach (array_slice($c_notif_items, 0, 6) as $item): ?>
            <a href="<?= $item['url'] ?>" class="notif-dd-item <?= $item['status'] !== 'selesai' && $item['status'] !== 'batal' ? 'unread' : '' ?>">
                <div class="notif-item-icon <?= $item['color'] ?>">
                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                </div>
                <div class="notif-item-content">
                    <div class="notif-item-title"><?= htmlspecialchars($item['title']) ?></div>
                    <div class="notif-item-sub"><?= htmlspecialchars($item['desc']) ?></div>
                    <div class="notif-item-time"><i class="fa-regular fa-clock" style="margin-right:.2rem"></i><?= date('d M Y, H:i', strtotime($item['time'])) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="notif-dd-footer">
            <a href="<?= BASE_URL ?>/customer/jemput.php">Jemput Sampah</a>
            <span style="color:var(--border)">•</span>
            <a href="<?= BASE_URL ?>/customer/penukaran.php">Tukar Poin</a>
        </div>
    </div>
</div>