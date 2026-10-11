<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url  = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrashSmart — Tempat Sampah IoT Berjiwa Edukator | Trashily</title>
    <meta name="description" content="TrashSmart: Tempat sampah pintar 1 meter dual kompartemen dengan baki inspeksi, behavioral nudge suara ramah, dan reward poin otomatis ke ekosistem Trashily." />

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
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#006e2f'
                        },
                        deep: '#0b1c30'
                    },
                    fontFamily: {
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                        body: ['Be Vietnam Pro', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .page-shell {
            background: radial-gradient(100% 50% at 50% 0%, rgba(22, 163, 74, 0.08) 0%, rgba(248, 250, 252, 1) 100%);
        }

        .soft-card {
            box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.07);
        }

        .sound-wave-bar {
            animation: soundBounce 1.2s ease-in-out infinite alternate;
        }

        @keyframes soundBounce {
            0% { height: 6px; }
            50% { height: 28px; }
            100% { height: 12px; }
        }

        .sound-wave-bar:nth-child(2) { animation-delay: 0.15s; }
        .sound-wave-bar:nth-child(3) { animation-delay: 0.3s; }
        .sound-wave-bar:nth-child(4) { animation-delay: 0.45s; }
        .sound-wave-bar:nth-child(5) { animation-delay: 0.2s; }
        .sound-wave-bar:nth-child(6) { animation-delay: 0.35s; }
    </style>
</head>

<body class="page-shell selection:bg-brand-500 selection:text-white">

    <!-- ==================== HEADER & MEGA MENU NAVBAR ==================== -->
    <header id="navbar" class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-3.5 flex items-center justify-between">
            <!-- Brand Logo -->
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

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="hidden sm:inline-flex px-4 py-2 rounded-full text-slate-700 hover:text-brand-700 font-semibold text-sm">Masuk</a>
                    <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">Daftar Gratis</a>
                <?php endif; ?>

                <!-- Burger for Mobile -->
                <button id="mobileMenuBtn" class="lg:hidden text-slate-700 hover:text-brand-700 p-2" aria-label="Menu">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
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
                        <!-- TrashSmart (Sedang Dilihat) -->
                        <a href="trashsmart.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-brand-300 hover:bg-brand-50/50 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-trash-can-arrow-up"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-brand-950">TrashSmart</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-200 text-brand-900 border border-brand-300">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Tempat sampah IoT edukatif dengan baki inspeksi, voice nudge, dan reward instan.
                                </p>
                            </div>
                            <i class="fa-solid fa-check text-xs text-brand-600 self-center"></i>
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

                <!-- Sub-menu Section for Inovasi & Layanan -->
                <div class="pt-2 pb-2">
                    <p class="px-4 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Layanan &amp; Inovasi</p>
                    <a href="trashsmart.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-bold bg-brand-50 text-brand-700 mt-1">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-trash-can-arrow-up text-brand-600"></i> TrashSmart</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-brand-200 text-brand-800">Baru</span>
                    </a>
                    <a href="smartkolecer.php" class="flex items-center justify-between px-4 py-2.5 rounded-lg font-semibold text-slate-800 hover:bg-slate-100">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-fan text-sky-600"></i> SmartKolecer</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-sky-100 text-sky-700">IoT</span>
                    </a>
                    <a href="harga.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Daftar Harga</a>
                    <a href="ulasan.php" class="block px-4 py-2.5 rounded-lg text-slate-800 font-semibold hover:bg-slate-100">Ulasan Pengguna</a>
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
    </script>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main class="max-w-7xl mx-auto px-4 md:px-8 py-12 md:py-20 space-y-24 md:space-y-32">

        <!-- ==================== 1. HERO SECTION ==================== -->
        <section class="grid lg:grid-cols-12 gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-brand-50 px-3.5 py-1.5 text-xs font-extrabold uppercase tracking-wider text-brand-800">
                        <i class="fa-solid fa-sparkles text-brand-600"></i> Inovasi Baru Ekosistem Trashily
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                        <i class="fa-solid fa-microchip text-teal-600"></i> IoT Smart Bin &bull; Behavioral Nudge
                    </span>
                </div>

                <!-- Main Title -->
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.12]">
                    TrashSmart: <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-brand-600 via-emerald-600 to-teal-600 bg-clip-text text-transparent">
                        Tempat Sampah Pintar Berjiwa Edukator
                    </span>
                </h1>

                <!-- Subtitle / Hook -->
                <p class="text-base sm:text-lg md:text-xl text-slate-600 leading-relaxed font-normal">
                    Bukan sekadar kotak sampah bermesin. TrashSmart memadukan kecerdasan teknologi IoT, edukasi perilaku manusia (<em class="font-semibold text-slate-800 not-italic">behavioral nudge</em>), dan timbangan reward instan yang membuktikan aksi memilah sampah bisa menjadi kebiasaan yang menyenangkan, mendidik, dan berdampak nyata.
                </p>

                <!-- Key Highlights Quick Stats -->
                <div class="grid grid-cols-3 gap-3 pt-2">
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 soft-card">
                        <p class="text-xs text-slate-500 font-medium">Dimensi Fisik</p>
                        <p class="font-display font-bold text-lg text-slate-900 mt-0.5">Tinggi 1 Meter</p>
                        <p class="text-[11px] text-brand-700 font-semibold">Dual Kompartemen</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 soft-card">
                        <p class="text-xs text-slate-500 font-medium">Bilik Atas</p>
                        <p class="font-display font-bold text-lg text-slate-900 mt-0.5">Baki Inspeksi</p>
                        <p class="text-[11px] text-emerald-700 font-semibold">Sensor Guru Pintar</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 soft-card">
                        <p class="text-xs text-slate-500 font-medium">Feedback</p>
                        <p class="font-display font-bold text-lg text-slate-900 mt-0.5">Voice Nudge</p>
                        <p class="text-[11px] text-teal-700 font-semibold">Teguran Real-Time</p>
                    </div>
                </div>

                <!-- CTA Action Buttons -->
                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <a href="#narasi-cerita" class="inline-flex items-center gap-2.5 rounded-full bg-brand-600 text-white px-7 py-3.5 font-bold shadow-lg shadow-brand-600/25 hover:bg-brand-700 hover:shadow-xl hover:-translate-y-0.5 transition-all text-sm md:text-base">
                        <i class="fa-solid fa-book-open-reader"></i> Baca Narasi Lengkap
                    </a>
                    <a href="#simulasi-suara" class="inline-flex items-center gap-2.5 rounded-full border border-slate-300 bg-white text-slate-800 px-6 py-3.5 font-bold shadow-sm hover:bg-slate-50 hover:border-slate-400 transition-all text-sm md:text-base">
                        <i class="fa-solid fa-volume-high text-brand-600"></i> Uji Simulasi Suara
                    </a>
                </div>
            </div>

            <!-- Hero Image & Interactive Card Showcase -->
            <div class="lg:col-span-5 relative">
                <div class="relative rounded-3xl overflow-hidden border border-slate-200/90 bg-white shadow-2xl soft-card group">
                    <div class="relative aspect-[4/3] sm:aspect-[1/1] overflow-hidden bg-slate-100">
                        <img src="assets/trashsmart.jpg" alt="TrashSmart Unit Fisik di Kantin Sekolah" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        <!-- Live Badge Floating on Image -->
                        <div class="absolute top-4 left-4 flex items-center gap-2 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/40 shadow-sm text-xs font-bold text-slate-800">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>Prototipe IoT Aktif</span>
                        </div>

                        <!-- Dual Compartment Tag Floating -->
                        <div class="absolute top-4 right-4 flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[11px] font-extrabold shadow">Organik</span>
                            <span class="px-2.5 py-1 rounded-full bg-amber-500 text-white text-[11px] font-extrabold shadow">Anorganik</span>
                        </div>

                        <!-- Bottom Captions on Image -->
                        <div class="absolute bottom-4 left-4 right-4 text-white">
                            <p class="font-display font-extrabold text-lg text-white">TrashSmart Unit 01</p>
                            <p class="text-xs text-slate-200 mt-0.5">Penempatan pilot project di kantin sekolah &amp; ruang publik terpadu.</p>
                        </div>
                    </div>

                    <!-- Hardware Highlights under Image -->
                    <div class="p-4 bg-slate-50/90 border-t border-slate-100 grid grid-cols-2 gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-qrcode text-brand-600"></i>
                            <span class="text-slate-700 font-medium">QR Code Sync Trashily</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-scale-balanced text-brand-600"></i>
                            <span class="text-slate-700 font-medium">Load Cell Gram Presisi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-brand-600"></i>
                            <span class="text-slate-700 font-medium">Modul Suara Ramah</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-tower-broadcast text-brand-600"></i>
                            <span class="text-slate-700 font-medium">Telemetri Ultrasonik IoT</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 2. LATAR BELAKANG / KERESAHAN NYATA ==================== -->
        <section id="narasi-cerita" class="relative scroll-mt-24">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 font-bold text-xs uppercase tracking-widest inline-flex items-center gap-1.5 mb-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> Keresahan Nyata di Lapangan
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Mengapa Tempat Sampah Biasa Selalu Berakhir Gagal?
                </h2>
                <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                    Masalahnya bukan karena masyarakat enggan peduli, melainkan sistem pemilahan konvensional yang pasif dan bisu.
                </p>
            </div>

            <!-- Narrative Cards Layout -->
            <div class="grid lg:grid-cols-12 gap-8 items-stretch">
                <!-- Left: Story & Quote -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/80 soft-card flex flex-col justify-between space-y-6">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                            <i class="fa-solid fa-dumpster"></i>
                        </div>
                        <h3 class="font-display text-2xl font-bold text-slate-900">
                            Realita di Sudut Kantin &amp; Ruang Publik
                        </h3>
                        <p class="text-slate-600 leading-relaxed text-base sm:text-lg">
                            Di sebuah sudut kantin sekolah atau ruang publik, tempat sampah bertuliskan <strong>"Organik"</strong> dan <strong>"Anorganik"</strong> sering kali berakhir menyedihkan: keduanya sama-sama dipenuhi botol plastik yang tercampur sisa kuah makanan basah.
                        </p>
                        <p class="text-slate-600 leading-relaxed text-base">
                            Ketika botol plastik tercampur kuah makanan berminyak, nilainya jatuh drastis dan baunya membusuk. Tempat sampah konvensional itu pasif—<strong>tidak ada teguran</strong> saat orang salah memasukkan, <strong>tidak ada penghargaan</strong> saat mereka memilah benar, dan tidak ada yang peduli ketika sampah salah tempat.
                        </p>
                    </div>

                    <!-- Highlighted Callout -->
                    <div class="p-5 rounded-2xl bg-amber-50/80 border border-amber-200/90 text-amber-950">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-lightbulb text-amber-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="font-bold text-sm text-amber-900">Akar Masalah Bukan Pengetahuan, Tapi Interaksi</p>
                                <p class="text-xs sm:text-sm text-amber-900/80 mt-1 leading-relaxed">
                                    Masyarakat tahu apa itu botol dan apa itu sisa makanan. Tetapi tanpa interaksi edukatif langsung di detik pembuangan (*point-of-action*), kebiasaan salah akan selalu terulang.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Hard Data Facts in Bogor & Problem Breakdown -->
                <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 to-slate-950 text-white rounded-3xl p-8 sm:p-10 shadow-xl flex flex-col justify-between space-y-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-rose-400">Statistik Nyata Kota Bogor</span>
                        <div class="mt-4 flex items-baseline gap-3">
                            <span class="font-display text-5xl sm:text-6xl font-extrabold text-white">&lt; 8%</span>
                            <span class="text-rose-300 font-semibold text-sm">Berhasil didaur ulang</span>
                        </div>
                        <p class="mt-3 text-slate-300 text-sm leading-relaxed">
                            Di Kota Bogor saja, ratusan ribu ton timbulan sampah diproduksi setiap tahun, tetapi yang berhasil diserap industri daur ulang belum sampai 8%. Mayoritas terkontaminasi dan tertimbun di TPA.
                        </p>
                    </div>

                    <!-- 3 Flaws of Conventional Trash Bins -->
                    <div class="space-y-3.5 border-t border-slate-800 pt-6">
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0 text-xs mt-0.5 font-bold">1</span>
                            <div>
                                <p class="font-bold text-sm text-white">Tanpa Teguran Sesaat</p>
                                <p class="text-xs text-slate-400">Orang membuang asal lempar tanpa ada feedback korektif langsung.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0 text-xs mt-0.5 font-bold">2</span>
                            <div>
                                <p class="font-bold text-sm text-white">Tanpa Penghargaan (No Reward)</p>
                                <p class="text-xs text-slate-400">Aksi memilah benar tidak memberikan apresiasi apapun kepada pemilah.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center shrink-0 text-xs mt-0.5 font-bold">3</span>
                            <div>
                                <p class="font-bold text-sm text-white">Kontaminasi Silang Fatal</p>
                                <p class="text-xs text-slate-400">Kuah sisa makanan merusak seluruh kertas dan plastik dalam wadah anorganik.</p>
                            </div>
                        </div>
                    </div>

                    <!-- The Transition -->
                    <div class="bg-white/10 rounded-2xl p-4 border border-white/15">
                        <p class="text-xs text-emerald-300 font-bold uppercase tracking-wider">Solusi Terobosan</p>
                        <p class="text-sm font-semibold text-white mt-1">
                            Dari keresahan nyata itulah <span class="text-emerald-400 font-extrabold">TrashSmart</span> dilahirkan oleh Trashily.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 3. ANATOMI HARDWARE TRASHSMART ==================== -->
        <section id="anatomi-alat" class="scroll-mt-24">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-3.5 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest inline-flex items-center gap-1.5 mb-3">
                    <i class="fa-solid fa-cube"></i> Spesifikasi &amp; Desain Fisik
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Anatomi Fisik TrashSmart Setinggi 1 Meter
                </h2>
                <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                    Dirancang dengan rangka kokoh, ergonomis untuk siswa sekolah maupun masyarakat umum, dan tidak membiarkan siapa pun sekadar melempar sampah lalu pergi begitu saja.
                </p>
            </div>

            <!-- Hardware Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Component 1: Dual Kompartemen Hijau & Kuning -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-door-open"></i>
                            </div>
                            <div class="flex gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-600 text-white">Hijau</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">Kuning</span>
                            </div>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Dual Pintu Kompartemen</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Rangka kokoh setinggi satu meter dengan dua wadah terisolasi: <strong>Hijau untuk sampah organik</strong> (sisa makanan basah, daun) dan <strong>Kuning untuk sampah anorganik</strong> (botol PET, gelas plastik, kaleng daur ulang).
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Mencegah kontaminasi silang bau busuk
                    </div>
                </div>

                <!-- Component 2: Stiker QR Code Unik Trashily -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-qrcode"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">Kamera HP</span>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Stiker QR Code Trashily</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Terletak di bagian atas bodi alat. Pengguna cukup memindai stiker dengan kamera ponsel untuk langsung menghubungkan sesi pembuangan ke akun web platform Trashily secara instan.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Membuka pintu corong atas secara otomatis
                    </div>
                </div>

                <!-- Component 3: Baki Inspeksi Pintar IoT -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">Guru Interaktif</span>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Baki Inspeksi Pintar</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Bilik penahan di bagian atas sebelum sampah jatuh ke dasar. Sensor deteksi membaca karakteristik objek yang baru ditaruh untuk memastikan tidak ada salah wadah ataupun kecurangan.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Mengunci rapat jika sampah tidak sesuai
                    </div>
                </div>

                <!-- Component 4: Modul Suara Behavioral Nudge -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-volume-high"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">Real-Time Voice</span>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Modul Suara Ramah</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Speaker pintar yang seketika menyuarakan arahan edukatif real-time saat sensor mendeteksi sampah salah kamar, memberikan sentuhan psikologis (*behavioral nudge*) di saat itu juga.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Edukasi santun tanpa menghakimi
                    </div>
                </div>

                <!-- Component 5: Sensor Timbangan Load Cell -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-weight-scale"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Presisi Gram</span>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Load Cell Timbangan Dasar</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Modul sensor timbangan di lantai dasar wadah membaca selisih berat bersih sampah yang meluncur dari baki ayun secara akurat untuk dikonversi menjadi reward poin instan.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Terkirim ke server dalam hitungan detik
                    </div>
                </div>

                <!-- Component 6: Sensor Ultrasonik Telemetri -->
                <div class="bg-white rounded-3xl p-7 border border-slate-200/90 soft-card hover:border-brand-300 hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-tower-broadcast"></i>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Telemetri Cloud</span>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900">Ultrasonik Sensor Atap</h3>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                            Sensor ketinggian memantau volume penampungan. Ketika kompartemen hampir penuh, notifikasi otomatis terkirim ke petugas kebersihan atau pengelola bank sampah.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center gap-2">
                        <i class="fa-solid fa-check text-brand-600"></i> Tidak pernah ada sampah meluap kotor
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 4. ALUR INTERAKSI STEP-BY-STEP ==================== -->
        <section id="alur-interaksi" class="scroll-mt-24">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-3.5 py-1.5 rounded-full bg-teal-50 border border-teal-200 text-teal-800 font-bold text-xs uppercase tracking-widest inline-flex items-center gap-1.5 mb-3">
                    <i class="fa-solid fa-route"></i> Alur Pemilahan Cerdas
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Bagaimana TrashSmart Bekerja Langkah demi Langkah?
                </h2>
                <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                    Alur interaksi dimulai dari genggaman tangan pengguna hingga poin reward mengalir ke kantong digital.
                </p>
            </div>

            <!-- Steps Journey -->
            <div class="space-y-6 relative before:absolute before:inset-0 before:left-8 md:before:left-1/2 before:w-0.5 before:-translate-x-1/2 before:bg-gradient-to-b before:from-brand-500 before:via-teal-400 before:to-emerald-600">

                <!-- Step 1 -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="bg-white p-7 rounded-3xl border border-slate-200/90 soft-card">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-brand-700 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">Langkah 1 &bull; Inisiasi</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Scan QR Code di Bodi Alat via Ponsel</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Pengguna cukup mengarahkan kamera smartphone ke stiker kode QR unik di bagian atas TrashSmart. Seketika akun platform web <strong>Trashily</strong> terhubung, pintu corong atas terbuka, dan sesi pemilahan resmi dimulai.
                            </p>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-brand-600 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-brand-600/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        1
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="p-5 rounded-2xl bg-brand-50/70 border border-brand-200/80 text-xs text-brand-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-mobile-screen mr-1.5 text-brand-600"></i> Seamless Handshake</p>
                            <p class="text-slate-600">Tidak perlu download aplikasi baru. Cukup browser smartphone yang terdaftar di akun Trashily.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="p-5 rounded-2xl bg-teal-50/70 border border-teal-200/80 text-xs text-teal-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-eye mr-1.5 text-teal-600"></i> Inspeksi Sebelum Masuk</p>
                            <p class="text-slate-600">Sampah tidak langsung jatuh bercampur. Ada jeda analisa sensor di bilik baki atas.</p>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-teal-600 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-teal-600/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        2
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="bg-white p-7 rounded-3xl border border-slate-200/90 soft-card">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-teal-700 bg-teal-50 px-3 py-1 rounded-full border border-teal-200">Langkah 2 &bull; Deteksi</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Baki Inspeksi Pintar Menganalisis Objek</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Sampah diletakkan di atas baki inspeksi pintar. Di bilik inilah teknologi IoT bekerja sebagai <em>"Guru Lingkungan Interaktif"</em>. Sensor membaca karakteristik fisik dan jenis material objek yang baru ditaruh.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: THE HIGHLIGHT BEHAVIORAL NUDGE -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="bg-white p-7 rounded-3xl border-2 border-brand-500 shadow-xl soft-card relative overflow-hidden">
                            <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-brand-100 rounded-full blur-2xl pointer-events-none"></div>
                            <span class="text-xs font-extrabold uppercase tracking-widest text-rose-700 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">Langkah 3 &bull; Inti Behavioral Nudge</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Teguran Ramah Real-Time Saat Salah Kamar</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Jika seseorang berniat membuang botol plastik tetapi keliru di sisi organik—atau sebaliknya, memasukkan sisa makanan basah ke anorganik—alat tidak diam. Baki <strong>tetap terkunci rapat</strong> dan modul suara menyuarakan teguran ramah secara langsung:
                            </p>
                            <div class="mt-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-left">
                                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fa-solid fa-volume-high text-amber-600"></i> Suara Modul TrashSmart:
                                </p>
                                <blockquote class="mt-1 text-sm font-semibold italic text-amber-950">
                                    "Peringatan! Ini terdeteksi sampah organik, mohon dipindahkan ke wadah hijau di sebelah ya!"
                                </blockquote>
                            </div>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500 to-rose-500 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-amber-500/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        3
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="p-5 rounded-2xl bg-rose-50/70 border border-rose-200/80 text-xs text-rose-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-brain mr-1.5 text-rose-600"></i> Sentuhan Psikologis Edukatif</p>
                            <p class="text-slate-600">Mendidik pengguna tepat di titik pembuangan agar terbiasa memilah dengan sadar tanpa rasa dipermalukan.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-xs text-emerald-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-scale-balanced mr-1.5 text-emerald-600"></i> Anti-Kecurangan Bobot</p>
                            <p class="text-slate-600">Load cell mengukur berat bersih selisih gram saat baki ayun meluncurkan muatan ke wadah bawah.</p>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-emerald-600 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-emerald-600/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        4
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="bg-white p-7 rounded-3xl border border-slate-200/90 soft-card">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">Langkah 4 &bull; Verifikasi</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Baki Ayun Terbuka &amp; Timbangan Load Cell Aktif</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Begitu jenis sampah dipastikan benar dan tidak ada upaya manipulasi, mekanisme baki ayun otomatis terbuka. Sampah meluncur ke wadah bawah, dan modul sensor timbangan (*load cell*) langsung membaca selisih berat bersih sampah yang masuk.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="bg-white p-7 rounded-3xl border border-slate-200/90 soft-card">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200">Langkah 5 &bull; Reward</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Sinkronisasi Cloud &amp; Poin Reward Cair</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Dalam hitungan detik, mikrokontroler mengirim data berat tersebut ke sistem platform Trashily. Saldo poin reward langsung bertambah di akun pengguna. Di lingkungan sekolah, poin ini dapat ditukar menjadi:
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200"><i class="fa-solid fa-utensils text-brand-600 mr-1"></i> Voucher Kantin</span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200"><i class="fa-solid fa-pen-ruler text-teal-600 mr-1"></i> Alat Koperasi</span>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200"><i class="fa-solid fa-star text-amber-500 mr-1"></i> Nilai Karakter Lingkungan</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-indigo-600 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-indigo-600/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        5
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200/80 text-xs text-indigo-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-gift mr-1.5 text-indigo-600"></i> Insentif Ekonomi Nyata</p>
                            <p class="text-slate-600">Memberikan kepuasan instan (*instant gratification*) yang memperkuat kebiasaan memilah berkelanjutan.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="relative flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/2 md:text-right pr-0 md:pr-10 order-2 md:order-1">
                        <div class="p-5 rounded-2xl bg-sky-50/70 border border-sky-200/80 text-xs text-sky-900 space-y-1.5">
                            <p class="font-bold"><i class="fa-solid fa-recycle mr-1.5 text-sky-600"></i> 100% Kering &amp; Bersih</p>
                            <p class="text-slate-600">Siap didistribusikan ke bank sampah atau dijadikan bilah baling-baling SmartKolecer tanpa bau.</p>
                        </div>
                    </div>
                    <div class="w-16 h-16 rounded-full bg-sky-600 text-white flex items-center justify-center font-display font-extrabold text-xl shadow-lg shadow-sky-600/30 z-10 shrink-0 order-1 md:order-2 ring-8 ring-white">
                        6
                    </div>
                    <div class="w-full md:w-1/2 pl-0 md:pl-10 order-3">
                        <div class="bg-white p-7 rounded-3xl border border-slate-200/90 soft-card">
                            <span class="text-xs font-extrabold uppercase tracking-widest text-sky-700 bg-sky-50 px-3 py-1 rounded-full border border-sky-200">Langkah 6 &bull; Sirkular</span>
                            <h3 class="font-display text-xl font-bold text-slate-900 mt-3">Telemetri Penuh &amp; Distribusi Daur Ulang Higienis</h3>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Ketika kompartemen hampir penuh, sensor ultrasonik atap otomatis mengirim notifikasi telemetri ke petugas. Sampah plastik yang terkumpul dipastikan <strong>100% kering, bersih</strong>, dan siap disalurkan langsung tanpa risiko bau busuk.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ==================== 5. SIMULASI SUARA INTERAKTIF (BEHAVIORAL NUDGE) ==================== -->
        <section id="simulasi-suara" class="scroll-mt-24 rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-brand-950 p-8 sm:p-12 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                <div class="text-center space-y-3 mb-10">
                    <span class="px-3 py-1 rounded-full bg-white/10 text-brand-300 font-bold text-xs uppercase tracking-widest border border-white/20 inline-flex items-center gap-2">
                        <i class="fa-solid fa-ear-listen"></i> Live Audio Simulator
                    </span>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-white">
                        Dengarkan Langsung Sentuhan "Behavioral Nudge"
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base max-w-2xl mx-auto">
                        Klik tombol simulasi di bawah untuk menguji respon modul suara ramah TrashSmart saat menghadapi berbagai skenario pembuangan sampah.
                    </p>
                </div>

                <!-- Audio Visualizer Display Box -->
                <div class="bg-black/40 backdrop-blur-md rounded-2xl p-6 border border-white/10 mb-8">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <!-- Visualizer bars -->
                            <div id="visualizerBox" class="flex items-end gap-1.5 h-10 px-3 py-1 bg-white/5 rounded-xl border border-white/10">
                                <div class="w-2 bg-brand-400 rounded-full sound-wave-bar" style="height: 10px;"></div>
                                <div class="w-2 bg-emerald-400 rounded-full sound-wave-bar" style="height: 16px;"></div>
                                <div class="w-2 bg-teal-400 rounded-full sound-wave-bar" style="height: 24px;"></div>
                                <div class="w-2 bg-brand-300 rounded-full sound-wave-bar" style="height: 14px;"></div>
                                <div class="w-2 bg-emerald-500 rounded-full sound-wave-bar" style="height: 20px;"></div>
                                <div class="w-2 bg-teal-300 rounded-full sound-wave-bar" style="height: 12px;"></div>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Status Speaker TrashSmart</p>
                                <p id="audioStatusText" class="text-sm font-bold text-brand-300 mt-0.5">Siap Memutar Simulasi Suara</p>
                            </div>
                        </div>

                        <!-- Subtitle pill -->
                        <div class="w-full sm:w-auto text-center sm:text-right">
                            <span id="speakerBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 border border-brand-500/30 text-xs font-semibold">
                                <i class="fa-solid fa-volume-low"></i> Audio Engine Aktif
                            </span>
                        </div>
                    </div>

                    <!-- Transcript subtitle -->
                    <div class="mt-4 pt-4 border-t border-white/10 text-center sm:text-left">
                        <p class="text-xs text-slate-400">Teks Ujaran:</p>
                        <p id="audioTranscriptText" class="font-display font-medium text-base text-white mt-1 italic">
                            "Pilih salah satu skenario di bawah untuk mendengarkan modul suara pintar..."
                        </p>
                    </div>
                </div>

                <!-- 3 Interactive Simulation Trigger Cards -->
                <div class="grid sm:grid-cols-3 gap-4">
                    <!-- Scenario 1 -->
                    <button type="button" onclick="playVoiceScenario(1)" class="group text-left p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-amber-400/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-play"></i>
                            </span>
                            <span class="text-[10px] uppercase font-bold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">Salah Kamar #1</span>
                        </div>
                        <p class="font-display font-bold text-sm text-white group-hover:text-amber-300 transition-colors">Sampah Organik Keliru</p>
                        <p class="text-xs text-slate-300 mt-1 line-clamp-2">Sisa makanan basah dimasukkan ke kompartemen anorganik kuning.</p>
                    </button>

                    <!-- Scenario 2 -->
                    <button type="button" onclick="playVoiceScenario(2)" class="group text-left p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-rose-400/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-play"></i>
                            </span>
                            <span class="text-[10px] uppercase font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">Salah Kamar #2</span>
                        </div>
                        <p class="font-display font-bold text-sm text-white group-hover:text-rose-300 transition-colors">Sampah Plastik Keliru</p>
                        <p class="text-xs text-slate-300 mt-1 line-clamp-2">Botol plastik minuman ditaruh ke sisi wadah organik hijau.</p>
                    </button>

                    <!-- Scenario 3 -->
                    <button type="button" onclick="playVoiceScenario(3)" class="group text-left p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 hover:border-emerald-400/50 transition-all">
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-sm font-bold group-hover:scale-110 transition-transform">
                                <i class="fa-solid fa-play"></i>
                            </span>
                            <span class="text-[10px] uppercase font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">Selesai &amp; Reward</span>
                        </div>
                        <p class="font-display font-bold text-sm text-white group-hover:text-emerald-300 transition-colors">Pemilahan Berhasil</p>
                        <p class="text-xs text-slate-300 mt-1 line-clamp-2">Botol anorganik terverifikasi valid, bobot dicatat, poin reward cair.</p>
                    </button>
                </div>
            </div>
        </section>

        <!-- ==================== 6. TIGA PILAR NILAI TRASHSMART ==================== -->
        <section class="scroll-mt-24">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="px-3.5 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest inline-flex items-center gap-1.5 mb-3">
                    <i class="fa-solid fa-shapes"></i> Tiga Pilar Fundamental
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">
                    Mengapa TrashSmart Lebih Unggul?
                </h2>
                <p class="mt-4 text-slate-600 text-base sm:text-lg leading-relaxed">
                    Menghubungkan tiga dimensi penting yang belum pernah disatukan oleh tempat sampah manapun sebelumnya.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Pillar 1 -->
                <div class="rounded-3xl bg-white p-8 border border-slate-200/90 soft-card relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <h3 class="font-display font-bold text-2xl text-slate-900">Kecerdasan IoT Presisi</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
                        Menggabungkan sensor deteksi karakteristik objek, load cell timbangan digital gram, dan telemetri nirkabel yang terhubung langsung ke platform cloud Trashily secara real-time.
                    </p>
                    <ul class="mt-5 space-y-2 text-xs text-slate-500 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Sinkronisasi sesi QR instan</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Deteksi kecurangan bobot</li>
                    </ul>
                </div>

                <!-- Pillar 2 -->
                <div class="rounded-3xl bg-white p-8 border border-slate-200/90 soft-card relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h3 class="font-display font-bold text-2xl text-slate-900">Edukasi Perilaku Manusia</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
                        Menerapkan konsep <em>Behavioral Nudge</em>: membimbing pengguna tepat di titik pembuangan dengan teguran suara ramah. Membentuk kebiasaan memilah yang sadar dan mandiri di kalangan siswa dan warga.
                    </p>
                    <ul class="mt-5 space-y-2 text-xs text-slate-500 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Teguran korektif tanpa menghakimi</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Karakter peduli lingkungan sejak dini</li>
                    </ul>
                </div>

                <!-- Pillar 3 -->
                <div class="rounded-3xl bg-white p-8 border border-slate-200/90 soft-card relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-arrows-spin"></i>
                    </div>
                    <h3 class="font-display font-bold text-2xl text-slate-900">Nilai Ekonomi Sirkular</h3>
                    <p class="mt-3 text-slate-600 leading-relaxed text-sm sm:text-base">
                        Memastikan sampah plastik tetap 100% kering dan higienis tanpa kontaminasi bau busuk. Nilai jual daur ulang melonjak tinggi dan pengguna menerima poin reward yang bernilai riil.
                    </p>
                    <ul class="mt-5 space-y-2 text-xs text-slate-500 border-t border-slate-100 pt-4">
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Poin voucher kantin &amp; koperasi</li>
                        <li class="flex items-center gap-2"><i class="fa-solid fa-check text-brand-600"></i> Bahan baku bersih untuk SmartKolecer</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- ==================== 7. SINERGI EKOSISTEM: TRASHSMART & SMARTKOLECER ==================== -->
        <section class="rounded-3xl bg-gradient-to-br from-brand-50 to-emerald-50 border border-brand-200/80 p-8 sm:p-12 soft-card">
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <span class="px-3.5 py-1.5 rounded-full bg-white text-brand-800 font-bold text-xs uppercase tracking-widest border border-brand-200 inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-network-wired"></i> Sinergi Ekosistem Trashily
                    </span>
                    <h2 class="font-display text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900">
                        Dari Wadah TrashSmart ke Baling-Baling SmartKolecer
                    </h2>
                    <p class="text-slate-600 leading-relaxed text-base sm:text-lg">
                        Sampah botol plastik bersih yang dipilah lewat unit <strong>TrashSmart</strong> tidak berakhir sia-sia di tempat penimbunan. Di ekosistem Trashily, plastik berkualitas tinggi tersebut langsung kami salurkan menjadi material bilah baling-baling <strong>SmartKolecer</strong>—kolecer IoT pemantau angin tradisional yang cerdas.
                    </p>
                    <div class="pt-2 flex flex-wrap gap-4">
                        <a href="smartkolecer.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-600 text-white font-bold text-sm shadow hover:bg-brand-700 transition">
                            <i class="fa-solid fa-fan"></i> Jelajahi SmartKolecer &rarr;
                        </a>
                        <a href="harga.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-brand-300 bg-white text-brand-800 font-bold text-sm hover:bg-brand-50 transition">
                            <i class="fa-solid fa-tags"></i> Cek Nilai Tukar Sampah
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-4 flex items-center justify-center">
                    <div class="w-full max-w-xs bg-white rounded-3xl p-6 border border-brand-200 shadow-md text-center space-y-4">
                        <div class="w-20 h-20 mx-auto rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center text-4xl shadow-inner">
                            <i class="fa-solid fa-infinity"></i>
                        </div>
                        <div>
                            <p class="font-display font-extrabold text-slate-900 text-lg">Loop Tertutup (Closed Loop)</p>
                            <p class="text-xs text-slate-500 mt-1">Pemilahan di hulu dengan TrashSmart &rarr; Inovasi daur ulang di hilir dengan SmartKolecer.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== 8. CALL TO ACTION ==================== -->
        <section class="rounded-3xl bg-gradient-to-r from-brand-700 via-emerald-700 to-teal-800 p-8 sm:p-14 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-3xl space-y-6">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-200 bg-white/10 px-3.5 py-1.5 rounded-full border border-white/20 inline-block">
                    Inisiasi Sekolah &amp; Fasilitas Publik
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight">
                    Jadikan Tempat Sampah di Lingkunganmu Cerdas &amp; Mendidik.
                </h2>
                <p class="text-brand-100 text-base sm:text-lg leading-relaxed">
                    Tertarik menghadirkan unit TrashSmart di kantin sekolah, kampus, atau gedung kantormu? Atau ingin mulai menyetor sampah dan mengumpulkan reward di Trashily?
                </p>
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <?php if ($logged_in): ?>
                        <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-900 px-7 py-3.5 font-bold shadow hover:bg-brand-50 transition">
                            <i class="fa-solid fa-gauge"></i> Masuk Dashboard Trashily
                        </a>
                    <?php else: ?>
                        <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-white text-brand-900 px-7 py-3.5 font-bold shadow hover:bg-brand-50 transition">
                            <i class="fa-solid fa-user-plus"></i> Daftar Akun Trashily Gratis
                        </a>
                    <?php endif; ?>
                    <a href="https://wa.me/6285782118017?text=Halo%20Trashily,%20saya%20tertarik%20dengan%20TrashSmart%20IoT" target="_blank" class="inline-flex items-center gap-2 rounded-full border border-white/30 px-6 py-3.5 font-bold text-white hover:bg-white/10 transition">
                        <i class="fa-brands fa-whatsapp text-emerald-300"></i> Konsultasi Unit TrashSmart
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- ==================== FOOTER ==================== -->
    <footer class="mt-24 bg-slate-900 text-slate-400 py-16 px-4 md:px-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                <!-- Col 1 -->
                <div class="space-y-4">
                    <a href="index.php" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl overflow-hidden">
                            <img src="assets/brand.png" alt="Trashily" class="w-full h-full object-contain">
                        </div>
                        <span class="font-display font-extrabold text-2xl text-white tracking-tight">Trashily<span class="text-brand-400">.</span></span>
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Ekosistem bank sampah digital berbasis IoT dan rewards platform. Mengubah sampah menjadi nilai riil dan aksi nyata.
                    </p>
                </div>

                <!-- Col 2: Inovasi & Layanan -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Layanan &amp; Inovasi</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="trashsmart.php" class="text-brand-400 font-semibold hover:text-brand-300 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-trash-can-arrow-up text-xs"></i> TrashSmart (IoT Bin)</a></li>
                        <li><a href="smartkolecer.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-fan text-xs"></i> SmartKolecer (IoT Wind)</a></li>
                        <li><a href="harga.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-tags text-xs"></i> Daftar Harga Sampah</a></li>
                        <li><a href="ulasan.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-comments text-xs"></i> Ulasan Komunitas</a></li>
                    </ul>
                </div>

                <!-- Col 3: Portal Akun -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Portal Akun</h4>
                    <ul class="space-y-2.5 text-sm">
                        <?php if ($logged_in): ?>
                            <li><a href="<?= $dash_url ?>" class="hover:text-brand-400 transition-colors">Dashboard Saya</a></li>
                            <li><a href="auth/logout.php" class="hover:text-rose-400 transition-colors">Keluar Akun</a></li>
                        <?php else: ?>
                            <li><a href="auth/login.php" class="hover:text-brand-400 transition-colors">Masuk Akun</a></li>
                            <li><a href="auth/register.php" class="hover:text-brand-400 transition-colors">Daftar Member</a></li>
                        <?php endif; ?>
                        <li><a href="index.php#faq" class="hover:text-brand-400 transition-colors">Pusat Bantuan FAQ</a></li>
                    </ul>
                </div>

                <!-- Col 4: Kontak -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Kontak Layanan</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-envelope text-brand-400 mt-1"></i>
                            <span>info@trashily.id</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-phone text-brand-400 mt-1"></i>
                            <span>+62 857 8211 8017</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-location-dot text-brand-400 mt-1"></i>
                            <span>Jl. Cikaret, Bogor, Jawa Barat</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; <?= date('Y') ?> <strong>Trashily</strong>. Hak Cipta Dilindungi.</p>
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> TrashSmart Telemetry Online
                </span>
            </div>
        </div>
    </footer>

    <!-- ==================== CLIENT AUDIO SIMULATOR SCRIPT ==================== -->
    <script>
        const scenarios = {
            1: {
                title: "Teguran: Sisa Makanan di Kompartemen Anorganik",
                badge: "Salah Kamar (Organik di Anorganik)",
                badgeColor: "bg-amber-500/20 text-amber-300 border-amber-500/30",
                text: "Peringatan! Ini terdeteksi sampah organik, mohon dipindahkan ke wadah hijau di sebelah ya!"
            },
            2: {
                title: "Teguran: Botol Plastik di Kompartemen Organik",
                badge: "Salah Kamar (Anorganik di Organik)",
                badgeColor: "bg-rose-500/20 text-rose-300 border-rose-500/30",
                text: "Peringatan! Ini terdeteksi botol plastik anorganik, mohon dipindahkan ke wadah kuning ya!"
            },
            3: {
                title: "Apresiasi: Verifikasi Bobot & Reward Sukses",
                badge: "Pemilahan Berhasil 100%",
                badgeColor: "bg-emerald-500/20 text-emerald-300 border-emerald-500/30",
                text: "Terima kasih! Sampah terverifikasi. 450 gram botol plastik dicatat, 45 poin telah ditambahkan ke akun Trashily Anda!"
            }
        };

        let currentUtterance = null;

        function playVoiceScenario(id) {
            const sc = scenarios[id];
            if (!sc) return;

            const statusText = document.getElementById('audioStatusText');
            const transcriptText = document.getElementById('audioTranscriptText');
            const speakerBadge = document.getElementById('speakerBadge');
            const visualizerBox = document.getElementById('visualizerBox');

            statusText.textContent = sc.title;
            transcriptText.textContent = `"${sc.text}"`;
            speakerBadge.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border ${sc.badgeColor}`;
            speakerBadge.innerHTML = `<i class="fa-solid fa-volume-high animate-bounce"></i> ${sc.badge}`;

            // Web Speech API
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const utter = new SpeechSynthesisUtterance(sc.text);
                utter.lang = 'id-ID';
                utter.rate = 0.95;
                utter.pitch = 1.05;

                // Pick Indonesian voice if available
                const voices = window.speechSynthesis.getVoices();
                const idVoice = voices.find(v => v.lang.includes('id') || v.lang.includes('ID'));
                if (idVoice) utter.voice = idVoice;

                utter.onstart = () => {
                    visualizerBox.classList.add('ring-2', 'ring-brand-400');
                };
                utter.onend = () => {
                    visualizerBox.classList.remove('ring-2', 'ring-brand-400');
                    speakerBadge.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-400"></i> Selesai Diputar`;
                };

                currentUtterance = utter;
                window.speechSynthesis.speak(utter);
            } else {
                // Fallback for browsers without speech synthesis
                alert(sc.text);
            }
        }

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
</body>

</html>
