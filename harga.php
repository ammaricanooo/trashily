<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

$kategori_order = ['organik_kering' => 'Organik Kering', 'plastik' => 'Plastik', 'kertas' => 'Kertas', 'logam' => 'Logam', 'kaca' => 'Kaca', 'elektronik' => 'Elektronik', 'lainnya' => 'Lainnya'];
$sampah = $conn->query("SELECT * FROM jenis_sampah WHERE is_active = 1 ORDER BY kategori, nama");
$grouped = [];
while ($row = $sampah->fetch_assoc()) {
    $grouped[$row['kategori']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Harga & Barang — Trashily</title>
    <meta name="description" content="Daftar harga sampah dan barang yang bisa ditukarkan di Trashily." />
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
                        <a href="harga.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-900 bg-brand-50 hover:bg-brand-100 text-brand-800">Daftar Harga</a>
                        <a href="ulasan.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-700">Ulasan</a>
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
                <a href="harga.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-brand-50 text-brand-700">Daftar Harga</a>
                <a href="ulasan.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Ulasan</a>
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

    <main class="max-w-7xl mx-auto px-4 py-16 md:py-20">
        <section class="mb-12 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-brand-800">Daftar Harga & Barang</span>
            <h1 class="mt-6 font-display text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">Harga sampah terpilah dan barang yang diterima Trashily</h1>
            <p class="mt-4 mx-auto max-w-2xl text-base md:text-lg text-slate-600">Kami menghargai sampah yang masih layak daur ulang dengan tarif yang transparan, serta menukarkan poinmu dengan barang kebutuhan sehari-hari.</p>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white overflow-hidden shadow-card">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-slate-900">Nama Sampah</th>
                            <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-slate-900">Kategori</th>
                            <th class="px-4 py-3 md:px-6 md:py-4 text-right font-display font-bold text-sm md:text-base text-slate-900">Poin / kg</th>
                            <th class="px-4 py-3 md:px-6 md:py-4 text-right font-display font-bold text-sm md:text-base text-slate-900">Harga / kg</th>
                            <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-slate-900 hidden md:table-cell">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $kategori_icons = [
                            'organik_kering' => '🍂',
                            'plastik' => '🧴',
                            'kertas' => '📄',
                            'logam' => '🔧',
                            'kaca' => '🍶',
                            'elektronik' => '📱',
                            'lainnya' => '📦'
                        ];
                        foreach ($kategori_order as $key => $label):
                            if (!empty($grouped[$key])):
                                foreach ($grouped[$key] as $item):
                        ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-4 py-3 md:px-6 md:py-4">
                                <div class="font-semibold text-slate-900"><?= htmlspecialchars($item['nama']) ?></div>
                            </td>
                            <td class="px-4 py-3 md:px-6 md:py-4">
                                <span class="inline-flex items-center gap-1 text-sm text-slate-700">
                                    <?= $kategori_icons[$key] ?? '📦' ?>
                                    <span><?= $label ?></span>
                                </span>
                            </td>
                            <td class="px-4 py-3 md:px-6 md:py-4 text-right">
                                <strong class="text-brand-700"><?= number_format((float)$item['poin_per_kg'], 0, ',', '.') ?></strong>
                            </td>
                            <td class="px-4 py-3 md:px-6 md:py-4 text-right">
                                <strong class="text-slate-900">Rp <?= number_format((float)$item['harga_per_kg'], 0, ',', '.') ?></strong>
                            </td>
                            <td class="px-4 py-3 md:px-6 md:py-4 text-sm text-slate-600 hidden md:table-cell">
                                <?= htmlspecialchars($item['deskripsi'] ?: 'Sampah yang siap dijemput dan dipilah sesuai kategori.') ?>
                            </td>
                        </tr>
                        <?php 
                                endforeach;
                            endif;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-4 md:px-6 md:py-5 bg-slate-50 border-t border-slate-200 text-xs md:text-sm text-slate-600 text-center">
                Klik dan geser tabel untuk melihat seluruh informasi pada perangkat mobile
            </div>
        </section>

        <section class="mt-14 rounded-3xl bg-gradient-to-r from-brand-700 to-brand-600 p-8 md:p-10 text-white shadow-premium">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-100">Mulai sekarang</p>
                    <h2 class="mt-2 font-display text-3xl font-extrabold">Jadikan sampahmu produk bernilai.</h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <?php if ($logged_in): ?>
                        <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-800 px-6 py-3 font-bold hover:bg-brand-50 transition">Buka Dashboard</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-800 px-6 py-3 font-bold hover:bg-brand-50 transition">Daftar Sekarang</a>
                    <?php endif; ?>
                    <a href="index.php#cara-kerja" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-3 font-bold text-white hover:bg-white/10 transition">Lihat Cara Kerja</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
