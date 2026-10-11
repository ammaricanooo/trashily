<?php
require_once __DIR__ . '/../includes/auth_check.php';
requireAdmin();
require_once __DIR__ . '/../config/database.php';

$pesan_sukses = '';
$pesan_error  = '';

// 1. Handle POST Actions (Tambah, Edit, Hapus, Update Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // TAMBAH JADWAL
    if ($action === 'tambah') {
        $judul          = trim($_POST['judul_kegiatan'] ?? '');
        $kategori       = trim($_POST['kategori'] ?? 'Sekolah');
        $tanggal        = trim($_POST['tanggal'] ?? '');
        $waktu_mulai    = !empty($_POST['waktu_mulai']) ? $_POST['waktu_mulai'] : null;
        $waktu_selesai  = !empty($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : null;
        $lokasi         = trim($_POST['lokasi'] ?? '');
        $target_peserta = trim($_POST['target_peserta'] ?? '');
        $kuota          = intval($_POST['kuota_peserta'] ?? 0);
        $deskripsi      = trim($_POST['deskripsi'] ?? '');
        $status         = trim($_POST['status'] ?? 'rencana');
        $link           = trim($_POST['link_pendaftaran'] ?? '');

        if (!empty($judul) && !empty($tanggal) && !empty($lokasi) && !empty($deskripsi)) {
            $stmt = $conn->prepare("
                INSERT INTO gembul_jadwal (
                    judul_kegiatan, kategori, tanggal, waktu_mulai, waktu_selesai,
                    lokasi, target_peserta, kuota_peserta, deskripsi, status, link_pendaftaran
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('sssssssisss', $judul, $kategori, $tanggal, $waktu_mulai, $waktu_selesai, $lokasi, $target_peserta, $kuota, $deskripsi, $status, $link);
            if ($stmt->execute()) {
                $pesan_sukses = "Jadwal kegiatan GEMBUL berhasil ditambahkan dan otomatis tampil di halaman publik!";
            } else {
                $pesan_error = "Gagal menyimpan jadwal: " . $conn->error;
            }
        } else {
            $pesan_error = "Mohon lengkapi judul, tanggal, lokasi, dan deskripsi kegiatan.";
        }
    }

    // EDIT JADWAL
    elseif ($action === 'edit') {
        $id             = intval($_POST['id'] ?? 0);
        $judul          = trim($_POST['judul_kegiatan'] ?? '');
        $kategori       = trim($_POST['kategori'] ?? 'Sekolah');
        $tanggal        = trim($_POST['tanggal'] ?? '');
        $waktu_mulai    = !empty($_POST['waktu_mulai']) ? $_POST['waktu_mulai'] : null;
        $waktu_selesai  = !empty($_POST['waktu_selesai']) ? $_POST['waktu_selesai'] : null;
        $lokasi         = trim($_POST['lokasi'] ?? '');
        $target_peserta = trim($_POST['target_peserta'] ?? '');
        $kuota          = intval($_POST['kuota_peserta'] ?? 0);
        $deskripsi      = trim($_POST['deskripsi'] ?? '');
        $status         = trim($_POST['status'] ?? 'rencana');
        $link           = trim($_POST['link_pendaftaran'] ?? '');

        if ($id > 0 && !empty($judul) && !empty($tanggal) && !empty($lokasi) && !empty($deskripsi)) {
            $stmt = $conn->prepare("
                UPDATE gembul_jadwal SET 
                    judul_kegiatan = ?, kategori = ?, tanggal = ?, waktu_mulai = ?, waktu_selesai = ?,
                    lokasi = ?, target_peserta = ?, kuota_peserta = ?, deskripsi = ?, status = ?, link_pendaftaran = ?
                WHERE id = ?
            ");
            $stmt->bind_param('sssssssisssi', $judul, $kategori, $tanggal, $waktu_mulai, $waktu_selesai, $lokasi, $target_peserta, $kuota, $deskripsi, $status, $link, $id);
            if ($stmt->execute()) {
                $pesan_sukses = "Data jadwal kegiatan berhasil diperbarui!";
            } else {
                $pesan_error = "Gagal memperbarui jadwal: " . $conn->error;
            }
        } else {
            $pesan_error = "Data jadwal tidak valid atau data wajib belum diisi.";
        }
    }

    // HAPUS JADWAL
    elseif ($action === 'hapus') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM gembul_jadwal WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $pesan_sukses = "Jadwal kegiatan berhasil dihapus!";
            } else {
                $pesan_error = "Gagal menghapus jadwal: " . $conn->error;
            }
        }
    }

    // UBAH STATUS CEPAT
    elseif ($action === 'update_status') {
        $id         = intval($_POST['id'] ?? 0);
        $new_status = trim($_POST['status'] ?? 'rencana');
        if ($id > 0 && in_array($new_status, ['rencana', 'berjalan', 'selesai', 'dibatalkan'])) {
            $stmt = $conn->prepare("UPDATE gembul_jadwal SET status = ? WHERE id = ?");
            $stmt->bind_param('si', $new_status, $id);
            if ($stmt->execute()) {
                $pesan_sukses = "Status kegiatan berhasil diperbarui ke " . ucfirst($new_status) . "!";
            } else {
                $pesan_error = "Gagal memperbarui status: " . $conn->error;
            }
        }
    }
}

