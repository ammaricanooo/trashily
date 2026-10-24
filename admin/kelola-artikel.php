<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pesan_sukses = trim($_GET['sukses'] ?? '');
$pesan_error  = trim($_GET['error'] ?? '');

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'hapus') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $pesan_sukses = "Artikel berhasil dihapus!";
            } else {
                $pesan_error = "Gagal menghapus artikel: " . $conn->error;
            }
        }
    }
}

// 2. Fetch Metrics & List
$tot_artikel   = $conn->query("SELECT COUNT(*) as c FROM artikel")->fetch_assoc()['c'] ?? 0;
$tot_publish   = $conn->query("SELECT COUNT(*) as c FROM artikel WHERE status='published'")->fetch_assoc()['c'] ?? 0;
$tot_views     = $conn->query("SELECT COALESCE(SUM(views),0) as c FROM artikel")->fetch_assoc()['c'] ?? 0;
$top_cat       = $conn->query("SELECT kategori, COUNT(*) as c FROM artikel GROUP BY kategori ORDER BY c DESC LIMIT 1")->fetch_assoc()['kategori'] ?? 'Edukasi';

// Filter
$search   = trim($_GET['q'] ?? '');
$filter_k = trim($_GET['kategori'] ?? '');
$filter_s = trim($_GET['status'] ?? '');

$where_clauses = ['1=1'];
$params = [];
$types  = '';

