<?php
session_start();
require_once 'config/database.php';
require_once 'includes/seo_helper.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url  = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

// Filter & Search
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';

$where = ["status = 'published'"];
$params = [];
$types = "";

if ($q !== '') {
    $where[] = "(judul LIKE ? OR ringkasan LIKE ? OR tags LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($kategori !== '' && $kategori !== 'Semua') {
    $where[] = "kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

$whereClause = implode(" AND ", $where);
$sql = "SELECT id, judul, slug, kategori, ringkasan, gambar, gambar_caption, penulis, penulis_role, baca_menit, views, created_at 
        FROM artikel 
        WHERE $whereClause 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$articles = $stmt->get_result();

// Get list of unique categories and count
$catQuery = $conn->query("SELECT kategori, COUNT(*) as total FROM artikel WHERE status = 'published' GROUP BY kategori");
$categories = [];
while ($c = $catQuery->fetch_assoc()) {
    $categories[] = $c;
}

// Get Popular Articles for sidebar
$popArticles = $conn->query("SELECT judul, slug, kategori, views, created_at FROM artikel WHERE status = 'published' ORDER BY views DESC LIMIT 5");

// SEO Configuration
$pageTitle = 'Artikel & Edukasi Lingkungan Bank Sampah | Trashily';
$pageDesc  = 'Pusat edukasi daur ulang sampah, panduan pemilahan bernilai rupiah, inovasi TrashSmart IoT, dan gerakan GEMBUL terlengkap dari Trashily Bogor.';
$canonical = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . ($_SERVER['HTTP_HOST'] ?? 'trashily.id') . BASE_URL . '/artikel.php' . ($kategori ? '?kategori='.urlencode($kategori) : '');

$breadcrumbs = [
    'Beranda' => (isset($_SERVER['HTTPS']) ? "https://" : "http://") . ($_SERVER['HTTP_HOST'] ?? 'trashily.id') . BASE_URL . '/',
    'Artikel & Edukasi' => $canonical
];

$faqs = [
    'Bagaimana cara memilah sampah agar bernilai tinggi di Trashily?' => 'Pisahkan sampah kering sesuai kategorinya: Plastik PET bening, Kaleng aluminium, dan Kardus tebal. Pastikan sampah dibilas bersih dari sisa makanan dan dipipihkan.',
    'Berapa harga sampah daur ulang per kilogram di Trashily Bogor?' => 'Harga bervariasi mulai dari Rp 3.500/kg untuk kaleng aluminium, Rp 2.000/kg botol plastik PET, hingga Rp 1.500/kg kardus.',
    'Apa itu inovasi TrashSmart dan GEMBUL?' => 'TrashSmart adalah tong sampah pintar berbasis IoT dengan sensor telemetri, sedangkan GEMBUL adalah program edukasi kreatif anak muda berbasis games simulasi dan Eco-Kit.'
];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php 
    render_seo_tags([
        'title' => $pageTitle,
        'description' => $pageDesc,
        'keywords' => 'artikel daur ulang, edukasi bank sampah, cara memilah sampah, trashsmart iot, gembul eco-kit, harga sampah plastik bogor, zero waste indonesia',
        'canonical' => $canonical,
        'breadcrumbs' => $breadcrumbs,
        'faqs' => $faqs
    ]); 
    ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                        }
                    },
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased selection:bg-brand-500 selection:text-white">

    <?php
    $active_nav = 'artikel';
    include __DIR__ . '/includes/public_navbar.php';
    ?>

    <!-- Breadcrumb SEO Semantik -->
    <nav class="bg-slate-100/70 border-b border-slate-200/60 py-2.5 px-4 md:px-8 text-xs text-slate-500" aria-label="Breadcrumb">
        <div class="max-w-7xl mx-auto flex items-center gap-2">
            <a href="index.php" class="hover:text-brand-700 transition flex items-center gap-1"><i class="fa-solid fa-house"></i> Beranda</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Artikel &amp; Edukasi</span>
            <?php if ($kategori): ?>
                <span>/</span>
                <span class="text-brand-700 font-semibold"><?= htmlspecialchars($kategori) ?></span>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Header Artikel -->
    <section class="relative bg-gradient-to-br from-emerald-900 via-brand-900 to-teal-950 text-white py-16 md:py-20 px-4 overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#4ade80_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
        <div class="max-w-4xl mx-auto text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-semibold text-brand-300 mb-4">
                <i class="fa-solid fa-book-open"></i> Pusat Literasi Lingkungan &bull; Trashily Editorial
            </div>
            <h1 class="font-display font-extrabold text-3xl md:text-5xl text-white tracking-tight leading-tight mb-4">
                Edukasi, Inovasi &amp; Panduan <span class="text-brand-300">Daur Ulang Modern</span>
            </h1>
            <p class="text-slate-200 text-sm md:text-base max-w-2xl mx-auto mb-8 leading-relaxed">
                Temukan panduan praktis memilah sampah bernilai rupiah tinggi, kabar inovasi teknologi TrashSmart IoT, serta aktivitas seru gerakan anak muda GEMBUL di Bogor.
            </p>

            <!-- Search Bar SEO Friendly -->
            <form action="artikel.php" method="GET" class="max-w-xl mx-auto flex items-center bg-white rounded-full p-1.5 shadow-2xl border border-white/20">
                <div class="pl-4 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Cari tips daur ulang, harga plastik, TrashSmart..." class="w-full px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none bg-transparent">
                <?php if ($kategori): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategori) ?>">
                <?php endif; ?>
                <button type="submit" class="px-5 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs transition shrink-0 shadow-md">
                    Cari Artikel
                </button>
            </form>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 md:px-8 py-10">
        <!-- Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-8 no-scrollbar">
            <a href="artikel.php<?= $q ? '?q='.urlencode($q) : '' ?>" class="shrink-0 px-4 py-2 rounded-full text-xs font-bold transition <?= empty($kategori) || $kategori === 'Semua' ? 'bg-brand-700 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700' ?>">
                Semua Topik
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="artikel.php?kategori=<?= urlencode($cat['kategori']) ?><?= $q ? '&q='.urlencode($q) : '' ?>" class="shrink-0 px-4 py-2 rounded-full text-xs font-bold transition <?= $kategori === $cat['kategori'] ? 'bg-brand-700 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-brand-500 hover:text-brand-700' ?>">
                    <?= htmlspecialchars($cat['kategori']) ?> <span class="opacity-75 font-normal">(<?= $cat['total'] ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Article List (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">
                <?php if ($articles->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php while ($row = $articles->fetch_assoc()): ?>
                            <article class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl hover:border-brand-300 transition-all duration-300 flex flex-col overflow-hidden group">
                                <a href="baca-artikel.php?slug=<?= urlencode($row['slug']) ?>" class="block relative h-48 bg-slate-100 overflow-hidden">
                                    <?php if (!empty($row['gambar'])): ?>
                                        <img src="<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['gambar_caption'] ?: $row['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-tr from-brand-800 to-emerald-600 flex items-center justify-center text-white text-4xl">
                                            <i class="fa-solid fa-newspaper opacity-40"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-[11px] font-bold bg-white/90 backdrop-blur-md text-brand-800 shadow-xs">
                                        <?= htmlspecialchars($row['kategori']) ?>
                                    </span>
                                </a>

                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-3 text-xs text-slate-400 mb-2.5">
                                            <span><i class="fa-regular fa-clock mr-1"></i> <?= $row['baca_menit'] ?> min baca</span>
                                            <span>&bull;</span>
                                            <span><i class="fa-regular fa-calendar mr-1"></i> <?= date('d M Y', strtotime($row['created_at'])) ?></span>
                                            <span>&bull;</span>
                                            <span><i class="fa-regular fa-eye mr-1"></i> <?= $row['views'] ?> views</span>
                                        </div>
                                        <h2 class="font-display font-bold text-base md:text-lg text-slate-900 group-hover:text-brand-600 transition-colors line-clamp-2 leading-snug mb-2">
                                            <a href="baca-artikel.php?slug=<?= urlencode($row['slug']) ?>">
                                                <?= htmlspecialchars($row['judul']) ?>
                                            </a>
                                        </h2>
                                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed mb-4">
                                            <?= htmlspecialchars($row['ringkasan']) ?>
                                        </p>
                                    </div>

                                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center font-bold text-[10px]">
                                                <?= strtoupper(substr($row['penulis'], 0, 1)) ?>
                                            </div>
                                            <span class="font-semibold text-slate-700"><?= htmlspecialchars($row['penulis']) ?></span>
                                        </div>
                                        <a href="baca-artikel.php?slug=<?= urlencode($row['slug']) ?>" class="text-brand-600 font-bold hover:text-brand-800 transition flex items-center gap-1 group-hover:translate-x-0.5">
                                            Baca Selengkapnya <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-4">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3 class="font-display font-bold text-lg text-slate-900 mb-2">Tidak Menemukan Artikel</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6">
                            Tidak ada artikel yang cocok dengan kata kunci "<strong><?= htmlspecialchars($q) ?></strong>". Coba cari kata kunci umum seperti "sampah", "plastik", atau "IoT".
                        </p>
                        <a href="artikel.php" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-brand-600 text-white font-bold text-xs hover:bg-brand-700 transition">
                            Reset Pencarian
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar (4 Cols) -->
            <aside class="lg:col-span-4 space-y-6">
                <!-- Promo CTA Card -->
                <div class="bg-gradient-to-br from-brand-800 to-teal-800 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-6 -bottom-6 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <span class="inline-block px-3 py-1 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider mb-3">
                        Ubah Sampah Jadi Hadiah
                    </span>
                    <h3 class="font-display font-extrabold text-lg text-white mb-2 leading-snug">
                        Punya Sampah Plastik &amp; Kardus di Rumah?
                    </h3>
                    <p class="text-xs text-emerald-100 leading-relaxed mb-5">
                        Setor langsung ke bank sampah Trashily atau minta armada kami jemput ke rumah Anda. Dapatkan poin reward instan!
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="harga.php" class="px-4 py-2.5 rounded-full bg-white text-brand-900 font-bold text-xs text-center hover:bg-emerald-50 transition shadow-sm">
                            Cek Daftar Harga
                        </a>
                        <a href="auth/register.php" class="px-4 py-2.5 rounded-full bg-brand-600/60 hover:bg-brand-600 border border-white/20 text-white font-bold text-xs text-center transition">
                            Daftar Sekarang
                        </a>
                    </div>
                </div>

                <!-- Popular Articles -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <i class="fa-solid fa-fire text-amber-500"></i>
                        <h3 class="font-display font-bold text-sm text-slate-900">Artikel Terpopuler</h3>
                    </div>
                    <div class="space-y-4">
                        <?php 
                        $i = 1;
                        while ($pop = $popArticles->fetch_assoc()): 
                        ?>
                            <div class="flex items-start gap-3 group">
                                <span class="w-6 h-6 rounded-lg bg-slate-100 group-hover:bg-brand-100 text-slate-500 group-hover:text-brand-700 font-bold text-xs flex items-center justify-center shrink-0 transition">
                                    <?= $i++ ?>
                                </span>
                                <div>
                                    <h4 class="font-semibold text-xs text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                        <a href="baca-artikel.php?slug=<?= urlencode($pop['slug']) ?>">
                                            <?= htmlspecialchars($pop['judul']) ?>
                                        </a>
                                    </h4>
                                    <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-2">
                                        <span><?= htmlspecialchars($pop['kategori']) ?></span>
                                        <span>&bull;</span>
                                        <span><i class="fa-regular fa-eye mr-1"></i><?= $pop['views'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- FAQ Singkat SEO -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                    <div class="flex items-center gap-2 pb-3 mb-4 border-b border-slate-100">
                        <i class="fa-solid fa-circle-question text-brand-600"></i>
                        <h3 class="font-display font-bold text-sm text-slate-900">FAQ Edukasi Sampah</h3>
                    </div>
                    <div class="space-y-3 text-xs">
                        <?php foreach ($faqs as $fq => $fa): ?>
                            <details class="group bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                <summary class="font-semibold text-slate-800 cursor-pointer list-none flex items-center justify-between">
                                    <span><?= htmlspecialchars($fq) ?></span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <p class="text-slate-600 mt-2 leading-relaxed pt-2 border-t border-slate-200/60">
                                    <?= htmlspecialchars($fa) ?>
                                </p>
                            </details>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <?php include __DIR__ . '/includes/public_footer.php'; ?>

</body>
</html>
