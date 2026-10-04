<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

$status_filter = $_GET['status'] ?? 'all';
$allowed_status = ['all', 'pending', 'approved', 'rejected'];
if (!in_array($status_filter, $allowed_status, true)) {
    $status_filter = 'all';
}

$condition = $status_filter === 'all' ? '' : " WHERE ul.status = ? ";
$params = [];
$types = '';
if ($status_filter !== 'all') {
    $params[] = $status_filter;
    $types = 's';
}

$baseQuery = "
    SELECT ul.*, u.nama as customer_nama,
           admin.nama as reviewer_nama
    FROM ulasan ul
    LEFT JOIN users u ON u.id = ul.customer_id
    LEFT JOIN users admin ON admin.id = ul.reviewed_by
";

if ($status_filter !== 'all') {
    $baseQuery .= " WHERE ul.status = ? ";
}
$baseQuery .= " ORDER BY CASE ul.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 WHEN 'rejected' THEN 2 ELSE 3 END, ul.created_at DESC";

$stmt = $conn->prepare($baseQuery);
if ($status_filter !== 'all') {
    $stmt->bind_param('s', $status_filter);
}
$stmt->execute();
$reviews = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $review_id = intval($_POST['id'] ?? 0);
    $admin_note = trim($_POST['admin_note'] ?? '');

    if ($review_id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE ulasan SET status = ?, admin_note = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssii', $new_status, $admin_note, $_SESSION['user_id'], $review_id);
        $stmt->execute();
        header('Location: kelola-ulasan.php?status=' . $status_filter);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan — Trashily</title>
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
                    <h1>Kelola Ulasan</h1>
                    <p>Moderasi ulasan pelanggan sebelum tampil di landing page</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">
            <div class="card">
                <div class="card-header" style="flex-wrap:wrap;gap:1rem">
                    <h3>Daftar Ulasan</h3>
                    <div class="filter-tabs" style="display:flex;flex-wrap:wrap;gap:.5rem">
                        <a href="kelola-ulasan.php?status=all" class="filter-tab <?= $status_filter === 'all' ? 'active' : '' ?>">Semua</a>
                        <a href="kelola-ulasan.php?status=pending" class="filter-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">Pending</a>
                        <a href="kelola-ulasan.php?status=approved" class="filter-tab <?= $status_filter === 'approved' ? 'active' : '' ?>">Disetujui</a>
                        <a href="kelola-ulasan.php?status=rejected" class="filter-tab <?= $status_filter === 'rejected' ? 'active' : '' ?>">Ditolak</a>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Rating</th>
                                <th>Komentar</th>
                                <th>Status</th>
                                <th>Admin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($reviews->num_rows === 0): ?>
                                <tr><td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fa-solid fa-star"></i></div>
                                        <p>Belum ada ulasan</p>
                                    </div>
                                </td></tr>
                            <?php else: ?>
                                <?php while ($row = $reviews->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="font-semibold"><?= htmlspecialchars($row['nama'] ?: ($row['customer_nama'] ?? 'Anonymous')) ?></div>
                                            <div class="text-small text-muted"><?= !empty($row['customer_id']) ? 'Member' : 'Non-member' ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-amber"><?= str_repeat('★', (int)$row['rating']) ?><?= str_repeat('☆', 5 - (int)$row['rating']) ?></span>
                                        </td>
                                        <td class="text-small text-muted" style="max-width:360px;white-space:normal">
                                            <?= htmlspecialchars($row['komentar']) ?>
                                            <?php if (!empty($row['admin_note'])): ?>
                                                <div class="mt-2 p-2 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600">
                                                    Catatan admin: <?= htmlspecialchars($row['admin_note']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = [
                                                'pending' => 'badge-gray',
                                                'approved' => 'badge-green',
                                                'rejected' => 'badge-red'
                                            ];
                                            $status_label = ['pending' => 'Pending', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];
                                            ?>
                                            <span class="badge <?= $status_class[$row['status']] ?? 'badge-gray' ?>"><?= $status_label[$row['status']] ?? ucfirst($row['status']) ?></span>
                                        </td>
                                        <td class="text-small text-muted"><?= htmlspecialchars($row['reviewer_nama'] ?? '-') ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'pending'): ?>
                                                <div style="display:flex;flex-direction:column;gap:.5rem;min-width:170px">
                                                    <form method="POST" style="display:flex;flex-direction:column;gap:.5rem">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <textarea name="admin_note" rows="2" class="form-control" placeholder="Catatan admin (opsional)"></textarea>
                                                        <button type="submit" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Setujui</button>
                                                    </form>
                                                    <form method="POST" style="display:flex;flex-direction:column;gap:.5rem">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <textarea name="admin_note" rows="2" class="form-control" placeholder="Alasan penolakan"></textarea>
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Tolak</button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-small text-muted">-</span>
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
</body>
</html>