if ($search !== '') {
    $where_clauses[] = "(judul LIKE ? OR penulis LIKE ? OR tags LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
if ($filter_k !== '') {
    $where_clauses[] = "kategori = ?";
    $params[] = $filter_k;
    $types .= 's';
}
if ($filter_s !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $filter_s;
    $types .= 's';
}

$where_sql = implode(' AND ', $where_clauses);
$stmtList  = $conn->prepare("SELECT * FROM artikel WHERE $where_sql ORDER BY created_at DESC");
if (!empty($types)) {
    $stmtList->bind_param($types, ...$params);
}
$stmtList->execute();
$artikel_list = $stmtList->get_result();

// Category dropdown list
$cats = ['Panduan Daur Ulang', 'Inovasi & IoT', 'Program GEMBUL', 'Edukasi Lingkungan', 'Kabar Komunitas', 'Gaya Hidup Hijau'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel &amp; SEO — Panel Admin Trashily</title>
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
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div>
                    <h1>Kelola Artikel &amp; SEO Google</h1>
                    <p>Publikasikan konten edukasi lingkungan, panduan pemilahan, dan optimasi SEO ranking Google</p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="../artikel.php" target="_blank" class="poin-badge" style="text-decoration:none;" title="Lihat daftar artikel publik">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Halaman Artikel
                </a>
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">

            <!-- NOTIFIKASI SUKSES / ERROR -->
            <?php if ($pesan_sukses): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '<?= addslashes($pesan_sukses) ?>',
                        timer: 2500,
                        showConfirmButton: false
                    });
                </script>
            <?php endif; ?>
            <?php if ($pesan_error): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '<?= addslashes($pesan_error) ?>'
                    });
                </script>
            <?php endif; ?>

            <!-- STATISTIK KARTU -->
            <div class="grid-stats" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem;">
                <div class="stat-card" style="background:#fff; border-radius:1rem; padding:1.25rem; border:1px solid #e2e8f0; display:flex; align-items:center; gap:1rem;">
                    <div style="width:48px; height:48px; border-radius:12px; background:#f0fdf4; color:#16a34a; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">Total Artikel</div>
                        <div style="font-size:1.5rem; font-weight:800; color:#0f172a;"><?= number_format($tot_artikel) ?></div>
                    </div>
                </div>

                <div class="stat-card" style="background:#fff; border-radius:1rem; padding:1.25rem; border:1px solid #e2e8f0; display:flex; align-items:center; gap:1rem;">
                    <div style="width:48px; height:48px; border-radius:12px; background:#ecfdf5; color:#059669; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">Dipublikasikan</div>
                        <div style="font-size:1.5rem; font-weight:800; color:#059669;"><?= number_format($tot_publish) ?></div>
                    </div>
                </div>

                <div class="stat-card" style="background:#fff; border-radius:1rem; padding:1.25rem; border:1px solid #e2e8f0; display:flex; align-items:center; gap:1rem;">
                    <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff; color:#2563eb; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">Total Pembaca</div>
                        <div style="font-size:1.5rem; font-weight:800; color:#2563eb;"><?= number_format($tot_views) ?></div>
                    </div>
                </div>

                <div class="stat-card" style="background:#fff; border-radius:1rem; padding:1.25rem; border:1px solid #e2e8f0; display:flex; align-items:center; gap:1rem;">
                    <div style="width:48px; height:48px; border-radius:12px; background:#fef3c7; color:#d97706; display:flex; align-items:center; justify-content:center; font-size:1.25rem;">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <div>
                        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">Sitemap XML</div>
                        <div style="font-size:0.9rem; font-weight:700; color:#0f172a; margin-top:0.25rem;">
                            <a href="../sitemap.xml" target="_blank" style="color:#d97706; text-decoration:underline;">Lihat XML</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ACTION BAR & FILTER -->
            <div style="background:#fff; border-radius:1rem; padding:1.25rem; border:1px solid #e2e8f0; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; align-items:center; justify-content:between; gap:1rem;">
                <form method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem; flex:1;">
                    <div style="position:relative; flex:1; min-width:200px;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:0.85rem; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:0.85rem;"></i>
                        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul, penulis, tag..." style="width:100%; padding:0.55rem 0.85rem 0.55rem 2.25rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                    </div>
                    <select name="kategori" style="padding:0.55rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c ?>" <?= $filter_k === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status" style="padding:0.55rem 0.85rem; border-radius:0.75rem; border:1px solid #cbd5e1; font-size:0.85rem;">
                        <option value="">Semua Status</option>
                        <option value="published" <?= $filter_s === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $filter_s === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                    <button type="submit" class="btn" style="background:#0f172a; color:#fff; padding:0.55rem 1.15rem; border-radius:0.75rem; font-size:0.85rem; font-weight:600; cursor:pointer;">
                        Filter
                    </button>
                    <?php if ($search || $filter_k || $filter_s): ?>
                        <a href="kelola-artikel.php" style="color:#64748b; font-size:0.85rem; text-decoration:none; padding:0.55rem;">Reset</a>
                    <?php endif; ?>
                </form>

                <a href="tambah-artikel.php" style="background:#16a34a; color:#fff; padding:0.65rem 1.25rem; border-radius:0.75rem; font-size:0.85rem; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:0.5rem; box-shadow:0 4px 6px -1px rgba(22, 163, 74, 0.2); transition:all 0.2s;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                    <i class="fa-solid fa-plus"></i> Buat Artikel Baru
                </a>
            </div>

            <!-- TABEL ARTIKEL -->
            <div style="background:#fff; border-radius:1rem; border:1px solid #e2e8f0; overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; text-align:left; font-size:0.85rem;">
                        <thead>
                            <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#475569; font-weight:700;">
                                <th style="padding:1rem;">Artikel</th>
                                <th style="padding:1rem;">Kategori</th>
                                <th style="padding:1rem;">Penulis</th>
                                <th style="padding:1rem; text-align:center;">SEO Score</th>
                                <th style="padding:1rem; text-align:center;">Views</th>
                                <th style="padding:1rem; text-align:center;">Status</th>
                                <th style="padding:1rem; text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($artikel_list->num_rows > 0): ?>
                                <?php while ($row = $artikel_list->fetch_assoc()): ?>
                                    <tr style="border-bottom:1px solid #f1f5f9;">
                                        <td style="padding:1rem; max-width:320px;">
                                            <div style="font-weight:700; color:#0f172a; margin-bottom:0.25rem;">
                                                <a href="../baca-artikel.php?slug=<?= urlencode($row['slug']) ?>" target="_blank" style="color:#0f172a; text-decoration:none;">
                                                    <?= htmlspecialchars($row['judul']) ?>
                                                </a>
                                            </div>
                                            <div style="font-size:0.75rem; color:#64748b; display:flex; align-items:center; gap:0.5rem;">
                                                <span><i class="fa-solid fa-link" style="font-size:0.7rem;"></i> /<?= htmlspecialchars($row['slug']) ?></span>
                                                <span>&bull;</span>
                                                <span><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                            </div>
                                        </td>
                                        <td style="padding:1rem;">
                                            <span style="display:inline-block; padding:0.25rem 0.65rem; border-radius:9999px; background:#f0fdf4; color:#166534; font-size:0.75rem; font-weight:700;">
                                                <?= htmlspecialchars($row['kategori']) ?>
                                            </span>
                                        </td>
                                        <td style="padding:1rem;">
                                            <div style="font-weight:600; color:#334155;"><?= htmlspecialchars($row['penulis']) ?></div>
                                            <div style="font-size:0.75rem; color:#94a3b8;"><?= htmlspecialchars($row['penulis_role']) ?></div>
                                        </td>
                                        <td style="padding:1rem; text-align:center;">
                                            <?php 
                                            $hasMeta = !empty($row['meta_title']) && !empty($row['meta_description']);
                                            if ($hasMeta): 
                                            ?>
                                                <span style="display:inline-flex; align-items:center; gap:0.25rem; color:#16a34a; font-weight:700; font-size:0.75rem; background:#dcfce7; padding:0.2rem 0.5rem; border-radius:6px;">
                                                    <i class="fa-solid fa-circle-check"></i> Siap Top 1
                                                </span>
                                            <?php else: ?>
                                                <span style="display:inline-flex; align-items:center; gap:0.25rem; color:#d97706; font-weight:700; font-size:0.75rem; background:#fef3c7; padding:0.2rem 0.5rem; border-radius:6px;">
                                                    <i class="fa-solid fa-triangle-exclamation"></i> Standar
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:1rem; text-align:center; font-weight:600; color:#475569;">
                                            <?= number_format($row['views']) ?>
                                        </td>
                                        <td style="padding:1rem; text-align:center;">
                                            <?php if ($row['status'] === 'published'): ?>
                                                <span style="padding:0.2rem 0.6rem; border-radius:9999px; background:#dcfce7; color:#15803d; font-size:0.75rem; font-weight:700;">
                                                    Published
                                                </span>
                                            <?php else: ?>
                                                <span style="padding:0.2rem 0.6rem; border-radius:9999px; background:#f1f5f9; color:#475569; font-size:0.75rem; font-weight:700;">
                                                    Draft
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:1rem; text-align:right;">
                                            <div style="display:inline-flex; align-items:center; gap:0.4rem;">
                                                <a href="../baca-artikel.php?slug=<?= urlencode($row['slug']) ?>" target="_blank" style="padding:0.35rem 0.65rem; border-radius:0.5rem; background:#f1f5f9; color:#334155; text-decoration:none;" title="Lihat di web">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                                <a href="edit-artikel.php?id=<?= $row['id'] ?>" style="padding:0.35rem 0.65rem; border-radius:0.5rem; background:#eff6ff; color:#2563eb; text-decoration:none;" title="Edit Artikel & SEO">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" onclick="konfirmasiHapus(<?= $row['id'] ?>, '<?= addslashes($row['judul']) ?>')" style="padding:0.35rem 0.65rem; border-radius:0.5rem; background:#fef2f2; color:#dc2626; border:none; cursor:pointer;" title="Hapus Artikel">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="padding:2.5rem; text-align:center; color:#64748b;">
                                        Belum ada artikel yang cocok dengan filter. Klik tombol <strong>Buat Artikel Baru</strong> untuk mulai menulis!
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>



<!-- FORM HAPUS HIDDEN -->
<form id="formHapus" method="POST" style="display:none;">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="id" id="hapus_id">
</form>

<script>
function konfirmasiHapus(id, judul) {
    Swal.fire({
        title: 'Hapus Artikel?',
        html: `Apakah Anda yakin ingin menghapus artikel <strong>"${judul}"</strong>? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('hapus_id').value = id;
            document.getElementById('formHapus').submit();
        }
    });
}
</script>

</body>
</html>
