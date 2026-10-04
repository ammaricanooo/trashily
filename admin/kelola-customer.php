<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $nama     = trim($_POST['nama'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $no_hp    = trim($_POST['no_hp'] ?? '');
        $alamat   = trim($_POST['alamat'] ?? '');

        if (!$nama || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Nama, email, dan password wajib diisi.']); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']); exit;
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar.']); exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?,?,?,?,?,'customer')");
        $stmt->bind_param('sssss', $nama, $email, $hash, $no_hp, $alamat);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Customer berhasil ditambahkan.']);
        exit;
    }

    if ($action === 'edit') {
        $id     = intval($_POST['id'] ?? 0);
        $nama   = trim($_POST['nama'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $no_hp  = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $poin   = intval($_POST['poin'] ?? 0);

        if (!$id || !$nama || !$email) {
            echo json_encode(['success' => false, 'message' => 'Nama dan email wajib diisi.']); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Format email tidak valid.']); exit;
        }

        // Cek email duplikat
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param('si', $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email sudah digunakan oleh customer lain.']); exit;
        }

        $stmt = $conn->prepare("UPDATE users SET nama = ?, email = ?, no_hp = ?, alamat = ?, poin = ? WHERE id = ? AND role = 'customer'");
        $stmt->bind_param('ssssii', $nama, $email, $no_hp, $alamat, $poin, $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Data customer berhasil diperbarui.']);
        exit;
    }

    if ($action === 'reset_password') {
        $id          = intval($_POST['id'] ?? 0);
        $new_password = $_POST['new_password'] ?? '';

        if (!$id || strlen($new_password) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter.']); exit;
        }

        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'customer'");
        $stmt->bind_param('si', $hash, $id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Password customer berhasil direset!']);
        exit;
    }

    if ($action === 'reset_poin') {
        $id = intval($_POST['id'] ?? 0);
        $conn->query("UPDATE users SET poin = 0 WHERE id = $id AND role = 'customer'");
        echo json_encode(['success' => true, 'message' => 'Poin customer berhasil direset.']);
        exit;
    }
}

$search = trim($_GET['search'] ?? '');
$where = "WHERE role='customer'";
if ($search) $where .= " AND (nama LIKE '%" . $conn->real_escape_string($search) . "%' OR email LIKE '%" . $conn->real_escape_string($search) . "%' OR no_hp LIKE '%" . $conn->real_escape_string($search) . "%')";

$customers = $conn->query("SELECT *, (SELECT COUNT(*) FROM transaksi WHERE customer_id = users.id) as total_trx FROM users $where ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Customer — Trashily</title>
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
                    <h1>Data Customer</h1>
                    <p>Kelola akun dan data customer</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_admin.php'; ?>
            </div>
        </div>
        <div class="content">
            <div class="card mb-2">
                <div class="card-body" style="padding:.875rem 1.25rem">
                    <form method="GET" class="search-form-wrap">
                        <div class="search-bar">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" name="search" placeholder="Cari nama, email, atau no. HP..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Cari</button>
                            <?php if ($search): ?><a href="kelola-customer.php" class="btn btn-ghost">Reset</a><?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Daftar Customer</h3>
                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalTambah').classList.add('open')">
                        <i class="fa-solid fa-user-plus"></i> Tambah Customer
                    </button>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. HP</th>
                                <th>Alamat</th>
                                <th>Poin</th>
                                <th>Total Transaksi</th>
                                <th>Bergabung</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($customers->num_rows === 0): ?>
                            <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👤</div><p>Belum ada customer</p></div></td></tr>
                            <?php else: ?>
                            <?php while ($c = $customers->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:.6rem">
                                        <div style="width:32px;height:32px;background:var(--primary-light);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:var(--primary-dark);flex-shrink:0">
                                            <?= strtoupper(substr($c['nama'], 0, 1)) ?>
                                        </div>
                                        <div style="font-weight:600"><?= htmlspecialchars($c['nama']) ?></div>
                                    </div>
                                </td>
                                <td class="text-muted text-small"><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= htmlspecialchars($c['no_hp'] ?: '-') ?></td>
                                <td style="max-width:160px">
                                    <div class="text-muted text-small" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($c['alamat'] ?? '') ?>">
                                        <?= htmlspecialchars($c['alamat'] ?: '-') ?>
                                    </div>
                                </td>
                                <td><strong style="color:var(--primary-dark)"><?= number_format($c['poin']) ?></strong></td>
                                <td><span class="badge badge-blue"><?= $c['total_trx'] ?> trx</span></td>
                                <td class="text-muted text-small"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                <td>
                                    <div style="display:flex;gap:.35rem;flex-wrap:wrap">
                                        <button class="btn btn-info btn-sm" onclick='openModalEdit(<?= json_encode($c) ?>)' title="Edit Customer">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="openModalResetPass(<?= $c['id'] ?>, '<?= addslashes($c['nama']) ?>')" title="Reset Password">
                                            <i class="fa-solid fa-key"></i> Pass
                                        </button>
                                        <?php if ($c['poin'] > 0): ?>
                                        <button class="btn btn-danger btn-sm" onclick="resetPoin(<?= $c['id'] ?>, '<?= addslashes($c['nama']) ?>')" title="Reset Poin">
                                            <i class="fa-solid fa-rotate-left"></i>
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
            </div>
        </div>
    </main>
</div>

<!-- MODAL TAMBAH CUSTOMER -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal" style="max-width:460px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus" style="color:var(--primary);margin-right:.5rem"></i>Tambah Customer</h3>
            <button class="modal-close" onclick="document.getElementById('modalTambah').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="cNama" placeholder="cth. Budi Santoso">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="cEmail" placeholder="email@contoh.com">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" class="form-control" id="cHp" placeholder="0812...">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" id="cPass" placeholder="••••••••">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" id="cAlamat" placeholder="Alamat lengkap..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modalTambah').classList.remove('open')">Batal</button>
            <button class="btn btn-primary" onclick="tambahCustomer()"><i class="fa-solid fa-check"></i> Simpan</button>
        </div>
    </div>
</div>

<!-- MODAL EDIT CUSTOMER -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal" style="max-width:480px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square" style="color:var(--primary);margin-right:.5rem"></i>Edit Data Customer</h3>
            <button class="modal-close" onclick="document.getElementById('modalEdit').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="editNama">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" class="form-control" id="editHp">
                </div>
                <div class="form-group">
                    <label class="form-label">Poin</label>
                    <input type="number" class="form-control" id="editPoin" min="0">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" id="editAlamat" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modalEdit').classList.remove('open')">Batal</button>
            <button class="btn btn-primary" onclick="simpanEditCustomer()"><i class="fa-solid fa-check"></i> Simpan Perubahan</button>
        </div>
    </div>
</div>

<!-- MODAL RESET PASSWORD -->
<div class="modal-overlay" id="modalResetPass">
    <div class="modal" style="max-width:400px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-key" style="color:var(--warning);margin-right:.5rem"></i>Reset Password Customer</h3>
            <button class="modal-close" onclick="document.getElementById('modalResetPass').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="resetPassId">
            <p style="font-size:.875rem;color:var(--text-dark);margin-bottom:.875rem">
                Reset password untuk customer: <strong id="resetPassNama">-</strong>
            </p>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" class="form-control" id="newPassInput" placeholder="Minimal 6 karakter">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modalResetPass').classList.remove('open')">Batal</button>
            <button class="btn btn-warning" onclick="simpanResetPass()"><i class="fa-solid fa-check"></i> Reset Password</button>
        </div>
    </div>
</div>

<script>
function tambahCustomer() {
    const fd = new FormData();
    fd.append('action', 'tambah');
    fd.append('nama', document.getElementById('cNama').value);
    fd.append('email', document.getElementById('cEmail').value);
    fd.append('no_hp', document.getElementById('cHp').value);
    fd.append('password', document.getElementById('cPass').value);
    fd.append('alamat', document.getElementById('cAlamat').value);

    fetch('kelola-customer.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('modalTambah').classList.remove('open');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#4CAF50' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}

function openModalEdit(customer) {
    document.getElementById('editId').value = customer.id;
    document.getElementById('editNama').value = customer.nama || '';
    document.getElementById('editEmail').value = customer.email || '';
    document.getElementById('editHp').value = customer.no_hp || '';
    document.getElementById('editPoin').value = customer.poin || 0;
    document.getElementById('editAlamat').value = customer.alamat || '';
    document.getElementById('modalEdit').classList.add('open');
}

function simpanEditCustomer() {
    const fd = new FormData();
    fd.append('action', 'edit');
    fd.append('id', document.getElementById('editId').value);
    fd.append('nama', document.getElementById('editNama').value);
    fd.append('email', document.getElementById('editEmail').value);
    fd.append('no_hp', document.getElementById('editHp').value);
    fd.append('poin', document.getElementById('editPoin').value);
    fd.append('alamat', document.getElementById('editAlamat').value);

    fetch('kelola-customer.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('modalEdit').classList.remove('open');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#4CAF50' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}

function openModalResetPass(id, nama) {
    document.getElementById('resetPassId').value = id;
    document.getElementById('resetPassNama').textContent = nama;
    document.getElementById('newPassInput').value = '';
    document.getElementById('modalResetPass').classList.add('open');
}

function simpanResetPass() {
    const id = document.getElementById('resetPassId').value;
    const newPass = document.getElementById('newPassInput').value.trim();

    if (!newPass || newPass.length < 6) {
        Swal.fire({ icon: 'warning', title: 'Password Terlalu Pendek', text: 'Password minimal 6 karakter.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const fd = new FormData();
    fd.append('action', 'reset_password');
    fd.append('id', id);
    fd.append('new_password', newPass);

    fetch('kelola-customer.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('modalResetPass').classList.remove('open');
                Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, confirmButtonColor: '#4CAF50' });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}

function resetPoin(id, nama) {
    Swal.fire({
        title: `Reset poin ${nama}?`,
        text: 'Poin customer akan dikembalikan ke 0.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Reset'
    }).then(res => {
        if (!res.isConfirmed) return;
        const fd = new FormData();
        fd.append('action', 'reset_poin');
        fd.append('id', id);
        fetch('kelola-customer.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Direset', confirmButtonColor: '#4CAF50' }).then(() => location.reload());
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
