<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

// Ambil jenis sampah aktif
$jenis_list = $conn->query("SELECT id, nama, kategori, poin_per_kg, harga_per_kg FROM jenis_sampah WHERE is_active = 1 ORDER BY kategori, nama");
$jenis_arr = [];
while ($j = $jenis_list->fetch_assoc()) $jenis_arr[] = $j;

// Filter & Search
$filter = $_GET['filter'] ?? ''; // 'setor_sendiri_pending', 'selesai', ''
$search = trim($_GET['search'] ?? '');
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

$where = "1=1";
$params = [];
$types  = '';

if ($filter === 'setor_sendiri_pending') {
    $where .= " AND t.tipe_transaksi = 'setor_sendiri' AND t.status = 'pending'";
} elseif ($filter === 'selesai') {
    $where .= " AND t.status = 'selesai'";
}

if ($search) {
    $where .= " AND (t.kode_transaksi LIKE ? OR u.nama LIKE ? OR u.no_hp LIKE ? OR t.nama_non_member LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}

$count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM transaksi t LEFT JOIN users u ON t.customer_id = u.id WHERE $where");
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$stmt = $conn->prepare("
    SELECT t.id, t.kode_transaksi, t.customer_id, t.nama_non_member, t.total_poin, t.total_uang, t.total_berat, t.status, t.tipe_transaksi, t.jadwal_setor, t.catatan, t.created_at,
           u.nama as customer_nama, u.no_hp
    FROM transaksi t
    LEFT JOIN users u ON t.customer_id = u.id
    WHERE $where
    ORDER BY 
        CASE WHEN t.tipe_transaksi = 'setor_sendiri' AND t.status = 'pending' THEN 0 ELSE 1 END,
        t.created_at DESC
    LIMIT ? OFFSET ?
");
$params[] = $limit; $params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transaksi_list = $stmt->get_result();

// Count Setor Sendiri pending
$count_ss_pending = $conn->query("SELECT COUNT(*) as c FROM transaksi WHERE tipe_transaksi = 'setor_sendiri' AND status = 'pending'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Setor — Trashily</title>
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
        /* Filter tabs */
        .filter-tab {
            padding: .4rem .9rem;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            border: 1.5px solid var(--color-border);
            background: #fff;
            cursor: pointer;
            text-decoration: none;
            color: var(--color-ink-muted);
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            white-space: nowrap;
        }
        .filter-tab:hover {
            border-color: var(--color-primary-container);
            color: var(--color-primary-dark);
            background: var(--color-primary-light);
        }
        .filter-tab.active {
            background: var(--color-primary-container);
            border-color: var(--color-primary-container);
            color: var(--color-primary-dark);
            font-weight: 700;
        }
        .filter-tab .tab-count {
            background: rgba(0,78,31,.15);
            border-radius: 999px;
            padding: .05rem .4rem;
            font-size: .68rem;
            font-weight: 700;
        }
        .filter-tab.active .tab-count { background: rgba(0,78,31,.2); }

        /* Segmented tipe transaksi */
        .type-segmented {
            display: flex;
            background: var(--color-surface);
            padding: 3px;
            border-radius: 10px;
            border: 1.5px solid var(--color-border);
            margin-bottom: .875rem;
            gap: 3px;
        }
        .type-seg-btn {
            flex: 1; padding: .475rem .75rem;
            border: none; background: transparent;
            font-size: .8rem; font-weight: 600;
            border-radius: 7px; cursor: pointer;
            color: var(--color-ink-muted);
            transition: all .15s;
            display: flex; align-items: center;
            justify-content: center; gap: .4rem;
        }
        .type-seg-btn.active {
            background: #fff;
            color: var(--color-primary-dark);
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            font-weight: 700;
        }

        /* Sampah rows di modal */
        .sampah-row {
            display: grid;
            grid-template-columns: 1fr 110px 130px 34px;
            gap: .5rem; align-items: center;
            padding: .5rem .625rem;
            background: var(--color-surface);
            border-radius: var(--radius-md);
            border: 1px solid var(--color-border);
            margin-bottom: .4rem;
        }
        @media(max-width: 580px) {
            .sampah-row { grid-template-columns: 1fr 1fr; }
        }

        .summary-box {
            display: grid; grid-template-columns: 1.2fr 1fr;
            gap: .6rem;
            background: var(--color-surface);
            border: 1.5px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: .75rem 1rem; margin-top: .75rem;
        }
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
                    <h1>Transaksi Setor Sampah</h1>
                    <p>Catat transaksi &amp; verifikasi setoran nasabah</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>

        <div class="content">
            <!-- Filter & Search -->
            <div class="card mb-4">
                <div class="card-body filter-search-wrap">
                    <div class="filter-tabs">
                        <a href="?filter=&search=<?= urlencode($search) ?>"
                           class="filter-tab <?= !$filter ? 'active' : '' ?>">
                            <i class="fa-solid fa-list text-xs"></i> Semua
                        </a>
                        <a href="?filter=setor_sendiri_pending&search=<?= urlencode($search) ?>"
                           class="filter-tab <?= $filter === 'setor_sendiri_pending' ? 'active' : '' ?>">
                            <i class="fa-solid fa-clock text-xs"></i>
                            Setor Sendiri Pending
                            <?php if ($count_ss_pending > 0): ?>
                            <span class="tab-count"><?= $count_ss_pending ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="?filter=selesai&search=<?= urlencode($search) ?>"
                           class="filter-tab <?= $filter === 'selesai' ? 'active' : '' ?>">
                            <i class="fa-solid fa-circle-check text-xs"></i> Selesai
                        </a>
                    </div>

                    <form method="GET" class="search-form-inline">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <div class="search-bar" style="min-width:220px; flex:1;">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search"
                                   placeholder="Cari kode transaksi atau nama customer..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <?php if ($search): ?>
                        <a href="?filter=<?= htmlspecialchars($filter) ?>"
                           class="btn btn-ghost btn-sm">
                            <i class="fa-solid fa-xmark"></i> Reset
                        </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Tabel Transaksi -->
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Transaksi <?= $search ? '— "' . htmlspecialchars($search) . '"' : '' ?></h3>
                    <button class="btn btn-primary btn-sm" onclick="openModal()">
                        <i class="fa-solid fa-plus"></i> Tambah Transaksi
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Kode &amp; Tipe</th>
                                <th>Penyetor</th>
                                <th>Tanggal</th>
                                <th>Berat</th>
                                <th>Uang Tunai</th>
                                <th>Poin</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transaksi_list->num_rows === 0): ?>
                            <tr><td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-icon">📋</div>
                                    <p>Belum ada transaksi<?= $search ? ' yang cocok' : '' ?></p>
                                </div>
                            </td></tr>
                            <?php else: ?>
                            <?php $no = $offset + 1; while ($row = $transaksi_list->fetch_assoc()):
                                $is_ss_pending = ($row['tipe_transaksi'] === 'setor_sendiri' && $row['status'] === 'pending');
                                $is_member = !empty($row['customer_id']);
                            ?>
                            <tr <?= $is_ss_pending ? 'style="background:#FFF8E1"' : '' ?>>
                                <td class="text-muted text-small"><?= $no++ ?></td>
                                <td>
                                    <span class="badge <?= $is_ss_pending ? 'badge-orange' : 'badge-green' ?>"><?= $row['kode_transaksi'] ?></span>
                                    <div class="text-muted text-small" style="margin-top:.2rem">
                                        <?= $row['tipe_transaksi'] === 'setor_sendiri' ? 'Setor Sendiri' : 'Langsung' ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($is_member): ?>
                                    <div style="font-weight:600;color:var(--text-dark)">
                                        <?= htmlspecialchars($row['customer_nama']) ?>
                                        <span class="badge badge-blue" style="font-size:.65rem;padding:.1rem .4rem;margin-left:.2rem">Member</span>
                                    </div>
                                    <div class="text-muted text-small"><?= htmlspecialchars($row['no_hp'] ?: '-') ?></div>
                                    <?php else: ?>
                                    <div style="font-weight:600;color:var(--text-dark)">
                                        <?= htmlspecialchars($row['nama_non_member'] ?: 'Pelanggan Langsung') ?>
                                        <span class="badge badge-gray" style="font-size:.65rem;padding:.1rem .4rem;margin-left:.2rem">Tamu</span>
                                    </div>
                                    <div class="text-muted text-small">Tanpa Akun</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($row['tipe_transaksi'] === 'setor_sendiri' && $row['jadwal_setor']): ?>
                                    <div style="font-weight:700;color:var(--primary-dark);font-size:.825rem">
                                        <?= date('d M Y H:i', strtotime($row['jadwal_setor'])) ?> WIB
                                    </div>
                                    <?php else: ?>
                                    <div class="text-muted text-small"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($row['total_berat'], 2) ?> kg</td>
                                <td>
                                    <strong style="color:#2E7D32"><?= formatRupiah($row['total_uang']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($is_member): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['total_poin']) ?></strong>
                                    <?php else: ?>
                                    <span class="text-muted text-small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badge = ['selesai' => 'badge-green', 'pending' => 'badge-orange', 'batal' => 'badge-red'];
                                    $status_txt = ['pending' => 'Pending', 'selesai' => 'Selesai', 'batal' => 'Batal'];
                                    ?>
                                    <span class="badge <?= $badge[$row['status']] ?? 'badge-gray' ?>"><?= $status_txt[$row['status']] ?? ucfirst($row['status']) ?></span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.35rem;flex-wrap:wrap">
                                        <?php if ($is_ss_pending): ?>
                                        <button class="btn btn-primary btn-sm" onclick="openVerifikasiSetor(<?= $row['id'] ?>)" title="Periksa &amp; Timbang">
                                            <i class="fa-solid fa-weight-scale"></i> Periksa
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="batalSetor(<?= $row['id'] ?>)" title="Batalkan">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
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
                        <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                        <a href="?page=<?= $i ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH TRANSAKSI LANGSUNG -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:620px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus" style="color:var(--primary);margin-right:.4rem"></i>Tambah Transaksi</h3>
            <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <!-- Selector Tipe -->
            <div class="type-segmented">
                <button type="button" class="type-seg-btn active" id="btnTypeMember" onclick="switchCustomerType('member')">
                    <i class="fa-solid fa-user-check"></i> Member Terdaftar
                </button>
                <button type="button" class="type-seg-btn" id="btnTypeGuest" onclick="switchCustomerType('guest')">
                    <i class="fa-solid fa-person-walking"></i> Pelanggan Langsung (Tamu)
                </button>
            </div>

            <!-- Customer Search / Input -->
            <div id="sectionMember" class="form-group" style="margin-bottom:.75rem">
                <div class="autocomplete-wrap">
                    <input type="text" id="customerSearch" class="form-control" placeholder="Cari nama / no. HP customer..." autocomplete="off">
                    <div class="autocomplete-list" id="autocompleteList"></div>
                </div>
                <div id="customerSelected" style="display:none;margin-top:.4rem;padding:.5rem .75rem;background:var(--primary-light);border-radius:8px;align-items:center;gap:.6rem">
                    <div style="width:30px;height:30px;background:var(--primary);border-radius:6px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.75rem;flex-shrink:0" id="customerAvatar">?</div>
                    <div style="flex:1">
                        <span style="font-weight:600;font-size:.85rem;color:var(--text-dark)" id="customerName">-</span>
                        <span class="text-muted text-small" id="customerInfo" style="margin-left:.4rem">-</span>
                    </div>
                    <span style="font-weight:700;font-size:.825rem;color:var(--primary-dark)" id="customerPoin">0 poin</span>
                </div>
                <input type="hidden" id="customerId" value="">
            </div>

            <div id="sectionGuest" class="form-group" style="display:none;margin-bottom:.75rem">
                <input type="text" id="guestNameInput" class="form-control" placeholder="Nama Pelanggan Tamu (opsional)">
            </div>

            <!-- Item Sampah Rows -->
            <div class="form-group" style="margin-bottom:.5rem">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
                    <label class="form-label" style="margin:0">Item Sampah</label>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="addSampahRow()" style="padding:.25rem .6rem;font-size:.75rem">
                        <i class="fa-solid fa-plus"></i> Tambah Item
                    </button>
                </div>
                <div class="sampah-rows" id="sampahRows"></div>
            </div>

            <!-- Summary Ringkas -->
            <div class="summary-box">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#2E7D32">TOTAL UANG TUNAI</div>
                    <div style="font-size:1.25rem;font-weight:800;color:#1B5E20" id="totalUangPreview">Rp 0</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:.72rem;font-weight:600;color:var(--primary-dark)">TOTAL POIN</div>
                    <div style="font-size:1.25rem;font-weight:800;color:var(--primary-dark)" id="totalPoinPreview">0 poin</div>
                </div>
            </div>

            <div class="form-group" style="margin-top:.75rem;margin-bottom:0">
                <input type="text" class="form-control" id="catatanTrx" placeholder="Catatan (opsional)...">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModal()">Batal</button>
            <button class="btn btn-primary" onclick="simpanTransaksi()">
                <i class="fa-solid fa-check"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- MODAL PERIKSA & VERIFIKASI SETOR SENDIRI -->
<div class="modal-overlay" id="modalVerifikasi">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-weight-scale" style="color:var(--primary);margin-right:.4rem"></i>Verifikasi Setor Sendiri</h3>
            <button class="modal-close" onclick="document.getElementById('modalVerifikasi').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="verifikasiTrxId">
            <div id="verifikasiInfoBox" style="background:var(--primary-light);border-radius:8px;padding:.6rem .875rem;margin-bottom:.75rem">
                <div style="font-weight:700;font-size:.85rem;color:var(--primary-dark)" id="vCustomerNama">-</div>
                <div style="font-size:.75rem;color:var(--primary-dark)" id="vJadwalSetor">-</div>
            </div>

            <div class="form-group" style="margin-bottom:.5rem">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem">
                    <label class="form-label" style="margin:0">Hasil Timbangan Aktual</label>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="addVerifikasiRow()" style="padding:.25rem .6rem;font-size:.75rem">
                        <i class="fa-solid fa-plus"></i> Tambah Item
                    </button>
                </div>
                <div id="verifikasiRows"></div>
            </div>

            <div class="summary-box">
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#2E7D32">TOTAL UANG</div>
                    <div style="font-size:1.15rem;font-weight:800;color:#1B5E20" id="verifikasiUangPreview">Rp 0</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:.72rem;font-weight:600;color:var(--primary-dark)">TOTAL POIN</div>
                    <div style="font-size:1.15rem;font-weight:800;color:var(--primary-dark)" id="verifikasiPoinPreview">0 poin</div>
                </div>
            </div>

            <div class="form-group" style="margin-top:.75rem;margin-bottom:0">
                <input type="text" class="form-control" id="verifikasiCatatanAdmin" placeholder="Catatan pemeriksa (opsional)...">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modalVerifikasi').classList.remove('open')">Batal</button>
            <button class="btn btn-primary" onclick="simpanVerifikasiSetor()">
                <i class="fa-solid fa-check"></i> Verifikasi Selesai
            </button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL TRANSAKSI -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:540px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:.4rem"></i>Detail Transaksi</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><div class="empty-icon">⏳</div><p>Memuat...</p></div>
        </div>
    </div>
</div>

<script>
const jenisList = <?= json_encode($jenis_arr) ?>;
let currentCustomerType = 'member'; // 'member' | 'guest'

function switchCustomerType(type) {
    currentCustomerType = type;
    if (type === 'member') {
        document.getElementById('btnTypeMember').classList.add('active');
        document.getElementById('btnTypeGuest').classList.remove('active');
        document.getElementById('sectionMember').style.display = 'block';
        document.getElementById('sectionGuest').style.display = 'none';
    } else {
        document.getElementById('btnTypeMember').classList.remove('active');
        document.getElementById('btnTypeGuest').classList.add('active');
        document.getElementById('sectionMember').style.display = 'none';
        document.getElementById('sectionGuest').style.display = 'block';
    }
    updateAllCalculations();
}

function openModal() {
    document.getElementById('modalTambah').classList.add('open');
    document.getElementById('sampahRows').innerHTML = '';
    document.getElementById('customerId').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('guestNameInput').value = '';
    document.getElementById('customerSelected').style.display = 'none';
    document.getElementById('catatanTrx').value = '';
    document.getElementById('totalPoinPreview').textContent = '0 poin';
    document.getElementById('totalUangPreview').textContent = 'Rp 0';
    switchCustomerType('member');
    addSampahRow();
}

function closeModal() {
    document.getElementById('modalTambah').classList.remove('open');
}

// ====== AUTOCOMPLETE CUSTOMER ======
let searchTimeout;
document.getElementById('customerSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('autocompleteList').classList.remove('open');
        return;
    }
    searchTimeout = setTimeout(() => {
        fetch(`../api/search-customer.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(res => {
                const list = document.getElementById('autocompleteList');
                list.innerHTML = '';
                if (res.success && res.data.length > 0) {
                    res.data.forEach(c => {
                        const item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.innerHTML = `
                            <div class="item-avatar">${c.nama.charAt(0).toUpperCase()}</div>
                            <div class="item-info">
                                <div class="item-name">${c.nama}</div>
                                <div class="item-sub">${c.no_hp} &bull; ${c.poin} poin</div>
                            </div>`;
                        item.addEventListener('click', () => selectCustomer(c));
                        list.appendChild(item);
                    });
                    list.classList.add('open');
                } else {
                    list.innerHTML = '<div class="autocomplete-item" style="color:var(--text-muted)"><div class="item-info"><div class="item-name">Tidak ditemukan</div></div></div>';
                    list.classList.add('open');
                }
            });
    }, 250);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.autocomplete-wrap')) {
        document.getElementById('autocompleteList').classList.remove('open');
    }
});

function selectCustomer(c) {
    document.getElementById('customerId').value = c.id;
    document.getElementById('customerSearch').value = c.nama;
    document.getElementById('autocompleteList').classList.remove('open');

    const sel = document.getElementById('customerSelected');
    document.getElementById('customerAvatar').textContent = c.nama.charAt(0).toUpperCase();
    document.getElementById('customerName').textContent = c.nama;
    document.getElementById('customerInfo').textContent = c.no_hp;
    document.getElementById('customerPoin').textContent = c.poin + ' poin';
    sel.style.display = 'flex';
}

// ====== SAMPAH ROWS ======
let rowCount = 0;

function formatRupiahJs(number) {
    return 'Rp ' + Math.round(number).toLocaleString('id-ID');
}

function addSampahRow() {
    rowCount++;
    const id = `row_${rowCount}`;
    const row = document.createElement('div');
    row.className = 'sampah-row';
    row.id = id;

    let optionsHtml = '<option value="">-- Pilih Sampah --</option>';
    const grouped = {};
    jenisList.forEach(j => {
        if (!grouped[j.kategori]) grouped[j.kategori] = [];
        grouped[j.kategori].push(j);
    });

    for (const kat in grouped) {
        optionsHtml += `<optgroup label="${kat.replace('_', ' ').toUpperCase()}">`;
        grouped[kat].forEach(j => {
            optionsHtml += `<option value="${j.id}" data-poin="${j.poin_per_kg}" data-harga="${j.harga_per_kg}">${j.nama} (${formatRupiahJs(j.harga_per_kg)}/kg &bull; ${j.poin_per_kg}p)</option>`;
        });
        optionsHtml += '</optgroup>';
    }

    row.innerHTML = `
        <select class="form-control jenis-select" onchange="hitungRow('${id}')" style="font-size:.825rem">
            ${optionsHtml}
        </select>
        <input type="number" class="form-control berat-input" placeholder="Berat (kg)" min="0.05" step="0.05" oninput="hitungRow('${id}')" style="font-size:.825rem">
        <div style="font-size:.78rem;font-weight:700;text-align:right" id="calc_${id}">
            <div style="color:#2E7D32" id="uang_${id}">Rp 0</div>
            <div style="color:var(--primary-dark)" id="poin_${id}">0 poin</div>
        </div>
        <button class="btn-remove-row" onclick="removeRow('${id}')" type="button"><i class="fa-solid fa-trash"></i></button>
    `;

    document.getElementById('sampahRows').appendChild(row);
}

function removeRow(id) {
    const rows = document.getElementById('sampahRows');
    if (rows.children.length <= 1) {
        Swal.fire({ icon: 'warning', title: 'Minimal 1 item', text: 'Harus ada minimal satu item sampah.', confirmButtonColor: '#4CAF50' });
        return;
    }
    document.getElementById(id)?.remove();
    updateAllCalculations();
}

function hitungRow(id) {
    const row = document.getElementById(id);
    const select = row.querySelector('.jenis-select');
    const beratInput = row.querySelector('.berat-input');
    const uangEl = document.getElementById(`uang_${id}`);
    const poinEl = document.getElementById(`poin_${id}`);

    const opt = select.options[select.selectedIndex];
    const poinPerKg = parseFloat(opt?.dataset?.poin || 0);
    const hargaPerKg = parseFloat(opt?.dataset?.harga || 0);
    const berat = parseFloat(beratInput.value || 0);

    const subtotalUang = Math.round(berat * hargaPerKg);
    const subtotalPoin = currentCustomerType === 'member' ? Math.round(berat * poinPerKg) : 0;

    uangEl.textContent = subtotalUang > 0 ? formatRupiahJs(subtotalUang) : 'Rp 0';
    poinEl.textContent = subtotalPoin > 0 ? `+${subtotalPoin} poin` : (currentCustomerType === 'member' ? '0 poin' : '-');
    poinEl.style.color = currentCustomerType === 'member' ? 'var(--primary-dark)' : 'var(--text-muted)';

    updateAllCalculations();
}

function updateAllCalculations() {
    let totalUang = 0;
    let totalPoin = 0;
    document.querySelectorAll('.sampah-row').forEach(row => {
        const select = row.querySelector('.jenis-select');
        const berat = parseFloat(row.querySelector('.berat-input').value || 0);
        const opt = select.options[select.selectedIndex];
        const poinPerKg = parseFloat(opt?.dataset?.poin || 0);
        const hargaPerKg = parseFloat(opt?.dataset?.harga || 0);

        totalUang += Math.round(berat * hargaPerKg);
        if (currentCustomerType === 'member') {
            totalPoin += Math.round(berat * poinPerKg);
        }
    });
    document.getElementById('totalUangPreview').textContent = formatRupiahJs(totalUang);
    document.getElementById('totalPoinPreview').textContent = (currentCustomerType === 'member' ? totalPoin.toLocaleString('id-ID') + ' poin' : '0 poin (Tamu)');
}

function simpanTransaksi() {
    const isMember = (currentCustomerType === 'member');
    let customerId = document.getElementById('customerId').value;
    let guestName  = document.getElementById('guestNameInput').value.trim();

    if (isMember && !customerId) {
        Swal.fire({ icon: 'warning', title: 'Pilih Customer', text: 'Pilih customer terdaftar atau beralih ke Pelanggan Langsung.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const items = [];
    let valid = true;

    document.querySelectorAll('.sampah-row').forEach(row => {
        const jenisId = row.querySelector('.jenis-select').value;
        const berat   = parseFloat(row.querySelector('.berat-input').value || 0);
        if (!jenisId || berat <= 0) { valid = false; return; }
        items.push({ jenis_id: parseInt(jenisId), berat });
    });

    if (!valid || items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Data Tidak Lengkap', text: 'Pastikan jenis dan berat sampah terisi.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const catatan = document.getElementById('catatanTrx').value;

    fetch('../api/tambah-transaksi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            is_member: isMember,
            customer_id: isMember ? parseInt(customerId) : null,
            nama_non_member: !isMember ? (guestName || 'Pelanggan Langsung') : null,
            items,
            catatan
        })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeModal();
            let msg = `<b>${res.nama_customer}</b><br>Uang Tunai: <b style="color:#2E7D32">${res.total_uang_fmt}</b>`;
            if (res.is_member) msg += `<br>Poin: <b style="color:#4CAF50">+${res.total_poin} poin</b>`;
            msg += `<br>Berat: ${res.total_berat} kg<br><br>Kode: <b>${res.kode}</b>`;

            Swal.fire({
                icon: 'success',
                title: 'Transaksi Tersimpan!',
                html: msg,
                confirmButtonColor: '#4CAF50'
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
        }
    })
    .catch(err => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.', confirmButtonColor: '#4CAF50' });
    });
}

// ====== VERIFIKASI SETOR SENDIRI ======
let vRowCount = 0;

function openVerifikasiSetor(id) {
    document.getElementById('verifikasiTrxId').value = id;
    document.getElementById('verifikasiRows').innerHTML = '';
    document.getElementById('verifikasiCatatanAdmin').value = '';
    document.getElementById('verifikasiPoinPreview').textContent = '0 poin';
    document.getElementById('verifikasiUangPreview').textContent = 'Rp 0';
    vRowCount = 0;
    document.getElementById('modalVerifikasi').classList.add('open');

    fetch(`../api/detail-transaksi.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const t = res.transaksi;
                document.getElementById('vCustomerNama').textContent = `Customer: ${t.customer} (${t.customer_hp || '-'})`;
                document.getElementById('vJadwalSetor').innerHTML = `Rencana Datang: <b>${t.jadwal_setor}</b>`;

                if (res.items && res.items.length > 0) {
                    res.items.forEach(it => {
                        addVerifikasiRow(it.jenis_sampah_id, it.berat);
                    });
                } else {
                    addVerifikasiRow();
                }
            } else {
                addVerifikasiRow();
            }
        });
}

function buildVerifikasiJenisOptions(selectedId) {
    const grouped = {};
    jenisList.forEach(j => {
        if (!grouped[j.kategori]) grouped[j.kategori] = [];
        grouped[j.kategori].push(j);
    });
    let html = '<option value="">-- Pilih Sampah --</option>';
    for (const kat in grouped) {
        html += `<optgroup label="${kat.replace('_',' ').toUpperCase()}">`;
        grouped[kat].forEach(j => {
            html += `<option value="${j.id}" data-poin="${j.poin_per_kg}" data-harga="${j.harga_per_kg}" ${j.id == selectedId ? 'selected' : ''}>${j.nama} (${formatRupiahJs(j.harga_per_kg)}/kg &bull; ${j.poin_per_kg}p)</option>`;
        });
        html += '</optgroup>';
    }
    return html;
}

function addVerifikasiRow(jenisId, berat) {
    vRowCount++;
    const id = `vrow_${vRowCount}`;
    const div = document.createElement('div');
    div.className = 'sampah-row';
    div.id = id;
    div.innerHTML = `
        <select class="form-control jenis-vsel" onchange="hitungVerifikasiRow('${id}')" style="font-size:.825rem">
            ${buildVerifikasiJenisOptions(jenisId || '')}
        </select>
        <input type="number" class="form-control berat-vsel" placeholder="Berat (kg)" min="0.01" step="0.01" value="${berat || ''}" oninput="hitungVerifikasiRow('${id}')" style="font-size:.825rem">
        <div style="font-size:.78rem;font-weight:700;text-align:right">
            <div style="color:#2E7D32" id="vuang_${id}">Rp 0</div>
            <div style="color:var(--primary-dark)" id="vpoin_${id}">0 poin</div>
        </div>
        <button type="button" class="btn-remove-row" onclick="removeVerifikasiRow('${id}')"><i class="fa-solid fa-trash"></i></button>
    `;
    document.getElementById('verifikasiRows').appendChild(div);
    if (jenisId) hitungVerifikasiRow(id);
}

function removeVerifikasiRow(id) {
    const rows = document.getElementById('verifikasiRows');
    if (rows.children.length <= 1) return;
    document.getElementById(id)?.remove();
    updateVerifikasiTotals();
}

function hitungVerifikasiRow(id) {
    const row = document.getElementById(id);
    const sel = row.querySelector('.jenis-vsel');
    const berat = parseFloat(row.querySelector('.berat-vsel').value || 0);
    const opt = sel.options[sel.selectedIndex];
    const poinPerKg = parseFloat(opt?.dataset?.poin || 0);
    const hargaPerKg = parseFloat(opt?.dataset?.harga || 0);

    const subtotalUang = Math.round(berat * hargaPerKg);
    const subtotalPoin = Math.round(berat * poinPerKg);

    document.getElementById(`vuang_${id}`).textContent = formatRupiahJs(subtotalUang);
    document.getElementById(`vpoin_${id}`).textContent = subtotalPoin > 0 ? `+${subtotalPoin} poin` : '0 poin';
    updateVerifikasiTotals();
}

function updateVerifikasiTotals() {
    let totalUang = 0;
    let totalPoin = 0;
    document.querySelectorAll('#verifikasiRows .sampah-row').forEach(row => {
        const sel = row.querySelector('.jenis-vsel');
        const berat = parseFloat(row.querySelector('.berat-vsel').value || 0);
        const opt = sel.options[sel.selectedIndex];
        const poinPerKg = parseFloat(opt?.dataset?.poin || 0);
        const hargaPerKg = parseFloat(opt?.dataset?.harga || 0);

        totalUang += Math.round(berat * hargaPerKg);
        totalPoin += Math.round(berat * poinPerKg);
    });
    document.getElementById('verifikasiUangPreview').textContent = formatRupiahJs(totalUang);
    document.getElementById('verifikasiPoinPreview').textContent = totalPoin.toLocaleString('id-ID') + ' poin';
}

function simpanVerifikasiSetor() {
    const id = document.getElementById('verifikasiTrxId').value;
    const catatan_admin = document.getElementById('verifikasiCatatanAdmin').value.trim();

    const items = [];
    let valid = true;

    document.querySelectorAll('#verifikasiRows .sampah-row').forEach(row => {
        const jenisId = row.querySelector('.jenis-vsel').value;
        const berat   = parseFloat(row.querySelector('.berat-vsel').value || 0);
        if (!jenisId || berat <= 0) { valid = false; return; }
        items.push({ jenis_id: parseInt(jenisId), berat });
    });

    if (!valid || items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Data tidak lengkap', text: 'Isi item & berat hasil timbangan.', confirmButtonColor: '#4CAF50' });
        return;
    }

    fetch('../api/setor-sendiri.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verifikasi', transaksi_id: parseInt(id), items, catatan_admin })
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            document.getElementById('modalVerifikasi').classList.remove('open');
            Swal.fire({
                icon: 'success',
                title: 'Berhasil Diverifikasi!',
                html: `Uang Tunai: <b style="color:#2E7D32">${res.total_uang_fmt}</b><br>Poin: <b style="color:#4CAF50">+${res.total_poin} poin</b>`,
                confirmButtonColor: '#4CAF50'
            }).then(() => location.reload());
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
        }
    });
}

