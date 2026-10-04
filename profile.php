<?php
require_once 'includes/auth_check.php';
requireLogin();
require_once 'config/database.php';

$uid  = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profil') {
        $nama   = trim($_POST['nama'] ?? '');
        $no_hp  = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        if (!$nama) {
            echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong.']); exit;
        }

        $stmt = $conn->prepare("UPDATE users SET nama=?, no_hp=?, alamat=? WHERE id=?");
        $stmt->bind_param('sssi', $nama, $no_hp, $alamat, $uid);
        $stmt->execute();

        $_SESSION['nama'] = $nama;
        echo json_encode(['success' => true, 'message' => 'Profil berhasil diperbarui.', 'nama' => $nama]);
        exit;
    }

    if ($action === 'ganti_password') {
        $lama  = $_POST['password_lama'] ?? '';
        $baru  = $_POST['password_baru'] ?? '';
        $ulang = $_POST['password_ulang'] ?? '';

        if (!$lama || !$baru || !$ulang) {
            echo json_encode(['success' => false, 'message' => 'Semua field password wajib diisi.']); exit;
        }
        if (strlen($baru) < 6) {
            echo json_encode(['success' => false, 'message' => 'Password baru minimal 6 karakter.']); exit;
        }
        if ($baru !== $ulang) {
            echo json_encode(['success' => false, 'message' => 'Konfirmasi password tidak cocok.']); exit;
        }

        $row = $conn->query("SELECT password FROM users WHERE id = $uid")->fetch_assoc();
        if (!password_verify($lama, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai.']); exit;
        }

        $hash = password_hash($baru, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param('si', $hash, $uid);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Password berhasil diubah.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']); exit;
}

// Load data user
$user = $conn->query("SELECT nama, email, no_hp, alamat, poin, role, created_at FROM users WHERE id = $uid")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — Trashily</title>
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
    <link rel="stylesheet" href="assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php if ($role === 'admin'): ?>
        <?php include 'includes/sidebar_admin.php'; ?>
    <?php else: ?>
        <?php include 'includes/sidebar_customer.php'; ?>
    <?php endif; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Profil Saya</h1>
                    <p>Kelola informasi akun Anda</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php if ($role === 'admin'): ?>
                    <?php include 'includes/notif_admin.php'; ?>
                <?php else: ?>
                    <?php include 'includes/notif_customer.php'; ?>
                    <div class="poin-badge">
                        <i class="fa-solid fa-star"></i>
                        <?= number_format($user['poin'], 0, ',', '.') ?> Poin
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="content">
            <div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem;align-items:start" class="profile-grid">

                <!-- Kartu Identitas -->
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:2rem 1.5rem">
                        <div style="width:72px;height:72px;border-radius:20px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.75rem;font-weight:700;color:var(--primary-dark)" id="avatarInitial">
                            <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                        </div>
                        <div style="font-size:1.05rem;font-weight:700;color:var(--text-dark);margin-bottom:.25rem" id="displayNama"><?= htmlspecialchars($user['nama']) ?></div>
                        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem"><?= htmlspecialchars($user['email']) ?></div>
                        <span class="badge <?= $role === 'admin' ? 'badge-blue' : 'badge-green' ?>" style="font-size:.75rem">
                            <i class="fa-solid <?= $role === 'admin' ? 'fa-shield-halved' : 'fa-user' ?>"></i>
                            <?= $role === 'admin' ? 'Administrator' : 'Customer' ?>
                        </span>

                        <?php if ($role === 'customer'): ?>
                        <div style="margin-top:1.25rem;padding:1rem;background:var(--primary-light);border-radius:12px">
                            <div style="font-size:.72rem;color:var(--primary-dark);font-weight:700;text-transform:uppercase;letter-spacing:.05em">Total Poin</div>
                            <div style="font-size:1.6rem;font-weight:700;color:var(--primary-dark);margin-top:.2rem"><?= number_format($user['poin'], 0, ',', '.') ?></div>
                        </div>
                        <?php endif; ?>

                        <div style="margin-top:1.1rem;text-align:left;display:flex;flex-direction:column;gap:.6rem">
                            <div style="display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:var(--text-muted)">
                                <i class="fa-solid fa-phone" style="width:16px;color:var(--text-muted)"></i>
                                <span id="displayHp"><?= $user['no_hp'] ? htmlspecialchars($user['no_hp']) : '<em>Belum diisi</em>' ?></span>
                            </div>
                            <div style="display:flex;align-items:flex-start;gap:.6rem;font-size:.82rem;color:var(--text-muted)">
                                <i class="fa-solid fa-location-dot" style="width:16px;margin-top:.15rem;color:var(--text-muted)"></i>
                                <span id="displayAlamat"><?= $user['alamat'] ? htmlspecialchars($user['alamat']) : '<em>Belum diisi</em>' ?></span>
                            </div>
                            <div style="display:flex;align-items:center;gap:.6rem;font-size:.82rem;color:var(--text-muted)">
                                <i class="fa-regular fa-calendar" style="width:16px;color:var(--text-muted)"></i>
                                <span>Bergabung <?= date('d M Y', strtotime($user['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Edit -->
                <div style="display:flex;flex-direction:column;gap:1.25rem">

                    <!-- Edit Data Diri -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa-solid fa-pen" style="color:var(--primary);margin-right:.5rem;font-size:.9rem"></i>Edit Data Diri</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="inputNama" value="<?= htmlspecialchars($user['nama']) ?>" placeholder="Nama lengkap">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">No. HP / WhatsApp</label>
                                    <input type="text" class="form-control" id="inputHp" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" placeholder="08xx-xxxx-xxxx">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.6;cursor:not-allowed" title="Email tidak bisa diubah">
                                <small style="font-size:.75rem;color:var(--text-muted);margin-top:.3rem;display:block"><i class="fa-solid fa-lock" style="font-size:.65rem"></i> Email tidak dapat diubah</small>
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" id="inputAlamat" rows="3" placeholder="Alamat lengkap..."><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
                            <button class="btn btn-primary" onclick="simpanProfil()">
                                <i class="fa-solid fa-check"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>

                    <!-- Ganti Password -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fa-solid fa-lock" style="color:var(--accent);margin-right:.5rem;font-size:.9rem"></i>Ganti Password</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Password Lama</label>
                                <div style="position:relative">
                                    <input type="password" class="form-control" id="pwLama" placeholder="Masukkan password lama" style="padding-right:2.75rem">
                                    <button type="button" onclick="togglePw('pwLama',this)" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group mb-0">
                                    <label class="form-label">Password Baru</label>
                                    <div style="position:relative">
                                        <input type="password" class="form-control" id="pwBaru" placeholder="Minimal 6 karakter" style="padding-right:2.75rem">
                                        <button type="button" onclick="togglePw('pwBaru',this)" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <div style="position:relative">
                                        <input type="password" class="form-control" id="pwUlang" placeholder="Ulangi password baru" style="padding-right:2.75rem">
                                        <button type="button" onclick="togglePw('pwUlang',this)" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
                            <button class="btn btn-primary" onclick="gantiPassword()">
                                <i class="fa-solid fa-key"></i> Ubah Password
                            </button>
                        </div>
                    </div>

                </div><!-- end form col -->
            </div><!-- end profile-grid -->
        </div>
    </main>
</div>

<style>
@media(max-width:768px) {
    .profile-grid { grid-template-columns: 1fr !important; }
}
</style>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}

function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}

function simpanProfil() {
    const nama   = document.getElementById('inputNama').value.trim();
    const no_hp  = document.getElementById('inputHp').value.trim();
    const alamat = document.getElementById('inputAlamat').value.trim();

    if (!nama) {
        Swal.fire({ icon: 'warning', title: 'Nama kosong', text: 'Nama tidak boleh kosong.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const fd = new FormData();
    fd.append('action', 'update_profil');
    fd.append('nama', nama);
    fd.append('no_hp', no_hp);
    fd.append('alamat', alamat);

    fetch(BASE_URL + '/profile.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // update kartu identitas tanpa reload
                document.getElementById('displayNama').textContent = res.nama;
                document.getElementById('avatarInitial').textContent = res.nama.charAt(0).toUpperCase();
                document.getElementById('displayHp').textContent  = no_hp || 'Belum diisi';
                document.getElementById('displayAlamat').textContent = alamat || 'Belum diisi';
                Swal.fire({ icon: 'success', title: 'Tersimpan', text: res.message, confirmButtonColor: '#4CAF50', timer: 2000, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}

function gantiPassword() {
    const lama  = document.getElementById('pwLama').value;
    const baru  = document.getElementById('pwBaru').value;
    const ulang = document.getElementById('pwUlang').value;

    if (!lama || !baru || !ulang) {
        Swal.fire({ icon: 'warning', title: 'Field kosong', text: 'Semua field password wajib diisi.', confirmButtonColor: '#4CAF50' });
        return;
    }
    if (baru.length < 6) {
        Swal.fire({ icon: 'warning', title: 'Password terlalu pendek', text: 'Password baru minimal 6 karakter.', confirmButtonColor: '#4CAF50' });
        return;
    }
    if (baru !== ulang) {
        Swal.fire({ icon: 'warning', title: 'Tidak cocok', text: 'Konfirmasi password tidak cocok.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const fd = new FormData();
    fd.append('action', 'ganti_password');
    fd.append('password_lama', lama);
    fd.append('password_baru', baru);
    fd.append('password_ulang', ulang);

    fetch(BASE_URL + '/profile.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('pwLama').value  = '';
                document.getElementById('pwBaru').value  = '';
                document.getElementById('pwUlang').value = '';
                Swal.fire({ icon: 'success', title: 'Password Diubah', text: res.message, confirmButtonColor: '#4CAF50', timer: 2200, showConfirmButton: false });
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
}
</script>
<?php include_once 'includes/notif_scripts.php'; ?>
</body>
</html>
