<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

// ===== Ubah dua baris ini setiap kali harga diperbarui =====
$periode = 'Oktober 2026';
$wilayah = 'Bogor';

// Urutan, nama tampilan, ikon, dan warna tiap kategori
$kategori_meta = [
    'kertas'         => ['Kertasan',           'fa-newspaper',   'sky'],
    'logam'          => ['Logam',              'fa-industry',    'yellow'],
    'plastik'        => ['Plastik',            'fa-bottle-water','brand'],
    'kaca'           => ['Botol Kaca',         'fa-wine-bottle', 'emerald'],
    'elektronik'     => ['Rongsok Elektronik', 'fa-plug',        'purple'],
    'organik_kering' => ['Organik Kering',     'fa-leaf',        'lime'],
    'lainnya'        => ['Lain-lain',          'fa-box',         'orange'],
];

$grouped = [];
$res = $conn->query("SELECT * FROM jenis_sampah WHERE is_active = 1 ORDER BY id");
while ($row = $res->fetch_assoc()) {
    $grouped[$row['kategori']][] = $row;
}

// Format tiap barang: [nama, harga, satuan, poin, keterangan]
$daftar_harga = [];
$total_barang = 0;
foreach ($kategori_meta as $key => [$label, $icon, $warna]) {
    if (empty($grouped[$key])) continue;
    $items = [];
    foreach ($grouped[$key] as $r) {
        $items[] = [
            $r['nama'],
            number_format((float)$r['harga_per_kg'], 0, ',', '.'),
            $r['satuan'] ?? 'Kg',
            number_format((float)$r['poin_per_kg'], 0, ',', '.'),
            $r['deskripsi'] ?? '',
        ];
    }
    $daftar_harga[$label] = ['icon' => $icon, 'warna' => $warna, 'items' => $items];
    $total_barang += count($items);
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Harga & Barang — Trashily</title>
    <meta name="description" content="Daftar harga sampah dan barang bekas di Trashily, wilayah Bogor, periode <?= htmlspecialchars($periode) ?>." />
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
                        <a href="harga.php" class="block px-3 py-2 rounded-lg text-sm font-semibold bg-brand-50 hover:bg-brand-100 text-brand-800">Daftar Harga</a>
                        <a href="ulasan.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-700">Ulasan</a>
                        <a href="smartkolecer.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-brand-700">SmartKolecer</a>
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
                <a href="harga.php" class="block px-4 py-2.5 rounded-lg font-semibold hover:bg-brand-50 text-brand-700">Daftar Harga</a>
                <a href="ulasan.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Ulasan</a>
                <a href="smartkolecer.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">SmartKolecer</a>
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
        <!-- Header -->
        <section class="mb-10 text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.2em] text-brand-800">
                <i class="fa-solid fa-calendar-days"></i> Harga <?= htmlspecialchars($periode) ?> &middot; Wilayah <?= htmlspecialchars($wilayah) ?>
            </span>
            <h1 class="mt-6 font-display text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900">Harga sampah terpilah dan barang yang diterima Trashily</h1>
            <p class="mt-4 mx-auto max-w-2xl text-base md:text-lg text-slate-600">Kami menghargai sampah yang masih layak daur ulang dengan tarif yang transparan. Ada <?= $total_barang ?> jenis barang dalam <?= count($daftar_harga) ?> kategori.</p>
        </section>

        <!-- Pencarian & lompat kategori -->
        <section class="mb-10 max-w-3xl mx-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="cariBarang" type="search" placeholder="Cari barang, misalnya: kardus, tembaga, PET..." class="w-full rounded-full border border-slate-200 bg-white pl-12 pr-5 py-3.5 text-slate-800 placeholder:text-slate-400 focus:border-brand-500 focus:outline-none soft-card">
            </div>
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <?php foreach ($daftar_harga as $nama_kat => $kat): ?>
                    <a href="#kat-<?= md5($nama_kat) ?>" class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-4 py-1.5 text-xs font-bold text-brand-800 hover:bg-brand-100 transition">
                        <i class="fa-solid <?= $kat['icon'] ?>"></i> <?= htmlspecialchars($nama_kat) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Daftar per kategori -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <?php foreach ($daftar_harga as $nama_kat => $kat): ?>
            <div id="kat-<?= md5($nama_kat) ?>" class="kategori-card rounded-3xl border border-slate-200 bg-white overflow-hidden soft-card scroll-mt-28">
                <div class="flex items-center justify-between gap-3 px-5 py-4 md:px-6 border-b border-slate-200 bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-<?= $kat['warna'] ?>-100 text-<?= $kat['warna'] ?>-700 flex items-center justify-center">
                            <i class="fa-solid <?= $kat['icon'] ?>"></i>
                        </div>
                        <h2 class="font-display font-bold text-lg text-slate-900"><?= htmlspecialchars($nama_kat) ?></h2>
                    </div>
                    <span class="text-xs font-bold text-slate-500"><?= count($kat['items']) ?> barang</span>
                </div>
                <table class="w-full">
                    <tbody>
                        <?php foreach ($kat['items'] as [$nama, $harga, $satuan, $poin, $ket]): ?>
                        <tr class="barang-row border-b border-slate-100 last:border-0 hover:bg-slate-50 transition">
                            <td class="px-5 py-3 md:px-6">
                                <div class="font-semibold text-slate-900 text-sm md:text-base"><?= htmlspecialchars($nama) ?></div>
                                <?php if ($ket !== ''): ?><div class="text-xs text-slate-500"><?= htmlspecialchars($ket) ?></div><?php endif; ?>
                            </td>
                            <td class="px-5 py-3 md:px-6 text-right whitespace-nowrap">
                                <strong class="text-brand-700 text-sm md:text-base">Rp <?= htmlspecialchars($harga) ?></strong>
                                <span class="text-xs text-slate-500">/ <?= htmlspecialchars($satuan) ?></span>
                                <div class="text-xs font-semibold text-amber-600"><i class="fa-solid fa-coins"></i> <?= htmlspecialchars($poin) ?> poin</div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </section>

        <p id="tidakAda" class="hidden mt-8 text-center text-slate-500">Barang tidak ditemukan. Coba kata kunci lain.</p>

        <p class="mt-10 text-center text-xs md:text-sm text-slate-500">
            Harga berlaku untuk wilayah <?= htmlspecialchars($wilayah) ?> periode <?= htmlspecialchars($periode) ?> dan dapat berubah sewaktu-waktu sesuai kondisi pasar.
        </p>

        <section class="mt-14 rounded-3xl bg-gradient-to-r from-brand-700 to-brand-600 p-8 md:p-10 text-white">
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

    <script>
        // Pencarian: sembunyikan baris yang tidak cocok, dan kategori yang kosong
        const cari = document.getElementById('cariBarang');
        cari.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            let totalTampil = 0;
            document.querySelectorAll('.kategori-card').forEach(card => {
                let tampil = 0;
                card.querySelectorAll('.barang-row').forEach(row => {
                    const cocok = row.textContent.toLowerCase().includes(q);
                    row.classList.toggle('hidden', !cocok);
                    if (cocok) tampil++;
                });
                card.classList.toggle('hidden', tampil === 0);
                totalTampil += tampil;
            });
            document.getElementById('tidakAda').classList.toggle('hidden', totalTampil > 0);
        });
    </script>
</body>
</html>