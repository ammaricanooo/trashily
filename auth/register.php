<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nama) || empty($email) || empty($password) || empty($no_hp)) {
        $error = 'Nama, email, no. HP, dan kata sandi wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Kata sandi minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi kata sandi tidak cocok.';
    } else {
        // Cek email duplikat
        $chk = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $chk->bind_param('s', $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param('sssss', $nama, $email, $hashed, $no_hp, $alamat);
            if ($stmt->execute()) {
                $success = 'Akun berhasil dibuat! Silakan masuk.';
            } else {
                $error = 'Terjadi kesalahan, silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Trashily Bank Sampah</title>
    <meta name="description" content="Daftar akun Trashily baru secara gratis dan mulai kumpulkan poin dari sampah rumah tangga Anda.">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* ============================================================
           DESIGN TOKENS — Eco-Positive Exchange (DESIGN.md)
        ============================================================ */
        :root {
            --surface:                  #f8f9ff;
            --surface-container-lowest: #ffffff;
            --surface-container-low:    #eff4ff;
            --surface-container:        #e5eeff;
            --on-surface:               #0b1c30;
            --on-surface-variant:       #3d4a3d;

            --primary:                  #006e2f;
            --primary-container:        #22c55e;
            --on-primary-container:     #004b1e;
            --inverse-primary:          #4ae176;

            --secondary:                #1f6c3a;
            --secondary-container:      #a4f1b2;

            --tertiary:                 #55615a;
            --green-pale:               #dcfce7;
            --input-bg:                 #f1f5f9;
            --outline-variant:          #bccbb9;
            --border-color:             #e2e8f0;

            --font-display:  'Plus Jakarta Sans', sans-serif;
            --font-body:     'Be Vietnam Pro', sans-serif;

            --r-sm:   0.25rem;
            --r-md:   0.5rem;   /* 8px for buttons/inputs */
            --r-lg:   1rem;
            --r-xl:   1.5rem;  /* 24px for large containers */
            --r-full: 9999px;

            --shadow-card: 0 2px 40px 0 rgba(31,108,58,.06), 0 1px 4px 0 rgba(31,108,58,.04);
            --shadow-float: 0 12px 32px rgba(0,110,47,.16);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: var(--font-body);
            background-color: var(--surface);
            color: var(--on-surface-variant);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* Container Card */
        .reg-container {
            display: flex;
            width: 100%;
            max-width: 1080px;
            min-height: 680px;
            background: var(--surface-container-lowest);
            border-radius: var(--r-xl);
            overflow: hidden;
            border: 1px solid var(--green-pale);
            box-shadow: var(--shadow-card);
        }

        /* Left Banner Side */
        .reg-banner {
            flex: 0.95;
            background: linear-gradient(148deg, #0d7940 0%, var(--primary) 50%, #004b1e 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            color: #ffffff;
            overflow: hidden;
        }

        .reg-banner::before {
            content: '';
            position: absolute;
            width: 450px; height: 450px;
            top: -150px; right: -150px;
            background: radial-gradient(circle, rgba(74,225,118,.14) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .reg-banner::after {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            bottom: -100px; left: -100px;
            background: radial-gradient(circle, rgba(74,225,118,.1) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .banner-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 2;
        }

        .banner-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: var(--primary-container);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            color: var(--on-primary-container);
            box-shadow: 0 4px 14px rgba(34,197,94,.3);
        }

        .brand-name {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,.85);
            background: rgba(255,255,255,.12);
            padding: 6px 14px;
            border-radius: var(--r-full);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.2);
            transition: all .2s ease;
        }
        .back-home:hover {
            background: rgba(255,255,255,.22);
            color: #ffffff;
        }

        .banner-hero {
            z-index: 2;
            margin: 28px 0;
        }

        .banner-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: var(--r-full);
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .banner-chip-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--inverse-primary);
        }

        .banner-hero h2 {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 800;
            line-height: 36px;
            margin-bottom: 12px;
        }

        .banner-hero h2 em {
            font-style: italic;
            color: var(--inverse-primary);
        }

        .banner-hero p {
            font-size: 14px;
            line-height: 22px;
            color: rgba(255, 255, 255, 0.85);
        }

        .banner-steps {
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 2;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            padding: 10px 14px;
            border-radius: var(--r-lg);
        }

        .step-number {
            width: 26px; height: 26px;
            background: var(--primary-container);
            color: var(--on-primary-container);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 12px;
            flex-shrink: 0;
        }

        /* Right Form Side */
        .reg-form-area {
            flex: 1.25;
            padding: 40px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--surface-container-lowest);
        }

        .form-header {
            margin-bottom: 24px;
        }

        .form-header h1 {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 800;
            color: var(--on-surface);
            line-height: 32px;
        }

        .form-header p {
            font-size: 14px;
            color: var(--tertiary);
            margin-top: 4px;
        }

        /* Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            margin-bottom: 6px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--on-surface);
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap i.input-icon {
            position: absolute;
            left: 16px;
            color: var(--tertiary);
            font-size: 0.95rem;
            transition: color 0.2s;
            pointer-events: none;
        }

        .input-wrap input,
        .input-wrap textarea {
            width: 100%;
            padding: 12px 16px 12px 46px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--r-md); /* 8px DESIGN.md */
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--on-surface);
            background: var(--input-bg);
            transition: all 0.2s ease;
        }

        .input-wrap textarea {
            resize: vertical;
            min-height: 64px;
            padding-top: 10px;
        }

        .input-wrap input:focus,
        .input-wrap textarea:focus {
            outline: none;
            background: var(--surface-container-lowest);
            border-color: var(--primary-container);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        }

        .input-wrap input:focus + i.input-icon,
        .input-wrap textarea:focus + i.input-icon {
            color: var(--primary-container);
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--tertiary);
            cursor: pointer;
            font-size: 0.9rem;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-pw:hover {
            color: var(--on-surface);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-container);
            color: var(--on-primary-container);
            border: none;
            border-radius: var(--r-md);
            font-family: var(--font-body);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.18s cubic-bezier(.34,1.56,.64,1), box-shadow 0.18s ease;
            margin-top: 14px;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.3);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 24px rgba(34, 197, 94, 0.4);
        }

        .btn-submit:active {
            transform: scale(0.99);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--tertiary);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s;
        }

        .form-footer a:hover {
            color: var(--on-primary-container);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .reg-banner {
                display: none;
            }
            .reg-container {
                max-width: 520px;
                min-height: auto;
            }
            .reg-form-area {
                padding: 36px 28px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

    <div class="reg-container">
        <!-- Visual Banner Side -->
        <div class="reg-banner">
            <div class="banner-top">
                <a href="../index.php" class="banner-brand">
                    <div class="brand-icon" style="background:transparent;padding:0;overflow:hidden">
                        <img src="../assets/brand.png" alt="Trashily" style="width:100%;height:100%;object-fit:contain">
                    </div>
                    <span class="brand-name">Trashily</span>
                </a>
                <a href="../index.php" class="back-home">
                    <i class="fa-solid fa-arrow-left"></i> Beranda
                </a>
            </div>

            <div class="banner-hero">
                <div class="banner-chip">
                    <span class="banner-chip-dot"></span>
                    Gabung Sekarang
                </div>
                <h2>Mulai Langkah Hijaumu<br><em>Hari Ini! 🌿</em></h2>
                <p>Bergabunglah dengan ribuan nasabah lainnya. Kumpulkan sampah terpilah, dapatkan poin, dan cairkan manfaatnya.</p>
            </div>

            <div class="banner-steps">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <span>Daftar akun nasabah gratis</span>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <span>Setor sampah ke lokasi / dijemput</span>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <span>Kumpulkan poin &amp; tukarkan hadiah</span>
                </div>
            </div>
        </div>

        <!-- Form Area Side -->
        <div class="reg-form-area">
            <div class="form-header">
                <h1>Buat Akun Baru ✨</h1>
                <p>Lengkapi data diri Anda untuk bergabung.</p>
            </div>

            <form method="POST" autocomplete="off">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="nama">Nama Lengkap</label>
                        <div class="input-wrap">
                            <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required autofocus>
                            <i class="fa-solid fa-user input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Alamat Email</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <i class="fa-regular fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="no_hp">No. HP / WhatsApp</label>
                        <div class="input-wrap">
                            <input type="tel" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>" required>
                            <i class="fa-solid fa-phone input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="alamat">Alamat Lengkap <span style="color:var(--tertiary); font-weight:400;">(Opsional)</span></label>
                        <div class="input-wrap">
                            <textarea id="alamat" name="alamat" placeholder="Jl. Contoh No. 12, RT/RW, Kota..."><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                            <i class="fa-solid fa-location-dot input-icon" style="top: 14px;"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pw1">Kata Sandi</label>
                        <div class="input-wrap">
                            <input type="password" id="pw1" name="password" placeholder="Min. 6 karakter" required>
                            <i class="fa-solid fa-lock input-icon"></i>
                            <button type="button" class="toggle-pw" onclick="togglePw('pw1', this)" aria-label="Lihat kata sandi">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pw2">Konfirmasi Sandi</label>
                        <div class="input-wrap">
                            <input type="password" id="pw2" name="confirm_password" placeholder="Ulangi kata sandi" required>
                            <i class="fa-solid fa-lock input-icon"></i>
                            <button type="button" class="toggle-pw" onclick="togglePw('pw2', this)" aria-label="Lihat kata sandi">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Daftar Sekarang</span>
                </button>
            </form>

            <div class="form-footer">
                Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
            </div>
        </div>
    </div>

    <!-- Toggle Password Script -->
    <script>
        function togglePw(id, button) {
            const input = document.getElementById(id);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa-regular fa-eye';
            }
        }
    </script>

    <!-- SweetAlert Notifications -->
    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Pendaftaran Gagal',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#ba1a1a',
            customClass: { popup: 'border-radius-20' }
        });
    </script>
    <?php endif; ?>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Akun Berhasil Dibuat!',
            text: 'Silakan masuk menggunakan akun Anda.',
            confirmButtonColor: '#006e2f',
            confirmButtonText: 'Masuk Sekarang',
            customClass: { popup: 'border-radius-20' }
        }).then(() => { 
            window.location.href = 'login.php'; 
        });
    </script>
    <?php endif; ?>

</body>
</html>