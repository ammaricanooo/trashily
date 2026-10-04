<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

$success_message = null;
$error_message = null;
$rating = 5;
$komentar = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only allow logged-in users to submit
    if (!$logged_in) {
        $error_message = 'Anda harus login terlebih dahulu untuk menulis ulasan.';
    } else {
        $rating = max(1, min(5, intval($_POST['rating'] ?? 5)));
        $komentar = trim($_POST['komentar'] ?? '');

        if ($komentar === '') {
            $error_message = 'Komentar wajib diisi.';
        } else {
            $customer_id = intval($_SESSION['user_id']);
            $nama = $_SESSION['nama'] ?? 'Pelanggan';

            $stmt = $conn->prepare("INSERT INTO ulasan (customer_id, nama, rating, komentar, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->bind_param('isis', $customer_id, $nama, $rating, $komentar);

            if ($stmt->execute()) {
                $success_message = 'Ulasan berhasil dikirim. Admin akan meninjau sebelum tampil di halaman utama.';
                $_POST = [];
                $rating = 5;
                $komentar = '';
            } else {
                $error_message = 'Gagal mengirim ulasan. Silakan coba lagi.';
            }
        }
    }
}

$approved_reviews = $conn->query("SELECT ul.nama, ul.rating, ul.komentar, ul.created_at FROM ulasan ul WHERE ul.status = 'approved' ORDER BY ul.created_at DESC LIMIT 12");
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan â€” Trashily</title>
    <meta name="description" content="Lihat testimoni masyarakat dan kirimkan ulasanmu ke Trashily." />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {50:'#f0fdf4',100:'#dcfce7',200:'#bbf7d0',300:'#86efac',400:'#4ade80',500:'#22c55e',600:'#16a34a',700:'#15803d',800:'#166534',900:'#006e2f'},
                        deep: '#0b1c30'
                    },
                    fontFamily: {
                        display: ['Plus Jakarta Sans','sans-serif'],
                        body: ['Be Vietnam Pro','sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Be Vietnam Pro', sans-serif; background: #f8fafc; color: #0f172a; }
        #ratingContainer i { transition: color 0.2s ease, transform 0.2s ease; }
        #ratingContainer i:hover { transform: scale(1.2); }
        .review-card-stars i { color: #fbbf24 !important; }
    </style>
</head>
<body class="bg-slate-50">
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl overflow-hidden group-hover:scale-105 transition-transform">
                    <img src="assets/brand.png" alt="Trashily" class="w-full h-full object-contain">
                </div>
                <span class="font-display text-xl font-extrabold text-slate-900 group-hover:text-brand-700 transition-colors">Trashily<span class="text-brand-600">.</span></span>
            </a>
            
            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-1 rounded-full border border-slate-200/60 bg-slate-50/50 backdrop-blur px-5 py-2">
                <a href="index.php#tentang-kami" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Cara Kerja</a>
                <a href="index.php#katalog" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Direktori Sampah</a>
                <div class="relative group">
                    <button type="button" class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">
                        Layanan <i class="fa-solid fa-chevron-down text-[10px]"></i>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full mt-2 min-w-[160px] opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-200 rounded-xl border border-slate-200 bg-white shadow-lg p-1">
                        <a href="harga.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-700">Daftar Harga</a>
                        <a href="ulasan.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 bg-brand-50 hover:bg-brand-100 text-brand-800">Ulasan</a>
                    </div>
                </div>
                <a href="index.php#ekosistem" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Keunggulan</a>
                <a href="index.php#rewards" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Hadiah</a>
                <a href="index.php#faq" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                    <button id="mobileMenuBtn" class="lg:hidden text-slate-700 hover:text-brand-700 p-2" aria-label="Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                <?php else: ?>
                    <a href="auth/login.php" class="hidden sm:inline-flex px-4 py-2 rounded-full text-slate-700 hover:text-brand-700 font-semibold">Masuk</a>
                    <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">Daftar Gratis</a>
                    <button id="mobileMenuBtn" class="lg:hidden text-slate-700 hover:text-brand-700 p-2" aria-label="Menu">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white">
            <nav class="max-w-7xl mx-auto px-4 py-4 space-y-2">
                <a href="index.php#tentang-kami" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Cara Kerja</a>
                <a href="index.php#katalog" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Direktori Sampah</a>
                <a href="harga.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Daftar Harga</a>
                <a href="ulasan.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-brand-50 text-brand-700">Ulasan</a>
                <a href="index.php#ekosistem" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Keunggulan</a>
                <a href="index.php#rewards" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Hadiah</a>
                <a href="index.php#faq" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">FAQ</a>
            </nav>
        </div>
    </header>

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>

    <main class="max-w-6xl mx-auto px-4 py-16 md:py-20">
        <section class="mb-12 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-brand-800">Ulasan Masyarakat</span>
            <h1 class="mt-6 font-display text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">Cerita mereka tentang Trashily</h1>
            <p class="mt-4 mx-auto max-w-2xl text-base md:text-lg text-slate-600">Berikan pengalamanmu, lalu tunggu admin memeriksa ulasan sebelum ditampilkan di halaman utama kami.</p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            <?php if ($approved_reviews && $approved_reviews->num_rows > 0): ?>
                <?php while ($row = $approved_reviews->fetch_assoc()): ?>
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-card hover:shadow-lg transition flex flex-col">
                        <div class="mb-4">
                            <div class="review-card-stars flex items-center gap-1 mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa-solid fa-star text-sm <?= $i <= (int)$row['rating'] ? 'text-amber-400' : 'text-slate-300' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-base leading-relaxed text-slate-700 mb-4 flex-grow">"<?= htmlspecialchars($row['komentar']) ?>"</p>
                        </div>
                        <div class="pt-4 border-t border-slate-200 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 font-display font-bold text-sm text-brand-800">
                                <?= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $row['nama']), 0, 2) ?: 'U') ?>
                            </div>
                            <div>
                                <h3 class="font-display font-extrabold text-sm text-slate-900"><?= htmlspecialchars($row['nama']) ?></h3>
                                <p class="text-xs text-slate-500"><?= date('d M Y', strtotime($row['created_at'])) ?></p>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="lg:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <i class="fa-solid fa-comment-dots text-4xl text-slate-300 mb-3 block"></i>
                    <p class="text-slate-600 font-semibold">Belum ada ulasan yang disetujui.</p>
                    <p class="text-sm text-slate-500">Jadilah yang pertama meninggalkan pengalamanmu menggunakan Trashily.</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- CTA Section -->
        <section class="rounded-3xl border border-slate-200 bg-white p-8 md:p-12 shadow-card">
            <?php if (!$logged_in): ?>
                <div class="text-center">
                    <h2 class="font-display text-3xl font-extrabold text-slate-900 mb-4">Bagikan Pengalamanmu</h2>
                    <p class="text-base text-slate-600 mb-8 max-w-2xl mx-auto">Daftarkan akun kamu untuk menulis ulasan dan membantu pelanggan lain menemukan layanan terbaik.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="auth/login.php" class="inline-flex items-center justify-center gap-2 rounded-full bg-brand-600 px-8 py-3.5 font-bold text-white hover:bg-brand-700 transition">
                            <i class="fa-solid fa-sign-in-alt"></i> Masuk
                        </a>
                        <a href="auth/register.php" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-brand-600 px-8 py-3.5 font-bold text-brand-600 hover:bg-brand-50 transition">
                            <i class="fa-solid fa-user-plus"></i> Daftar Gratis
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div>
                    <h2 class="font-display text-3xl font-extrabold text-slate-900 mb-8 text-center">Tulis Ulasan Anda</h2>
                    
                    <?php if ($success_message): ?>
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?= htmlspecialchars($success_message) ?></div>
                    <?php elseif ($error_message): ?>
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error_message) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="max-w-2xl mx-auto space-y-5">
                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">Nama Akun</label>
                            <input type="text" value="<?= htmlspecialchars($_SESSION['nama'] ?? '') ?>" class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-800 font-semibold" readonly disabled>
                            <p class="text-xs text-slate-500 mt-1">Nama dari akun terdaftar Anda</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">Rating</label>
                            <div class="flex items-center gap-3" id="ratingContainer">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" name="rating" value="<?= $i ?>" id="star-<?= $i ?>" <?= ($rating == $i) ? 'checked' : '' ?> style="display:none;">
                                    <label for="star-<?= $i ?>" class="cursor-pointer" style="font-size:28px; transition: all 0.2s ease;">
                                        <i class="fa-solid fa-star" data-star="<?= $i ?>" style="color: #cbd5e1; cursor: pointer;"></i>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-bold text-slate-700">Komentar</label>
                            <textarea name="komentar" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none" placeholder="Ceritakan pengalaman kamu menggunakan Trashily..." required><?= htmlspecialchars($komentar ?? '') ?></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-brand-600 px-8 py-3.5 font-bold text-white hover:bg-brand-700 transition">
                                <i class="fa-solid fa-paper-plane"></i> Kirim Ulasan
                            </button>
                            <p class="text-xs text-slate-500 mt-4">Ulasan Anda akan diverifikasi admin sebelum ditampilkan.</p>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </section>

    <script>
        const ratingContainer = document.getElementById('ratingContainer');
        const stars = ratingContainer.querySelectorAll('i[data-star]');
        const radioInputs = document.querySelectorAll('input[name="rating"]');

        // Function to update star display
        function updateStars(rating) {
            stars.forEach(star => {
                const starNum = parseInt(star.getAttribute('data-star'));
                if (starNum <= rating) {
                    star.style.color = '#fbbf24';
                } else {
                    star.style.color = '#cbd5e1';
                }
            });
        }

        // Add click handlers to stars
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const starNum = parseInt(this.getAttribute('data-star'));
                document.getElementById(`star-${starNum}`).checked = true;
                updateStars(starNum);
            });

            // Hover effect
            star.addEventListener('mouseenter', function() {
                const starNum = parseInt(this.getAttribute('data-star'));
                stars.forEach(s => {
                    const sNum = parseInt(s.getAttribute('data-star'));
                    if (sNum <= starNum) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#cbd5e1';
                    }
                });
            });
        });

        // Reset on mouse leave
        ratingContainer.addEventListener('mouseleave', function() {
            const checkedInput = document.querySelector('input[name="rating"]:checked');
            const rating = checkedInput ? parseInt(checkedInput.value) : 0;
            updateStars(rating);
        });

        // Initialize on page load
        const initialRating = document.querySelector('input[name="rating"]:checked');
        if (initialRating) {
            updateStars(parseInt(initialRating.value));
        }
    </script>
</body>
</html>

