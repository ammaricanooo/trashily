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

                <!-- Full-Width Flush Dropdown Trigger -->
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

        <!-- Full-Width Mega Menu Dropdown Flush Under Navbar -->
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

                        <!-- SmartKolecer (Sedang Dilihat) -->
                        <a href="smartkolecer.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-brand-300 hover:bg-brand-50/50 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-fan"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-brand-950">SmartKolecer</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-200 text-brand-900 border border-brand-300">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Baling-baling angin dari sampah daur ulang dengan pemantauan sensor digital.
                                </p>
                            </div>
                            <i class="fa-solid fa-check text-xs text-brand-600 self-center"></i>
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

                        <!-- Ulasan Komunitas -->
                        <a href="ulasan.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-comments"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">Ulasan Komunitas</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">Testimoni</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Cerita dan testimoni pengalaman nyata warga, sekolah, serta mitra Trashily.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
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
                    <a href="smartkolecer.php" class="flex items-center justify-between px-4 py-2 rounded-lg font-bold bg-brand-50 text-brand-700">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-fan text-sky-600 text-sm"></i> SmartKolecer</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-sky-100 text-sky-700">IoT</span>
                    </a>
                    <a href="harga.php" class="block px-4 py-2 text-slate-800 font-semibold hover:bg-slate-100">Daftar Harga</a>
                    <a href="ulasan.php" class="block px-4 py-2 text-slate-800 font-semibold hover:bg-slate-100">Ulasan Pengguna</a>
                </div>
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
            <div class="rounded-3xl bg-slate-900 border border-brand-200/40 aspect-[4/3] flex items-center justify-center soft-card overflow-hidden shadow-2xl relative group">
                <img src="assets/smartkolecer.jpg" alt="SmartKolecer - Kolecer Berbasis IoT dari Sampah Daur Ulang" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            </div>
        </section>

        <!-- APA ITU -->
        <section class="mb-20 text-center">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Tentang Produk</p>
            <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Apa itu SmartKolecer?</h2>
            <!-- TODO: isi dengan penjelasan lengkap -->
            <p class="mt-4 mx-auto max-w-3xl text-slate-600 text-base md:text-lg">Kolecer adalah baling-baling bambu tradisional yang berputar mengikuti angin. SmartKolecer menambahkan sensor dan koneksi IoT agar putarannya bisa dipantau, sementara baling-balingnya dibuat dari sampah yang dipilah dan didaur ulang.</p>
        </section>

        <!-- KENAPA SMARTKOLECER (SEJARAH) -->
        <section id="sejarah" class="mb-20">
            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Latar Belakang</p>
                <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Kenapa SmartKolecer dibuat?</h2>
                <!-- TODO: ceritakan masalah yang mendorong proyek ini -->
                <p class="mt-4 mx-auto max-w-3xl text-slate-600 text-base md:text-lg">Dari banyaknya sampah anorganik yang menumpuk, kami melihat peluang: kalau sampah bisa jadi bahan baku teknologi sederhana, nilainya tidak berhenti sebagai limbah.</p>
            </div>

            <div class="max-w-3xl mx-auto">
                <?php
                // TODO: ganti dengan perjalanan proyek yang sebenarnya
                $sejarah = [
                    ['Awal 2026',  'Ide muncul',      'Tim melihat masalah sampah plastik dan ingin membuatnya bermanfaat.'],
                    ['Pertengahan 2026', 'Prototipe pertama', 'Baling-baling pertama dibuat dari sampah dan diuji di lapangan.'],
                    ['Akhir 2026', 'Terhubung IoT',   'Sensor ditambahkan agar putaran bisa dipantau, lalu diintegrasikan dengan Trashily.'],
                ];
                foreach ($sejarah as $i => [$waktu, $judul, $isi]): ?>
                <div class="relative pl-10 pb-8 last:pb-0">
                    <?php if ($i < count($sejarah) - 1): ?>
                    <span class="absolute left-[15px] top-8 bottom-0 w-0.5 bg-brand-200"></span>
                    <?php endif; ?>
                    <span class="absolute left-0 top-1 w-8 h-8 rounded-full bg-brand-600 text-white text-xs font-bold flex items-center justify-center"><?= $i + 1 ?></span>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-brand-700"><?= htmlspecialchars($waktu) ?></p>
                    <h3 class="mt-1 font-display text-xl font-bold text-slate-900"><?= htmlspecialchars($judul) ?></h3>
                    <p class="mt-1 text-sm md:text-base text-slate-600"><?= htmlspecialchars($isi) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- PERBEDAAN DENGAN KOLECER BIASA -->
        <section id="perbedaan" class="mb-20">
            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Bedanya Apa?</p>
                <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">SmartKolecer vs Kolecer Biasa</h2>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden soft-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-slate-900">Aspek</th>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-slate-500">Kolecer Biasa</th>
                                <th class="px-4 py-3 md:px-6 md:py-4 text-left font-display font-bold text-sm md:text-base text-brand-700">SmartKolecer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // TODO: sesuaikan dengan spesifikasi produk yang sebenarnya
                            $beda = [
                                ['Bahan baling-baling', 'Bambu atau kayu baru',      'Sampah plastik/kertas daur ulang'],
                                ['Pemantauan',          'Dilihat langsung di tempat', 'Terpantau lewat sensor IoT'],
                                ['Data',                'Tidak ada',                  'Putaran tercatat dan tersimpan'],
                                ['Dampak lingkungan',   'Menebang bahan alam',        'Mengurangi sampah di lingkungan'],
                            ];
                            foreach ($beda as [$aspek, $biasa, $smart]): ?>
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3 md:px-6 md:py-4 font-semibold text-slate-900"><?= htmlspecialchars($aspek) ?></td>
                                <td class="px-4 py-3 md:px-6 md:py-4 text-sm text-slate-500"><i class="fa-solid fa-xmark text-slate-300 mr-2"></i><?= htmlspecialchars($biasa) ?></td>
                                <td class="px-4 py-3 md:px-6 md:py-4 text-sm font-semibold text-brand-800 bg-brand-50/50"><i class="fa-solid fa-check text-brand-600 mr-2"></i><?= htmlspecialchars($smart) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
        <section id="tim" class="mb-20">
            <div class="text-center mb-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-700">Di Balik Layar</p>
                <h2 class="mt-2 font-display text-3xl md:text-4xl font-extrabold text-slate-900">Tim Kami</h2>
                <p class="mt-4 mx-auto max-w-2xl text-slate-600">Orang-orang yang membuat SmartKolecer dan Trashily berjalan.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php
                // TODO: isi nama, peran, dan nama file foto (simpan di assets/tim/)
                $tim = [
                    ['Nur Komalasari', 'Ketua Tim',      'anggota1.jpg'],
                    ['Justine', 'Pengembang IoT', 'anggota2.jpg'],
                    ['Aprilia', 'Desain Produk',  'anggota3.jpg'],
                    ['M Phazri Septian', 'Pengembang Web', 'anggota4.jpg'],
                     ['Hani', 'Pengembang Web', 'anggota4.jpg'],
                ];
                foreach ($tim as [$nama, $peran, $foto]):
                    $path = 'assets/tim/' . $foto;
                    $ada  = file_exists(__DIR__ . '/' . $path);
                    $inisial = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nama), 0, 2)) ?: 'T';
                ?>
                <div class="rounded-3xl border border-brand-100 bg-white overflow-hidden soft-card text-center group">
                    <div class="aspect-square bg-brand-50 overflow-hidden">
                        <?php if ($ada): ?>
                            <img src="<?= htmlspecialchars($path) ?>" alt="<?= htmlspecialchars($nama) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center font-display font-extrabold text-5xl text-brand-300"><?= $inisial ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4">
                        <h3 class="font-display font-bold text-slate-900"><?= htmlspecialchars($nama) ?></h3>
                        <p class="mt-1 text-xs font-semibold text-brand-700"><?= htmlspecialchars($peran) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SINERGI TRASHSMART -->
        <section class="mb-20 rounded-3xl bg-emerald-50 border border-emerald-200 p-8 md:p-12 soft-card">
            <div class="grid md:grid-cols-12 gap-8 items-center">
                <div class="md:col-span-8 space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs uppercase tracking-wider">
                        <i class="fa-solid fa-link text-emerald-600"></i> Integrasi Hulu ke Hilir
                    </span>
                    <h2 class="font-display text-2xl md:text-3xl font-extrabold text-slate-900">
                        Dipilah Higienis Lewat TrashSmart
                    </h2>
                    <p class="text-slate-600 text-sm md:text-base leading-relaxed">
                        Bahan baku bilah SmartKolecer dipasok dari plastik berkualitas prima yang telah terverifikasi bersih dan bebas bau melalui tempat sampah pintar <strong>TrashSmart</strong> di sekolah dan ruang publik.
                    </p>
                    <div class="pt-2">
                        <a href="trashsmart.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 text-white font-bold text-sm shadow hover:bg-emerald-700 transition">
                            <i class="fa-solid fa-trash-can-arrow-up"></i> Pelajari TrashSmart IoT &rarr;
                        </a>
                    </div>
                </div>
                <div class="md:col-span-4 flex justify-center">
                    <div class="w-36 h-36 rounded-3xl overflow-hidden border border-emerald-300 shadow-md">
                        <img src="assets/trashsmart.jpg" alt="TrashSmart" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
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

    <!-- FOOTER -->
    <footer class="mt-20 bg-slate-900 text-slate-400 py-12 px-4 md:px-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <p>&copy; <?= date('Y') ?> <strong>Trashily</strong>. Hak Cipta Dilindungi.</p>
            <div class="flex items-center gap-4">
                <a href="trashsmart.php" class="text-slate-300 hover:text-white transition">TrashSmart</a>
                <a href="smartkolecer.php" class="text-slate-300 hover:text-white transition">SmartKolecer</a>
                <a href="harga.php" class="text-slate-300 hover:text-white transition">Daftar Harga</a>
                <a href="ulasan.php" class="text-slate-300 hover:text-white transition">Ulasan</a>
            </div>
        </div>
    </footer>
</body>
</html>