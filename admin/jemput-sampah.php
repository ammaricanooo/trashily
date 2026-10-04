<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

// Jenis sampah aktif
$jenis_list = $conn->query("SELECT id, nama, kategori, poin_per_kg FROM jenis_sampah WHERE is_active = 1 ORDER BY kategori, nama");
$jenis_arr  = [];
while ($j = $jenis_list->fetch_assoc()) $jenis_arr[] = $j;

// Filter & pagination
$status_filter = $_GET['status'] ?? '';
$search        = trim($_GET['search'] ?? '');
$page          = max(1, intval($_GET['page'] ?? 1));
$limit         = 10;
$offset        = ($page - 1) * $limit;

$where  = '1=1';
$params = [];
$types  = '';

if ($status_filter && in_array($status_filter, ['menunggu','dikonfirmasi','dijemput','selesai','batal'])) {
    $where   .= ' AND js.status = ?';
    $params[] = $status_filter;
    $types   .= 's';
}
if ($search) {
    $where   .= ' AND (js.kode_jemput LIKE ? OR u.nama LIKE ? OR u.no_hp LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'sss';
}

$count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM jemput_sampah js JOIN users u ON js.customer_id = u.id WHERE $where");
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows  = $count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list_stmt = $conn->prepare("
    SELECT js.id, js.kode_jemput, js.alamat_jemput, js.jarak_km, js.biaya_ongkir, js.jadwal_jemput, js.status,
           js.catatan_customer, js.created_at,
           u.nama as customer_nama, u.no_hp,
           t.kode_transaksi, t.total_poin
    FROM jemput_sampah js
    JOIN users u ON js.customer_id = u.id
    LEFT JOIN transaksi t ON js.transaksi_id = t.id
    WHERE $where
    ORDER BY
        CASE js.status WHEN 'menunggu' THEN 0 WHEN 'dikonfirmasi' THEN 1 WHEN 'dijemput' THEN 2 ELSE 3 END,
        js.jadwal_jemput ASC
    LIMIT ? OFFSET ?
");
$params[] = $limit; $params[] = $offset;
$types .= 'ii';
$list_stmt->bind_param($types, ...$params);
$list_stmt->execute();
$list = $list_stmt->get_result();

// Ringkasan status
$summary = [];
foreach (['menunggu','dikonfirmasi','dijemput','selesai','batal'] as $s) {
    $r = $conn->query("SELECT COUNT(*) as c FROM jemput_sampah WHERE status = '$s'")->fetch_assoc();
    $summary[$s] = $r['c'];
}

$status_badge = [
    'menunggu'     => ['badge-orange', 'Menunggu'],
    'dikonfirmasi' => ['badge-blue',   'Dikonfirmasi'],
    'dijemput'     => ['badge-blue',   'Sedang Dijemput'],
    'selesai'      => ['badge-green',  'Selesai'],
    'batal'        => ['badge-red',    'Dibatalkan'],
];

$kategori_icon = [
    'organik_kering' => 'fa-seedling',
    'plastik'        => 'fa-bottle-water',
    'kertas'         => 'fa-file',
    'logam'          => 'fa-screwdriver-wrench',
    'kaca'           => 'fa-wine-bottle',
    'elektronik'     => 'fa-microchip',
    'lainnya'        => 'fa-box',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jemput Sampah — Admin</title>
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
    <style>
        .filter-tabs { display: flex; gap: .5rem; flex-wrap: wrap; }
        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .9rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            border: 1.5px solid var(--color-border);
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            color: var(--color-ink-muted);
            transition: background .15s, border-color .15s, color .15s, box-shadow .15s;
            white-space: nowrap;
        }
        .filter-tab:hover {
            background: var(--color-primary-light);
            border-color: var(--color-primary-container);
            color: var(--color-primary-dark);
        }
        .filter-tab.active {
            background: var(--color-primary-container);
            border-color: var(--color-primary-container);
            color: var(--color-primary-dark);
            box-shadow: 0 4px 12px rgba(34,197,94,.18);
        }
        .filter-tab .tab-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.3rem;
            background: rgba(0,110,47,.08);
            color: var(--color-primary-dark);
            border-radius: 999px;
            padding: .08rem .42rem;
            font-size: .68rem;
            font-weight: 700;
        }
        .filter-tab.active .tab-count {
            background: rgba(0,78,31,.18);
            color: var(--color-primary-dark);
        }

        .stats-5 { grid-template-columns: repeat(5, 1fr) !important; }
        @media (max-width: 900px) { .stats-5 { grid-template-columns: repeat(3, 1fr) !important; } }
        @media (max-width: 600px) { .stats-5 { grid-template-columns: repeat(2, 1fr) !important; } }

        .filter-search-wrap {
            display: flex;
            gap: .875rem;
            flex-wrap: wrap;
            align-items: center;
            padding: .875rem 1.25rem;
        }
        .filter-search-wrap .filter-tabs {
            flex: 1;
            min-width: 0;
            margin-bottom: 0;
        }
        .filter-search-wrap .search-form-inline {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex-shrink: 0;
            margin-left: auto;
        }
        @media (max-width: 768px) {
            .filter-search-wrap { flex-direction: column; align-items: stretch; }
            .filter-search-wrap .filter-tabs { overflow-x: auto; flex-wrap: nowrap; scrollbar-width: none; padding-bottom: .25rem; }
            .filter-search-wrap .filter-tabs::-webkit-scrollbar { display: none; }
            .filter-search-wrap .search-form-inline { width: 100%; margin-left: 0; }
            .filter-search-wrap .search-form-inline .search-bar { flex: 1; }
        }

        .selesaikan-form { display: flex; flex-direction: column; gap: .75rem; }
        .selesaikan-row {
            display: grid;
            grid-template-columns: 1fr 110px 70px 32px;
            gap: .6rem;
            align-items: center;
            padding: .75rem .875rem;
            background: var(--bg-light);
            border-radius: 10px;
            border: 1.5px solid var(--border);
        }
        @media (max-width: 540px) {
            .selesaikan-row { grid-template-columns: 1fr 90px 60px 28px; gap: .4rem; padding: .65rem .75rem; }
        }
        .selesaikan-row .row-name { font-size: .85rem; font-weight: 600; }
        .selesaikan-row .row-hint { font-size: .72rem; color: var(--text-muted); }
        .selesaikan-row .row-poin { font-size: .75rem; font-weight: 600; color: var(--primary-dark); text-align: center; }
        .btn-del-row { width: 30px; height: 30px; background: var(--danger-light); border: none; border-radius: 8px; cursor: pointer; color: var(--danger); display: flex; align-items: center; justify-content: center; transition: background .15s; flex-shrink: 0; }
        .btn-del-row:hover { background: var(--danger); color: #fff; }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_admin.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Jemput Sampah</h1>
                    <p>Kelola permintaan penjemputan dari customer</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">
            <!-- Stat Cards -->
            <div class="stats-grid stats-5" style="margin-bottom:1.5rem">
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $summary['menunggu'] ?></div>
                        <div class="stat-label">Menunggu</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $summary['dikonfirmasi'] ?></div>
                        <div class="stat-label">Dikonfirmasi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fa-solid fa-truck"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $summary['dijemput'] ?></div>
                        <div class="stat-label">Dijemput</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fa-solid fa-check-double"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $summary['selesai'] ?></div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fa-solid fa-ban"></i></div>
                    <div class="stat-info">
                        <div class="stat-value"><?= $summary['batal'] ?></div>
                        <div class="stat-label">Dibatalkan</div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-2">
                <div class="filter-search-wrap">
                    <div class="filter-tabs">
                        <a href="?status=&search=<?= urlencode($search) ?>" class="filter-tab <?= !$status_filter ? 'active' : '' ?>">
                            Semua <span class="tab-count"><?= array_sum($summary) ?></span>
                        </a>
                        <?php
                        $tab_labels = ['menunggu'=>'Menunggu','dikonfirmasi'=>'Dikonfirmasi','dijemput'=>'Dijemput','selesai'=>'Selesai','batal'=>'Batal'];
                        foreach ($tab_labels as $sv => $sl):
                        ?>
                        <a href="?status=<?= $sv ?>&search=<?= urlencode($search) ?>" class="filter-tab <?= $status_filter === $sv ? 'active' : '' ?>">
                            <?= $sl ?> <span class="tab-count"><?= $summary[$sv] ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <form method="GET" class="search-form-inline">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                        <div class="search-bar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" placeholder="Cari kode, nama, no. HP..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                        <?php if ($search): ?><a href="?status=<?= $status_filter ?>" class="btn btn-ghost btn-sm">Reset</a><?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Permintaan</h3>
                    <span class="text-muted text-small"><?= number_format($total_rows) ?> permintaan</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Jadwal</th>
                                <th>Alamat</th>
                                <th>Jarak & Ongkir</th>
                                <th>Status</th>
                                <th>Poin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($list->num_rows === 0): ?>
                            <tr><td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-truck-ramp-box" style="font-size:3rem;opacity:.3"></i></div>
                                    <p>Tidak ada permintaan jemput</p>
                                </div>
                            </td></tr>
                            <?php else: ?>
                            <?php $no = $offset + 1; while ($row = $list->fetch_assoc()):
                                [$bcls, $btxt] = $status_badge[$row['status']] ?? ['badge-gray', ucfirst($row['status'])];
                                $jadwal_dt = strtotime($row['jadwal_jemput']);
                                $is_today  = date('Y-m-d', $jadwal_dt) === date('Y-m-d');
                                $is_past   = $jadwal_dt < time() && $row['status'] === 'menunggu';
                            ?>
                            <tr <?= $is_past ? 'style="background:#FFF8E1"' : '' ?>>
                                <td class="text-muted text-small"><?= $no++ ?></td>
                                <td>
                                    <span class="badge badge-blue"><?= $row['kode_jemput'] ?></span>
                                    <?php if ($is_today && $row['status'] !== 'selesai' && $row['status'] !== 'batal'): ?>
                                    <div style="margin-top:.2rem"><span class="badge badge-orange" style="font-size:.65rem">Hari Ini</span></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight:600"><?= htmlspecialchars($row['customer_nama']) ?></div>
                                    <div class="text-muted text-small"><?= $row['no_hp'] ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600;font-size:.875rem"><?= date('d M Y', $jadwal_dt) ?></div>
                                    <div class="text-muted text-small"><?= date('H:i', $jadwal_dt) ?> WIB</div>
                                </td>
                                <td style="max-width:160px">
                                    <div style="font-size:.825rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($row['alamat_jemput']) ?>">
                                        <?= htmlspecialchars($row['alamat_jemput']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size:.85rem;font-weight:600"><?= number_format($row['jarak_km'] ?? 0, 1, ',', '.') ?> km</div>
                                    <div class="text-muted text-small">Rp <?= number_format($row['biaya_ongkir'] ?? 0, 0, ',', '.') ?></div>
                                </td>
                                <td><span class="badge <?= $bcls ?>"><?= $btxt ?></span></td>
                                <td>
                                    <?php if ($row['status'] === 'selesai' && $row['total_poin']): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['total_poin']) ?> poin</strong>
                                    <?php else: ?>
                                    <span class="text-muted text-small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                                        <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <?php if ($row['status'] === 'menunggu'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'dikonfirmasi')" title="Konfirmasi">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'batal')" title="Batalkan">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <?php elseif ($row['status'] === 'dikonfirmasi'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="updateStatus(<?= $row['id'] ?>, 'dijemput')" title="Tandai Dijemput">
                                            <i class="fa-solid fa-truck"></i>
                                        </button>
                                        <?php elseif ($row['status'] === 'dijemput'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="openSelesaikan(<?= $row['id'] ?>)" title="Selesaikan & Input Poin">
                                            <i class="fa-solid fa-weight-scale"></i> Timbang
                                        </button>
                                        <?php endif; ?>
                                    </div>
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
                        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a><?php endif; ?>
                        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <a href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:520px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-truck" style="color:var(--primary);margin-right:.5rem"></i>Detail Permintaan</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>
        </div>
    </div>
</div>

<!-- MODAL SELESAIKAN (INPUT TIMBANGAN) -->
<div class="modal-overlay" id="modalSelesaikan">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-weight-scale" style="color:var(--primary);margin-right:.5rem"></i>Selesaikan & Input Poin</h3>
            <button class="modal-close" onclick="document.getElementById('modalSelesaikan').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="selesaikanId">
            <div class="form-group">
                <label class="form-label">Item Sampah (Berat Aktual)</label>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem">
                    <span class="text-muted text-small">Input berat nyata hasil timbangan</span>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="addSelesaikanRow()">
                        <i class="fa-solid fa-plus"></i> Tambah Item
                    </button>
                </div>
                <div id="selesaikanRows" class="selesaikan-form"></div>
            </div>

            <div class="poin-preview">
                <div class="label"><i class="fa-solid fa-star" style="margin-right:.4rem;color:var(--accent)"></i>Total Poin</div>
                <div class="value" id="selesaikanPoinPreview">0 poin</div>
            </div>

            <div class="form-group mt-2">
                <label class="form-label">Catatan Admin <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(opsional)</span></label>
                <textarea class="form-control" id="selesaikanCatatan" placeholder="Catatan hasil penjemputan..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modalSelesaikan').classList.remove('open')">Batal</button>
            <button class="btn btn-primary" onclick="simpanSelesaikan()">
                <i class="fa-solid fa-check"></i> Selesaikan & Beri Poin
            </button>
        </div>
    </div>
</div>

<script>
const jenisList = <?= json_encode($jenis_arr) ?>;

const statusInfo = {
    menunggu:     { cls: 'badge-orange', txt: 'Menunggu Konfirmasi' },
    dikonfirmasi: { cls: 'badge-blue',   txt: 'Dikonfirmasi' },
    dijemput:     { cls: 'badge-blue',   txt: 'Sedang Dijemput' },
    selesai:      { cls: 'badge-green',  txt: 'Selesai' },
    batal:        { cls: 'badge-red',    txt: 'Dibatalkan' },
};

// ====== UPDATE STATUS ======
function updateStatus(id, status) {
    const labels = { dikonfirmasi: 'Konfirmasi', dijemput: 'Tandai Dijemput', batal: 'Batalkan' };
    const colors = { dikonfirmasi: '#4CAF50', dijemput: '#1E88E5', batal: '#E53935' };

    Swal.fire({
        title: labels[status] + '?',
        input: status === 'batal' ? 'textarea' : undefined,
        inputPlaceholder: 'Catatan (opsional)',
        showCancelButton: true,
        confirmButtonColor: colors[status] || '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, ' + labels[status],
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/jemput-sampah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update_status', jemput_id: id, status, catatan: r.value || '' })
        })
        .then(r => r.json())
        .then(res => {
            Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Berhasil' : 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' })
            .then(() => { if (res.success) location.reload(); });
        });
    });
}

