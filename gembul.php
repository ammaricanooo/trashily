<?php
session_start();
require_once __DIR__ . '/config/database.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

// Query Live Jadwal Kegiatan GEMBUL
$jadwal_list = [];
$stats_kegiatan = ['total' => 0, 'berjalan' => 0, 'rencana' => 0, 'selesai' => 0];

if (isset($conn) && $conn instanceof mysqli) {
    $sql = "SELECT * FROM gembul_jadwal ORDER BY 
            CASE status 
                WHEN 'berjalan' THEN 1 
                WHEN 'rencana' THEN 2 
                WHEN 'selesai' THEN 3 
                ELSE 4 
            END, tanggal ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $jadwal_list[] = $row;
            $stats_kegiatan['total']++;
            if (isset($stats_kegiatan[$row['status']])) {
                $stats_kegiatan[$row['status']]++;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEMBUL — Gemar Berdampak untuk Lingkungan | Trashily</title>
    <meta name="description" content="GEMBUL: Gerakan perubahan perilaku pilah sampah lewat Creative Eco-Kit, Ular Tangga edukasi sekolah, magnet kulkas daur ulang, dan Eco-Pass Trashily." />
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
                        amberbrand: {50:'#fffbeb',100:'#fef3c7',200:'#fde68a',300:'#fcd34d',400:'#fbbf24',500:'#f59e0b',600:'#d97706',700:'#b45309',800:'#92400e',900:'#78350f'},
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
        .page-shell { background: linear-gradient(180deg, rgba(245,158,11,0.06) 0%, rgba(22,163,74,0.04) 15%, rgba(250,250,249,1) 30%, rgba(250,250,249,1) 100%); }
        .soft-card { box-shadow: 0 10px 40px rgba(180,83,9,0.06); }
        .board-tile { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .board-tile:hover { transform: translateY(-3px); }
        .dice-spin { animation: diceRoll 0.6s ease-in-out; }
        @keyframes diceRoll {
            0% { transform: rotate(0deg) scale(0.9); }
            50% { transform: rotate(180deg) scale(1.15); }
            100% { transform: rotate(360deg) scale(1); }
        }
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
                <span class="font-display text-xl font-extrabold text-slate-900 group-hover:text-brand-700 transition-colors">Trashily<span class="text-amber-500">.</span></span>
            </a>

            <!-- Desktop Navigation: Capsule with rounded border and blur at top, borderless on scroll -->
            <nav id="navLinkWrapper" class="hidden lg:flex items-center gap-1 rounded-full border border-slate-200/80 bg-white/70 backdrop-blur-md px-5 py-2 shadow-sm transition-all duration-300">
                <a href="index.php#tentang-kami" class="text-sm font-semibold text-slate-700 hover:text-amber-600 transition-colors px-3 py-1.5">Tentang Kami</a>
                <a href="#masalah" class="text-sm font-semibold text-slate-700 hover:text-amber-600 transition-colors px-3 py-1.5">Latar Belakang</a>
                <a href="#ecokit" class="text-sm font-semibold text-slate-700 hover:text-amber-600 transition-colors px-3 py-1.5">Creative Eco-Kit</a>
                <a href="#simulasi-games" class="text-sm font-semibold text-slate-700 hover:text-amber-600 transition-colors px-3 py-1.5">Ular Tangga</a>
                <a href="#jadwal" class="text-sm font-semibold text-amber-700 hover:text-amber-800 transition-colors px-3 py-1.5 font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-check text-xs"></i> Jadwal Publik
                </a>

                <!-- Full-Width Flush Dropdown Trigger -->
                <div id="layananNavTrigger" class="relative group/trigger">
                    <button type="button" class="flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 transition-colors px-3 py-1.5">
                        Layanan &amp; Inovasi <i class="fa-solid fa-chevron-down text-[10px] opacity-80 transition-transform duration-200" id="layananChevron"></i>
                    </button>
                </div>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="hidden sm:inline-flex px-4 py-2 rounded-full text-slate-700 hover:text-brand-700 font-semibold">Masuk</a>
                    <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-amber-500 hover:bg-amber-600 text-slate-950 px-5 py-2.5 font-extrabold text-sm shadow-md transition">Daftar Gratis</a>
                <?php endif; ?>
                <button id="mobileMenuBtn" class="lg:hidden text-slate-700 hover:text-brand-700 p-2" aria-label="Menu">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Full-Width Mega Menu Dropdown Flush Under Navbar -->
        <div id="megaMenuDropdown" class="absolute top-full left-0 right-0 w-full bg-white border-b border-slate-200 shadow-xl rounded-none opacity-0 invisible transition-all duration-200 pointer-events-none z-50 text-left before:content-[''] before:absolute before:-top-6 before:left-0 before:right-0 before:h-6 mt-0 border-t">
            <div class="max-w-5xl mx-auto px-4 md:px-8 py-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekosistem &amp; Layanan Trashily</span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Inovasi Bank Sampah Digital &bull; Edukasi &bull; IoT</span>
                </div>

                <!-- 2 Kiri, 2 Kanan Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    <!-- Kiri: 3 Inovasi & Gerakan -->
                    <div class="space-y-2">
                        <!-- TrashSmart -->
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

                        <!-- SmartKolecer -->
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

                        <!-- GEMBUL (Aktif) -->
                        <a href="gembul.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-amber-300 bg-amber-50/60 transition-all duration-200">
                            <div class="text-amber-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-dice"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-amber-950 font-extrabold">GEMBUL</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-200 text-amber-900 border border-amber-300">Aktif</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Gemar Berdampak untuk Lingkungan: Creative Eco-Kit, boardgame sekolah &amp; tantangan rumah.
                                </p>
                            </div>
                            <i class="fa-solid fa-check text-xs text-amber-600 self-center"></i>
                        </a>

                        <!-- Everwear -->
                        <a href="everwear.php" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                            <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">Everwear</h4>
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">Donasi Tekstil</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                    Donasi baju layak pakai ke komunitas &amp; recycle tekstil dengan sistem poin Trashily.
                                </p>
                            </div>
                            <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                        </a>
                    </div>

                    <!-- Kanan: 3 Layanan & Agenda -->
                    <div class="space-y-2">
                        <!-- Daftar Harga -->
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

                        <!-- Ulasan Komunitas -->
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

                        <!-- Jadwal Kegiatan Publik -->
                        <a href="#jadwal" class="group/item flex items-start gap-4 p-3 rounded-2xl border border-transparent hover:border-amber-200 hover:bg-amber-50/40 transition-all duration-200">
                            <div class="text-amber-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-amber-700 transition-colors">Jadwal Kegiatan GEMBUL</h4>
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

        <!-- Mobile Drawer Navigation -->
        <div id="mobileMenu" class="hidden lg:hidden mt-3 rounded-2xl p-6 space-y-2 bg-white border border-slate-200">
            <div class="space-y-2 py-2">
                <a href="index.php#tentang-kami" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Tentang Kami</a>
                <a href="#masalah" class="block text-slate-800 font-bold hover:text-amber-600 text-base py-2.5 border-b border-slate-100">Latar Masalah</a>
                <a href="#ecokit" class="block text-slate-800 font-bold hover:text-amber-600 text-base py-2.5 border-b border-slate-100">Creative Eco-Kit</a>
                <a href="#simulasi-games" class="block text-slate-800 font-bold hover:text-amber-600 text-base py-2.5 border-b border-slate-100">Simulasi Boardgame</a>
                <a href="#jadwal" class="block text-amber-700 font-bold hover:text-amber-800 text-base py-2.5 border-b border-slate-100">Jadwal Kegiatan Publik (<?= count($jadwal_list) ?>)</a>

                <!-- Sub-menu Section for Mobile -->
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
                    <a href="gembul.php" class="flex items-center justify-between text-amber-900 font-bold text-base py-2 bg-amber-50 px-2 rounded-lg">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-dice text-amber-600 text-sm"></i> GEMBUL</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-amber-200 text-amber-800">Aktif</span>
                    </a>
                    <a href="everwear.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-base py-2">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-shirt text-brand-600 text-sm"></i> Everwear</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-full bg-brand-100 text-brand-700">Donasi</span>
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
                    <a href="auth/register.php" class="w-full text-center py-3 rounded-xl bg-amber-500 text-slate-950 font-bold text-sm shadow-md hover:bg-amber-600">Daftar Gratis</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 md:px-8 py-10">
        <!-- ==================== HERO SECTION ==================== -->
        <section class="grid lg:grid-cols-12 gap-10 items-center mb-24">
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-xs tracking-wider uppercase shadow-sm">
                    <i class="fa-solid fa-dice text-amber-600"></i> Gerakan Perubahan Perilaku &bull; Creative Eco-Kit
                </div>
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.12]">
                    GEMBUL <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 via-amber-500 to-emerald-600">
                        Gemar Berdampak untuk Lingkungan
                    </span>
                </h1>
                <p class="text-slate-600 text-base sm:text-lg leading-relaxed max-w-2xl">
                    Gerakan anak muda yang mengajak siswa dan keluarga memahami, mencoba, dan mempertahankan kebiasaan memilah sampah. Bukan sekadar sosialisasi satu arah, melainkan rangkaian <strong>Creative Eco-Kit</strong>: board games seru di sekolah, magnet kulkas pengingat harian di rumah, dan <strong>Eco-Pass</strong> yang terhubung ke ekosistem digital Trashily.
                </p>

                <!-- Quick Stats Badge -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div class="text-2xl font-extrabold text-amber-600 font-display">30</div>
                        <div class="text-xs text-slate-500 font-semibold mt-0.5">Petak Ular Tangga</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div class="text-2xl font-extrabold text-emerald-600 font-display">3–7</div>
                        <div class="text-xs text-slate-500 font-semibold mt-0.5">Hari Eco-Challenge</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div class="text-2xl font-extrabold text-amber-600 font-display">4</div>
                        <div class="text-xs text-slate-500 font-semibold mt-0.5">Kategori Magnet</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <div class="text-2xl font-extrabold text-sky-600 font-display">1</div>
                        <div class="text-xs text-slate-500 font-semibold mt-0.5">Eco-Pass Daur Ulang</div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="#ecokit" class="inline-flex items-center gap-2 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold px-6 py-3.5 text-sm shadow-md hover:shadow-lg transition-all hover:-translate-y-0.5">
                        <i class="fa-solid fa-box-open"></i> Jelajahi Creative Eco-Kit
                    </a>
                    <a href="#jadwal" class="inline-flex items-center gap-2 rounded-full bg-white border border-slate-200 hover:border-amber-300 text-slate-800 font-bold px-6 py-3.5 text-sm shadow-sm hover:shadow transition">
                        <i class="fa-solid fa-calendar-days text-amber-600"></i> Jadwal Kegiatan (<?= count($jadwal_list) ?>)
                    </a>
                    <a href="#simulasi-games" class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-800 font-bold px-5 py-3.5 text-sm transition">
                        <i class="fa-solid fa-gamepad text-emerald-600"></i> Coba Simulasi Boardgame
                    </a>
                </div>

                <p class="text-xs text-slate-400 italic">
                    *Catatan: Dokumen program ini adalah rancangan yang diuji dan disimulasikan bersama mitra komunitas (SMKN 4 Bogor, Komunitas Alkesa, Bank Sampah WU).
                </p>
            </div>

            <!-- Hero Image Visual Showcase -->
            <div class="lg:col-span-5">
                <div class="relative group">
                    <div class="absolute -inset-2 bg-gradient-to-r from-amber-400 to-emerald-400 rounded-3xl blur-xl opacity-30 group-hover:opacity-50 transition duration-500"></div>
                    <div class="relative rounded-3xl overflow-hidden border-2 border-white shadow-2xl bg-white">
                        <img src="assets/gembul.jpg" alt="Siswa Bermain Ular Tangga GEMBUL dan Creative Eco-Kit" class="w-full h-[440px] object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent flex flex-col justify-end p-6 text-white">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/90 backdrop-blur-md text-slate-950 font-extrabold text-xs w-max mb-2">
                                <i class="fa-solid fa-school"></i> Pilot Project: SMKN 4 Bogor &bull; Rumah Siswa
                            </span>
                            <h3 class="font-display font-bold text-lg text-white">Creative Eco-Kit Prototype</h3>
                            <p class="text-xs text-slate-200 mt-1 leading-relaxed">
                                Papan Ular Tangga 30 petak, Kartu Kasus Pilah, Magnet Kulkas berbahan barang bekas, dan Eco-Pass berbahan kertas daur ulang.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== LATAR BELAKANG & DATA MASALAH ==================== -->
        <section id="masalah" class="mb-24 scroll-mt-28">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-triangle-exclamation"></i> Mengapa Ini Penting?
                </div>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Menjembatani Kesenjangan Antara <br>
                    <span class="text-rose-600">"Tahu Harus Pilah"</span> dan <span class="text-emerald-600">"Melakukannya Setiap Hari"</span>
                </h2>
                <p class="text-slate-600 text-sm sm:text-base mt-3">
                    Berdasarkan baseline RPJMD Kota Bogor 2025–2029, sebagian besar sampah dihasilkan dari lingkup rumah tangga, sementara pengurangan di sumber masih menghadapi tantangan kebiasaan nyata.
                </p>
            </div>

            <!-- 3 Stat Cards from RPJMD Bogor -->
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <div class="rounded-3xl bg-white border border-rose-100 p-8 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-dumpster"></i>
                    </div>
                    <div class="text-3xl font-extrabold font-display text-slate-900">275.707,63 <span class="text-lg font-bold text-slate-500">Ton/Thn</span></div>
                    <h3 class="font-bold text-slate-800 mt-2">Timbulan Sampah Kota Bogor</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Volume timbulan sampah tahunan Kota Bogor berdasarkan data awal RPJMD yang memerlukan percepatan reduksi dari hulu.
                    </p>
                </div>

                <div class="rounded-3xl bg-white border border-amber-100 p-8 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-house-user"></i>
                    </div>
                    <div class="text-3xl font-extrabold font-display text-slate-900">62,44%</div>
                    <h3 class="font-bold text-slate-800 mt-2">Porsi Sampah Rumah Tangga</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Mayoritas sampah berasal dari aktivitas domestik rumah tangga: sisa makanan organik, kemasan jajanan, dan kantong kresek.
                    </p>
                </div>

                <div class="rounded-3xl bg-white border border-emerald-100 p-8 shadow-sm hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-4">
                        <i class="fa-solid fa-recycle"></i>
                    </div>
                    <div class="text-3xl font-extrabold font-display text-slate-900">7,98%</div>
                    <h3 class="font-bold text-slate-800 mt-2">Capaian Pengurangan 3R</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Tingkat pengurangan sampah melalui mekanisme 3R saat ini masih rendah, menunjukkan butuh alat bantu aksi yang lebih menyenangkan.
                    </p>
                </div>
            </div>

            <!-- Two Settings: Sekolah vs Rumah -->
            <div class="rounded-3xl bg-gradient-to-br from-amber-50/60 to-emerald-50/60 border border-slate-200/80 p-8 md:p-10">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div class="space-y-4">
                        <span class="px-3 py-1 rounded-full bg-white border border-amber-200 text-amber-800 font-extrabold text-xs uppercase">
                            Tantangan di Sekolah
                        </span>
                        <h3 class="font-display text-2xl font-bold text-slate-900">Sampah Kantin, Minuman Kemasan, &amp; MBG</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Di lingkungan sekolah (seperti rencana piloting di <strong>SMKN 4 Bogor</strong>), siswa kerap kebingungan menempatkan sisa makanan MBG (Makan Bergizi Gratis), memisahkan sedotan dan tutup cup plastik, atau tidak mengetahui alur akhir tempat sampah sekolah.
                        </p>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Solusi: Ular Tangga GEMBUL &amp; Estafet Pilah dalam bentuk permainan cepat 15–20 menit.
                        </div>
                    </div>

                    <div class="space-y-4 md:border-l md:border-slate-200 md:pl-8">
                        <span class="px-3 py-1 rounded-full bg-white border border-emerald-200 text-emerald-800 font-extrabold text-xs uppercase">
                            Tantangan di Rumah
                        </span>
                        <h3 class="font-display text-2xl font-bold text-slate-900">Kebiasaan Berbeda Antar Anggota Keluarga</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">
                            Di rumah, orang tua dan anak memiliki ritme berbeda dan tidak ada pengingat fisik yang terpampang jelas. GEMBUL memindahkan pesan lingkungan ke tempat yang paling sering dibuka setiap hari: <strong>Pintu Kulkas</strong>.
                        </p>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Solusi: Magnet Kulkas "Pilah di Rumah" dari kardus dan kalender bekas + Tantangan 3–7 Hari.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== CREATIVE ECO-KIT SHOWCASE ==================== -->
        <section id="ecokit" class="mb-24 scroll-mt-28">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-cubes"></i> Komponen Lengkap
                </div>
                <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Creative Eco-Kit: Dua Paket &amp; Satu Penghubung
                </h2>
                <p class="text-slate-600 text-sm sm:text-base mt-3">
                    Toolkit tidak dibuat terlalu minimalis. Dibuat berwujud nyata dari bahan daur ulang (kardus, kertas cetak sisa, magnet lembaran bekas) sehingga hemat biaya dan ramah lingkungan.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Paket 1: Sekolah -->
                <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-6 border border-amber-100">
                            <i class="fa-solid fa-school"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-extrabold text-[11px] uppercase tracking-wider">
                            Paket Sekolah
                        </span>
                        <h3 class="font-display text-2xl font-extrabold text-slate-900 mt-3 mb-2">Games &amp; Kompetisi Seru</h3>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Belajar keputusan pemilahan sampah lewat kompetisi kelompok dan diskusi seru di ruang kelas.
                        </p>

                        <ul class="space-y-4 text-xs text-slate-700">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-dice text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Ular Tangga GEMBUL (30 Petak):</strong>
                                    Papan A2/A3, dadu, pion. Kelompok 4-6 siswa. Tangga untuk kebiasaan baik, ular untuk kelalaian.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-bolt text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Estafet Pilah (8–10 Menit):</strong>
                                    Memindahkan 10-12 kartu bergambar ke wadah kategori yang tepat, fokus pada ketepatan dan alasan.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-table-cells text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Eco-Bingo (3 &times; 3 Grid):</strong>
                                    Icebreaker pengamatan cepat mencocokkan tindakan nyata seperti membawa botol minum ulang.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-clone text-amber-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Kartu Pilah &amp; Kartu Misi:</strong>
                                    Set kartu contoh botol, kardus, sisa nasi, tisu kotor, minyak dengan pertanyaan reflektif.
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-amber-700 font-bold">
                        <span>Durasi Sesi: 45 Menit</span>
                        <span>Sasaran: Siswa SMKN 4</span>
                    </div>
                </div>

                <!-- Paket 2: Rumah -->
                <div class="rounded-3xl bg-white border border-slate-200 p-8 shadow-sm hover:shadow-xl transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-6 border border-emerald-100">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[11px] uppercase tracking-wider">
                            Paket Rumah
                        </span>
                        <h3 class="font-display text-2xl font-extrabold text-slate-900 mt-3 mb-2">Aktivitas Bersama Keluarga</h3>
                        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
                            Pengingat visual yang terpampang setiap hari di pintu kulkas untuk memicu kebiasaan berkelanjutan.
                        </p>

                        <ul class="space-y-4 text-xs text-slate-700">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-magnet text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Magnet Kulkas "Pilah di Rumah":</strong>
                                    Papan magnet 3-4 kategori dari kardus dan magnet lembaran bekas iklan/kalender.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-fire text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Eco-Challenge Keluarga (3–7 Hari):</strong>
                                    Kartu misi sukarela: Hari 1 amati, Hari 2 pisahkan kemasan, Hari 3 ajak keluarga, ulangi hingga hari ke-7.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-square-check text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Mini Tracker Mingguan:</strong>
                                    Papan cek sederhana di balik kertas kalender bekas untuk mencatat aksi tanpa perlu foto ribet.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-sheet-plastic text-emerald-600 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-slate-900 font-bold">Kartu Pilah Mini Dapur:</strong>
                                    Versi ringkas kartu sekolah yang ditempel dekat area memasak sebagai contekan praktis.
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-700 font-bold">
                        <span>Tanpa Bahan Baru Mahal</span>
                        <span>Praktis &amp; Aman</span>
                    </div>
                </div>

                <!-- Paket 3: Penghubung (Eco-Pass) -->
                <div class="rounded-3xl bg-gradient-to-br from-amber-500 to-emerald-600 text-white p-8 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-white/20 text-white flex items-center justify-center text-2xl mb-6 backdrop-blur-md">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-white/20 text-white font-extrabold text-[11px] uppercase tracking-wider backdrop-blur-sm">
                            Penghubung Ekosistem
                        </span>
                        <h3 class="font-display text-2xl font-extrabold text-white mt-3 mb-2">Eco-Pass GEMBUL</h3>
                        <p class="text-xs text-white/90 mb-6 leading-relaxed">
                            Kartu fisik berbasis kertas daur ulang padat atau seed paper dengan kode unik dan QR code ke web Trashily.
                        </p>

                        <ul class="space-y-4 text-xs text-white/90">
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-qrcode text-amber-200 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-white font-bold">Kode Unik &amp; QR Scan:</strong>
                                    Mengarahkan siswa ke halaman aktivitas Trashily untuk melihat progres misi dan rekap skor.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-stamp text-amber-200 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-white font-bold">Stempel / Cek Manual Fasilitator:</strong>
                                    Jika belum terhubung ke sistem digital, kartu tetap berjalan mulus dengan cap manual fisik.
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fa-solid fa-shield-halved text-amber-200 text-sm mt-0.5 shrink-0"></i>
                                <div>
                                    <strong class="text-white font-bold">Privasi Terjaga:</strong>
                                    Didesain anonim tanpa mewajibkan data pribadi atau swafoto siswa yang tidak perlu.
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-4 border-t border-white/20 flex items-center justify-between text-xs text-amber-200 font-bold">
                        <span>Bahan: Kertas Daur Ulang</span>
                        <span>Terintegrasi Trashily</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== INTERACTIVE MINI-GAME SIMULASI ULAR TANGGA ==================== -->
        <section id="simulasi-games" class="mb-24 scroll-mt-28">
            <div class="rounded-3xl bg-white border border-slate-200 p-8 md:p-12 shadow-sm">
                <div class="max-w-3xl mb-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-gamepad"></i> Coba Langsung di Web
                    </div>
                    <h2 class="font-display text-3xl font-extrabold text-slate-900">Simulasi Interaktif: Ular Tangga GEMBUL</h2>
                    <p class="text-slate-600 text-sm mt-1">
                        Rasakan bagaimana keputusan pemilahan sampah menentukan langkahmu. Dadu menentukan petak, kasus menguji pemahamanmu!
                    </p>
                </div>

                <div class="grid lg:grid-cols-12 gap-8 items-center">
                    <!-- Board Grid Preview (Petak 1-30 mini visualizer) -->
                    <div class="lg:col-span-7 bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-6">
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200 text-xs">
                            <span class="font-bold text-slate-700"><i class="fa-solid fa-map mr-1 text-amber-600"></i> Mini Board (Petak 1–30)</span>
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1 text-emerald-700 font-semibold"><i class="fa-solid fa-arrow-trend-up"></i> Tangga (+Langkah)</span>
                                <span class="flex items-center gap-1 text-rose-700 font-semibold"><i class="fa-solid fa-arrow-trend-down"></i> Ular (-Langkah)</span>
                            </div>
                        </div>

                        <!-- 30 Tiles Mini Matrix -->
                        <div id="boardTilesGrid" class="grid grid-cols-6 gap-2 text-center text-xs font-bold font-display">
                            <!-- JS Populates 30 tiles -->
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                            <span>Posisi Pion: <strong id="currentPosText" class="text-amber-600 font-bold">Petak 1</strong></span>
                            <span>Skor Edukasi: <strong id="currentScoreText" class="text-emerald-600 font-bold">0 Poin</strong></span>
                        </div>
                    </div>

                    <!-- Dice & Decision Interactive Box -->
                    <div class="lg:col-span-5 bg-gradient-to-br from-amber-50 to-emerald-50/50 border border-amber-200 rounded-2xl p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-amber-800">Giliran Melempar</span>
                                <span id="diceValueBadge" class="px-2.5 py-0.5 rounded-full bg-white border border-amber-300 text-amber-700 font-bold text-xs">Dadu Siap</span>
                            </div>

                            <div class="text-center py-6">
                                <div id="diceIcon" class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-md text-4xl text-amber-600 border border-amber-200">
                                    <i class="fa-solid fa-dice-one"></i>
                                </div>
                                <div class="mt-4">
                                    <button id="btnRollDice" onclick="rollGembulDice()" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm shadow-md transition active:scale-95">
                                        <i class="fa-solid fa-dice"></i> Lempar Dadu Sekarang!
                                    </button>
                                </div>
                            </div>

                            <!-- Case Box -->
                            <div id="caseBox" class="mt-4 p-4 rounded-xl bg-white border border-slate-200 text-xs">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5 mb-1 text-sm" id="caseTitle">
                                    <i class="fa-solid fa-lightbulb text-amber-500"></i> Siap Memulai Petualangan?
                                </div>
                                <p class="text-slate-600 leading-relaxed" id="caseDesc">
                                    Klik tombol lempar dadu untuk menggerakkan pion dari petak 1 menuju petak 30.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 pt-3 border-t border-amber-200/60 text-right">
                            <button onclick="resetGembulSim()" class="text-xs text-slate-500 hover:text-slate-800 font-semibold underline">
                                Reset Permainan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== JADWAL KEGIATAN GEMBUL (PUBLIK CRUD LIVE) ==================== -->
        <section id="jadwal" class="mb-24 scroll-mt-28">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-xs font-bold uppercase tracking-wider mb-2">
                        <i class="fa-solid fa-calendar-check text-amber-600"></i> Terbuka untuk Umum
                    </div>
                    <h2 class="font-display text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Jadwal &amp; Agenda Kegiatan GEMBUL
                    </h2>
                    <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-2xl">
                        Ikuti sesi roadshow permainan di sekolah, workshop perakitan magnet kulkas daur ulang, aksi susur sungai bersama Alkesa, dan tantangan keluarga.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <?php if ($logged_in && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin/kelola-gembul.php" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow transition">
                            <i class="fa-solid fa-pen-to-square"></i> Kelola Jadwal (Admin)
                        </a>
                    <?php endif; ?>
                    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Trashily%2C%20saya%20ingin%20mengundang%20sesi%20GEMBUL%20ke%20sekolah%20kami" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow transition">
                        <i class="fa-brands fa-whatsapp text-sm"></i> Ajukan Jadwal Sekolah
                    </a>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase">Total Kegiatan</div>
                        <div class="text-2xl font-bold font-display text-slate-900"><?= $stats_kegiatan['total'] ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center"><i class="fa-solid fa-list-check"></i></div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-emerald-700 font-semibold uppercase">Sedang Berjalan</div>
                        <div class="text-2xl font-bold font-display text-emerald-600"><?= $stats_kegiatan['berjalan'] ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-bolt"></i></div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-amber-700 font-semibold uppercase">Akan Datang</div>
                        <div class="text-2xl font-bold font-display text-amber-600"><?= $stats_kegiatan['rencana'] ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-clock"></i></div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-500 font-semibold uppercase">Telah Terlaksana</div>
                        <div class="text-2xl font-bold font-display text-slate-600"><?= $stats_kegiatan['selesai'] ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center"><i class="fa-solid fa-circle-check"></i></div>
                </div>
            </div>

            <!-- Kegiatan Cards Grid -->
            <?php if (empty($jadwal_list)): ?>
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                    <i class="fa-solid fa-calendar-xmark text-4xl text-slate-300 mb-3"></i>
                    <h3 class="font-display font-bold text-lg text-slate-800">Belum Ada Jadwal Kegiatan Aktif</h3>
                    <p class="text-sm text-slate-500 mt-1">Jadwal kegiatan akan segera diperbarui oleh tim koordinator GEMBUL.</p>
                </div>
            <?php else: ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($jadwal_list as $item): 
                        $status_badge = [
                            'rencana' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-300', 'label' => 'Akan Datang'],
                            'berjalan' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-300', 'label' => 'Sedang Berlangsung'],
                            'selesai' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'border' => 'border-slate-300', 'label' => 'Selesai'],
                            'dibatalkan' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'border' => 'border-rose-300', 'label' => 'Dibatalkan'],
                        ][$item['status']] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'label' => $item['status']];

                        $cat_icons = [
                            'Sekolah' => 'fa-school text-amber-600',
                            'Komunitas' => 'fa-users text-emerald-600',
                            'Workshop' => 'fa-hammer text-purple-600',
                            'Aksi Lapangan' => 'fa-person-walking text-blue-600',
                            'Riset' => 'fa-flask text-teal-600',
                        ];
                        $icon = $cat_icons[$item['kategori']] ?? 'fa-calendar-day text-slate-600';
                        $formatted_date = date('d F Y', strtotime($item['tanggal']));
                        $waktu = substr($item['waktu_mulai'], 0, 5) . ($item['waktu_selesai'] ? ' - ' . substr($item['waktu_selesai'], 0, 5) . ' WIB' : ' WIB');
                    ?>
                    <div class="rounded-3xl bg-white border border-slate-200 hover:border-amber-300 p-6 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <!-- Header Card: Category & Status -->
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-xs">
                                    <i class="fa-solid <?= $icon ?>"></i> <?= htmlspecialchars($item['kategori']) ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider border <?= $status_badge['bg'] ?> <?= $status_badge['text'] ?> <?= $status_badge['border'] ?>">
                                    <?= $status_badge['label'] ?>
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="font-display font-extrabold text-lg text-slate-900 group-hover:text-amber-700 transition-colors leading-snug">
                                <?= htmlspecialchars($item['judul_kegiatan']) ?>
                            </h3>

                            <!-- Meta: Date, Time, Location -->
                            <div class="mt-4 space-y-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <i class="fa-regular fa-calendar text-amber-600 w-4 text-center"></i>
                                    <span class="font-semibold text-slate-800"><?= $formatted_date ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fa-regular fa-clock text-amber-600 w-4 text-center"></i>
                                    <span><?= $waktu ?></span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <i class="fa-solid fa-location-dot text-rose-500 w-4 text-center mt-0.5"></i>
                                    <span class="font-medium"><?= htmlspecialchars($item['lokasi']) ?></span>
                                </div>
                            </div>

                            <!-- Description -->
                            <p class="text-xs text-slate-500 mt-4 leading-relaxed line-clamp-3">
                                <?= htmlspecialchars($item['deskripsi']) ?>
                            </p>
                        </div>

                        <!-- Footer Card: Target, Kuota, Action -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-slate-400 font-medium">Target:</span>
                                <span class="font-bold text-slate-700 ml-1"><?= htmlspecialchars($item['target_peserta'] ?: 'Terbuka') ?></span>
                                <?php if ($item['kuota_peserta'] > 0): ?>
                                    <span class="text-slate-400 ml-1">(<?= $item['kuota_peserta'] ?> kuota)</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($item['link_pendaftaran'])): ?>
                                <a href="<?= htmlspecialchars($item['link_pendaftaran']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-sm transition">
                                    Daftar <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            <?php else: ?>
                                <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Trashily%2C%20saya%20tertarik%20dengan%20kegiatan%3A%20<?= urlencode($item['judul_kegiatan']) ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs transition">
                                    <i class="fa-brands fa-whatsapp"></i> Info WA
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ==================== ROADMAP KERJA 10 HARI ==================== -->
        <section class="mb-24">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-timeline"></i> Tahapan Pengembangan
                </div>
                <h2 class="font-display text-3xl font-extrabold text-slate-900 tracking-tight">Rencana Kerja 10 Hari (Fase Prototype)</h2>
                <p class="text-slate-600 text-sm mt-2">
                    Rancangan bertahap mulai dari riset kecil di rumah hingga simulasi internal sebelum deployment resmi di sekolah.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold text-[10px]">Hari 1–3</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Riset &amp; Desain</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Data pemilahan rumah, skenario sampah kantin/MBG, draft papan Ular Tangga, kartu &amp; magnet.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold text-[10px]">Hari 4</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Kampanye Isu</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Publikasi konten edukatif, koordinasi penawaran rencana sesi games ke pihak sekolah.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold text-[10px]">Hari 5–6</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Survei Lapangan</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Pengamatan kondisi sungai bersama Komunitas Alkesa untuk menyelaraskan materi kasus edukasi.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-extrabold text-[10px]">Hari 7</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Aksi Ciliwung</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Mengikuti aksi pemulihan sungai, pencatatan kategori sampah lapangan untuk materi kartu.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-extrabold text-[10px]">Hari 8–9</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Observasi WU</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Cek alur pilah di Bank Sampah WU, rakit prototipe magnet kardus, susun mockup Eco-Pass QR.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-extrabold text-[10px]">Hari 10</span>
                    <h3 class="font-display font-bold text-sm text-slate-900 mt-2">Simulasi Internal</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Playtest Ular Tangga, Estafet, Bingo, dan Eco-Challenge 3–7 hari bersama tim inti.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==================== KOLABORATOR & TIM ==================== -->
        <section class="mb-24 rounded-3xl bg-slate-900 text-white p-8 md:p-12 shadow-xl">
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 text-amber-400 font-bold text-xs uppercase tracking-wider border border-amber-500/30">
                        <i class="fa-solid fa-handshake"></i> Kolaborasi Lintas Peran
                    </span>
                    <h2 class="font-display text-2xl md:text-3xl font-extrabold text-white">
                        Menghubungkan Edukasi Komunitas dengan Sistem Sampah Nyata
                    </h2>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        GEMBUL berfokus pada <strong>desain pengalaman belajar dan material fisik interaktif</strong>, berkolaborasi dengan:
                    </p>
                    <div class="grid sm:grid-cols-2 gap-4 pt-2 text-xs text-slate-300">
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <strong class="text-amber-400 block mb-1 font-bold text-sm">SMKN 4 Bogor</strong>
                            Calon sekolah lokasi piloting sesi games interaktif dan pengenalan Eco-Pass.
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <strong class="text-emerald-400 block mb-1 font-bold text-sm">Komunitas Alkesa</strong>
                            Ruang belajar dan aksi lapangan dampak sampah di aliran Sungai Ciliwung.
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <strong class="text-sky-400 block mb-1 font-bold text-sm">Bank Sampah WU</strong>
                            Lokasi observasi alur penanganan nyata sampah anorganik dan organik.
                        </div>
                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                            <strong class="text-brand-400 block mb-1 font-bold text-sm">Trashily Platform</strong>
                            Pencatatan misi Eco-Pass digital, direktori harga daur ulang, dan reward poin.
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 flex flex-col gap-3 justify-center">
                    <a href="trashsmart.php" class="p-4 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/10 transition flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-trash-can-arrow-up text-brand-400 text-lg"></i>
                            <div>
                                <strong class="text-white block font-bold">TrashSmart IoT</strong>
                                <span class="text-slate-400 text-[11px]">Tempat sampah inspeksi pintar</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-400"></i>
                    </a>

                    <a href="smartkolecer.php" class="p-4 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/10 transition flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-fan text-sky-400 text-lg"></i>
                            <div>
                                <strong class="text-white block font-bold">SmartKolecer IoT</strong>
                                <span class="text-slate-400 text-[11px]">Kolecer dari limbah plastik</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-400"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ==================== CALL TO ACTION ==================== -->
        <section class="rounded-3xl bg-gradient-to-r from-amber-600 via-amber-500 to-emerald-600 p-8 md:p-12 text-slate-950 shadow-xl">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest text-slate-900 bg-white/40 px-3 py-1 rounded-full">
                        Mulai dari Hal Kecil
                    </span>
                    <h2 class="mt-3 font-display text-3xl md:text-4xl font-extrabold text-slate-950">
                        Mengenal sampah tidak harus lewat ceramah panjang.
                    </h2>
                    <p class="text-slate-900/90 text-sm md:text-base mt-2 max-w-xl">
                        Mulai dari satu permainan seru, lanjut ke satu kebiasaan di kulkas rumah, lalu ulangi bersama keluarga dan teman sekolah.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="#jadwal" class="inline-flex items-center gap-2 rounded-full bg-slate-950 hover:bg-slate-900 text-white px-6 py-3.5 font-extrabold text-sm shadow-md transition">
                        <i class="fa-solid fa-calendar-check text-amber-400"></i> Ikuti Jadwal Terbuka
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo%20GEMBUL%2C%20saya%20tertarik%20mengajukan%20sesi%20Creative%20Eco-Kit" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-white/90 hover:bg-white text-slate-950 px-6 py-3.5 font-bold text-sm shadow-sm transition">
                        <i class="fa-brands fa-whatsapp text-emerald-600"></i> Hubungi Fasilitator
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- ==================== FOOTER ==================== -->
    <footer class="mt-20 bg-slate-900 text-slate-400 py-12 px-4 md:px-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <p>&copy; <?= date('Y') ?> <strong>Trashily</strong> &bull; GEMBUL (Gemar Berdampak untuk Lingkungan). Hak Cipta Dilindungi.</p>
            <div class="flex flex-wrap items-center gap-4">
                <a href="trashsmart.php" class="text-slate-300 hover:text-white transition">TrashSmart</a>
                <a href="smartkolecer.php" class="text-slate-300 hover:text-white transition">SmartKolecer</a>
                <a href="gembul.php" class="text-amber-400 hover:text-amber-300 font-bold transition">GEMBUL</a>
                <a href="harga.php" class="text-slate-300 hover:text-white transition">Daftar Harga</a>
                <a href="ulasan.php" class="text-slate-300 hover:text-white transition">Ulasan</a>
            </div>
        </div>
    </footer>

    <!-- ==================== JAVASCRIPT ==================== -->
    <script>
        /* Mobile Menu Toggle */
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        /* Subpage Navbar Scroll Styling */
        function applySubpageNavbarState() {
            const nav = document.getElementById('navbar');
            const navLinkWrapper = document.getElementById('navLinkWrapper');
            const isScrolled = window.scrollY > 20;

            if (nav) {
                nav.classList.toggle('bg-white', isScrolled);
                nav.classList.toggle('border-b-0', isScrolled);
                nav.classList.toggle('shadow-sm', isScrolled);
                nav.classList.toggle('bg-white/80', !isScrolled);
                nav.classList.toggle('border-slate-200/80', !isScrolled);
                nav.classList.toggle('backdrop-blur-xl', !isScrolled);
            }

            if (navLinkWrapper) {
                navLinkWrapper.classList.toggle('rounded-full', !isScrolled);
                navLinkWrapper.classList.toggle('border', !isScrolled);
                navLinkWrapper.classList.toggle('border-slate-200/80', !isScrolled);
                navLinkWrapper.classList.toggle('bg-white/70', !isScrolled);
                navLinkWrapper.classList.toggle('backdrop-blur-md', !isScrolled);
                navLinkWrapper.classList.toggle('px-5', !isScrolled);
                navLinkWrapper.classList.toggle('py-2', !isScrolled);
                navLinkWrapper.classList.toggle('shadow-sm', !isScrolled);

                navLinkWrapper.classList.toggle('border-transparent', isScrolled);
                navLinkWrapper.classList.toggle('bg-transparent', isScrolled);
                navLinkWrapper.classList.toggle('shadow-none', isScrolled);
                navLinkWrapper.classList.toggle('px-0', isScrolled);
                navLinkWrapper.classList.toggle('py-0', isScrolled);
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

        /* ==================== INTERACTIVE BOARD GAME ENGINE ==================== */
        const gembulCases = {
            3: { type: 'ladder', to: 10, title: 'Tangga: Botol Bersih!', desc: 'Kamu memisahkan botol plastik dari kuah bakso kantin & mengeringkannya. Naik ke petak 10!' },
            7: { type: 'snake', to: 2, title: 'Ular: Kemasan Berminyak Campur!', desc: 'Kemasan berminyak dibuang bareng kardus kering daur ulang. Kardus jadi rusak. Turun ke petak 2.' },
            12: { type: 'ladder', to: 19, title: 'Tangga: Pasang Magnet Kulkas!', desc: 'Keluarga memasang pengingat magnet di kulkas dan mencatat 3 hari berturut-turut. Naik ke petak 19!' },
            16: { type: 'snake', to: 8, title: 'Ular: Sisa Makanan di MBG!', desc: 'Kotak susu masih penuh dicampur sisa nasi kotak. Turun ke petak 8.' },
            21: { type: 'ladder', to: 27, title: 'Tangga: Verifikasi Eco-Pass!', desc: 'Menyelesaikan tantangan pilah 7 hari dan distempel oleh fasilitator. Loncat ke petak 27!' },
            28: { type: 'snake', to: 15, title: 'Ular: Sedotan Sekali Pakai!', desc: 'Lupa membawa tumbler dan membuang 5 gelas plastik sekaligus. Turun ke petak 15.' }
        };

        let currentTile = 1;
        let currentScore = 0;

        function renderBoard() {
            const grid = document.getElementById('boardTilesGrid');
            if (!grid) return;
            grid.innerHTML = '';
            for (let i = 1; i <= 30; i++) {
                const tile = document.createElement('div');
                tile.className = 'board-tile p-2 rounded-xl border flex flex-col items-center justify-center min-h-[46px]';
                
                if (i === currentTile) {
                    tile.classList.add('bg-amber-500', 'text-slate-950', 'border-amber-600', 'shadow-md', 'scale-105');
                    tile.innerHTML = `<span class="text-[10px] leading-none">Pion</span><span class="font-extrabold text-sm">${i}</span>`;
                } else if (gembulCases[i]) {
                    if (gembulCases[i].type === 'ladder') {
                        tile.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-300');
                        tile.innerHTML = `<span class="text-[9px] text-emerald-600">&uarr; ${gembulCases[i].to}</span><span>${i}</span>`;
                    } else {
                        tile.classList.add('bg-rose-50', 'text-rose-800', 'border-rose-300');
                        tile.innerHTML = `<span class="text-[9px] text-rose-600">&darr; ${gembulCases[i].to}</span><span>${i}</span>`;
                    }
                } else if (i === 30) {
                    tile.classList.add('bg-amber-100', 'text-amber-900', 'border-amber-400');
                    tile.innerHTML = `<span class="text-[9px]">Finish</span><span>30</span>`;
                } else {
                    tile.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
                    tile.innerHTML = `<span>${i}</span>`;
                }
                grid.appendChild(tile);
            }
            document.getElementById('currentPosText').textContent = `Petak ${currentTile}`;
            document.getElementById('currentScoreText').textContent = `${currentScore} Poin`;
        }

        renderBoard();

        const diceIcons = [
            'fa-dice-one', 'fa-dice-two', 'fa-dice-three', 'fa-dice-four', 'fa-dice-five', 'fa-dice-six'
        ];

        function rollGembulDice() {
            const btn = document.getElementById('btnRollDice');
            const iconWrap = document.getElementById('diceIcon');
            const valBadge = document.getElementById('diceValueBadge');
            const caseTitle = document.getElementById('caseTitle');
            const caseDesc = document.getElementById('caseDesc');

            btn.disabled = true;
            iconWrap.classList.add('dice-spin');

            const roll = Math.floor(Math.random() * 6) + 1;

            setTimeout(() => {
                iconWrap.classList.remove('dice-spin');
                iconWrap.innerHTML = `<i class="fa-solid ${diceIcons[roll - 1]}"></i>`;
                valBadge.textContent = `Dadu: ${roll}`;

                let nextTile = currentTile + roll;
                if (nextTile > 30) nextTile = 30;
                currentTile = nextTile;
                currentScore += 5;

                if (gembulCases[currentTile]) {
                    const c = gembulCases[currentTile];
                    caseTitle.innerHTML = `<i class="fa-solid ${c.type === 'ladder' ? 'fa-arrow-trend-up text-emerald-600' : 'fa-arrow-trend-down text-rose-600'}"></i> ${c.title}`;
                    caseDesc.textContent = c.desc;
                    currentTile = c.to;
                    if (c.type === 'ladder') currentScore += 10;
                    else currentScore = Math.max(0, currentScore - 5);
                } else if (currentTile === 30) {
                    caseTitle.innerHTML = `<i class="fa-solid fa-trophy text-amber-500"></i> Selamat! Kamu Mencapai Finish!`;
                    caseDesc.textContent = `Hebat! Kamu telah menyelesaikan petualangan Ular Tangga GEMBUL dengan total skor ${currentScore} Poin. Saatnya praktikkan di rumah!`;
                } else {
                    caseTitle.innerHTML = `<i class="fa-solid fa-person-walking text-slate-700"></i> Maju ke Petak ${currentTile}`;
                    caseDesc.textContent = `Langkah aman! Ingat selalu: periksa kebersihan kemasan, pisahkan sisa makanan sebelum membuang.`;
                }

                renderBoard();
                btn.disabled = false;
            }, 600);
        }

        function resetGembulSim() {
            currentTile = 1;
            currentScore = 0;
            document.getElementById('caseTitle').innerHTML = `<i class="fa-solid fa-lightbulb text-amber-500"></i> Siap Memulai Petualangan?`;
            document.getElementById('caseDesc').textContent = `Klik tombol lempar dadu untuk menggerakkan pion dari petak 1 menuju petak 30.`;
            document.getElementById('diceValueBadge').textContent = `Dadu Siap`;
            document.getElementById('diceIcon').innerHTML = `<i class="fa-solid fa-dice-one"></i>`;
            renderBoard();
        }
    </script>

    <!-- FLOATING CHATBOT AI -->
    <?php include __DIR__ . '/includes/chatbot.php'; ?>
</body>
</html>
