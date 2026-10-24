<?php
/**
 * Shared Public Navbar Component — Trashily
 * Standardized across index.php, trashsmart.php, smartkolecer.php, gembul.php, artikel.php, baca-artikel.php, harga.php, ulasan.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_user_logged_in = isset($_SESSION['user_id']);
$user_dash_url = $is_user_logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;
$current_page = basename($_SERVER['PHP_SELF']);
$active_nav = $active_nav ?? '';
?>

<!-- ==================== SHARED PUBLIC NAVBAR ==================== -->
<header id="navbar" class="sticky top-0 z-50 border-b border-transparent bg-transparent transition-all duration-300">
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
                <button type="button" class="flex items-center gap-1.5 text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5 <?= in_array($active_nav, ['trashsmart','smartkolecer','gembul','everwear','harga','ulasan']) ? 'text-brand-700 font-bold' : '' ?>">
                    Layanan &amp; Inovasi <i class="fa-solid fa-chevron-down text-[10px] opacity-80 transition-transform duration-200" id="layananChevron"></i>
                </button>
            </div>

            <a href="artikel.php" class="text-sm font-semibold <?= ($current_page === 'artikel.php' || $current_page === 'baca-artikel.php') ? 'text-brand-700 font-bold' : 'text-slate-700 hover:text-brand-700' ?> transition-colors px-3 py-1.5">
                Artikel &amp; Edukasi
            </a>

            <a href="index.php#faq" class="text-sm font-semibold text-slate-700 hover:text-brand-700 transition-colors px-3 py-1.5">FAQ</a>
        </nav>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <?php if ($is_user_logged_in): ?>
                <a href="<?= $user_dash_url ?>" class="hidden sm:inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="hidden sm:inline-flex px-4 py-2 rounded-full text-slate-700 hover:text-brand-700 font-semibold text-sm transition">Masuk</a>
                <a href="auth/register.php" class="inline-flex items-center gap-2 rounded-full bg-brand-600 text-white px-5 py-2.5 font-bold text-sm shadow-md hover:bg-brand-700 transition">Daftar Gratis</a>
            <?php endif; ?>

            <!-- Mobile Menu Toggle Button -->
            <button id="mobileMenuBtn" onclick="togglePublicMobileMenu()" class="lg:hidden text-slate-700 hover:text-brand-700 p-2 focus:outline-none" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars text-xl" id="mobileMenuIcon"></i>
            </button>
        </div>
    </div>

    <!-- Mega Menu Dropdown: Full Width Edge-to-Edge -->
    <div id="megaMenuDropdown" class="absolute top-full left-0 right-0 w-full bg-white border-b border-slate-200 shadow-xl rounded-none opacity-0 invisible transition-all duration-200 pointer-events-none z-50 text-left before:content-[''] before:absolute before:-top-6 before:left-0 before:right-0 before:h-6 mt-0 border-t">
        <div class="max-w-5xl mx-auto px-4 md:px-8 py-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekosistem &amp; Layanan Trashily</span>
                </div>
                <span class="text-xs text-slate-400 font-medium">Inovasi Bank Sampah Digital &bull; Edukasi &bull; IoT</span>
            </div>

            <!-- 2 Kolom Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                <!-- Kolom Kiri -->
                <div class="space-y-2">
                    <!-- PIRU Smart Bin -->
                    <a href="piru.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200 <?= $current_page === 'piru.php' ? 'bg-brand-50/50 border-brand-200' : '' ?>">
                        <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                            <i class="fa-solid fa-microchip"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">PIRU (Pilah Runtah)</h4>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">IoT AI &bull; YOLO</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Tong sampah pintar berbasis Computer Vision YOLO &amp; sensor telemetri dengan reward poin instan.
                            </p>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                    </a>

                    <!-- SmartKolecer -->
                    <a href="smartkolecer.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200 <?= $current_page === 'smartkolecer.php' ? 'bg-brand-50/50 border-brand-200' : '' ?>">
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

                    <!-- GEMBUL -->
                    <a href="gembul.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-amber-200 hover:bg-amber-50/30 transition-all duration-200 <?= $current_page === 'gembul.php' ? 'bg-amber-50/50 border-amber-300' : '' ?>">
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

                    <!-- Everwear -->
                    <a href="everwear.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200">
                        <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">Everwear</h4>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">Donasi Tekstil</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Donasi baju layak pakai ke komunitas &amp; recycle tekstil dengan poin Trashily.
                            </p>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                    </a>
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-2">
                    <!-- Daftar Harga -->
                    <a href="harga.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200 <?= $current_page === 'harga.php' ? 'bg-brand-50/50 border-brand-200' : '' ?>">
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
                    <a href="ulasan.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200 <?= $current_page === 'ulasan.php' ? 'bg-brand-50/50 border-brand-200' : '' ?>">
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
                    <a href="gembul.php#jadwal" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-amber-200 hover:bg-amber-50/40 transition-all duration-200">
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

                    <!-- Artikel & Edukasi Lingkungan -->
                    <a href="artikel.php" class="group/item flex items-start gap-4 p-3.5 rounded-2xl border border-transparent hover:border-slate-200 hover:bg-slate-50/80 transition-all duration-200 <?= ($current_page === 'artikel.php' || $current_page === 'baca-artikel.php') ? 'bg-brand-50/50 border-brand-200' : '' ?>">
                        <div class="text-brand-600 text-2xl pt-1 shrink-0 group-hover/item:scale-110 transition-transform">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-display font-bold text-sm text-slate-900 group-hover/item:text-brand-600 transition-colors">Artikel &amp; Edukasi</h4>
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-50 text-brand-700">Pusat Literasi</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Panduan praktis pilah sampah bernilai tinggi, inovasi IoT, dan gerakan lingkungan Bogor.
                            </p>
                        </div>
                        <i class="fa-solid fa-arrow-right text-xs text-slate-300 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-1 group-hover/item:text-brand-600 transition-all self-center"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div id="publicMobileDrawer" class="hidden lg:hidden bg-white border-b border-slate-200 px-6 py-5 space-y-3 transition-all duration-300">
        <a href="index.php#tentang-kami" class="block font-bold text-slate-800 hover:text-brand-600 py-2 border-b border-slate-100">Tentang Kami</a>
        <a href="index.php#cara-kerja" class="block font-bold text-slate-800 hover:text-brand-600 py-2 border-b border-slate-100">Cara Kerja</a>
        
        <div class="py-2 border-b border-slate-100 space-y-1">
            <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Layanan &amp; Inovasi</p>
            <a href="piru.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-sm py-1.5">
                <span class="flex items-center gap-2"><i class="fa-solid fa-microchip text-brand-600"></i> PIRU (Pilah Runtah)</span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-100 text-brand-700">AI IoT</span>
            </a>
            <a href="smartkolecer.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-sm py-1.5">
                <span class="flex items-center gap-2"><i class="fa-solid fa-fan text-sky-600"></i> SmartKolecer</span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-sky-100 text-sky-700">Kolecer</span>
            </a>
            <a href="gembul.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-amber-600 text-sm py-1.5">
                <span class="flex items-center gap-2"><i class="fa-solid fa-dice text-amber-500"></i> GEMBUL</span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-amber-100 text-amber-700">Eco-Kit</span>
            </a>
            <a href="everwear.php" class="flex items-center justify-between text-slate-800 font-bold hover:text-brand-600 text-sm py-1.5">
                <span class="flex items-center gap-2"><i class="fa-solid fa-shirt text-brand-600"></i> Everwear</span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-brand-100 text-brand-700">Tekstil</span>
            </a>
            <a href="harga.php" class="block text-slate-800 font-bold hover:text-brand-600 text-sm py-1.5">Daftar Harga Sampah</a>
            <a href="ulasan.php" class="block text-slate-800 font-bold hover:text-brand-600 text-sm py-1.5">Ulasan Komunitas</a>
        </div>

        <a href="artikel.php" class="block font-bold text-slate-800 hover:text-brand-600 py-2 border-b border-slate-100">Artikel &amp; Edukasi</a>
        <a href="index.php#faq" class="block font-bold text-slate-800 hover:text-brand-600 py-2">FAQ</a>

        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2.5">
            <?php if ($is_user_logged_in): ?>
                <a href="<?= $user_dash_url ?>" class="w-full text-center py-2.5 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md">
                    <i class="fa-solid fa-gauge mr-1"></i> Dashboard Saya
                </a>
            <?php else: ?>
                <a href="auth/login.php" class="w-full text-center py-2.5 rounded-xl bg-slate-100 text-slate-800 font-bold text-sm hover:bg-slate-200">Masuk</a>
                <a href="auth/register.php" class="w-full text-center py-2.5 rounded-xl bg-brand-600 text-white font-bold text-sm shadow-md hover:bg-brand-700">Daftar Gratis</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
/* Subpage Navbar Dynamic Scroll Behavior */
function applySharedNavbarState() {
    const nav = document.getElementById('navbar');
    const navLinkWrapper = document.getElementById('navLinkWrapper');
    const isScrolled = window.scrollY > 20;

    if (nav) {
        if (isScrolled) {
            nav.classList.add('bg-white/95', 'backdrop-blur-md', 'border-slate-200/80', 'shadow-xs', 'py-3');
            nav.classList.remove('bg-transparent', 'border-transparent', 'shadow-none', 'py-3.5');
        } else {
            nav.classList.add('bg-transparent', 'border-transparent', 'shadow-none', 'py-3.5');
            nav.classList.remove('bg-white/95', 'backdrop-blur-md', 'border-slate-200/80', 'shadow-xs', 'py-3');
        }
    }

    if (navLinkWrapper) {
        if (isScrolled) {
            navLinkWrapper.classList.remove('rounded-full', 'border', 'border-slate-200/80', 'bg-white/70', 'backdrop-blur-md', 'px-5', 'py-2', 'shadow-xs');
            navLinkWrapper.classList.add('border-transparent', 'bg-transparent', 'shadow-none', 'px-0', 'py-0');
        } else {
            navLinkWrapper.classList.add('rounded-full', 'border', 'border-slate-200/80', 'bg-white/70', 'backdrop-blur-md', 'px-5', 'py-2', 'shadow-xs');
            navLinkWrapper.classList.remove('border-transparent', 'bg-transparent', 'shadow-none', 'px-0', 'py-0');
        }
    }
}
applySharedNavbarState();
window.addEventListener('scroll', applySharedNavbarState);

/* Mega Menu Dropdown Interaction */
(function initSharedMegaMenu() {
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

/* Mobile Menu Drawer Toggle */
function togglePublicMobileMenu() {
    const drawer = document.getElementById('publicMobileDrawer');
    const icon = document.getElementById('mobileMenuIcon');
    if (!drawer) return;
    
    drawer.classList.toggle('hidden');
    if (drawer.classList.contains('hidden')) {
        icon.className = 'fa-solid fa-bars text-xl';
    } else {
        icon.className = 'fa-solid fa-xmark text-xl text-rose-500';
    }
}
</script>
