<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/everwear/dashboard.php' : 'everwear/dashboard.php') : null;
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Everwear — Donasi Baju Layak & Recycle | Trashily</title>
    <meta name="description" content="Everwear: Program donasi baju layak pakai dan recycle tekstil. Semua donasi diverifikasi, ditimbang, dan mendapat 10 poin per kg dalam satu sistem poin Trashily." />
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
        body { font-family: 'Be Vietnam Pro', sans-serif; background: #fafaf9; color: #0f172a; }
        .page-shell { background: linear-gradient(180deg, rgba(22,163,74,0.06) 0%, rgba(22,163,74,0.04) 15%, rgba(250,250,249,1) 30%, rgba(250,250,249,1) 100%); }
        .soft-card { box-shadow: 0 10px 40px rgba(22,163,74,0.08); }
    </style>
</head>
<body class="page-shell">
    <!-- ==================== HEADER & MEGA MENU ==================== -->
    <header id="navbar" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-xl transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl overflow-hidden group-hover:scale-105 transition-transform">
                    <img src="assets/brand.png" alt="Trashily" class="w-full h-full object-contain">
                </div>
                <span class="font-display text-xl font-extrabold text-slate-900 group-hover:text-brand-700 transition-colors">Trashily<span class="text-brand-600">.</span></span>
            </a>

            <nav id="navLinkWrapper" class="hidden lg:flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/70 backdrop-blur-md px-5 py-2 shadow-sm transition-all duration-300">
                <a href="index.php#tentang-kami" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors px-3 py-1.5">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors px-3 py-1.5">Cara Kerja</a>
                <div id="layananNavTrigger" class="relative group/trigger">
                    <button type="button" class="flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 transition-colors px-3 py-1.5">
                        Layanan &amp; Inovasi <i class="fa-solid fa-chevron-down text-[10px] opacity-80 transition-transform duration-200" id="layananChevron"></i>
                    </button>
                </div>
                <a href="index.php#faq" class="text-sm font-semibold text-slate-700 hover:text-brand-600 transition-colors px-3 py-1.5">FAQ</a>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= BASE_URL . '/' . $dash_url ?>" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/login.php?from=everwear" class="hidden sm:inline-flex px-4 py-2 rounded-full text-slate-700 hover:text-brand-700 font-semibold">Masuk</a>
                    <a href="auth/register.php?from=everwear" class="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 font-extrabold text-sm shadow-md transition">Daftar Gratis</a>
                <?php endif; ?>
                <button id="mobileMenuBtn" class="lg:hidden text-slate-700 hover:text-brand-700 p-2" aria-label="Menu">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Full-Width Mega Menu Dropdown -->
        <div id="megaMenuDropdown" class="absolute top-full left-0 right-0 w-full bg-white border-b border-slate-200 shadow-xl rounded-none opacity-0 invisible transition-all duration-200 pointer-events-none z-50 text-left before:content-[''] before:absolute before:-top-6 before:left-0 before:right-0 before:h-6 mt-0 border-t">
            <div class="max-w-5xl mx-auto px-4 md:px-8 py-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekosistem &amp; Layanan Trashily</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Inovasi Bank Sampah Digital &bull; Edukasi &bull; IoT</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <div class="space-y-2">
                        <a href="trashsmart.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-trash-can-arrow-up"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">TrashSmart</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">IoT Bin</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Tempat sampah IoT edukatif dengan baki inspeksi, voice nudge, dan reward instan.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                        </a>

                        <a href="smartkolecer.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
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

                        <a href="gembul.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-amber-500 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-dice"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-amber-600 transition-colors">GEMBUL</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-50 text-amber-700">Creative Eco-Kit</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Gerakan pilah sampah: Ular Tangga edukasi sekolah, magnet kulkas &amp; tantangan rumah.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-amber-600 transition-all self-center"></i>
                        </a>

                        <a href="everwear.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-brand-300 bg-brand-50/60 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-brand-950 font-extrabold">Everwear</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-200 text-brand-900 border border-brand-300">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Donasi baju layak pakai ke komunitas &amp; recycle tekstil dengan sistem poin Trashily.
                                </p>
                            </div>
                            <i class="fa-solid fa-check text-xs text-brand-600 self-center"></i>
                        </a>
                    </div>


                    <div class="space-y-2">
                        <a href="harga.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
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

                        <a href="ulasan.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
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

                        <a href="gembul.php#jadwal" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-amber-200 hover:bg-amber-50/40 transition-all duration-200">
                            <div class="text-amber-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-amber-700 transition-colors">Jadwal Kegiatan</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-100 text-amber-800">Publik</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Lihat agenda roadshow sekolah, aksi bersih sungai, dan workshop daur ulang terbuka.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-amber-600 transition-all self-center"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden lg:hidden mt-3 rounded-2xl p-6 space-y-2 bg-white border border-slate-200">
            <div class="space-y-2 py-2">
                <a href="index.php#tentang-kami" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Tentang Kami</a>
                <a href="index.php#cara-kerja" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Cara Kerja</a>
                
                <div class="py-2 border-b border-slate-100 space-y-1">
                    <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Layanan &amp; Inovasi</p>
                    <a href="trashsmart.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-base py-2">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-trash-can-arrow-up text-brand-600 text-sm"></i> TrashSmart</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-brand-100 text-brand-700">IoT</span>
                    </a>
                    <a href="smartkolecer.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-base py-2">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-fan text-sky-600 text-sm"></i> SmartKolecer</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-sky-100 text-sky-700">IoT</span>
                    </a>
                    <a href="gembul.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-amber-600 text-base py-2">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-dice text-amber-500 text-sm"></i> GEMBUL</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-amber-100 text-amber-700">Eco-Kit</span>
                    </a>
                    <a href="everwear.php" class="flex items-center justify-between text-brand-900 font-bold text-base py-2 bg-brand-50 px-2 rounded-lg">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-shirt text-brand-600 text-sm"></i> Everwear</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-brand-200 text-brand-800">Aktif</span>
                    </a>
                    <a href="harga.php" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2">Daftar Harga</a>
                    <a href="ulasan.php" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2">Ulasan Pengguna</a>
                </div>
            </div>
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="w-full text-center py-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md">
                        <i class="fa-solid fa-gauge mr-1.5"></i> Dashboard Saya
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="w-full text-center py-3 rounded-xl bg-slate-100 text-slate-800 font-bold text-sm hover:bg-slate-200">Masuk</a>
                    <a href="auth/register.php?from=everwear" class="w-full text-center py-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md hover:bg-brand-700">Daftar Gratis</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 md:px-8 py-10">
        <!-- HERO SECTION -->
        <section class="grid lg:grid-cols-12 gap-10 items-center mb-24">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-100 text-brand-900 border border-brand-300 font-extrabold text-xs tracking-wider uppercase shadow-sm">
                    <i class="fa-solid fa-shirt text-brand-600"></i> Program Donasi Tekstil &bull; Sustainability
                </div>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.12]">
                    Everwear <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 via-brand-500 to-emerald-600">
                        Baju Layak Pakai Disalurkan, Tekstil Rusak Direcycle
                    </span>
                </h1>
                <p class="text-slate-600 text-base sm:text-lg leading-relaxed max-w-2xl">
                    Program donasi tekstil dengan satu sistem poin Trashily. Donasikan baju layak pakai atau tekstil recycle; keduanya diverifikasi, ditimbang, dan mendapatkan poin sesuai beratnya.
                </p>

                <div class="flex flex-wrap gap-3">
                    <?php if ($logged_in): ?>
                        <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg transition-all hover:-translate-y-0.5">
                            <i class="fa-solid fa-gauge"></i> Buka Dashboard
                        </a>
                    <?php else: ?>
                        <a href="auth/register.php?from=everwear" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg transition-all hover:-translate-y-0.5">
                            <i class="fa-solid fa-user-plus"></i> Daftar & Mulai Donasi
                        </a>
                    <?php endif; ?>
                    <a href="#cara-kerja" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-white border-2 border-brand-200 text-brand-700 font-bold text-sm hover:bg-brand-50 transition-all">
                        <i class="fa-solid fa-circle-info"></i> Cara Kerja
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5 flex items-center justify-center">
                <img src="assets/everwear_illustrator.png" alt="Ilustrasi Everwear" class="w-full max-w-4xl scale-125 object-contain">
            </div>
        </section>

        <!-- MENGAPA EVERWEAR PENTING (DATA & URGENSI) -->
        <section id="urgensi" class="mb-24">
            <div class="text-center mb-12">
                <span class="inline-block px-4 py-1.5 rounded-full bg-brand-100 text-brand-800 font-extrabold text-xs uppercase tracking-wider mb-3">Latar Belakang & Impact</span>
                <h2 class="font-display text-3xl md:text-4xl font-extrabold text-slate-900">Mengapa Everwear Sangat Penting?</h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto mt-2">Everwear hadir mengisi <em>blindspot</em> pengelolaan limbah tekstil yang selama ini terabaikan.</p>
            </div>

            <!-- Bento Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <!-- Card 1 -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
                    <div class="text-4xl md:text-5xl font-display font-extrabold text-brand-400 mb-2">275.707</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Ton Sampah / Tahun di Kota Bogor</div>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Di mana <strong>171 Bank Sampah & 32 TPS3R</strong> saat ini hanya fokus pada sampah plastik, kertas, dan organik saja.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="bg-gradient-to-br from-amber-500 to-red-600 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
                    <div class="text-4xl md:text-5xl font-display font-extrabold text-white mb-2">2,3 Juta</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-amber-100 mb-4">Ton Limbah Tekstil Nasional</div>
                    <p class="text-sm text-amber-50 leading-relaxed">
                        Akibat fenomena <em>fast fashion</em>, dan mirisnya <strong>90% limbah tekstil ini berakhir menumpuk di TPA</strong> tanpa daur ulang.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="bg-gradient-to-br from-brand-600 to-emerald-800 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
                    <div class="text-4xl md:text-5xl font-display font-extrabold text-brand-200 mb-2">Solusi #1</div>
                    <div class="text-xs font-bold uppercase tracking-wider text-brand-100 mb-4">Platform Sirkuler Pertama</div>
                    <p class="text-sm text-brand-50 leading-relaxed">
                        Everwear jadi wadah sirkuler pertama di Bogor yang menghubungkan donasi, edukasi, dan <em>upcycling</em> secara terpadu.
                    </p>
                </div>
            </div>

            <!-- Solusi Everwear Details Grid (3 Pilar Terpadu) -->
            <div class="bg-white rounded-3xl p-8 md:p-12 border border-slate-200 shadow-xl">
                <h3 class="font-display font-extrabold text-2xl text-slate-900 mb-8 text-center">Bagaimana Everwear Menyelesaikan Masalah Ini?</h3>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Pilar 1 -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-2xl mb-4">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Edukasi Overconsumption</h4>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Mengedukasi masyarakat untuk mengurangi konsumsi pakaian berlebihan dan membiasakan diri lebih bijak sebelum membeli pakaian baru.
                        </p>
                    </div>

                    <!-- Pilar 2 -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-2xl mb-4">
                            <i class="fa-solid fa-hands-holding-circle"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Baju Layak Pakai</h4>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            Pakaian bersih dan dalam kondisi baik disalurkan ke warga yang membutuhkan. Baju layak tetap mendapat poin setelah diverifikasi dan ditimbang.
                        </p>
                        <a href="https://www.instagram.com/pasarbogorgr/" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-xs text-brand-700 font-bold hover:underline mt-auto">
                            <i class="fa-brands fa-instagram text-sm"></i> @pasarbogorgr
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>

                    <!-- Pilar 3 -->
                    <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-14 h-14 rounded-2xl bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-2xl mb-4">
                            <i class="fa-solid fa-recycle"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 text-lg mb-2">Tekstil Tidak Layak (Recycle)</h4>
                        <p class="text-slate-600 text-sm leading-relaxed mb-4">
                            Baju robek, kusam, atau rusak didaur ulang lewat sistem bank sampah Trashily. Pengguna mendapat poin yang bisa ditukar hadiah.
                        </p>
                        <span class="inline-flex items-center gap-1.5 text-xs text-brand-700 font-bold bg-brand-50 px-3 py-1 rounded-full border border-brand-200 mt-auto">
                            <i class="fa-solid fa-coins"></i> Sistem Poin Trashily
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CARA KERJA -->
        <section id="cara-kerja" class="mb-24 bg-gradient-to-br from-brand-50 to-emerald-50 rounded-3xl p-8 md:p-12">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Cara Kerja Everwear</h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto">
                    Proses sederhana untuk menyalurkan pakaian Anda dengan dampak maksimal
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 shadow-md text-center">
                    <div class="w-14 h-14 bg-brand-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                    <h4 class="font-display font-bold text-lg text-slate-900 mb-3">Login & Daftar</h4>
                    <p class="text-slate-600 text-sm">Login ke akun Trashily untuk mengakses sistem donasi Everwear</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-md text-center">
                    <div class="w-14 h-14 bg-brand-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
