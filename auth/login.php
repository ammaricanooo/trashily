<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit;
}

$error = '';
$logged_out = isset($_GET['logged_out']) && $_GET['logged_out'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, nama, email, password, role, poin FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['nama']      = $user['nama'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['poin']      = $user['poin'];

            $redirect = $user['role'] === 'admin' ? '../admin/dashboard.php' : '../customer/dashboard.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Email atau kata sandi tidak cocok.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Trashily Bank Sampah</title>
    <meta name="description" content="Masuk ke akun Trashily Anda untuk mengelola setor sampah dan memantau poin.">
    
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
        .login-container {
            display: flex;
            width: 100%;
            max-width: 1060px;
            min-height: 640px;
            background: var(--surface-container-lowest);
            border-radius: var(--r-xl); /* 24px DESIGN.md */
            overflow: hidden;
            border: 1px solid var(--green-pale);
            box-shadow: var(--shadow-card);
        }

        /* Left Banner Side */
        .login-banner {
            flex: 1.1;
            background: linear-gradient(148deg, #0d7940 0%, var(--primary) 50%, #004b1e 100%);
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            color: #ffffff;
            overflow: hidden;
        }

        .login-banner::before {
            content: '';
            position: absolute;
            width: 450px; height: 450px;
            top: -150px; right: -150px;
            background: radial-gradient(circle, rgba(74,225,118,.14) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-banner::after {
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
            margin: 32px 0;
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
            margin-bottom: 18px;
        }
        .banner-chip-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--inverse-primary);
        }

        .banner-hero h2 {
            font-family: var(--font-display);
            font-size: 32px;
            font-weight: 800;
            line-height: 40px;
            margin-bottom: 14px;
        }

        .banner-hero h2 em {
            font-style: italic;
            color: var(--inverse-primary);
        }

        .banner-hero p {
            font-size: 15px;
            line-height: 24px;
            color: rgba(255, 255, 255, 0.85);
            max-width: 420px;
        }

        .banner-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 2;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            padding: 12px 18px;
            border-radius: var(--r-lg);
        }
        .feature-item i {
            color: var(--inverse-primary);
            font-size: 1rem;
        }

        /* Right Form Side */
        .login-form-area {
            flex: 1;
            padding: 56px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--surface-container-lowest);
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h1 {
            font-family: var(--font-display);
            font-size: 26px;
            font-weight: 800;
            color: var(--on-surface);
            line-height: 34px;
        }

        .form-header p {
            font-size: 14px;
            color: var(--tertiary);
            margin-top: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--on-surface);
            margin-bottom: 8px;
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
            font-size: 1rem;
            transition: color 0.2s;
            pointer-events: none;
        }

        /* Input field per DESIGN.md: light background #f1f5f9 -> white + primary green border on focus */
        .input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            border: 1.5px solid var(--border-color);
            border-radius: var(--r-md); /* 8px DESIGN.md */
            font-family: var(--font-body);
            font-size: 14px;
            color: var(--on-surface);
            background: var(--input-bg);
            transition: all 0.2s ease;
        }

        .input-wrap input:focus {
            outline: none;
            background: var(--surface-container-lowest);
            border-color: var(--primary-container);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.18);
        }

        .input-wrap input:focus + i.input-icon {
            color: var(--primary-container);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: var(--tertiary);
            cursor: pointer;
            font-size: 0.95rem;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--on-surface);
        }

        /* Primary Button per DESIGN.md: Primary Green background, scale 1.02 hover */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary-container);
            color: var(--on-primary-container);
            border: none;
            border-radius: var(--r-md); /* 8px DESIGN.md */
            font-family: var(--font-body);
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.18s cubic-bezier(.34,1.56,.64,1), box-shadow 0.18s ease, background 0.18s ease;
            margin-top: 8px;
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
            margin-top: 28px;
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
            .login-banner {
                display: none;
            }
            .login-container {
                max-width: 440px;
                min-height: auto;
            }
            .login-form-area {
                padding: 40px 28px;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <!-- Visual Banner Side (DESIGN.md) -->
        <div class="login-banner">
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
                    Bank Sampah Digital
                </div>
                <h2>Ubah Sampah Jadi<br><em>Manfaat Nyata</em></h2>
                <p>Kelola limbah rumah tangga dengan bijak, kumpulkan poin penukaran, dan berkontribusi untuk lingkungan yang lebih bersih.</p>
            </div>

            <div class="banner-features">
                <div class="feature-item">
                    <i class="fa-solid fa-shield-circle-check"></i>
                    <span>Sistem transaksi &amp; penimbangan akurat</span>
                </div>
                <div class="feature-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Tukarkan poin dengan sembako &amp; alat tulis</span>
                </div>
            </div>
        </div>

        <!-- Form Area Side -->
        <div class="login-form-area">
            <div class="form-header">
                <h1>Selamat Datang Kembali 👋</h1>
                <p>Silakan masuk ke akun Trashily Anda.</p>
            </div>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="input-wrap">
                        <input type="email" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                        <i class="fa-regular fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                        <i class="fa-solid fa-lock input-icon"></i>
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()" aria-label="Lihat kata sandi">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
                </button>
            </form>

            <div class="form-footer">
                Belum memiliki akun? <a href="register.php">Daftar gratis</a>
            </div>
        </div>
    </div>

    <!-- Toggle Password Visibility -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

    <!-- SweetAlert2 Toast/Alerts -->
    <?php if ($logged_out): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil Keluar',
            text: 'Anda telah berhasil keluar dari sistem.',
            confirmButtonColor: '#006e2f',
            customClass: { popup: 'border-radius-20' }
        });
    </script>
    <?php endif; ?>

    <?php if ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal Masuk',
            text: '<?= addslashes($error) ?>',
            confirmButtonColor: '#ba1a1a',
            customClass: { popup: 'border-radius-20' }
        });
    </script>
    <?php endif; ?>

</body>
</html>