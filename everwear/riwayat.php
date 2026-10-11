<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT nama, poin FROM users WHERE id = $uid")->fetch_assoc();
$_SESSION['poin'] = $user['poin'];

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$total_rows  = $conn->query("SELECT COUNT(*) as c FROM everwear_transaksi WHERE customer_id = $uid")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT id, kode_transaksi, jumlah_pcs, pcs_layak, pcs_recycle, berat_kg, poin_diperoleh, status, created_at
    FROM everwear_transaksi
    WHERE customer_id = $uid
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

$status_badge = [
    'pending' => ['badge-orange', 'Menunggu'],
    'proses' => ['badge-blue', 'Proses'],
    'selesai' => ['badge-green', 'Selesai'],
    'dibatalkan' => ['badge-red', 'Dibatalkan'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sampah — Everwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_everwear.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Riwayat Sampah</h1>
                    <p>Lihat semua riwayat donasi pakaian Anda</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-coins text-xs"></i>
                    <?= number_format($user['poin'], 0, ',', '.') ?> Poin
                </div>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Lengkap Donasi</h3>
                    <a href="setor.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Donasi Baru
                    </a>
                </div>

                <?php if ($list->num_rows === 0): ?>
                <div class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa-solid fa-shirt" style="font-size:3rem;opacity:.3"></i></div>
                    <p>Belum ada riwayat donasi</p>
                    <a href="setor.php" class="btn btn-primary" style="margin-top:1rem">
                        <i class="fa-solid fa-plus"></i> Mulai Donasi
                    </a>
                </div>

                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jumlah Baju</th>
                                <th>Berat</th>
                                <th>Status</th>
                                <th>Poin</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list->fetch_assoc()):
                                [$bcls, $btxt] = $status_badge[$row['status']] ?? ['badge-gray', ucfirst($row['status'])];
                            ?>
                            <tr>
                                <td><span class="badge badge-blue"><?= $row['kode_transaksi'] ?></span></td>
                                <td>
                                    1. Baju layak: <strong><?= intval($row['pcs_layak']) ?></strong> pcs<br>
                                    2. Recycle: <strong><?= intval($row['pcs_recycle']) ?></strong> pcs
                                </td>
                                <td><?= number_format($row['berat_kg'], 2) ?> kg</td>
                                <td><span class="badge <?= $bcls ?>"><?= $btxt ?></span></td>
                                <td>
                                    <?php if ($row['poin_diperoleh'] > 0): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['poin_diperoleh']) ?> poin</strong>
                                    <?php else: ?>
                                    <span class="text-small text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted text-small"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div style="padding:1rem 1.25rem;display:flex;justify-content:center">
                    <div class="pagination">
                        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a><?php endif; ?>
                        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
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
