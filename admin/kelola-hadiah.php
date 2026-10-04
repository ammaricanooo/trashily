<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $nama  = trim($_POST['nama'] ?? '');
        $desc  = trim($_POST['deskripsi'] ?? '');
        $poin  = intval($_POST['poin_dibutuhkan'] ?? 0);
        $stok  = intval($_POST['stok'] ?? 0);
        $aktif = intval($_POST['is_active'] ?? 1);

        if (!$nama || $poin <= 0) {
            echo json_encode(['success' => false, 'message' => 'Data tidak valid.']); exit;
        }

        if ($action === 'tambah') {
            $stmt = $conn->prepare("INSERT INTO hadiah (nama, deskripsi, poin_dibutuhkan, stok, is_active) VALUES (?,?,?,?,?)");
            $stmt->bind_param('ssiii', $nama, $desc, $poin, $stok, $aktif);
        } else {
            $id = intval($_POST['id'] ?? 0);
            $stmt = $conn->prepare("UPDATE hadiah SET nama=?, deskripsi=?, poin_dibutuhkan=?, stok=?, is_active=? WHERE id=?");
            $stmt->bind_param('ssiiii', $nama, $desc, $poin, $stok, $aktif, $id);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan.']);
        exit;
    }

    if ($action === 'hapus') {
        $id = intval($_POST['id'] ?? 0);
        $conn->query("DELETE FROM hadiah WHERE id = $id");
        echo json_encode(['success' => true, 'message' => 'Hadiah berhasil dihapus.']);
        exit;
    }
}

$hadiah_list = $conn->query("SELECT * FROM hadiah ORDER BY poin_dibutuhkan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hadiah — Trashily</title>
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
                    <h1>Kelola Hadiah</h1>
                    <p>Atur hadiah yang bisa ditukarkan customer</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3>Daftar Hadiah</h3>
                    <button class="btn btn-primary btn-sm" onclick="openForm()">
                        <i class="fa-solid fa-plus"></i> Tambah Hadiah
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Hadiah</th>
                                <th>Deskripsi</th>
                                <th>Poin Dibutuhkan</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($hadiah_list->num_rows === 0): ?>
                            <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🎁</div><p>Belum ada hadiah</p></div></td></tr>
                            <?php else: ?>
                            <?php while ($row = $hadiah_list->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                                <td class="text-muted text-small"><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td><strong style="color:var(--accent)"><?= number_format($row['poin_dibutuhkan']) ?></strong> poin</td>
                                <td>
                                    <span class="badge <?= $row['stok'] > 0 ? 'badge-green' : 'badge-red' ?>">
                                        <?= $row['stok'] ?> stok
                                    </span>
                                </td>
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
                                        <button class="btn btn-danger btn-sm" onclick="hapus(<?= $row['id'] ?>, '<?= addslashes($row['nama']) ?>')">
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
    <div class="modal" style="max-width:460px">
        <div class="modal-header">
            <h3 id="formTitle">Tambah Hadiah</h3>
            <button class="modal-close" onclick="closeForm()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <input type="hidden" id="formAction" value="tambah">
            <div class="form-group">
                <label class="form-label">Nama Hadiah</label>
                <input type="text" class="form-control" id="fNama" placeholder="cth. Pulpen">
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea class="form-control" id="fDesc" placeholder="Deskripsi hadiah..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Poin Dibutuhkan</label>
                    <input type="number" class="form-control" id="fPoin" placeholder="cth. 50" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Stok</label>
                    <input type="number" class="form-control" id="fStok" placeholder="cth. 100" min="0">
                </div>
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
    document.getElementById('formTitle').textContent = 'Tambah Hadiah';
    document.getElementById('formAction').value = 'tambah';
    ['editId','fNama','fDesc','fPoin','fStok'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fStatus').value = '1';
    document.getElementById('modalForm').classList.add('open');
}

function editForm(data) {
    document.getElementById('formTitle').textContent = 'Edit Hadiah';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('editId').value = data.id;
    document.getElementById('fNama').value = data.nama;
    document.getElementById('fDesc').value = data.deskripsi || '';
    document.getElementById('fPoin').value = data.poin_dibutuhkan;
    document.getElementById('fStok').value = data.stok;
    document.getElementById('fStatus').value = data.is_active;
    document.getElementById('modalForm').classList.add('open');
}

function closeForm() { document.getElementById('modalForm').classList.remove('open'); }

function submitForm() {
    const fd = new FormData();
    fd.append('action', document.getElementById('formAction').value);
    fd.append('id', document.getElementById('editId').value);
    fd.append('nama', document.getElementById('fNama').value);
    fd.append('deskripsi', document.getElementById('fDesc').value);
    fd.append('poin_dibutuhkan', document.getElementById('fPoin').value);
    fd.append('stok', document.getElementById('fStok').value);
    fd.append('is_active', document.getElementById('fStatus').value);

    fetch('kelola-hadiah.php', { method: 'POST', body: fd })
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

function hapus(id, nama) {
    Swal.fire({
        title: `Hapus "${nama}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Hapus'
    }).then(res => {
        if (!res.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'hapus');
        fd.append('id', id);
        fetch('kelola-hadiah.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Terhapus', confirmButtonColor: '#4CAF50' }).then(() => location.reload());
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
