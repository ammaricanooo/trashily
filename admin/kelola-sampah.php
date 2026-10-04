<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $nama       = trim($_POST['nama'] ?? '');
        $kategori   = $_POST['kategori'] ?? '';
        $poin_kg    = floatval($_POST['poin_per_kg'] ?? 0);
        $harga_kg   = floatval($_POST['harga_per_kg'] ?? 0);
        $deskripsi  = trim($_POST['deskripsi'] ?? '');
        $is_active  = intval($_POST['is_active'] ?? 1);

        $kategori_allowed = ['organik_kering','plastik','kertas','logam','kaca','elektronik','lainnya'];
        if (!$nama || !in_array($kategori, $kategori_allowed) || ($poin_kg <= 0 && $harga_kg <= 0)) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid. Minimal poin atau harga harus lebih dari 0.']); exit;
        }

        if ($action === 'tambah') {
            $stmt = $conn->prepare("INSERT INTO jenis_sampah (nama, kategori, poin_per_kg, harga_per_kg, deskripsi, is_active) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('siddsi', $nama, $kategori, $poin_kg, $harga_kg, $deskripsi, $is_active);
        } else {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $conn->prepare("UPDATE jenis_sampah SET nama=?, kategori=?, poin_per_kg=?, harga_per_kg=?, deskripsi=?, is_active=? WHERE id=?");
            $stmt->bind_param('siddisi', $nama, $kategori, $poin_kg, $harga_kg, $deskripsi, $is_active, $id);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => $action === 'tambah' ? 'Jenis sampah berhasil ditambahkan.' : 'Data berhasil diperbarui.']);
        exit;
    }

    if ($action === 'hapus') {
        $id = intval($_POST['id'] ?? 0);
        $check = $conn->query("SELECT COUNT(*) as c FROM detail_transaksi WHERE jenis_sampah_id = $id")->fetch_assoc()['c'];
        if ($check > 0) {
            echo json_encode(['success' => false, 'message' => 'Tidak bisa dihapus, sudah digunakan dalam transaksi.']); exit;
        }
        $conn->query("DELETE FROM jenis_sampah WHERE id = $id");
        echo json_encode(['success' => true, 'message' => 'Data berhasil dihapus.']);
        exit;
    }
}

