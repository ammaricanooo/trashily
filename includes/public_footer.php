<?php
/**
 * Shared Public Footer Component — Trashily
 * Standardized across all public subpages
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_user_logged_in = isset($_SESSION['user_id']);
$user_dash_url = $is_user_logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;
?>

<!-- ==================== SHARED LUXURY DARK FOOTER ==================== -->
<footer class="mt-24 bg-slate-900 text-slate-400 pt-16 pb-12 px-4 md:px-8 border-t border-slate-800">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
            <!-- Col 1: Brand Info (Span 2) -->
            <div class="lg:col-span-2 space-y-4">
                <a href="index.php" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl overflow-hidden">
                        <img src="assets/brand.png" alt="Trashily" style="width:100%;height:100%;object-fit:contain">
                    </div>
                    <span class="font-display font-extrabold text-2xl text-white tracking-tight">Trashily<span class="text-brand-400">.</span></span>
                </a>
                <p class="text-sm text-slate-400 leading-relaxed max-w-sm">
                    Ekosistem bank sampah digital berbasis IoT dan rewards platform pertama di Bogor. Mengubah sampah daur ulang menjadi nilai riil dan aksi nyata bagi lingkungan.
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

            <!-- Col 2: Layanan & Inovasi -->
            <div>
                <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Layanan &amp; Inovasi</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="piru.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-microchip text-xs"></i> PIRU (Pilah Runtah) Smart Bin</a></li>
                    <li><a href="smartkolecer.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-fan text-xs"></i> SmartKolecer Edukasi</a></li>
                    <li><a href="gembul.php" class="text-amber-400 hover:text-amber-300 font-semibold transition-colors flex items-center gap-1.5"><i class="fa-solid fa-dice text-xs"></i> GEMBUL (Creative Eco-Kit)</a></li>
                    <li><a href="everwear.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-shirt text-xs"></i> Everwear Donasi Tekstil</a></li>
                    <li><a href="harga.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-tags text-xs"></i> Daftar Harga Sampah</a></li>
                    <li><a href="ulasan.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-comments text-xs"></i> Ulasan Komunitas</a></li>
                </ul>
            </div>

            <!-- Col 3: Portal & Navigasi -->
            <div>
                <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Portal &amp; Informasi</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="artikel.php" class="text-emerald-400 hover:text-emerald-300 font-semibold transition-colors flex items-center gap-1.5"><i class="fa-solid fa-book-open text-xs"></i> Artikel &amp; Edukasi</a></li>
                    <?php if ($is_user_logged_in): ?>
                        <li><a href="<?= $user_dash_url ?>" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-gauge text-xs"></i> Dashboard Saya</a></li>
                        <li><a href="auth/logout.php" class="hover:text-rose-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-right-from-bracket text-xs"></i> Keluar Akun</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-right-to-bracket text-xs"></i> Masuk Akun</a></li>
                        <li><a href="auth/register.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-user-plus text-xs"></i> Daftar Member</a></li>
                    <?php endif; ?>
                    <li><a href="index.php#faq" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-circle-question text-xs"></i> Pusat Bantuan FAQ</a></li>
                    <li><a href="sitemap.xml" target="_blank" class="hover:text-brand-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-sitemap text-xs"></i> Sitemap XML</a></li>
                </ul>
            </div>

            <!-- Col 4: Kontak -->
            <div>
                <h4 class="font-display font-bold text-white text-sm uppercase tracking-wider mb-4">Kontak Layanan</h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-envelope text-brand-400 mt-1"></i>
                        <span>halo@trashily.id</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-phone text-brand-400 mt-1"></i>
                        <span>+62 857 8211 8017</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i class="fa-solid fa-location-dot text-brand-400 mt-1"></i>
                        <span>Jl. Cikaret, Kota Bogor, Jawa Barat</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom Bar -->
        <div class="pt-8 border-t border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 text-xs">
            <p>&copy; <?= date('Y') ?> <strong>Trashily</strong>. Hak Cipta Dilindungi Undang-Undang.</p>
            <div class="flex flex-col md:flex-row items-center gap-3">
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Systems 100% Operational
                </span>
                <span>Dibuat dengan <i class="fa-solid fa-heart text-rose-500"></i> untuk Indonesia Hijau</span>
            </div>
        </div>
    </div>
</footer>

<!-- FLOATING CHATBOT AI -->
<?php include_once __DIR__ . '/chatbot.php'; ?>
