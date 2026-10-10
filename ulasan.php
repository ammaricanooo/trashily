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
    <header id="navbar" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl overflow-hidden group-hover:scale-105 transition-transform">
                    <img src="assets/brand.png" alt="Trashily" class="w-full h-full object-contain">
                </div>
                <span class="font-display text-xl font-extrabold text-slate-900 group-hover:text-brand-700 transition-colors">Trashily<span class="text-brand-600">.</span></span>
            </a>
            
            <!-- Desktop Navigation: Capsule with rounded border and blur at top, borderless on scroll -->
            <nav id="navLinkWrapper" class="hidden lg:flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/70 backdrop-blur-md px-5 py-2 shadow-sm transition-all duration-300">
                <a href="index.php#tentang-kami" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">Cara Kerja</a>
                <div id="layananNavTrigger" class="relative group/trigger">
                    <button type="button" class="flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 transition-colors px-3 py-1.5">
                        Layanan &amp; Inovasi <i class="fa-solid fa-chevron-down text-[10px] opacity-80 transition-transform duration-200" id="layananChevron"></i>
                    </button>
                </div>
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

        <!-- Mega Menu Dropdown: Full Width, Direct Child of Header -->
        <div id="megaMenuDropdown" class="absolute top-full left-0 right-0 w-full bg-white border-b border-slate-200 shadow-xl rounded-none opacity-0 invisible transition-all duration-200 pointer-events-none z-50 text-left before:content-[''] before:absolute before:-top-6 before:left-0 before:right-0 before:h-6 mt-0 border-t">
            <div class="max-w-5xl mx-auto px-4 md:px-8 py-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekosistem &amp; Layanan Trashily</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Inovasi Bank Sampah Digital &bull; Edukasi &bull; IoT</span>
                </div>

                <!-- 2 Kiri, 2 Kanan Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <!-- Kiri: 2 Inovasi IoT -->
                    <div class="space-y-2">
                        <!-- TrashSmart -->
                        <a href="trashsmart.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-trash-can-arrow-up"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">TrashSmart</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">Baru &bull; IoT</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Tempat sampah IoT edukatif dengan baki inspeksi, voice nudge, dan reward instan.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                        </a>

                        <!-- SmartKolecer -->
                        <a href="smartkolecer.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-fan"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">SmartKolecer</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">IoT Edukasi</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Baling-baling angin dari sampah daur ulang dengan pemantauan sensor digital.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                        </a>
                    </div>

                    <!-- Kanan: 2 Layanan & Komunitas -->
                    <div class="space-y-2">
                        <!-- Daftar Harga -->
                        <a href="harga.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-tags"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">Daftar Harga</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">Katalog</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Katalog nilai tukar poin dan rupiah transparan per kg untuk tiap kategori sampah.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                        </a>

                        <!-- Ulasan Komunitas (Sedang Dilihat) -->
                        <a href="ulasan.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-brand-300 hover:bg-brand-50/50 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-brand-950">Ulasan Komunitas</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-200 text-brand-900 border border-brand-300">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Cerita dan testimoni pengalaman nyata warga, sekolah, serta mitra Trashily.
                                </p>
                            </div>
                            <i class="fa-solid fa-check text-xs text-brand-600 self-center"></i>
                        </a>
                    </div>
                </div>

                <!-- Bottom Quick Links Strip -->
                <div class="mt-4 pt-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-500 gap-3">
                    <span class="text-slate-400 font-medium">Jelajahi Fitur Lainnya:</span>
                    <div class="flex items-center gap-6 font-semibold">
                        <a href="index.php#katalog" class="text-slate-600 hover:text-brand-600 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-recycle text-brand-600 text-xs"></i> Direktori Sampah
                        </a>
                        <a href="index.php#ekosistem" class="text-slate-600 hover:text-brand-600 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-cubes text-brand-600 text-xs"></i> Keunggulan Ekosistem
                        </a>
                        <a href="index.php#rewards" class="text-slate-600 hover:text-brand-600 transition-colors flex items-center gap-1.5">
                            <i class="fa-solid fa-gift text-brand-600 text-xs"></i> Program Hadiah
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white">
            <nav class="max-w-7xl mx-auto px-4 py-4 space-y-1">
                <a href="index.php#tentang-kami" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Cara Kerja</a>
                <a href="index.php#katalog" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Direktori Sampah</a>

                <!-- Sub-menu Section for Mobile -->
                <div class="py-2 border-b border-slate-100 space-y-1">
                    <p class="px-4 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Layanan &amp; Inovasi</p>
                    <a href="trashsmart.php" class="flex items-center justify-between px-4 py-2 text-slate-800 font-bold hover:text-brand-600">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-trash-can-arrow-up text-brand-600 text-sm"></i> TrashSmart</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-brand-100 text-brand-700">Baru</span>
                    </a>
                    <a href="smartkolecer.php" class="flex items-center justify-between px-4 py-2 text-slate-800 font-semibold hover:bg-slate-100">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-fan text-sky-600 text-sm"></i> SmartKolecer</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-sky-100 text-sky-700">IoT</span>
                    </a>
                    <a href="harga.php" class="block px-4 py-2 text-slate-800 font-semibold hover:bg-slate-100">Daftar Harga</a>
                    <a href="ulasan.php" class="block px-4 py-2 rounded-lg font-bold bg-brand-50 text-brand-700">Ulasan Pengguna</a>
                </div>
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

        /* Navbar Dynamic Scroll: blur & rounded pill at top, solid white borderless on scroll */
        function applySubpageNavbarState() {
            const nav = document.getElementById('navbar');
            const navLinkWrapper = document.getElementById('navLinkWrapper');
            const isScrolled = window.scrollY > 20;

            if (nav) {
                nav.classList.toggle('bg-white', isScrolled);
                nav.classList.toggle('border-slate-200', isScrolled);
                nav.classList.toggle('shadow-sm', isScrolled);

                nav.classList.toggle('bg-white/80', !isScrolled);
                nav.classList.toggle('border-slate-200/80', !isScrolled);
                nav.classList.toggle('backdrop-blur-xl', !isScrolled);
            }

            if (navLinkWrapper) {
                // Di paling atas: tetap blur dan rounded border pertahanin
                navLinkWrapper.classList.toggle('rounded-full', !isScrolled);
                navLinkWrapper.classList.toggle('border', !isScrolled);
                navLinkWrapper.classList.toggle('border-slate-200/80', !isScrolled);
                navLinkWrapper.classList.toggle('bg-white/70', !isScrolled);
                navLinkWrapper.classList.toggle('backdrop-blur-md', !isScrolled);
                navLinkWrapper.classList.toggle('px-5', !isScrolled);
                navLinkWrapper.classList.toggle('py-2', !isScrolled);
                navLinkWrapper.classList.toggle('shadow-sm', !isScrolled);

                // Pas di scroll: tanpa border di daftar menunya
                navLinkWrapper.classList.toggle('border-transparent', isScrolled);
                navLinkWrapper.classList.toggle('bg-transparent', isScrolled);
                navLinkWrapper.classList.toggle('shadow-none', isScrolled);
                navLinkWrapper.classList.toggle('px-0', isScrolled);
                navLinkWrapper.classList.toggle('py-0', isScrolled);
            }

            const megaMenuDropdown = document.getElementById('megaMenuDropdown');
            if (megaMenuDropdown) {
                // Di paling atas: full width dan dibuat agak kebawah dikit (mt-3 & border-t)
                // Pas di scroll: nempel flush di bawah navbar (mt-0 & border-t-0)
                megaMenuDropdown.classList.toggle('mt-0', !isScrolled);
                megaMenuDropdown.classList.toggle('border-t', !isScrolled);
                megaMenuDropdown.classList.toggle('mt-0', isScrolled);
                megaMenuDropdown.classList.toggle('border-t-0', isScrolled);
            }
        }
        applySubpageNavbarState();
        window.addEventListener('scroll', applySubpageNavbarState);

        /* Desktop Mega Menu Dropdown Interaction */
        (function initMegaMenu() {
            const trigger = document.getElementById('layananNavTrigger');
            const dropdown = document.getElementById('megaMenuDropdown');
            const chevron = document.getElementById('layananChevron');
            let timer;

            if (!trigger || !dropdown) return;

            const openMenu = () => {
                clearTimeout(timer);
                dropdown.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
                dropdown.classList.add('opacity-100', 'visible', 'pointer-events-auto');
                if (chevron) chevron.classList.add('rotate-180');
            };

            const closeMenu = () => {
                timer = setTimeout(() => {
                    dropdown.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                    dropdown.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
                    if (chevron) chevron.classList.remove('rotate-180');
                }, 150);
            };

            trigger.addEventListener('mouseenter', openMenu);
            trigger.addEventListener('mouseleave', closeMenu);
            dropdown.addEventListener('mouseenter', openMenu);
            dropdown.addEventListener('mouseleave', closeMenu);

            const triggerBtn = trigger.querySelector('button');
            if (triggerBtn) {
                triggerBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (dropdown.classList.contains('opacity-100')) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });
            }

            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && !trigger.contains(e.target)) {
                    dropdown.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                    dropdown.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
                    if (chevron) chevron.classList.remove('rotate-180');
                }
            });
        })();
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

