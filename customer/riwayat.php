<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$total_rows = $conn->query("SELECT COUNT(*) as c FROM transaksi WHERE customer_id = $uid")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT t.id, t.kode_transaksi, t.total_poin, t.total_berat, t.status, t.catatan, t.created_at
    FROM transaksi t
    WHERE t.customer_id = $uid
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Setor — Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Riwayat Setor Sampah</h1>
                    <p>Semua transaksi setor Anda</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-star"></i>
                    <?= number_format($_SESSION['poin'], 0, ',', '.') ?> Poin
                </div>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Transaksi</h3>
                    <span class="text-muted text-small"><?= $total_rows ?> transaksi</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Berat</th>
                                <th>Poin Didapat</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($list->num_rows === 0): ?>
                            <tr><td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-recycle" style="font-size:3rem;opacity:.3"></i></div>
                                    <p>Belum ada riwayat setor</p>
                                </div>
                            </td></tr>
                            <?php else: ?>
                            <?php while ($row = $list->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-green"><?= $row['kode_transaksi'] ?></span></td>
                                <td><?= number_format($row['total_berat'], 2) ?> kg</td>
                                <td><strong style="color:var(--primary-dark)">+<?= number_format($row['total_poin']) ?> poin</strong></td>
                                <td>
                                    <?php $b = ['selesai'=>'badge-green','pending'=>'badge-orange','batal'=>'badge-red']; ?>
                                    <span class="badge <?= $b[$row['status']] ?? 'badge-gray' ?>"><?= ucfirst($row['status']) ?></span>
                                </td>
                                <td class="text-muted text-small"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
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
            </div>
        </div>
    </main>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:.5rem"></i>Detail Transaksi</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><div class="empty-icon">⏳</div><p>Memuat...</p></div>
        </div>
    </div>
</div>

<script>
function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div class="empty-state" style="padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';

    fetch(BASE_URL + `/api/detail-transaksi.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const t = res.transaksi;
            let itemsHtml = res.items.map(i => `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.875rem">${i.nama}</div>
                        <div class="text-muted text-small">${i.berat} kg × ${i.poin_per_kg} poin/kg</div>
                    </div>
                    <div style="font-weight:700;color:var(--primary-dark)">+${i.subtotal_poin} poin</div>
                </div>`).join('');

            document.getElementById('detailContent').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Kode Transaksi</div>
                        <div style="font-weight:700">${t.kode}</div>
                    </div>
                    <div style="background:var(--primary-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Total Poin</div>
                        <div style="font-weight:700;color:var(--primary-dark)">${t.total_poin} poin</div>
                    </div>
                </div>
                <div style="font-weight:600;margin-bottom:.5rem;font-size:.875rem">Item yang Disetor:</div>
                ${itemsHtml}
                <div style="margin-top:.75rem" class="text-muted text-small">Tanggal: ${t.tanggal}</div>`;
        });
}
</script>
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