function batalSetor(id) {
    Swal.fire({
        title: 'Batalkan Pengajuan Setor?',
        text: 'Pengajuan ini akan dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Batalkan'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch('../api/setor-sendiri.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'batal', transaksi_id: parseInt(id) })
        })
        .then(r => r.json())
        .then(res => {
            Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Dibatalkan' : 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' })
            .then(() => { if (res.success) location.reload(); });
        });
    });
}

// ====== DETAIL TRANSAKSI ======
function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div class="empty-state" style="padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';

    fetch(`../api/detail-transaksi.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                document.getElementById('detailContent').innerHTML = '<div class="empty-state"><p>Gagal memuat data</p></div>';
                return;
            }
            const t = res.transaksi;
            let itemsHtml = res.items.map(i => `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.85rem">${i.nama}</div>
                        <div class="text-muted text-small">${i.berat_fmt} kg &bull; ${i.harga_per_kg_fmt}/kg &bull; ${i.poin_per_kg}p</div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-weight:700;color:#2E7D32;font-size:.85rem">${i.subtotal_uang_fmt}</div>
                        <div style="font-size:.72rem;color:var(--primary-dark)">${t.is_member ? '+' + i.subtotal_poin + ' poin' : '-'}</div>
                    </div>
                </div>`).join('');

            document.getElementById('detailContent').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;margin-bottom:.875rem">
                    <div style="background:var(--bg-light);border-radius:8px;padding:.75rem">
                        <div class="text-muted text-small">Kode Transaksi</div>
                        <div style="font-weight:700;color:var(--text-dark)">${t.kode}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:8px;padding:.75rem">
                        <div class="text-muted text-small">Penyetor</div>
                        <div style="font-weight:700;color:var(--text-dark)">
                            ${t.customer}
                            ${t.is_member ? '<span class="badge badge-blue" style="font-size:.625rem;margin-left:.2rem">Member</span>' : '<span class="badge badge-gray" style="font-size:.625rem;margin-left:.2rem">Tamu</span>'}
                        </div>
                    </div>
                    <div style="background:#E8F5E9;border-radius:8px;padding:.75rem">
                        <div class="text-muted text-small" style="color:#2E7D32">Total Uang Tunai</div>
                        <div style="font-weight:800;color:#1B5E20;font-size:1.1rem">${t.total_uang_fmt}</div>
                    </div>
                    <div style="background:var(--primary-light);border-radius:8px;padding:.75rem">
                        <div class="text-muted text-small">Total Poin</div>
                        <div style="font-weight:800;color:var(--primary-dark);font-size:1.1rem">${t.is_member ? t.total_poin + ' poin' : '-'}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:8px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Total Berat</div>
                        <div style="font-weight:700;color:var(--text-dark)">${t.total_berat} kg</div>
                    </div>
                </div>
                <div style="font-weight:600;margin-bottom:.35rem;font-size:.825rem">Detail Item:</div>
                ${itemsHtml}
                <div style="margin-top:.75rem" class="text-muted text-small">Dicatat: ${t.tanggal} oleh ${t.admin}</div>`;
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