// ====== SELESAIKAN ======
let selesaikanRowCount = 0;

function openSelesaikan(id) {
    document.getElementById('selesaikanId').value = id;
    document.getElementById('selesaikanRows').innerHTML = '';
    document.getElementById('selesaikanCatatan').value = '';
    document.getElementById('selesaikanPoinPreview').textContent = '0 poin';
    selesaikanRowCount = 0;
    document.getElementById('modalSelesaikan').classList.add('open');

    // Pre-fill dengan item dari detail permintaan
    fetch(BASE_URL + `/api/detail-jemput.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.items.length > 0) {
                res.items.forEach(it => {
                    const jenis = jenisList.find(j => j.nama === it.nama);
                    if (jenis) addSelesaikanRow(jenis.id, it.est_berat);
                });
            } else {
                addSelesaikanRow();
            }
        })
        .catch(() => addSelesaikanRow());
}

function buildJenisOptions(selectedId) {
    const grouped = {};
    jenisList.forEach(j => {
        if (!grouped[j.kategori]) grouped[j.kategori] = [];
        grouped[j.kategori].push(j);
    });
    let html = '<option value="">-- Pilih Sampah --</option>';
    const icons = { organik_kering:'fa-seedling', plastik:'fa-bottle-water', kertas:'fa-file', logam:'fa-screwdriver-wrench', kaca:'fa-wine-bottle', elektronik:'fa-microchip', lainnya:'fa-box' };
    for (const kat in grouped) {
        html += `<optgroup label="${kat.replace('_',' ').toUpperCase()}">`;
        grouped[kat].forEach(j => {
            html += `<option value="${j.id}" data-poin="${j.poin_per_kg}" ${j.id == selectedId ? 'selected' : ''}>${j.nama} (${j.poin_per_kg} poin/kg)</option>`;
        });
        html += '</optgroup>';
    }
    return html;
}

function addSelesaikanRow(jenisId, berat) {
    selesaikanRowCount++;
    const id = `srow_${selesaikanRowCount}`;
    const div = document.createElement('div');
    div.className = 'selesaikan-row';
    div.id = id;
    div.innerHTML = `
        <select class="form-control jenis-sel" onchange="hitungSelesaikan('${id}')">
            ${buildJenisOptions(jenisId || '')}
        </select>
        <input type="number" class="form-control berat-sel" placeholder="Berat (kg)" min="0.01" step="0.01" value="${berat || ''}" oninput="hitungSelesaikan('${id}')">
        <div class="row-poin" id="poin_${id}">0 poin</div>
        <button class="btn-del-row" onclick="removeSelesaikanRow('${id}')"><i class="fa-solid fa-trash" style="font-size:.75rem"></i></button>
    `;
    document.getElementById('selesaikanRows').appendChild(div);
    if (jenisId) hitungSelesaikan(id);
}

function removeSelesaikanRow(id) {
    const rows = document.getElementById('selesaikanRows');
    if (rows.children.length <= 1) return;
    document.getElementById(id)?.remove();
    updateSelesaikanTotal();
}

function hitungSelesaikan(id) {
    const row = document.getElementById(id);
    const sel = row.querySelector('.jenis-sel');
    const berat = parseFloat(row.querySelector('.berat-sel').value || 0);
    const poinPerKg = parseFloat(sel.options[sel.selectedIndex]?.dataset?.poin || 0);
    const subtotal = Math.round(berat * poinPerKg);
    const el = document.getElementById(`poin_${id}`);
    el.textContent = subtotal > 0 ? `+${subtotal}` : '0';
    updateSelesaikanTotal();
}

function updateSelesaikanTotal() {
    let total = 0;
    document.querySelectorAll('.selesaikan-row').forEach(row => {
        const sel = row.querySelector('.jenis-sel');
        const berat = parseFloat(row.querySelector('.berat-sel').value || 0);
        const poin = parseFloat(sel.options[sel.selectedIndex]?.dataset?.poin || 0);
        total += Math.round(berat * poin);
    });
    document.getElementById('selesaikanPoinPreview').textContent = total.toLocaleString('id-ID') + ' poin';
}

function simpanSelesaikan() {
    const jemputId = parseInt(document.getElementById('selesaikanId').value);
    const catatan  = document.getElementById('selesaikanCatatan').value;
    const items = [];
    let valid = true;

    document.querySelectorAll('.selesaikan-row').forEach(row => {
        const jenisId = row.querySelector('.jenis-sel').value;
        const berat   = parseFloat(row.querySelector('.berat-sel').value || 0);
        if (!jenisId || berat <= 0) { valid = false; return; }
        items.push({ jenis_id: parseInt(jenisId), berat });
    });

    if (!valid || items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Data tidak lengkap', text: 'Pastikan semua item terisi.', confirmButtonColor: '#4CAF50' });
        return;
    }

    Swal.fire({
        title: 'Selesaikan & Beri Poin?',
        text: 'Poin akan langsung dikreditkan ke customer.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Selesaikan',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/jemput-sampah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'selesaikan', jemput_id: jemputId, items, catatan })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('modalSelesaikan').classList.remove('open');
                Swal.fire({
                    icon: 'success',
                    title: 'Selesai!',
                    html: `Poin <b style="color:#4CAF50">${res.total_poin}</b> berhasil dikreditkan.<br>Kode Transaksi: <b>${res.kode}</b>`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
    });
}

// ====== DETAIL ======
function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div class="empty-state" style="padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';

    fetch(BASE_URL + `/api/detail-jemput.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                document.getElementById('detailContent').innerHTML = '<div class="empty-state"><p>Gagal memuat</p></div>';
                return;
            }
            const d  = res.data;
            const si = statusInfo[d.status] || { cls: 'badge-gray', txt: d.status };

            let itemsHtml = (res.items || []).map(it => `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.55rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.85rem">${it.nama}</div>
                        <div class="text-muted text-small">Est. ${it.est_berat} kg &bull; ${it.poin_per_kg} poin/kg</div>
                    </div>
                </div>`).join('');

            document.getElementById('detailContent').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:1rem">
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Kode</div>
                        <div style="font-weight:700">${d.kode}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Status</div>
                        <span class="badge ${si.cls}">${si.txt}</span>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Customer</div>
                        <div style="font-weight:600">${d.customer}</div>
                        <div class="text-muted text-small">${d.no_hp}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Jadwal</div>
                        <div style="font-weight:600">${d.jadwal}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Jarak / Biaya Penjemputan</div>
                        <div style="font-weight:600">${d.jarak_km} km (Rp ${d.biaya_ongkir})</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Alamat Penjemputan</div>
                        <div style="font-size:.875rem">${d.alamat}</div>
                    </div>
                    ${d.catatan_customer ? `<div style="background:var(--accent-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Catatan Customer</div>
                        <div style="font-size:.875rem">${d.catatan_customer}</div>
                    </div>` : ''}
                    ${d.catatan_admin ? `<div style="background:var(--primary-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Catatan Admin</div>
                        <div style="font-size:.875rem;color:var(--primary-dark)">${d.catatan_admin}</div>
                    </div>` : ''}
                    ${d.status === 'selesai' && d.total_poin ? `<div style="background:var(--primary-light);border-radius:10px;padding:.875rem;grid-column:1/-1;text-align:center">
                        <div class="text-muted text-small">Poin Dikreditkan</div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--primary-dark)">+${d.total_poin} poin</div>
                    </div>` : ''}
                </div>
                <div style="font-weight:600;font-size:.875rem;margin-bottom:.5rem">Jenis Sampah Diajukan:</div>
                ${itemsHtml || '<p class="text-muted text-small">Tidak ada item</p>'}
            `;
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