<h4 class="font-display font-bold text-lg text-slate-900 mb-3">Isi Jumlah & Berat</h4>
                     <p class="text-slate-600 text-sm">Masukkan jumlah baju layak, jumlah recycle, dan estimasi berat total</p>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-md text-center">
                    <div class="w-14 h-14 bg-brand-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
<h4 class="font-display font-bold text-lg text-slate-900 mb-3">Setor atau Jemput</h4>
                     <p class="text-slate-600 text-sm">Pilih setor sendiri ke Trashily atau ajukan penjemputan dari lokasi Anda</p>
                </div>
            </div>

            <div class="mt-8 p-6 bg-brand-900 text-white rounded-2xl text-center">
                <p class="text-lg font-semibold mb-2"><i class="fa-solid fa-info-circle mr-2"></i> Informasi Penting</p>
                <p class="text-brand-100">Untuk menggunakan fitur sistem Everwear, Anda harus <strong>login</strong> terlebih dahulu. Halaman ini hanya memperkenalkan konsep program Everwear.</p>
            </div>
        </section>

        <!-- KEUNTUNGAN -->
        <section class="mb-24">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Keuntungan Everwear</h2>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-heart text-2xl text-brand-600"></i>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Bantu Komunitas</h4>
                    <p class="text-sm text-slate-600">Baju layak pakai membantu sesama yang membutuhkan</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-coins text-2xl text-brand-600"></i>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Dapatkan Poin</h4>
                    <p class="text-sm text-slate-600">Baju layak dan recycle mendapat 10 poin per kg setelah verifikasi</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-leaf text-2xl text-brand-600"></i>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Kurangi Limbah</h4>
                    <p class="text-sm text-slate-600">Tekstil tidak berakhir di TPA, lebih ramah lingkungan</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-brand-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-chart-line text-2xl text-brand-600"></i>
                    </div>
                    <h4 class="font-bold text-slate-900 mb-2">Tracking Digital</h4>
                    <p class="text-sm text-slate-600">Pantau histori donasi & poin terintegrasi Trashily</p>
                </div>
            </div>
        </section>

        <!-- CTA SECTION -->
        <section class="bg-gradient-to-r from-brand-600 to-brand-800 rounded-3xl p-8 md:p-12 text-center text-white shadow-2xl">
            <h2 class="font-display text-3xl md:text-4xl font-extrabold mb-4">Siap Berkontribusi?</h2>
            <p class="text-brand-100 text-lg mb-8 max-w-2xl mx-auto">
                Bergabunglah dengan ribuan pengguna Trashily yang sudah berkontribusi untuk lingkungan dan komunitas melalui Everwear
            </p>
            <?php if ($logged_in): ?>
                <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-white text-brand-700 font-bold text-lg shadow-xl hover:bg-brand-50 transition-all hover:-translate-y-1">
                    <i class="fa-solid fa-gauge"></i> Buka Dashboard
                </a>
            <?php else: ?>
                <a href="auth/register.php?from=everwear" class="inline-flex items-center gap-2 px-8 py-4 rounded-full bg-white text-brand-700 font-bold text-lg shadow-xl hover:bg-brand-50 transition-all hover:-translate-y-1">
                    <i class="fa-solid fa-user-plus"></i> Daftar Sekarang Gratis
                </a>
            <?php endif; ?>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="mt-24 py-12 bg-slate-900 text-slate-300">
        <div class="max-w-7xl mx-auto px-4 md:px-8 text-center">
            <div class="flex items-center justify-center gap-3 mb-4">
                <img src="assets/brand.png" alt="Trashily" class="w-10 h-10 rounded-xl">
                <span class="font-display text-2xl font-extrabold text-white">Trashily</span>
            </div>
            <p class="text-sm text-slate-400 mb-6">Ekosistem Bank Sampah Digital & Rewards Platform</p>
            <div class="flex justify-center gap-6 text-sm">
                <a href="index.php" class="hover:text-white transition">Beranda</a>
                <a href="index.php#tentang-kami" class="hover:text-white transition">Tentang</a>
                <a href="harga.php" class="hover:text-white transition">Harga</a>
                <a href="ulasan.php" class="hover:text-white transition">Ulasan</a>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-800 text-xs text-slate-500">
                &copy; <?= date('Y') ?> Trashily by Youth Advisory Bogor. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Mega Menu Dropdown
        const layananNavTrigger = document.getElementById('layananNavTrigger');
        const megaMenuDropdown = document.getElementById('megaMenuDropdown');
        const layananChevron = document.getElementById('layananChevron');
        
        if (layananNavTrigger && megaMenuDropdown) {
            let timeout;
            
            layananNavTrigger.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
                megaMenuDropdown.classList.remove('opacity-0', 'invisible', 'pointer-events-none');
                megaMenuDropdown.classList.add('opacity-100', 'visible', 'pointer-events-auto');
                if (layananChevron) layananChevron.style.transform = 'rotate(180deg)';
            });
            
            layananNavTrigger.addEventListener('mouseleave', () => {
                timeout = setTimeout(() => {
                    if (!megaMenuDropdown.matches(':hover')) {
                        megaMenuDropdown.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                        megaMenuDropdown.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
                        if (layananChevron) layananChevron.style.transform = 'rotate(0deg)';
                    }
                }, 100);
            });
            
            megaMenuDropdown.addEventListener('mouseenter', () => {
                clearTimeout(timeout);
            });
            
            megaMenuDropdown.addEventListener('mouseleave', () => {
                megaMenuDropdown.classList.add('opacity-0', 'invisible', 'pointer-events-none');
                megaMenuDropdown.classList.remove('opacity-100', 'visible', 'pointer-events-auto');
                if (layananChevron) layananChevron.style.transform = 'rotate(0deg)';
            });
        }
    </script>
</body>
</html>
