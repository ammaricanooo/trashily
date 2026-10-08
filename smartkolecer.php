<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartKolecer — Trashily</title>
    <meta name="description" content="SmartKolecer: kolecer IoT dengan baling-baling dari sampah daur ulang." />
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
        .page-shell { background: linear-gradient(180deg, rgba(12,123,59,0.06) 0%, rgba(248,250,252,1) 18%, rgba(248,250,252,1) 100%); }
        .soft-card { box-shadow: 0 8px 40px rgba(31,108,58,0.06); }
    </style>
</head>
<body class="page-shell">
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
                        <a href="ulasan.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-700">Ulasan</a>
                        <a href="smartkolecer.php" class="block px-3 py-2 rounded-lg text-sm font-semibold bg-brand-50 hover:bg-brand-100 text-brand-800">SmartKolecer</a>
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
                <a href="ulasan.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Ulasan</a>
                <a href="smartkolecer.php" class="block px-4 py-2.5 rounded-lg font-semibold hover:bg-brand-50 text-brand-700">SmartKolecer</a>
                <a href="index.php#ekosistem" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Keunggulan</a>
                <a href="index.php#rewards" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Hadiah</a>
                <a href="index.php#faq" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">FAQ</a>
            </nav>
        </div>
    </header>

    <script>
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });
    </script>

    <main class="max-w-7xl mx-auto px-4 py-16 md:py-20">

        <!-- HERO -->
        <section class="grid md:grid-cols-2 gap-10 items-center mb-20">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-brand-800">Inovasi Kami</span>
                <h1 class="mt-6 font-display text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">SmartKolecer, <span class="text-brand-600">baling-baling dari sampah</span> yang cerdas</h1>
                <!-- TODO: ganti dengan deskripsi singkat resmi dari tim -->
                <p class="mt-4 text-base md:text-lg text-slate-600">Kolecer berbasis IoT yang baling-balingnya dibuat dari sampah daur ulang. Sampah yang kamu setor di Trashily bisa mendapat kehidupan kedua sebagai teknologi yang berguna.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#cara-kerja-kolecer" class="inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-6 py-3 font-bold shadow-md hover:bg-brand-700 transition">Lihat Cara Kerja</a>
                    <a href="#fitur" class="inline-flex items-center gap-2 rounded-full border border-brand-600 text-brand-700 px-6 py-3 font-bold hover:bg-brand-50 transition">Fitur Utama</a>
                </div>
            </div>
            <div class="rounded-3xl bg-gradient-to-br from-brand-100 to-brand-50 border border-brand-200 aspect-[4/3] flex items-center justify-center soft-card overflow-hidden">
                <!-- TODO: ganti dengan foto produk, simpan di assets/smartkolecer.png -->
                <!-- <img src="assets/smartkolecer.png" alt="SmartKolecer" class="w-full h-full object-cover"> -->
                <i class="fa-solid fa-fan text-brand-600 text-8xl"></i>
            </div>
        </section>

        <!-- APA ITU -->
        <section class="mb-20 text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Tentang Produk</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Apa itu SmartKolecer?</h2>
            <!-- TODO: isi dengan penjelasan lengkap -->
            <p class="mt-4 mx-auto max-w-3xl text-slate-600 text-base md:text-lg">Kolecer adalah baling-baling bambu tradisional yang berputar mengikuti angin. SmartKolecer menambahkan sensor dan koneksi IoT agar putarannya bisa dipantau, sementara baling-balingnya dibuat dari sampah yang dipilah dan didaur ulang.</p>
        </section>

        <!-- CARA KERJA -->
        <section id="cara-kerja-kolecer" class="mb-20">
            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Dari Sampah ke Teknologi</p>
                <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Cara kerjanya</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <?php
                // TODO: sesuaikan langkah-langkahnya
                $langkah = [
                    ['fa-recycle',    '1. Setor sampah',       'Warga menyetor sampah terpilah ke Trashily dan mendapat poin.'],
                    ['fa-hammer',     '2. Diolah jadi baling-baling', 'Sampah plastik/kertas terpilih diolah menjadi bilah baling-baling SmartKolecer.'],
                    ['fa-wifi',       '3. Terhubung IoT',      'Sensor membaca putaran dan mengirim datanya agar bisa dipantau.'],
                ];
                foreach ($langkah as [$ikon, $judul, $isi]): ?>
                <div class="rounded-3xl border border-brand-100 bg-white p-6 soft-card">
                    <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-xl"><i class="fa-solid <?= $ikon ?>"></i></div>
                    <h3 class="mt-4 font-display text-xl font-bold text-slate-900"><?= htmlspecialchars($judul) ?></h3>
                    <p class="mt-2 text-sm md:text-base text-slate-600"><?= htmlspecialchars($isi) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- FITUR -->
        <section id="fitur" class="mb-20 rounded-3xl bg-brand-50 p-8 md:p-12">
            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Fitur Utama</p>
                <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Kenapa SmartKolecer?</h2>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                // TODO: ganti sesuai sensor dan fitur sebenarnya
                $fitur = [
                    ['fa-leaf',         'Ramah Lingkungan', 'Baling-baling dari sampah daur ulang.'],
                    ['fa-gauge-high',   'Pantau Putaran',   'Kecepatan putar terbaca real-time.'],
                    ['fa-cloud',        'Data Tersimpan',   'Data sensor dikirim ke server lewat IoT.'],
                    ['fa-battery-full', 'Hemat Energi',     'Digerakkan angin, minim daya listrik.'],
                ];
                foreach ($fitur as [$ikon, $judul, $isi]): ?>
                <div class="rounded-2xl bg-white p-6 soft-card">
                    <i class="fa-solid <?= $ikon ?> text-brand-600 text-2xl"></i>
                    <h3 class="mt-3 font-display font-bold text-slate-900"><?= htmlspecialchars($judul) ?></h3>
                    <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars($isi) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- TIM -->
        <section class="mb-20 text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Di Balik Layar</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Tim Kami</h2>
            <!-- TODO: isi nama tim dan anggota -->
            <p class="mt-4 mx-auto max-w-2xl text-slate-600">SmartKolecer dikembangkan oleh [nama tim] sebagai bagian dari ekosistem Trashily.</p>
        </section>

        <!-- CTA -->
        <section class="rounded-3xl bg-gradient-to-r from-brand-700 to-brand-600 p-8 md:p-10 text-white">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-100">Ikut berkontribusi</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold">Setor sampahmu, jadi bagian dari SmartKolecer.</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <?php if ($logged_in): ?>
                        <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-800 px-6 py-3 font-bold hover:bg-brand-50 transition">Buka Dashboard</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-800 px-6 py-3 font-bold hover:bg-brand-50 transition">Daftar Sekarang</a>
                    <?php endif; ?>
                    <a href="harga.php" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-3 font-bold text-white hover:bg-white/10 transition">Lihat Daftar Harga</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>