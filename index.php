<?php
session_start();
require_once 'config/database.php';

$stat_customer = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'] ?? 0;
$stat_setor    = $conn->query("SELECT COUNT(*) as c FROM transaksi WHERE status='selesai'")->fetch_assoc()['c'] ?? 0;
$stat_berat    = $conn->query("SELECT COALESCE(SUM(total_berat),0) as c FROM transaksi WHERE status='selesai'")->fetch_assoc()['c'] ?? 0;
$stat_poin     = $conn->query("SELECT COALESCE(SUM(total_poin),0) as c FROM transaksi WHERE status='selesai'")->fetch_assoc()['c'] ?? 0;

$logged_in = isset($_SESSION['user_id']);
$dash_url  = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

$ulasan_table_exists = $conn->query("SHOW TABLES LIKE 'ulasan'")->num_rows > 0;
$approved_reviews = null;
if ($ulasan_table_exists) {
    $approved_reviews = $conn->query("SELECT u.nama, ul.rating, ul.komentar, ul.created_at
        FROM ulasan ul
        LEFT JOIN users u ON u.id = ul.customer_id
        WHERE ul.status = 'approved'
        ORDER BY ul.created_at DESC
        LIMIT 3");
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trashily — Ecosystem Bank Sampah Digital & Rewards Platform</title>
    <meta name="description" content="Platform bank sampah berbasis poin tercanggih. Setor sampah pilahan, jemput ke rumah, dan tukarkan poin untuk hadiah seperti pulpen, alat tulis, atau sembako.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,700&family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
                            900: '#006e2f',
                            950: '#003f1d',
                        },
                        darkNavy: '#0b1c30',
                        surfaceLight: '#f8fafc',
                    },
                    fontFamily: {
                        display: ['Plus Jakarta Sans', 'sans-serif'],
                        body: ['Be Vietnam Pro', 'sans-serif'],
                    },
                    boxShadow: {
                        'glow': '0 0 35px rgba(34, 197, 94, 0.2)',
                        'glow-lg': '0 0 60px rgba(34, 197, 94, 0.3)',
                        'glass': '0 20px 50px rgba(11, 28, 48, 0.05), 0 2px 6px rgba(0, 110, 47, 0.04)',
                        'card': '0 10px 30px -5px rgba(15, 23, 42, 0.05), 0 4px 12px -2px rgba(15, 23, 42, 0.03)',
                        'premium': '0 25px 60px -15px rgba(0, 110, 47, 0.12)',
                    }
                }
            }
        }
    </script>

    <style>
        /* CSS Root variables for original Hero styling compatibility */
        :root {
            --background: #edf6f0;
            --surface: #f8f9ff;
            --on-surface: #0b1c30;
            --on-surface-variant: #3d4a3d;
            --primary: #006e2f;
            --primary-container: #22c55e;
            --on-primary-container: #004b1e;
            --inverse-primary: #4ae176;
            --secondary: #1f6c3a;
            --green-vivid: #22c55e;
            --green-deep: #006e2f;
            --green-mid: #1f6c3a;
            --green-pale: #dcfce7;
            --ink: #0b1c30;
            --ink-muted: #55615a;
            --white: #ffffff;
            --font-display: 'Plus Jakarta Sans', sans-serif;
            --font-body: 'Be Vietnam Pro', sans-serif;
            --container: 1240px;
            --shadow-float: 0 16px 40px rgba(0, 110, 47, .18);
        }

        body {
            font-family: var(--font-body);
            background: #f8fafc;
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Nav & Hero specific styling preserved for 100% exact look */
        .hero {
            min-height: 92vh;
            background: linear-gradient(135deg, #0c7b3b 0%, #006e2f 38%, #054b25 100%);
            display: flex;
            align-items: center;
            padding: 110px 40px 80px;
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .06);
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 20%, rgba(74, 225, 118, .18) 0%, transparent 23%),
                radial-gradient(circle at 80% 75%, rgba(255, 255, 255, .08) 0%, transparent 22%),
                url("data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%234ae176' fill-opacity='0.045'%3E%3Cpath d='M32 32c0-8.837-7.163-16-16-16S0 23.163 0 32s7.163 16 16 16 16-7.163 16-16zm16-16c0-8.837-7.163-16-16-16S16 7.163 16 16s7.163 16 16 16 16-7.163 16-16z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            opacity: .85;
        }

        .hero-orb {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            background: radial-gradient(circle, rgba(74, 225, 118, .18) 0%, transparent 68%);
            filter: blur(6px);
        }

        .hero-orb-1 {
            width: 650px;
            height: 650px;
            top: -260px;
            left: -200px;
        }

        .hero-orb-2 {
            width: 450px;
            height: 450px;
            bottom: -160px;
            right: 5%;
        }

        .hero-inner {
            max-width: var(--container);
            margin: 0 auto;
            width: 100%;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-family: var(--font-display);
            font-size: 54px;
            font-weight: 800;
            line-height: 64px;
            letter-spacing: -0.03em;
            color: #fff;
            margin-bottom: 22px;
            text-shadow: 0 14px 52px rgba(0, 0, 0, .18);
        }

        .hero h1 em {
            font-style: italic;
            color: var(--inverse-primary);
        }

        .hero-desc {
            font-family: var(--font-body);
            font-size: 18px;
            line-height: 28px;
            color: rgba(255, 255, 255, .88);
            max-width: 520px;
            margin-bottom: 36px;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn-pill-white {
            background: linear-gradient(135deg, #ffffff 0%, #f2fff5 100%);
            color: var(--ink);
            box-shadow: 0 10px 30px rgba(0, 110, 47, .18);
            padding: 14px 28px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 15px;
            transition: all .25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pill-white:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(0, 110, 47, .28);
        }

        .btn-pill-ghost {
            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1.5px solid rgba(255, 255, 255, .35);
            backdrop-filter: blur(8px);
            padding: 14px 28px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 15px;
            transition: all .25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-pill-ghost:hover {
            background: rgba(255, 255, 255, .22);
            transform: translateY(-3px);
        }

        .hero-visual {
            position: relative;
            padding: 24px 20px;
        }

        .hero-img-wrap {
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .hero-img-wrap img {
            width: 100%;
            height: auto;
            display: block;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.18));
        }

        @media(max-width: 900px) {
            .hero {
                padding: 110px 20px 60px;
            }

            .hero-inner {
                grid-template-columns: 1fr;
                gap: 48px;
                text-align: center;
            }

            .hero h1 {
                font-size: 38px;
                line-height: 46px;
            }

            .hero-desc {
                max-width: none;
            }

            .hero-btns {
                justify-content: center;
            }

            .hero-visual {
                max-width: 440px;
                margin: 0 auto;
            }
        }

        /* Custom Scrollbar & Animations */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #22c55e;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #16a34a;
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity .7s cubic-bezier(0.16, 1, 0.3, 1), transform .7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Marquee Animation */
        @keyframes marquee {
            0% {
                transform: translateX(0%);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        @keyframes marqueeRev {
            0% {
                transform: translateX(-50%);
            }

            100% {
                transform: translateX(0%);
            }
        }

        .animate-marquee {
            display: flex;
            width: max-content;
            animation: marquee 35s linear infinite;
        }

        .animate-marquee-rev {
            display: flex;
            width: max-content;
            animation: marqueeRev 38s linear infinite;
        }

        .marquee-container:hover .animate-marquee,
        .marquee-container:hover .animate-marquee-rev {
            animation-play-state: paused;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- ==================== CLEAN NAVBAR ==================== -->
    <header id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 px-4 md:px-10 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="#" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl overflow-hidden group-hover:scale-105 transition-transform">
                    <img src="assets/brand.png" alt="Trashily" style="width:100%;height:100%;object-fit:contain">
                </div>
                <span class="font-display font-extrabold text-xl text-white tracking-tight group-hover:text-brand-300 transition-colors" id="navBrandName">Trashily<span class="text-brand-300">.</span></span>
            </a>

            <!-- Desktop Navigation Links -->
            <nav id="navLinkWrapper" class="hidden lg:flex items-center gap-6 rounded-full border border-white/20 bg-white/10 backdrop-blur-sm px-5 py-2 nav-link-group">
                <a href="#tentang-kami" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">Tentang Kami</a>
                <a href="#cara-kerja" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">Cara Kerja</a>
                <a href="#katalog" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">Direktori Sampah</a>
                <div class="relative group">
                    <button type="button" class="flex items-center gap-1.5 text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">
                        Layanan <i class="fa-solid fa-chevron-down text-[10px] opacity-80"></i>
                    </button>
                    <div class="absolute left-1/2 -translate-x-1/2 top-full mt-3 min-w-[180px] opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all duration-200 rounded-2xl border border-white/20 bg-white/95 shadow-xl p-2 backdrop-blur-md">
                        <a href="harga.php" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-800">Daftar Harga</a>
                        <a href="ulasan.php" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-800">Ulasan</a>
                       <a href="smartkolecer.php" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 hover:bg-brand-50 hover:text-brand-800">SmartKolecer</a>
                    </div>
                </div>
                <a href="#ekosistem" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">Keunggulan</a>
                <a href="#rewards" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">Hadiah</a>
                <a href="#faq" class="text-sm font-bold text-white hover:text-brand-200 transition-colors nav-link-item">FAQ</a>
            </nav>

            <!-- CTA Actions -->
            <div class="hidden md:flex items-center gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white hover:bg-brand-50 text-brand-900 font-bold text-sm shadow-md transition-all hover:-translate-y-0.5" id="navBtnFill">
                        <i class="nav-btn-icon fa-solid fa-gauge text-brand-600"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="px-5 py-2.5 rounded-full text-white hover:text-brand-100 font-semibold text-sm transition-all border border-white/30 hover:bg-white/10" id="navBtnGhost">
                        Masuk
                    </a>
                    <a href="auth/register.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white hover:bg-brand-50 text-brand-900 font-bold text-sm shadow-lg transition-all hover:-translate-y-0.5" id="navBtnFill">
                        <i class="nav-btn-icon fa-solid fa-user-plus text-brand-600 text-xs"></i> Daftar Gratis
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Burger -->
            <button id="burgerBtn" onclick="toggleMobileMenu()" class="lg:hidden text-white hover:text-brand-200 p-2 focus:outline-none" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Mobile Drawer Navigation -->
        <div id="mobileMenu" class="hidden lg:hidden mt-3 rounded-2xl p-6 space-y-2 bg-white border border-slate-200 mobile-nav-shell">
            <div id="mobileNavWrapper" class="space-y-2 rounded-2xl bg-white py-2 mobile-nav-list">
                <a href="#tentang-kami" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Tentang Kami</a>
                <a href="#cara-kerja" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Cara Kerja</a>
                <a href="#katalog" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Direktori Sampah</a>
                <a href="harga.php" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Daftar Harga</a>
                <a href="ulasan.php" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Ulasan</a>
                <a href="smartkolecer.php" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">SmartKolecer</a>
                <a href="#ekosistem" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Keunggulan</a>
                <a href="#rewards" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5 border-b border-slate-100">Hadiah</a>
                <a href="#faq" onclick="toggleMobileMenu()" class="block text-slate-800 font-bold hover:text-brand-600 text-base py-2.5">FAQ</a>
            </div>
            <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                <?php if ($logged_in): ?>
                    <a href="<?= $dash_url ?>" class="w-full text-center py-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md">
                        <i class="fa-solid fa-gauge mr-1.5"></i> Dashboard Saya
                    </a>
                <?php else: ?>
                    <a href="auth/login.php" class="w-full text-center py-3 rounded-xl bg-slate-100 text-slate-800 font-bold text-sm hover:bg-slate-200">Masuk</a>
                    <a href="auth/register.php" class="w-full text-center py-3 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md hover:bg-brand-700">Daftar Gratis</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ==================== HERO SECTION (PRESERVED EXCLUSIVELY) ==================== -->
    <section class="hero">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-inner">
            <div class="hero-content">
                <h1>Ubah Sampah Jadi<br><em>Manfaat Nyata</em></h1>
                <p class="hero-desc">Setor sampah pilahan, kumpulkan poin, dan tukarkan dengan hadiah. Menjaga lingkungan kini lebih mudah dan menguntungkan.</p>
                <div class="hero-btns">
                    <?php if ($logged_in): ?>
                        <a href="<?= $dash_url ?>" class="btn btn-pill-white"><i class="fa-solid fa-gauge"></i> Buka Dashboard</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="btn btn-pill-white"><i class="fa-solid fa-user-plus"></i> Daftar Gratis</a>
                        <a href="#cara-kerja" class="btn btn-pill-ghost"><i class="fa-solid fa-circle-play"></i> Cara Kerja</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-img-wrap">
                    <img src="assets/illustration-hero.png" alt="Ilustrasi pengelolaan sampah digital">
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIGHT MODE LIVE METRICS BENTO BAR ==================== -->
    <section class="relative z-20 -mt-10 px-4 md:px-8">
        <div class="max-w-7xl mx-auto bg-white/95 backdrop-blur-xl border border-slate-200/80 rounded-3xl p-6 md:p-10 shadow-xl shadow-brand-900/5">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 divide-y md:divide-y-0 md:divide-x divide-slate-100">
                <!-- Stat 1 -->
                <div class="text-center pt-4 md:pt-0 reveal">
                    <div class="flex items-center justify-center gap-2 mb-2 text-brand-600 text-xs font-extrabold uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-ping"></span> Member Aktif
                    </div>
                    <div class="text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight stat-counter" data-target="<?= $stat_customer + 51 ?>">
                        <?= number_format($stat_customer + 51) ?>+
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Komunitas Penyelamat Lingkungan</p>
                </div>
                <!-- Stat 2 -->
                <div class="text-center pt-4 md:pt-0 reveal" style="transition-delay: .1s">
                    <div class="flex items-center justify-center gap-2 mb-2 text-emerald-600 text-xs font-extrabold uppercase tracking-widest">
                        <i class="fa-solid fa-circle-check"></i> Transaksi Berhasil
                    </div>
                    <div class="text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight stat-counter" data-target="<?= $stat_setor + 43 ?>">
                        <?= number_format($stat_setor + 43) ?>+
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Setor &amp; Penjemputan Selesai</p>
                </div>
                <!-- Stat 3 -->
                <div class="text-center pt-4 md:pt-0 reveal" style="transition-delay: .2s">
                    <div class="flex items-center justify-center gap-2 mb-2 text-brand-600 text-xs font-extrabold uppercase tracking-widest">
                        <i class="fa-solid fa-recycle"></i> Sampah Terkelola
                    </div>
                    <div class="text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight stat-counter" data-target="<?= $stat_berat + 56 ?>" data-suffix=" kg">
                        <?= number_format($stat_berat + 56, 0) ?> kg
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Didaur Ulang dari TPA</p>
                </div>
                <!-- Stat 4 -->
                <div class="text-center pt-4 md:pt-0 reveal" style="transition-delay: .3s">
                    <div class="flex items-center justify-center gap-2 mb-2 text-amber-600 text-xs font-extrabold uppercase tracking-widest">
                        <i class="fa-solid fa-coins"></i> Total Poin Dibagikan
                    </div>
                    <div class="text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight stat-counter" data-target="<?= $stat_poin + 2101 ?>">
                        <?= number_format($stat_poin + 2101) ?>
                    </div>
                    <p class="text-xs text-slate-500 font-medium mt-1">Diklaim Menjadi Rewards</p>
                </div>
            </div>

            <!-- Live Impact Banner Footer -->
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-600">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                    <span class="px-3 py-1 rounded-full bg-brand-100 text-brand-800 font-extrabold border border-brand-200">Eco-Impact Live</span>
                    <span>Setara dengan <strong class="text-slate-900 font-bold">~3.8 Ton CO2</strong> emisi dikurangi &amp; <strong class="text-slate-900 font-bold">162 Pohon</strong> terselamatkan!</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                    <span class="font-semibold text-slate-700">Sistem Verifikasi Digital 24/7 Operational</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== ABOUT / TENTANG KAMI SECTION ==================== -->
    <section id="tentang-kami" class="py-24 px-4 md:px-8 bg-gradient-to-b from-surfaceLight to-white border-t border-slate-200/80 relative overflow-hidden">
        <!-- Subtle Background Glow Elements -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Section Header -->
            <div class="text-center max-w-4xl mx-auto mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-users-rays text-brand-600"></i> Inisiatif Youth Advisory Bogor
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight leading-tight">
                    Membangun Generasi Muda Berdaya Lewat <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-emerald-500">Kreativitas &amp; Daur Ulang</span>
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4 leading-relaxed">
                    Diinisiasi oleh <strong>Ketua Youth Advisory Bogor</strong> bersama seluruh anggota, Trashily lahir bukan hanya sebagai bank sampah digital, namun sebagai wadah eksplorasi kewirausahaan hijau bagi orang muda.
                </p>
            </div>

            <!-- Story & Pillars Bento Grid -->
            <div class="grid grid-cols-1 items-stretch mb-20">
                <!-- Main Story Card (Left 7 Cols) -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-8 md:p-10 border border-slate-200/80 shadow-xl shadow-slate-200/40 flex flex-col justify-between reveal">
                    <div>
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 text-xl font-bold shadow-sm">
                                <i class="fa-solid fa-seedling"></i>
                            </div>
                            <div>
                                <h3 class="font-display font-extrabold text-2xl text-slate-900">Perjalanan 1+ Tahun Trashily</h3>
                                <p class="text-xs font-semibold text-brand-700 uppercase tracking-wider">Akselerasi Ecopreneurship Pemuda</p>
                            </div>
                        </div>

                        <div class="space-y-4 text-slate-600 text-sm md:text-base leading-relaxed">
                            <p>
                                <strong>Trashily</strong> berawal dari inisiatif visioner <strong>Ketua Youth Advisory Bogor</strong> yang kemudian dikembangkan, diuji, dan dipublikasikan secara kolaboratif bersama seluruh <strong>anggota Youth Advisory Bogor</strong>.
                            </p>
                            <p>
                                Kami meyakini bahwa tantangan sampah anorganik di perkotaan tidak cukup diselesaikan hanya dengan penampungan biasa. Trashily hadir merevolusi paradigma tersebut dengan menjadikan platform digital ini sebagai <strong>wadah pemantik kreativitas generasi muda</strong>, mendorong mereka mengolah barang bekas menjadi produk inovatif bernilai ekonomi tinggi, serta melahirkan bibit wirausahawan muda (<em>ecopreneurs</em>).
                            </p>
                            <p>
                                Selama <strong>kurang lebih 1 tahun berjalan</strong>, ekosistem Trashily telah konsisten mendampingi masyarakat memilah sampah, menggerakkan aksi nyata lingkungan, dan membuktikan bahwa sampah yang terkelola dengan baik adalah modal awal menuju kemandirian ekonomi pemuda.
                            </p>
                        </div>
                    </div>

                    <!-- Milestone Highlights -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-8 mt-8 border-t border-slate-100">
                        <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                            <div class="text-2xl md:text-3xl font-display font-extrabold text-brand-700">1+ Thn</div>
                            <div class="text-xs text-slate-500 font-medium mt-0.5">Operasional Berkelanjutan</div>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                            <div class="text-2xl md:text-3xl font-display font-extrabold text-brand-700">100%</div>
                            <div class="text-xs text-slate-500 font-medium mt-0.5">Karya Pemuda Bogor</div>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                            <div class="text-2xl md:text-3xl font-display font-extrabold text-brand-700">Upcycle</div>
                            <div class="text-xs text-slate-500 font-medium mt-0.5">Wirausaha Barang Bekas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==================== GALLERY / FOTO HASIL KREATIFITAS & AKTIVITAS ==================== -->
            <div class="reveal">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                    <div>
                        <span class="text-xs font-extrabold uppercase tracking-widest text-brand-600">Showcase Karya &amp; Kegiatan</span>
                        <h3 class="text-2xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight mt-1">
                            Galeri Produk Daur Ulang &amp; Aktivitas Pemuda
                        </h3>
                    </div>
                    <p class="text-slate-500 text-sm max-w-md">
                        Bukti nyata kreativitas pemuda Youth Advisory Bogor dalam mentransformasikan limbah anorganik menjadi produk bernilai estetika dan daya jual.
                    </p>
                </div>

                <!-- 4 Cards Photo Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Foto 1: Aksesoris & Tas Upcycle -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group flex flex-col">
                        <div class="relative h-56 overflow-hidden bg-slate-100">
                            <img src="./assets/result/4.png" alt="Produk Upcycling Limbah Plastik" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">
                                    Fashion Upcycling
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors">Pouch &amp; Tas Jinjing Daur Ulang</h4>
                                <p class="text-slate-500 text-xs mt-2 leading-relaxed">Produk fashion fungsional hasil pengolahan limbah kantong kresek dan plastik kemasan tebal yang dipress dan dijahit estetik.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-brand-700 font-bold">
                                <span><i class="fa-solid fa-tag mr-1"></i> Nilai Ekonomi</span>
                                <span class="text-slate-400 font-normal">Karya Pemuda</span>
                            </div>
                        </div>
                    </div>

                    <!-- Foto 2: Eco-Planter & Home Decor -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group flex flex-col">
                        <div class="relative h-56 overflow-hidden bg-slate-100">
                            <img src="./assets/result/2.png" alt="Pot Tanaman Daur Ulang Kaca & Botol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">
                                    Eco-Home Decor
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors">Tempat Minuman</h4>
                                <p class="text-slate-500 text-xs mt-2 leading-relaxed">Minuman yang disajikan dalam wadah ramah lingkungan yang terbuat dari bahan daur ulang.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-brand-700 font-bold">
                                <span><i class="fa-solid fa-leaf mr-1"></i> Ramah Lingkungan</span>
                                <span class="text-slate-400 font-normal">Karya Pemuda</span>
                            </div>
                        </div>
                    </div>

                    <!-- Foto 3: Workshop & Aktivitas Komunitas -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group flex flex-col">
                        <div class="relative h-56 overflow-hidden bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1528605248644-14dd04022da1?auto=format&fit=crop&w=800&q=80" alt="Workshop Kreativitas Youth Advisory Bogor" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">
                                    Youth Workshop
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors">Pelatihan Wirausaha Kreatif</h4>
                                <p class="text-slate-500 text-xs mt-2 leading-relaxed">Kegiatan rutin anggota Youth Advisory Bogor dalam mengedukasi teknik penyortiran, pembersihan, dan upcycling limbah anorganik.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-brand-700 font-bold">
                                <span><i class="fa-solid fa-users mr-1"></i> Kolaboratif</span>
                                <span class="text-slate-400 font-normal">Aktivitas Rutin</span>
                            </div>
                        </div>
                    </div>

                    <!-- Foto 4: Stationery & Kerajinan Kertas Daur Ulang -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 overflow-hidden shadow-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 group flex flex-col">
                        <div class="relative h-56 overflow-hidden bg-slate-100">
                            <img src="./assets/result/7.jpeg" alt="Stationery & Kerajinan Kertas Daur Ulang" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 left-3">
                                <span class="px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">
                                    Eco-Stationery
                                </span>
                            </div>
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors">Notebook &amp; Kemasan Daur Ulang</h4>
                                <p class="text-slate-500 text-xs mt-2 leading-relaxed">Kardus dan kertas HVS bekas yang diproses menjadi buku catatan daur ulang, gift box, dan souvenir bernilai jual premium.</p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-brand-700 font-bold">
                                <span><i class="fa-solid fa-star mr-1"></i> Produk Unggulan</span>
                                <span class="text-slate-400 font-normal">Karya Pemuda</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manifesto / Inspirational Quote Box -->
            <div class="mt-16 bg-gradient-to-r from-brand-900 via-brand-800 to-emerald-900 rounded-3xl p-8 md:p-12 text-white relative overflow-hidden shadow-xl reveal">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-start gap-4 max-w-3xl">
                        <div class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-brand-300 text-2xl flex-shrink-0">
                            <i class="fa-solid fa-quote-left"></i>
                        </div>
                        <div>
                            <blockquote class="text-base md:text-xl font-display font-medium text-slate-100 italic leading-snug">
                                "Kami percaya bahwa pemuda bukan hanya penerus masa depan, melainkan penggerak utama perubahan hari ini. Bersama Trashily, kami membuktikan sampah bukan akhir dari suatu barang, tetapi awal dari kreativitas dan kemandirian wirausaha."
                            </blockquote>
                            <div class="mt-4 text-xs font-bold uppercase tracking-widest text-brand-300 flex flex-col md:flex-row items-start md:items-center gap-2">
                                <span>Inisiator &amp; Pengembang:</span>
                                <span class="text-white font-extrabold">Youth Advisory Bogor</span>
                            </div>
                        </div>
                    </div>
                    <a href="#cara-kerja" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-400 hover:bg-brand-300 text-brand-950 font-bold text-sm shadow-lg transition-all hover:scale-105 whitespace-nowrap">
                        <span>Ikut Berkontribusi</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIGHT MODE ALUR 4 LANGKAH KERJA ==================== -->
    <section id="cara-kerja" class="py-24 px-4 md:px-8 bg-white border-t border-slate-200/80 relative">
        <div class="max-w-7xl mx-auto">
            <!-- Section Title -->
            <div class="text-center max-w-3xl mx-auto mb-20 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-route"></i> Simple Workflow
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Mulai Daur Ulang dalam 4 Langkah Simpel
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4">
                    Tanpa ribet! Sistem otomatisasi Trashily memudahkan siapa pun berpartisipasi dan mendapatkan keuntungan langsung.
                </p>
            </div>

            <!-- 4 Step Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                <!-- Step 1 -->
                <div class="bg-slate-50/80 border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col justify-between reveal">
                    <div>
                        <div class="flex items-center justify-between mb-8">
                            <span class="text-xs font-extrabold px-3 py-1.5 rounded-full bg-brand-100 text-brand-800 border border-brand-200 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                Langkah 01
                            </span>
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-brand-600 text-xl group-hover:scale-110 group-hover:border-brand-500 transition-all">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-3 group-hover:text-brand-700 transition-colors">Buat Akun Digital</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Daftar gratis hanya dengan email atau nomor WhatsApp dalam waktu kurang dari 60 detik tanpa syarat rumit.</p>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-200 text-xs text-slate-500 font-semibold flex items-center justify-between">
                        <span>Aktivasi Instan</span>
                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-brand-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-slate-50/80 border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col justify-between reveal" style="transition-delay: .1s">
                    <div>
                        <div class="flex items-center justify-between mb-8">
                            <span class="text-xs font-extrabold px-3 py-1.5 rounded-full bg-brand-100 text-brand-800 border border-brand-200 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                Langkah 02
                            </span>
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-emerald-600 text-xl group-hover:scale-110 group-hover:border-brand-500 transition-all">
                                <i class="fa-solid fa-recycle"></i>
                            </div>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-3 group-hover:text-brand-700 transition-colors">Pilah Sampah Rumah</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Pisahkan sampah kering berdasarkan kategori: Plastik, Kertas, Logam, Botol Kaca, atau Minyak Jelantah.</p>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-200 text-xs text-slate-500 font-semibold flex items-center justify-between">
                        <span>Panduan Pemilahan</span>
                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-brand-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-slate-50/80 border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col justify-between reveal" style="transition-delay: .2s">
                    <div>
                        <div class="flex items-center justify-between mb-8">
                            <span class="text-xs font-extrabold px-3 py-1.5 rounded-full bg-brand-100 text-brand-800 border border-brand-200 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                Langkah 03
                            </span>
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-blue-600 text-xl group-hover:scale-110 group-hover:border-brand-500 transition-all">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-3 group-hover:text-brand-700 transition-colors">Setor / Penjemputan</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Bawa langsung ke Dropbox Trashily terdekat atau minta kurir kami datang menjemput ke pintu rumah Anda.</p>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-200 text-xs text-slate-500 font-semibold flex items-center justify-between">
                        <span>Door-to-Door Pickup</span>
                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-brand-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="bg-slate-50/80 border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col justify-between reveal" style="transition-delay: .3s">
                    <div>
                        <div class="flex items-center justify-between mb-8">
                            <span class="text-xs font-extrabold px-3 py-1.5 rounded-full bg-brand-100 text-brand-800 border border-brand-200 group-hover:bg-brand-600 group-hover:text-white transition-colors">
                                Langkah 04
                            </span>
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-amber-600 text-xl group-hover:scale-110 group-hover:border-brand-500 transition-all">
                                <i class="fa-solid fa-pen-nib"></i>
                            </div>
                        </div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-3 group-hover:text-brand-700 transition-colors">Tukarkan Poin Hadiah</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Poin otomatis bertambah setelah penimbangan digital. Tukarkan kapan saja ke hadiah pulpen, alat tulis, atau sembako.</p>
                    </div>
                    <div class="mt-8 pt-4 border-t border-slate-200 text-xs text-slate-500 font-semibold flex items-center justify-between">
                        <span>Instan Cashout</span>
                        <i class="fa-solid fa-chevron-right text-slate-400 group-hover:text-brand-600 group-hover:translate-x-1 transition-all"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIGHT MODE DIREKTORI SAMPAH & RATE MARQUEE ==================== -->
    <section id="katalog" class="py-24 px-4 md:px-8 bg-slate-50 border-t border-slate-200/80 overflow-hidden">
        <div class="max-w-7xl mx-auto mb-12">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 reveal">
                <div>
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                        <i class="fa-solid fa-list-check"></i> Transparent Pricing
                    </span>
                    <h2 class="text-3xl md:text-4xl font-display font-extrabold text-slate-900 tracking-tight">
                        Direktori Sampah &amp; Estimasi Rate Poin
                    </h2>
                </div>
                <p class="text-slate-600 text-sm max-w-md">
                    Seluruh nilai poin dihitung berdasarkan bobot riil dan standar industri daur ulang secara adil &amp; transparan.
                </p>
            </div>
        </div>

        <!-- Infinite Scroll Ticker 1 -->
        <div class="marquee-container overflow-hidden py-3 bg-white border-y border-slate-200 shadow-sm">
            <div class="animate-marquee flex gap-4">
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center"><i class="fa-solid fa-bottle-water"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Botol Plastik PET Bening</span><span class="text-xs text-brand-700 font-semibold">15 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-box"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kardus &amp; Box Bekas</span><span class="text-xs text-amber-700 font-semibold">12 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center"><i class="fa-solid fa-shield-cat"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kaleng Alumunium Minuman</span><span class="text-xs text-blue-700 font-semibold">30 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-700 flex items-center justify-center"><i class="fa-solid fa-droplet"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Minyak Jelantah Dapur</span><span class="text-xs text-orange-700 font-semibold">22 Poin / Liter</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center"><i class="fa-solid fa-bolt"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kabel Tembaga &amp; E-Waste</span><span class="text-xs text-purple-700 font-semibold">40 Poin / kg</span></div>
                </div>
                <!-- Duplicated for smooth loop -->
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center"><i class="fa-solid fa-bottle-water"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Botol Plastik PET Bening</span><span class="text-xs text-brand-700 font-semibold">15 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fa-solid fa-box"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kardus &amp; Box Bekas</span><span class="text-xs text-amber-700 font-semibold">12 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center"><i class="fa-solid fa-shield-cat"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kaleng Alumunium Minuman</span><span class="text-xs text-blue-700 font-semibold">30 Poin / kg</span></div>
                </div>
            </div>
        </div>

        <!-- Infinite Scroll Ticker 2 (Reverse) -->
        <div class="marquee-container overflow-hidden py-3 bg-white border-b border-slate-200 shadow-sm mt-3">
            <div class="animate-marquee-rev flex gap-4">
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-wine-bottle"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Botol Kaca Sirup / Kecap</span><span class="text-xs text-emerald-700 font-semibold">10 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fa-solid fa-shirt"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kain &amp; Tekstil Afkir</span><span class="text-xs text-rose-700 font-semibold">8 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center"><i class="fa-solid fa-newspaper"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kertas HVS &amp; Majalah</span><span class="text-xs text-sky-700 font-semibold">14 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-yellow-100 text-yellow-700 flex items-center justify-center"><i class="fa-solid fa-industry"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Besi &amp; Seng Konstruksi</span><span class="text-xs text-yellow-700 font-semibold">25 Poin / kg</span></div>
                </div>
                <!-- Duplicated -->
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fa-solid fa-wine-bottle"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Botol Kaca Sirup / Kecap</span><span class="text-xs text-emerald-700 font-semibold">10 Poin / kg</span></div>
                </div>
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-slate-50 border border-slate-200/90 whitespace-nowrap">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fa-solid fa-shirt"></i></div>
                    <div><span class="font-bold text-sm text-slate-900 block">Kain &amp; Tekstil Afkir</span><span class="text-xs text-rose-700 font-semibold">8 Poin / kg</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIGHT MODE EKOSISTEM FITUR UNGGULAN ==================== -->
    <section id="ekosistem" class="py-24 px-4 md:px-8 bg-white border-t border-slate-200/80 relative">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center max-w-4xl mx-auto mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-cubes"></i> Advanced Platform
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Fitur Unggulan Berstandar Enterprise
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4">
                    Dirancang dengan standar teknologi modern untuk memastikan kenyamanan, keamanan transaksi, dan transparansi penuh bagi seluruh pengguna.
                </p>
            </div>

            <!-- Bento Grid Layout -->
            <div class="grid grid-cols-1 gap-6">
                <!-- Large Card 1 (Span 2 cols) -->
                <div class="md:col-span-2 bg-gradient-to-br from-brand-900 via-brand-800 to-brand-950 rounded-3xl p-8 md:p-10 shadow-xl shadow-brand-900/10 text-white relative overflow-hidden group reveal">
                    <div class="absolute top-0 right-0 w-80 h-80 bg-brand-400/10 rounded-full blur-3xl pointer-events-none group-hover:bg-brand-400/20 transition-all"></div>
                    <div class="relative z-10">
                        <span class="px-3 py-1 rounded-full bg-white/10 text-brand-100 font-bold text-xs border border-white/20 mb-6 inline-block">
                            Layanan On-Demand
                        </span>
                        <h3 class="font-display font-bold text-2xl md:text-3xl text-white mb-4">
                            Penjemputan Door-to-Door Presisi Tinggi
                        </h3>
                        <p class="text-brand-100 text-base leading-relaxed max-w-xl mb-8">
                            Tak perlu repot keluar rumah! Cukup ajukan jadwal melalui aplikasi, dan armada ramah lingkungan Trashily akan menjemput sampah pilahan langsung ke depan pintu Anda.
                        </p>

                        <!-- Feature bullets -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-black/20 border border-white/10">
                                <i class="fa-solid fa-location-dot text-brand-300 text-lg"></i>
                                <span class="text-sm font-semibold text-white">Lacak Status Kurir Real-Time</span>
                            </div>
                            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-black/20 border border-white/10">
                                <i class="fa-solid fa-calendar-check text-emerald-300 text-lg"></i>
                                <span class="text-sm font-semibold text-white">Jadwal Penjemputan Fleksibel</span>
                            </div>
                        </div>
                    </div>
                                    <!-- Card 2: SmartKolecer -->
                <div class="bg-brand-50 border border-brand-100 rounded-3xl p-8 md:p-10 shadow-sm hover:shadow-xl transition-all duration-300 relative overflow-hidden group reveal">
                    <div class="absolute -right-10 -top-10 w-64 h-64 bg-brand-300/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="relative z-10 grid md:grid-cols-3 gap-8 items-center">
                        <div class="md:col-span-2">
                            <span class="px-3 py-1 rounded-full bg-white text-brand-800 font-bold text-xs border border-brand-200 mb-6 inline-block">
                                Inovasi Daur Ulang &amp; IoT
                            </span>
                            <h3 class="font-display font-bold text-2xl md:text-3xl text-slate-900 mb-4">
                                Sampahmu Didaur Ulang Jadi SmartKolecer
                            </h3>
                            <p class="text-slate-600 text-base leading-relaxed max-w-xl mb-6">
                                Sampah yang disetor di Trashily tidak berhenti di pengepul. Sebagian kami olah menjadi baling-baling SmartKolecer, kolecer berbasis IoT yang bisa dipantau secara digital.
                            </p>
                            <a href="smartkolecer.php" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition-all hover:-translate-y-0.5">
                                Pelajari SmartKolecer <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                        <div class="flex items-center justify-center">
                            <!-- TODO: ganti ikon dengan foto: <img src="assets/smartkolecer.png" alt="SmartKolecer" class="rounded-2xl w-full"> -->
                            <div class="w-40 h-40 rounded-3xl bg-white border border-brand-100 shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-fan text-brand-600 text-6xl group-hover:rotate-180 transition-transform duration-700"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

       <!-- ==================== MITRA & KOLABORATOR ==================== -->
    <section id="kolaborator" class="py-24 px-4 md:px-8 bg-brand-50 border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-handshake"></i> Ekosistem Kolaborasi
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Mitra &amp; Kolaborator
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4">
                    Trashily adalah induk dari ekosistem yang terhubung dengan para kolaborator kami.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- TrashSmart -->
                <a href="https://URL-TRASHSMART" target="_blank" rel="noopener" class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex flex-col justify-between group reveal">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 text-xl mb-5"><i class="fa-solid fa-trash-can"></i></div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-2 group-hover:text-brand-700 transition-colors">TrashSmart</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Deskripsi singkat TrashSmart.</p>
                    </div>
                    <span class="mt-6 pt-4 border-t border-slate-100 text-sm font-bold text-brand-700 inline-flex items-center gap-2">Kunjungi Website <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></span>
                </a>

                <!-- Gembul -->
                <a href="https://URL-GEMBUL" target="_blank" rel="noopener" class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex flex-col justify-between group reveal" style="transition-delay:.1s">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 text-xl mb-5"><i class="fa-solid fa-leaf"></i></div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-2 group-hover:text-brand-700 transition-colors">Gembul</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Deskripsi singkat Gembul.</p>
                    </div>
                    <span class="mt-6 pt-4 border-t border-slate-100 text-sm font-bold text-brand-700 inline-flex items-center gap-2">Kunjungi Website <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i></span>
                </a>

                <!-- Everware (via WhatsApp) -->
                <a href="https://wa.me/62XXXXXXXXXX?text=Halo%20Everware%2C%20saya%20dari%20Trashily" target="_blank" rel="noopener" class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-2 flex flex-col justify-between group reveal" style="transition-delay:.2s">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 text-xl mb-5"><i class="fa-solid fa-recycle"></i></div>
                        <h3 class="font-display font-bold text-xl text-slate-900 mb-2 group-hover:text-brand-700 transition-colors">Everware</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Deskripsi singkat Everware.</p>
                    </div>
                    <span class="mt-6 pt-4 border-t border-slate-100 text-sm font-bold text-emerald-700 inline-flex items-center gap-2"><i class="fa-brands fa-whatsapp"></i> Hubungi via WhatsApp</span>
                </a>
            </div>
        </div>
    </section>
    <!-- ==================== LIGHT MODE CATALOG HADIAH SHOWCASE ==================== -->
    <section id="rewards" class="py-24 px-4 md:px-8 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto">
            <!-- Section Title -->
            <div class="text-center max-w-4xl mx-auto mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-gift"></i> Rewards Center
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Pilihan Hadiah &amp; Voucher Menarik
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4">
                    Kumpulkan poin dari setiap kilogram sampah dan tukarkan dengan berbagai kategori hadiah sesuai kebutuhan harian Anda.
                </p>
            </div>

            <!-- Rewards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Reward 1 — Gelas -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/6.jpeg" alt="Gelas" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Gelas</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Gelas Cantik Ramah Lingkungan</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Gelas berkualitas yang dibuat dari bahan daur ulang — cocok untuk minum teh, kopi, maupun jus sehari-hari.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>

                <!-- Reward 2 — Totebag -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal" style="transition-delay:.08s">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/7.jpeg" alt="Totebag" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Totebag</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Totebag Eco-Friendly</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Tas jinjing serbaguna dari bahan ramah lingkungan — stylish untuk dibawa belanja, kampus, maupun kerja.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>

                <!-- Reward 3 — Tumbler -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal" style="transition-delay:.16s">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/8.jpeg" alt="Tumbler" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Tumbler</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Tumbler Anti Bocor</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Tumbler berkualitas untuk mengurangi sampah plastik botol — jaga minuman tetap segar, kurangi jejak karbon.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>

                <!-- Reward 4 — Dompet -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal" style="transition-delay:.24s">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/9.jpeg" alt="Dompet" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Dompet</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Dompet Upcycle Stylish</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Dompet praktis hasil kreasi upcycling bahan bekas — desain estetik, fungsional, dan punya nilai sosial tinggi.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>

                <!-- Reward 5 — Pulpen -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal" style="transition-delay:.32s">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/10.jpeg" alt="Pulpen" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Pulpen</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Pulpen Daur Ulang Premium</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Pulpen berkualitas dari bahan daur ulang — sempurna untuk belajar, menulis, dan aktivitas kreatif sehari-hari.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>

                <!-- Reward 6 — Notebook -->
                <div class="bg-white border border-slate-200 hover:border-brand-500/50 rounded-3xl overflow-hidden transition-all duration-300 hover:-translate-y-2 group shadow-sm hover:shadow-xl flex flex-col reveal" style="transition-delay:.4s">
                    <div class="relative h-52 overflow-hidden bg-slate-100">
                        <img src="<?= BASE_URL ?>/assets/result/11.jpeg" alt="Notebook" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <span class="absolute top-3 left-3 px-3 py-1 rounded-full bg-white/90 backdrop-blur-md text-slate-900 font-extrabold text-[11px] shadow-sm">Notebook</span>
                    </div>
                    <div class="p-5 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="font-display font-bold text-base text-slate-900 group-hover:text-brand-600 transition-colors mb-1">Notebook Eco-Print</h3>
                            <p class="text-slate-500 text-xs leading-relaxed">Buku catatan dengan sampul berbahan daur ulang — ideal untuk mencatat ide, jurnal harian, atau tugas sekolah.</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-brand-700 font-bold"><i class="fa-solid fa-coins mr-1"></i>Tukar Poin</span>
                            <span class="text-slate-400 font-medium">Ready Stock</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LIGHT MODE TESTIMONI & IMPACT ==================== -->
    <section class="py-24 px-4 md:px-8 bg-white border-t border-slate-200/80 relative">
        <div class="max-w-7xl mx-auto">
            <!-- Section Header -->
            <div class="text-center max-w-4xl mx-auto mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-heart"></i> Social Proof
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Dipercaya Ratusan Keluarga &amp; Pengusaha
                </h2>
                <p class="text-slate-600 text-base md:text-lg mt-4">
                    Dengarkan langsung cerita mereka yang telah mendapatkan manfaat ekonomi sambil menjaga kelestarian lingkungan.
                </p>
            </div>

            <!-- Testimonial Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php if ($approved_reviews && $approved_reviews->num_rows > 0): ?>
                    <?php $i = 0; while ($review = $approved_reviews->fetch_assoc()): $i++; $initials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $review['nama'] ?? 'M'), 0, 2)); if (!$initials) $initials = 'M'; ?>
                    <div class="bg-slate-50 border border-slate-200 rounded-3xl p-8 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between reveal" style="transition-delay: <?= ($i - 1) * .1 ?>s">
                        <div>
                            <div class="flex items-center gap-1 text-amber-500 text-sm mb-4">
                                <?php for ($star = 1; $star <= 5; $star++): ?>
                                    <i class="fa-solid fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-slate-700 text-sm italic leading-relaxed mb-6">
                                "<?= htmlspecialchars($review['komentar']) ?>"
                            </p>
                        </div>
                        <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
                            <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center text-lg border border-brand-200">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($review['nama']) ?></h4>
                                <p class="text-xs text-slate-500"><?= date('d M Y', strtotime($review['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="md:col-span-3 bg-slate-50 border border-dashed border-slate-300 rounded-3xl p-10 text-center text-slate-600">
                        Belum ada ulasan yang disetujui. Jadilah yang pertama memberi testimoni.
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 text-center reveal">
                <a href="ulasan.php" class="inline-flex items-center gap-2 text-sm font-bold text-brand-700 hover:text-brand-800 transition-colors">
                    <i class="fa-solid fa-arrow-right"></i> Lihat semua ulasan
                </a>
            </div>
        </div>
    </section>

    <!-- ==================== BUG-FREE LIGHT MODE FAQ ACCORDION ==================== -->
    <section id="faq" class="py-24 px-4 md:px-8 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-4xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16 reveal">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-100 border border-brand-200 text-brand-800 font-bold text-xs uppercase tracking-widest mb-4">
                    <i class="fa-solid fa-circle-question"></i> FAQ Center
                </span>
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 tracking-tight">
                    Pertanyaan Sering Diajukan
                </h2>
                <p class="text-slate-600 text-base mt-4">
                    Punya pertanyaan seputar cara kerja, penjemputan, atau penukaran poin? Cari jawabannya di sini.
                </p>
            </div>

            <!-- Accordion Items Container -->
            <div class="space-y-4 reveal">
                <!-- FAQ Item 1 -->
                <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-brand-300">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-6 text-left font-display font-bold text-base md:text-lg text-slate-900 flex items-center justify-between gap-4 focus:outline-none">
                        <span>Apakah pendaftaran akun Trashily dipungut biaya?</span>
                        <i class="faq-icon fa-solid fa-chevron-down text-brand-600 transition-transform duration-300"></i>
                    </button>
                    <div class="faq-answer hidden px-6 pb-6 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-4">
                        Sama sekali tidak! Pendaftaran akun Trashily 100% GRATIS tanpa biaya pendaftaran maupun biaya bulanan. Anda dapat langsung mengumpulkan poin setelah pendaftaran selesai.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-brand-300">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-6 text-left font-display font-bold text-base md:text-lg text-slate-900 flex items-center justify-between gap-4 focus:outline-none">
                        <span>Bagaimana cara mengajukan penjemputan sampah ke rumah?</span>
                        <i class="faq-icon fa-solid fa-chevron-down text-brand-600 transition-transform duration-300"></i>
                    </button>
                    <div class="faq-answer hidden px-6 pb-6 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-4">
                        Setelah login ke dashboard, pilih menu "Penjemputan Sampah", masukkan alamat lokasi Anda, pilih jenis sampah &amp; estimasi berat, lalu tentukan waktu penjemputan. Kurir kami akan menghubungi Anda melalui WhatsApp sebelum berangkat.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="faq-item bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm transition-all hover:border-brand-300">
                    <button type="button" onclick="toggleFaq(this)" class="w-full p-6 text-left font-display font-bold text-base md:text-lg text-slate-900 flex items-center justify-between gap-4 focus:outline-none">
                        <span>Apakah ada batas minimum berat sampah yang disetor?</span>
                        <i class="faq-icon fa-solid fa-chevron-down text-brand-600 transition-transform duration-300"></i>
                    </button>
                    <div class="faq-answer hidden px-6 pb-6 text-slate-600 text-sm leading-relaxed border-t border-slate-100 pt-4">
                        Untuk setor langsung di lokasi Trashily / Dropbox Trashily tidak ada batas minimum. Namun untuk layanan penjemputan door-to-door, batas minimum setor adalah 1 kg (gabungan dari berbagai jenis sampah).
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LUXURIOUS LIGHT CTA BANNER ==================== -->
    <section class="py-20 px-4 md:px-8 bg-slate-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative bg-gradient-to-r from-brand-900 via-brand-800 to-brand-950 border border-brand-700 rounded-3xl p-10 md:p-16 shadow-2xl text-center overflow-hidden reveal text-white">
                <!-- Glowing background elements -->
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-3xl mx-auto space-y-6">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 text-brand-200 border border-white/20 font-bold text-xs uppercase tracking-widest">
                        <i class="fa-solid fa-leaf"></i> Join The Movement
                    </span>
                    <h2 class="text-3xl md:text-5xl font-display font-extrabold text-white tracking-tight leading-tight">
                        Siap Memulai Langkah Hijau &amp; Meraih Manfaatnya?
                    </h2>
                    <p class="text-brand-100 text-base md:text-lg">
                        Bergabunglah bersama ribuan anggota aktif Trashily. Daftar dalam 1 menit, pilah sampah Anda, dan nikmati poin reward instan hari ini!
                    </p>

                    <div class="pt-6 flex flex-wrap items-center justify-center gap-4">
                        <?php if ($logged_in): ?>
                            <a href="<?= $dash_url ?>" class="px-8 py-4 rounded-2xl bg-white hover:bg-brand-50 text-brand-900 font-extrabold text-base shadow-xl transition-all hover:scale-105 inline-flex items-center gap-2">
                                <i class="fa-solid fa-gauge text-brand-600"></i> Masuk ke Dashboard Saya
                            </a>
                        <?php else: ?>
                            <a href="auth/register.php" class="px-8 py-4 rounded-2xl bg-white hover:bg-brand-50 text-brand-900 font-extrabold text-base shadow-xl transition-all hover:scale-105 inline-flex items-center gap-2">
                                <i class="fa-solid fa-rocket text-brand-600 text-sm"></i> Daftar Akun Gratis Sekarang
                            </a>
                            <a href="https://wa.me/6285782118017" target="_blank" class="px-8 py-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-extrabold text-base border border-white/30 transition-all inline-flex items-center gap-2">
                                <i class="fa-brands fa-whatsapp text-emerald-300"></i> Konsultasi WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== LUXURY DARK/LIGHT FINISH FOOTER ==================== -->
    <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 px-4 md:px-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
                <!-- Col 1: Brand Info (Span 2) -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="#" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl overflow-hidden">
                            <img src="assets/brand.png" alt="Trashily" style="width:100%;height:100%;object-fit:contain">
                        </div>
                        <span class="font-display font-extrabold text-2xl text-white tracking-tight">Trashily<span class="text-brand-400">.</span></span>
                    </a>
                    <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                        Ekosistem bank sampah digital berbasis poin terdepan. Mendorong pengelolaan sampah berkelanjutan dan memberikan nilai ekonomi nyata bagi masyarakat.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <a href="https://www.instagram.com/sari.nrs" target="_blank" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:text-brand-400 hover:border-brand-500/40 transition-all" aria-label="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="https://wa.me/6285782118017" target="_blank" class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:text-brand-400 hover:border-brand-500/40 transition-all" aria-label="WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Navigation -->
                <div>
                    <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Navigasi Utama</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#tentang-kami" class="hover:text-brand-400 transition-colors">Tentang Kami &amp; Inisiatif</a></li>
                        <li><a href="#cara-kerja" class="hover:text-brand-400 transition-colors">Cara Kerja</a></li>
                        <li><a href="#katalog" class="hover:text-brand-400 transition-colors">Direktori Sampah</a></li>
                        <li><a href="#ekosistem" class="hover:text-brand-400 transition-colors">Keunggulan Platform</a></li>
                        <li><a href="#rewards" class="hover:text-brand-400 transition-colors">Katalog Hadiah</a></li>
                    </ul>
                </div>

                <!-- Col 3: Portal Links -->
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
                        <li><a href="#faq" class="hover:text-brand-400 transition-colors">Pusat Bantuan FAQ</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact -->
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

            <!-- Footer Bottom Bar -->
            <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
                <p>&copy; <?= date('Y') ?> <strong>Trashily</strong>. Hak Cipta Dilindungi.</p>
                <div class="flex flex-col md:flex-row items-center gap-3">
                    <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Systems 100% Operational
                    </span>
                    <span>Dibuat dengan <i class="fa-solid fa-heart text-rose-500"></i> untuk Indonesia Hijau</span>
                </div>
            </div>
        </div>
    </footer>
    <!-- ==================== POPUP NEWS SMARTKOLECER ==================== -->
    <div id="kolecerPopup" class="fixed inset-0 z-[60] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="kolecerPopupTitle">
        <!-- Backdrop -->
        <div id="kolecerBackdrop" class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>

        <!-- Card -->
        <div id="kolecerCard" class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden opacity-0 translate-y-6 scale-95 transition-all duration-300">
            <button type="button" id="kolecerClose" class="absolute top-3 right-3 z-10 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-slate-600 hover:text-slate-900 shadow flex items-center justify-center transition" aria-label="Tutup">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Header visual -->
            <div class="bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800 px-8 pt-10 pb-8 text-center relative overflow-hidden">
                <div class="absolute -top-10 -left-10 w-40 h-40 bg-brand-300/20 rounded-full blur-2xl pointer-events-none"></div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white font-bold text-[11px] uppercase tracking-widest">
                    <i class="fa-solid fa-bolt"></i> Inovasi Baru
                </span>
                <!-- TODO: ganti ikon dengan foto: <img src="assets/smartkolecer.png" alt="SmartKolecer" class="mx-auto mt-5 h-32 object-contain"> -->
                <div class="mx-auto mt-5 w-24 h-24 rounded-3xl bg-white/15 border border-white/25 flex items-center justify-center">
                    <i class="fa-solid fa-fan text-white text-5xl" style="animation: spin 4s linear infinite"></i>
                </div>
            </div>

            <!-- Body -->
            <div class="px-8 py-7 text-center">
                <h3 id="kolecerPopupTitle" class="font-display font-extrabold text-2xl text-slate-900">Kenalan dengan SmartKolecer</h3>
                <p class="mt-3 text-sm text-slate-600 leading-relaxed">
                    Kolecer berbasis IoT dengan baling-baling dari sampah daur ulang. Sampah yang kamu setor di Trashily bisa jadi bagian dari inovasi ini.
                </p>
                <div class="mt-6 flex flex-col gap-3">
                    <a href="smartkolecer.php" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md transition hover:-translate-y-0.5">
                        Pelajari SmartKolecer <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                    <button type="button" id="kolecerLater" class="text-sm font-semibold text-slate-500 hover:text-slate-800 transition">Nanti saja</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
    <!-- ==================== JAVASCRIPT LOGIC ==================== -->
    <script>
        /* Navbar scroll behavior for clean light mode */
        function applyNavbarState() {
            const nav = document.getElementById('navbar');
            const brandName = document.getElementById('navBrandName');
            const navLinkItems = document.querySelectorAll('.nav-link-item');
            const navLinkWrapper = document.getElementById('navLinkWrapper');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileNavWrapper = document.getElementById('mobileNavWrapper');
            const navBtnGhost = document.getElementById('navBtnGhost');
            const navBtnFill = document.getElementById('navBtnFill');
            const burgerBtn = document.querySelector('#burgerBtn');
            const isScrolled = window.scrollY > 30;

            nav.classList.toggle('bg-white/95', isScrolled);
            nav.classList.toggle('backdrop-blur-xl', isScrolled);
            nav.classList.toggle('border-b', isScrolled);
            nav.classList.toggle('border-slate-200/80', isScrolled);
            nav.classList.toggle('shadow-md', isScrolled);
            nav.classList.toggle('py-3', isScrolled);
            nav.classList.toggle('py-4', !isScrolled);

            if (brandName) {
                brandName.classList.toggle('text-white', !isScrolled);
                brandName.classList.toggle('text-slate-900', isScrolled);
            }

            navLinkItems.forEach(item => {
                item.classList.toggle('text-white', !isScrolled);
                item.classList.toggle('text-slate-700', isScrolled);
            });

            if (navLinkWrapper) {
                navLinkWrapper.classList.toggle('bg-white/10', !isScrolled);
                navLinkWrapper.classList.toggle('border-white/20', !isScrolled);
                navLinkWrapper.classList.toggle('backdrop-blur-sm', !isScrolled);
                navLinkWrapper.classList.toggle('bg-transparent', isScrolled);
                navLinkWrapper.classList.toggle('border-transparent', isScrolled);
            }

            if (mobileMenu) {
                if (isScrolled) {
                    mobileMenu.classList.remove('bg-white', 'border-slate-200');
                    mobileMenu.classList.add('bg-transparent', 'border-transparent');
                } else {
                    mobileMenu.classList.remove('bg-transparent', 'border-transparent');
                    mobileMenu.classList.add('bg-white', 'border-slate-200');
                }
            }

            if (mobileNavWrapper) {
                if (isScrolled) {
                    mobileNavWrapper.classList.remove('bg-white', 'border-slate-200');
                    mobileNavWrapper.classList.add('bg-transparent', 'border-transparent');
                } else {
                    mobileNavWrapper.classList.remove('bg-transparent', 'border-transparent');
                    mobileNavWrapper.classList.add('bg-white', 'border-slate-200');
                }
            }

            if (navBtnGhost) {
                navBtnGhost.classList.toggle('text-white', !isScrolled);
                navBtnGhost.classList.toggle('border-white/30', !isScrolled);
                navBtnGhost.classList.toggle('text-slate-800', isScrolled);
                navBtnGhost.classList.toggle('border-slate-300', isScrolled);
                navBtnGhost.classList.toggle('hover:bg-slate-100', isScrolled);
            }

            const navBtnIcons = document.querySelectorAll('.nav-btn-icon');
            navBtnIcons.forEach(icon => {
                icon.classList.toggle('text-brand-600', !isScrolled);
                icon.classList.toggle('text-white', isScrolled);
            });

            if (navBtnFill) {
                navBtnFill.classList.toggle('bg-white', !isScrolled);
                navBtnFill.classList.toggle('text-brand-900', !isScrolled);
                navBtnFill.classList.toggle('bg-brand-600', isScrolled);
                navBtnFill.classList.toggle('text-white', isScrolled);
                navBtnFill.classList.toggle('hover:bg-brand-700', isScrolled);
            }

            if (burgerBtn) {
                burgerBtn.classList.toggle('text-white', !isScrolled);
                burgerBtn.classList.toggle('text-slate-900', isScrolled);
            }
        }

        applyNavbarState();
        window.addEventListener('scroll', applyNavbarState);

        /* Mobile Menu Toggle */
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.querySelector('#burgerBtn i');
            menu.classList.toggle('hidden');
            if (menu.classList.contains('hidden')) {
                icon.className = 'fa-solid fa-bars text-xl';
            } else {
                icon.className = 'fa-solid fa-xmark text-xl';
            }
        }

        /* Scroll Reveal Intersection Observer */
        const revealObs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    revealObs.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.1
        });
        document.querySelectorAll('.reveal').forEach(el => revealObs.observe(el));

        /* Stats Counter Animation */
        let countedStats = false;

        function animateStats() {
            document.querySelectorAll('.stat-counter').forEach(el => {
                const target = parseInt(el.getAttribute('data-target'));
                const suffix = el.getAttribute('data-suffix') || '+';
                if (!target) return;
                let count = 0;
                const step = target / 60;
                const timer = setInterval(() => {
                    count += step;
                    if (count >= target) {
                        count = target;
                        clearInterval(timer);
                    }
                    el.textContent = Math.floor(count).toLocaleString('id-ID') + suffix;
                }, 25);
            });
        }
        const metricsEl = document.querySelector('.stat-counter');
        if (metricsEl) {
            new IntersectionObserver(entries => {
                if (entries[0].isIntersecting && !countedStats) {
                    countedStats = true;
                    animateStats();
                }
            }, {
                threshold: 0.3
            }).observe(metricsEl.parentElement.parentElement);
        }

        /* Clean & Bug-Free FAQ Accordion Toggle */
        function toggleFaq(btn) {
            const item = btn.closest('.faq-item');
            const answer = item.querySelector('.faq-answer');
            const icon = item.querySelector('.faq-icon');
            const isOpening = answer.classList.contains('hidden');

            // Close all items
            document.querySelectorAll('.faq-answer').forEach(ans => ans.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(ic => ic.classList.remove('rotate-180'));

            if (isOpening) {
                answer.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        }

               /* Popup News SmartKolecer: tampil sekali per sesi browser */
        (function () {
            const popup = document.getElementById('kolecerPopup');
            if (!popup) return;
            const backdrop = document.getElementById('kolecerBackdrop');
            const card = document.getElementById('kolecerCard');
            const KEY = 'kolecerPopupSeen';

            function seen() {
                try { return sessionStorage.getItem(KEY) === '1'; } catch (e) { return false; }
            }
            function markSeen() {
                try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
            }

            function openPopup() {
                popup.classList.remove('hidden');
                popup.classList.add('flex');
                document.body.classList.add('overflow-hidden');
                requestAnimationFrame(() => {
                    backdrop.classList.remove('opacity-0');
                    card.classList.remove('opacity-0', 'translate-y-6', 'scale-95');
                });
            }

            function closePopup() {
                markSeen();
                backdrop.classList.add('opacity-0');
                card.classList.add('opacity-0', 'translate-y-6', 'scale-95');
                document.body.classList.remove('overflow-hidden');
                setTimeout(() => {
                    popup.classList.add('hidden');
                    popup.classList.remove('flex');
                }, 300);
            }

            document.getElementById('kolecerClose').addEventListener('click', closePopup);
            document.getElementById('kolecerLater').addEventListener('click', closePopup);
            backdrop.addEventListener('click', closePopup);
            document.addEventListener('keydown', e => { if (e.key === 'Escape' && !popup.classList.contains('hidden')) closePopup(); });

            // Tombol "Pelajari" juga dianggap sudah dilihat
            popup.querySelector('a[href="smartkolecer.php"]').addEventListener('click', markSeen);

            // Tampil 1 detik setelah halaman dibuka. Tambahkan ?popup=1 di URL untuk memaksa tampil (untuk tes).
            const force = new URLSearchParams(location.search).get('popup') === '1';
            if (force || !seen()) setTimeout(openPopup, 1000);
        })(); 
    </script>
</body>

</html>