$sampah_list = $conn->query("SELECT * FROM jenis_sampah ORDER BY kategori, nama");
$kategori_options = [
    'organik_kering' => '🍂 Organik Kering',
    'plastik'  => '🧴 Plastik',
    'kertas'   => '📄 Kertas',
    'logam'    => '🔧 Logam',
    'kaca'     => '🍶 Kaca',
    'elektronik' => '📱 Elektronik',
    'lainnya'  => '📦 Lainnya',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jenis Sampah — Trashily</title>
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
                    <h1>Jenis Sampah</h1>
                    <p>Kelola kategori, tarif poin, dan tarif harga uang sampah</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3>Jenis Sampah</h3>
                    <button class="btn btn-primary btn-sm" onclick="openForm()">
                        <i class="fa-solid fa-plus"></i> Tambah Jenis
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Poin / kg</th>
                                <th>Harga / kg (Rp)</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($sampah_list->num_rows === 0): ?>
                            <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">♻️</div><p>Belum ada data</p></div></td></tr>
                            <?php else: ?>
                            <?php while ($row = $sampah_list->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td>
                                    <?php
                                    $emoji = ['organik_kering'=>'🍂','plastik'=>'🧴','kertas'=>'📄','logam'=>'🔧','kaca'=>'🍶','elektronik'=>'📱','lainnya'=>'📦'];
                                    echo ($emoji[$row['kategori']] ?? '📦') . ' ' . ucwords(str_replace('_',' ',$row['kategori']));
                                    ?>
                                </td>
                                <td><strong style="color:var(--primary-dark)"><?= number_format($row['poin_per_kg'], 0) ?></strong> poin</td>
                                <td><strong style="color:#2E7D32"><?= formatRupiah($row['harga_per_kg']) ?></strong></td>
                                <td class="text-muted text-small"><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td>
                                    <span class="badge <?= $row['is_active'] ? 'badge-green' : 'badge-gray' ?>">
                                        <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.4rem">
                                        <button class="btn btn-info btn-sm" onclick='editForm(<?= json_encode($row) ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="hapusSampah(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>')">
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

<!-- MODAL FORM -->
<div class="modal-overlay" id="modalForm">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3 id="formTitle">Tambah Jenis Sampah</h3>
            <button class="modal-close" onclick="closeForm()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <input type="hidden" id="formAction" value="tambah">
            <div class="form-group">
                <label class="form-label">Nama Sampah</label>
                <input type="text" class="form-control" id="fNama" placeholder="cth. Botol Plastik PET">
            </div>
            <div class="form-group">
                <label class="form-label">Kategori</label>
                <select class="form-control" id="fKategori">
                    <?php foreach ($kategori_options as $val => $label): ?>
                    <option value="<?= $val ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Poin per kg</label>
                    <input type="number" class="form-control" id="fPoin" placeholder="cth. 20" min="0" step="0.5">
                </div>
                <div class="form-group">
                    <label class="form-label">Harga per kg (Rp)</label>
                    <input type="number" class="form-control" id="fHarga" placeholder="cth. 2000" min="0" step="100">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" id="fDeskripsi" placeholder="Deskripsi singkat..."></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select class="form-control" id="fStatus">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeForm()">Batal</button>
            <button class="btn btn-primary" onclick="submitForm()"><i class="fa-solid fa-check"></i> Simpan</button>
        </div>
    </div>
</div>

<script>
function openForm() {
    document.getElementById('formTitle').textContent = 'Tambah Jenis Sampah';
    document.getElementById('formAction').value = 'tambah';
    document.getElementById('editId').value = '';
    document.getElementById('fNama').value = '';
    document.getElementById('fKategori').value = 'organik_kering';
    document.getElementById('fPoin').value = '';
    document.getElementById('fHarga').value = '';
    document.getElementById('fDeskripsi').value = '';
    document.getElementById('fStatus').value = '1';
    document.getElementById('modalForm').classList.add('open');
}

function editForm(data) {
    document.getElementById('formTitle').textContent = 'Edit Jenis Sampah';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = data.id;
    document.getElementById('fNama').value = data.nama;
    document.getElementById('fKategori').value = data.kategori;
    document.getElementById('fPoin').value = data.poin_per_kg;
    document.getElementById('fHarga').value = data.harga_per_kg || 0;
    document.getElementById('fDeskripsi').value = data.deskripsi || '';
    document.getElementById('fStatus').value = data.is_active;
    document.getElementById('modalForm').classList.add('open');
}

function closeForm() {
    document.getElementById('modalForm').classList.remove('open');
}

function submitForm() {
    const fd = new FormData();
    fd.append('action', document.getElementById('formAction').value);
    fd.append('id', document.getElementById('editId').value);
    fd.append('nama', document.getElementById('fNama').value);
    fd.append('kategori', document.getElementById('fKategori').value);
    fd.append('poin_per_kg', document.getElementById('fPoin').value);
    fd.append('harga_per_kg', document.getElementById('fHarga').value);
    fd.append('deskripsi', document.getElementById('fDeskripsi').value);
    fd.append('is_active', document.getElementById('fStatus').value);

    fetch('kelola-sampah.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeForm();
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#4CAF50' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}

function hapusSampah(id, nama) {
    Swal.fire({
        title: `Hapus "${nama}"?`,
        text: 'Data yang sudah digunakan dalam transaksi tidak bisa dihapus.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then(res => {
        if (!res.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'hapus');
        fd.append('id', id);
        fetch('kelola-sampah.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: data.message, confirmButtonColor: '#4CAF50' })
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
