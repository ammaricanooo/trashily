<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

$filter = $_GET['status'] ?? 'pending';
$allowed = ['pending', 'diproses', 'selesai', 'batal', 'semua'];
if (!in_array($filter, $allowed)) $filter = 'pending';

$where = $filter !== 'semua' ? "WHERE p.status = '$filter'" : "WHERE 1=1";

$penukaran_list = $conn->query("
    SELECT p.id, p.kode_penukaran, p.poin_digunakan, p.status, p.created_at,
           u.nama as customer_nama, u.no_hp,
           h.nama as hadiah_nama
    FROM penukaran p
    JOIN users u ON p.customer_id = u.id
    JOIN hadiah h ON p.hadiah_id = h.id
    $where
    ORDER BY p.created_at DESC
    LIMIT 50
");

$counts = [];
foreach (['pending','diproses','selesai','batal'] as $s) {
    $counts[$s] = $conn->query("SELECT COUNT(*) as c FROM penukaran WHERE status='$s'")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penukaran Poin — Trashily</title>
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
    <?php include '../includes/sidebar_admin.php'; ?>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Penukaran Poin</h1>
                    <p>Kelola permintaan penukaran poin customer</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>
        <div class="content">
            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <?php
                $tabs = ['pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Dibatalkan', 'semua' => 'Semua'];
                foreach ($tabs as $val => $label):
                    $active = $filter === $val;
                    $count = isset($counts[$val]) ? $counts[$val] : 0;
                ?>
                <a href="?status=<?= $val ?>" class="filter-tab <?= $active ? 'active' : '' ?>">
                    <?= $label ?>
                    <?php if ($val !== 'semua' || $count > 0): ?>
                        <span class="tab-count"><?= $count ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Penukaran — <?= $tabs[$filter] ?></h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Hadiah</th>
                                <th>Poin</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($penukaran_list->num_rows === 0): ?>
                            <tr><td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon">🎁</div>
                                    <p>Tidak ada penukaran <?= $filter !== 'semua' ? $filter : '' ?></p>
                                </div>
                            </td></tr>
                            <?php else: ?>
                            <?php while ($row = $penukaran_list->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-blue"><?= $row['kode_penukaran'] ?></span></td>
                                <td>
                                    <div style="font-weight:600"><?= htmlspecialchars($row['customer_nama']) ?></div>
                                    <div class="text-muted text-small"><?= $row['no_hp'] ?></div>
                                </td>
                                <td><?= htmlspecialchars($row['hadiah_nama']) ?></td>
                                <td><strong style="color:var(--accent)"><?= number_format($row['poin_digunakan']) ?> poin</strong></td>
                                <td>
                                    <?php
                                    $badge = ['pending' => 'badge-orange', 'diproses' => 'badge-blue', 'selesai' => 'badge-green', 'batal' => 'badge-red'];
                                    $label_map = ['pending' => 'Pending', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'batal' => 'Batal'];
                                    ?>
                                    <span class="badge <?= $badge[$row['status']] ?>"><?= $label_map[$row['status']] ?></span>
                                </td>
                                <td class="text-muted text-small"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <div style="display:flex;gap:.4rem">
                                        <button class="btn btn-info btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'diproses', '<?= $row['kode_penukaran'] ?>')">
                                            <i class="fa-solid fa-play"></i> Proses
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'batal', '<?= $row['kode_penukaran'] ?>')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <?php elseif ($row['status'] === 'diproses'): ?>
                                    <button class="btn btn-primary btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'selesai', '<?= $row['kode_penukaran'] ?>')">
                                        <i class="fa-solid fa-check"></i> Selesai
                                    </button>
                                    <?php else: ?>
                                    <span class="text-muted text-small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function updateStatus(id, status, kode) {
    const labels = { diproses: 'Diproses', selesai: 'Selesai', batal: 'Dibatalkan' };
    const icons  = { diproses: 'question', selesai: 'success', batal: 'warning' };

    Swal.fire({
        title: `Set ke "${labels[status]}"?`,
        text: `Penukaran ${kode} akan diubah statusnya.`,
        icon: icons[status] || 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'batal' ? '#E53935' : '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal'
    }).then(res => {
        if (!res.isConfirmed) return;
        fetch(BASE_URL + '/api/tukar-poin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ penukaran_id: id, status })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, confirmButtonColor: '#4CAF50' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#4CAF50' });
            }
        });
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