// 2. Query KPI Ringkasan
$stat_total    = $conn->query("SELECT COUNT(*) as c FROM gembul_jadwal")->fetch_assoc()['c'] ?? 0;
$stat_rencana  = $conn->query("SELECT COUNT(*) as c FROM gembul_jadwal WHERE status = 'rencana'")->fetch_assoc()['c'] ?? 0;
$stat_berjalan = $conn->query("SELECT COUNT(*) as c FROM gembul_jadwal WHERE status = 'berjalan'")->fetch_assoc()['c'] ?? 0;
$stat_selesai  = $conn->query("SELECT COUNT(*) as c FROM gembul_jadwal WHERE status = 'selesai'")->fetch_assoc()['c'] ?? 0;

// 3. Filter & Search
$filter_kategori = trim($_GET['kategori'] ?? '');
$filter_status   = trim($_GET['status'] ?? '');
$search          = trim($_GET['search'] ?? '');

$where_clauses = ["1=1"];
$params        = [];
$types         = '';

if ($filter_kategori !== '') {
    $where_clauses[] = "kategori = ?";
    $params[] = $filter_kategori;
    $types .= 's';
}
if ($filter_status !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $filter_status;
    $types .= 's';
}
if ($search !== '') {
    $where_clauses[] = "(judul_kegiatan LIKE ? OR lokasi LIKE ? OR target_peserta LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

$where_sql = implode(' AND ', $where_clauses);
$stmtList  = $conn->prepare("SELECT * FROM gembul_jadwal WHERE $where_sql ORDER BY tanggal ASC, waktu_mulai ASC");
if (!empty($types)) {
    $stmtList->bind_param($types, ...$params);
}
$stmtList->execute();
$jadwal_list = $stmtList->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Kegiatan GEMBUL — Panel Admin Trashily</title>
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
                    <h1>Jadwal Kegiatan GEMBUL</h1>
                    <p>Kelola jadwal games sekolah, aksi Ciliwung, dan workshop yang dapat diakses publik</p>
                </div>
            </div>
            <div class="topbar-right">
                <a href="../gembul.php#jadwal" target="_blank" class="poin-badge" style="text-decoration:none;" title="Lihat bagaimana jadwal ditampilkan di halaman publik">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Lihat Halaman Publik
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
                        title: 'Oops...',
                        text: '<?= addslashes($pesan_error) ?>'
                    });
                </script>
            <?php endif; ?>

            <!-- STAT CARDS (METRIK KEGIATAN) -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stat_total ?></div>
                        <div class="stat-label">Total Agenda Terdaftar</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stat_rencana ?></div>
                        <div class="stat-label">Rencana Mendatang</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="fa-solid fa-play"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stat_berjalan ?></div>
                        <div class="stat-label">Sedang Berjalan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $stat_selesai ?></div>
                        <div class="stat-label">Telah Terlaksana</div>
                    </div>
                </div>
            </div>

            <!-- SEARCH & FILTER BAR -->
            <div class="card mb-4">
                <div class="card-body" style="padding: 1rem 1.25rem;">
                    <form method="GET" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem;">
                        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:.75rem;">
                            <!-- Search -->
                            <div style="position:relative; min-width:240px;">
                                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama agenda, lokasi..." 
                                       class="form-control" style="padding-left:2.2rem; font-size:.85rem;">
                                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:.85rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.8rem;"></i>
                            </div>

                            <!-- Filter Kategori -->
                            <select name="kategori" class="form-control" style="width:auto; font-size:.85rem;" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <option value="Sekolah" <?= ($filter_kategori === 'Sekolah') ? 'selected' : '' ?>>Sekolah (Games &amp; Edukasi)</option>
                                <option value="Aksi Sungai" <?= ($filter_kategori === 'Aksi Sungai') ? 'selected' : '' ?>>Aksi Sungai (Alkesa)</option>
                                <option value="Workshop" <?= ($filter_kategori === 'Workshop') ? 'selected' : '' ?>>Workshop (Eco-Kit)</option>
                                <option value="Komunitas" <?= ($filter_kategori === 'Komunitas') ? 'selected' : '' ?>>Komunitas / Keluarga</option>
                                <option value="Riset & Prototipe" <?= ($filter_kategori === 'Riset & Prototipe') ? 'selected' : '' ?>>Riset &amp; Prototipe</option>
                            </select>

                            <!-- Filter Status -->
                            <select name="status" class="form-control" style="width:auto; font-size:.85rem;" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="rencana" <?= ($filter_status === 'rencana') ? 'selected' : '' ?>>Rencana</option>
                                <option value="berjalan" <?= ($filter_status === 'berjalan') ? 'selected' : '' ?>>Sedang Berjalan</option>
                                <option value="selesai" <?= ($filter_status === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                                <option value="dibatalkan" <?= ($filter_status === 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>

                            <button type="submit" class="btn btn-ghost btn-sm">
                                <i class="fa-solid fa-filter"></i> Terapkan
                            </button>
                            <?php if ($filter_kategori || $filter_status || $search): ?>
                                <a href="kelola-gembul.php" class="btn btn-ghost btn-sm" title="Reset filter">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Tombol Tambah Kegiatan -->
                        <div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openTambahModal()">
                                <i class="fa-solid fa-plus"></i> Tambah Kegiatan Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABEL JADWAL KEGIATAN -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Daftar Agenda Kegiatan GEMBUL (<?= $jadwal_list->num_rows ?> Kegiatan)</h3>
                        <p style="font-size:.75rem; color:var(--text-muted); margin-top:2px;">
                            Data di bawah ini disinkronkan langsung ke kalender jadwal di halaman publik <strong>gembul.php</strong>.
                        </p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:130px;">Tanggal &amp; Waktu</th>
                                <th>Agenda &amp; Deskripsi</th>
                                <th style="width:110px;">Kategori</th>
                                <th>Lokasi Penyelenggaraan</th>
                                <th style="width:130px;">Target Peserta</th>
                                <th style="width:90px;">Status</th>
                                <th style="width:140px; text-align:right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jadwal_list->num_rows === 0): ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon"><i class="fa-solid fa-calendar-xmark"></i></div>
                                            <p>Belum ada jadwal kegiatan yang sesuai dengan kriteria filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = $jadwal_list->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong style="color:var(--text-dark); display:block; font-size:.82rem;">
                                            <?= date('d M Y', strtotime($row['tanggal'])) ?>
                                        </strong>
                                        <span style="font-size:.72rem; color:var(--text-muted); font-family:monospace;">
                                            <?php if ($row['waktu_mulai']): ?>
                                                <?= substr($row['waktu_mulai'], 0, 5) ?> <?= $row['waktu_selesai'] ? '- ' . substr($row['waktu_selesai'], 0, 5) : 'WIB' ?>
                                            <?php else: ?>
                                                Fleksibel
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-weight:700; color:var(--text-dark); font-size:.88rem; margin-bottom:2px;">
                                            <?= htmlspecialchars($row['judul_kegiatan']) ?>
                                        </div>
                                        <div style="font-size:.75rem; color:var(--text-muted); line-height:1.4; max-width:320px;">
                                            <?= htmlspecialchars(mb_strimwidth($row['deskripsi'], 0, 110, '...')) ?>
                                        </div>
                                        <?php if ($row['link_pendaftaran']): ?>
                                            <a href="<?= htmlspecialchars($row['link_pendaftaran']) ?>" target="_blank" style="font-size:.7rem; color:var(--color-primary); font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:3px; margin-top:3px;">
                                                <i class="fa-solid fa-link text-[9px]"></i> Link Info / Pendaftaran
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['kategori'] === 'Sekolah'): ?>
                                            <span class="badge badge-green"><i class="fa-solid fa-graduation-cap"></i> Sekolah</span>
                                        <?php elseif ($row['kategori'] === 'Aksi Sungai'): ?>
                                            <span class="badge badge-blue"><i class="fa-solid fa-water"></i> Aksi Sungai</span>
                                        <?php elseif ($row['kategori'] === 'Workshop'): ?>
                                            <span class="badge badge-amber"><i class="fa-solid fa-screwdriver-wrench"></i> Workshop</span>
                                        <?php else: ?>
                                            <span class="badge badge-gray"><i class="fa-solid fa-people-roof"></i> <?= htmlspecialchars($row['kategori']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:.8rem; color:var(--text-body);">
                                        <i class="fa-solid fa-location-dot" style="font-size:.7rem; color:var(--text-muted); margin-right:3px;"></i>
                                        <?= htmlspecialchars($row['lokasi']) ?>
                                    </td>
                                    <td style="font-size:.78rem; color:var(--text-body);">
                                        <strong><?= htmlspecialchars($row['target_peserta'] ?: '-') ?></strong>
                                        <?php if ($row['kuota_peserta'] > 0): ?>
                                            <div style="font-size:.7rem; color:var(--text-muted);"><?= $row['kuota_peserta'] ?> kuota</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'rencana'): ?>
                                            <span class="badge badge-blue">Rencana</span>
                                        <?php elseif ($row['status'] === 'berjalan'): ?>
                                            <span class="badge badge-amber"><i class="fa-solid fa-spinner fa-spin"></i> Berjalan</span>
                                        <?php elseif ($row['status'] === 'selesai'): ?>
                                            <span class="badge badge-green"><i class="fa-solid fa-check"></i> Selesai</span>
                                        <?php else: ?>
                                            <span class="badge badge-red">Dibatalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:inline-flex; gap:.35rem; align-items:center;">
                                            <!-- Tombol Edit -->
                                            <button type="button" class="btn btn-ghost btn-sm" onclick='openEditModal(<?= json_encode($row) ?>)' title="Edit Kegiatan">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Menu Ubah Status Cepat -->
                                            <button type="button" class="btn btn-ghost btn-sm" onclick='ubahStatus(<?= $row['id'] ?>, "<?= $row['status'] ?>")' title="Ubah Status">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmHapus(<?= $row['id'] ?>, '<?= addslashes($row['judul_kegiatan']) ?>')" title="Hapus Kegiatan">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
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

<!-- ==================== MODAL TAMBAH JADWAL ==================== -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-calendar-plus" style="color:var(--color-primary)"></i> Tambah Jadwal Kegiatan GEMBUL</h3>
            <button class="modal-close" onclick="closeModal('modalTambah')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="tambah">
            <div class="modal-body" style="padding:1.25rem 1.5rem; max-height:75vh; overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label">Nama / Judul Kegiatan *</label>
                    <input type="text" name="judul_kegiatan" required class="form-control" placeholder="Contoh: Sesi Edukasi Ular Tangga GEMBUL di SMKN 4 Bogor">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="kategori" required class="form-control">
                            <option value="Sekolah">Sekolah (Games &amp; Edukasi)</option>
                            <option value="Aksi Sungai">Aksi Sungai (Alkesa)</option>
                            <option value="Workshop">Workshop (Creative Eco-Kit)</option>
                            <option value="Komunitas">Komunitas &amp; Keluarga</option>
                            <option value="Riset & Prototipe">Riset &amp; Prototipe</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Kegiatan *</label>
                        <input type="date" name="tanggal" required class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="waktu_mulai" class="form-control" value="08:30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="waktu_selesai" class="form-control" value="11:30">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status Awal</label>
                        <select name="status" class="form-control">
                            <option value="rencana" selected>Rencana</option>
                            <option value="berjalan">Berjalan</option>
                            <option value="selesai">Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Penyelenggaraan *</label>
                    <input type="text" name="lokasi" required class="form-control" placeholder="Contoh: SMKN 4 Bogor / Jalur Sungai Ciliwung Sempur">
                </div>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Target Sasaran Peserta</label>
                        <input type="text" name="target_peserta" class="form-control" placeholder="Contoh: Siswa Kelas X &amp; Guru">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Kuota</label>
                        <input type="number" name="kuota_peserta" class="form-control" placeholder="50" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Rencana Kegiatan *</label>
                    <textarea name="deskripsi" required class="form-control" rows="3" placeholder="Jelaskan alur aktivitas, materi toolkit yang dipakai (Ular Tangga / Magnet Kulkas / Eco-Pass)..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Link Info / Pendaftaran Relawan (Opsional)</label>
                    <input type="url" name="link_pendaftaran" class="form-control" placeholder="https://wa.me/... atau link form pendaftaran">
                </div>
            </div>
            <div class="modal-footer" style="padding:1rem 1.5rem; display:flex; justify-content:flex-end; gap:.5rem;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== MODAL EDIT JADWAL ==================== -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:600px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color:var(--color-primary)"></i> Edit Jadwal Kegiatan GEMBUL</h3>
            <button class="modal-close" onclick="closeModal('modalEdit')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body" style="padding:1.25rem 1.5rem; max-height:75vh; overflow-y:auto;">
                <div class="form-group">
                    <label class="form-label">Nama / Judul Kegiatan *</label>
                    <input type="text" name="judul_kegiatan" id="edit_judul" required class="form-control">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Kategori *</label>
                        <select name="kategori" id="edit_kategori" required class="form-control">
                            <option value="Sekolah">Sekolah (Games &amp; Edukasi)</option>
                            <option value="Aksi Sungai">Aksi Sungai (Alkesa)</option>
                            <option value="Workshop">Workshop (Creative Eco-Kit)</option>
                            <option value="Komunitas">Komunitas &amp; Keluarga</option>
                            <option value="Riset & Prototipe">Riset &amp; Prototipe</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Kegiatan *</label>
                        <input type="date" name="tanggal" id="edit_tanggal" required class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" name="waktu_mulai" id="edit_waktu_mulai" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" name="waktu_selesai" id="edit_waktu_selesai" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="rencana">Rencana</option>
                            <option value="berjalan">Berjalan</option>
                            <option value="selesai">Selesai</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Penyelenggaraan *</label>
                    <input type="text" name="lokasi" id="edit_lokasi" required class="form-control">
                </div>

                <div style="display:grid; grid-template-columns:2fr 1fr; gap:.85rem;">
                    <div class="form-group">
                        <label class="form-label">Target Sasaran Peserta</label>
                        <input type="text" name="target_peserta" id="edit_target" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Estimasi Kuota</label>
                        <input type="number" name="kuota_peserta" id="edit_kuota" class="form-control" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Rencana Kegiatan *</label>
                    <textarea name="deskripsi" id="edit_deskripsi" required class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Link Info / Pendaftaran Relawan (Opsional)</label>
                    <input type="url" name="link_pendaftaran" id="edit_link" class="form-control">
                </div>
            </div>
            <div class="modal-footer" style="padding:1rem 1.5rem; display:flex; justify-content:flex-end; gap:.5rem;">
                <button type="button" class="btn btn-ghost" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- FORM TERSEMBUNYI UNTUK HAPUS & STATUS -->
<form id="formHapus" method="POST" style="display:none;">
    <input type="hidden" name="action" value="hapus">
    <input type="hidden" name="id" id="hapus_id">
</form>

<form id="formStatus" method="POST" style="display:none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="id" id="status_id">
    <input type="hidden" name="status" id="status_val">
</form>

<script>
function openTambahModal() {
    document.getElementById('modalTambah').classList.add('open');
}

function openEditModal(row) {
    document.getElementById('edit_id').value = row.id;
    document.getElementById('edit_judul').value = row.judul_kegiatan;
    document.getElementById('edit_kategori').value = row.kategori;
    document.getElementById('edit_tanggal').value = row.tanggal;
    document.getElementById('edit_waktu_mulai').value = row.waktu_mulai ? row.waktu_mulai.substring(0, 5) : '';
    document.getElementById('edit_waktu_selesai').value = row.waktu_selesai ? row.waktu_selesai.substring(0, 5) : '';
    document.getElementById('edit_status').value = row.status;
    document.getElementById('edit_lokasi').value = row.lokasi;
    document.getElementById('edit_target').value = row.target_peserta || '';
    document.getElementById('edit_kuota').value = row.kuota_peserta || 0;
    document.getElementById('edit_deskripsi').value = row.deskripsi;
    document.getElementById('edit_link').value = row.link_pendaftaran || '';

    document.getElementById('modalEdit').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function confirmHapus(id, judul) {
    Swal.fire({
        title: 'Hapus Agenda Ini?',
        text: 'Agenda "' + judul + '" akan dihapus secara permanen dari kalender publik.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ba1a1a',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('hapus_id').value = id;
            document.getElementById('formHapus').submit();
        }
    });
}

function ubahStatus(id, currentStatus) {
    Swal.fire({
        title: 'Ubah Status Kegiatan',
        text: 'Pilih status baru untuk agenda ini:',
        icon: 'question',
        input: 'select',
        inputOptions: {
            'rencana': 'Rencana',
            'berjalan': 'Sedang Berjalan',
            'selesai': 'Selesai',
            'dibatalkan': 'Dibatalkan'
        },
        inputValue: currentStatus,
        showCancelButton: true,
        confirmButtonColor: '#006e2f',
        confirmButtonText: 'Simpan Status',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            document.getElementById('status_id').value = id;
            document.getElementById('status_val').value = result.value;
            document.getElementById('formStatus').submit();
        }
    });
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>
</body>
</html>
