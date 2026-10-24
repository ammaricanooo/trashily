<?php
session_start();
require_once 'config/database.php';
require_once 'includes/seo_helper.php';

$logged_in = isset($_SESSION['user_id']);
$dash_url  = $logged_in ? ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'customer/dashboard.php') : null;

// Get Slug
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: artikel.php");
    exit;
}

// Fetch Article
$stmt = $conn->prepare("SELECT * FROM artikel WHERE slug = ? AND status = 'published' LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    http_response_code(404);
    die('<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Artikel Tidak Ditemukan | Trashily</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-50 flex items-center justify-center min-h-screen text-center p-6"><div class="bg-white p-10 rounded-3xl border border-slate-200 max-w-md shadow-xl"><h1 class="text-3xl font-extrabold text-slate-800 mb-2">404</h1><p class="text-sm text-slate-500 mb-6">Maaf, artikel yang Anda cari tidak ditemukan atau telah dipindahkan.</p><a href="artikel.php" class="px-6 py-2.5 rounded-full bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700">Kembali ke Koleksi Artikel</a></div></body></html>');
}

// Increment Views safely (using cookie/session to avoid inflating on reload)
$viewCookie = 'viewed_art_' . $article['id'];
if (!isset($_COOKIE[$viewCookie])) {
    $conn->query("UPDATE artikel SET views = views + 1 WHERE id = " . intval($article['id']));
    setcookie($viewCookie, '1', time() + 3600, '/');
    $article['views']++;
}

// URL & Canonical
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$siteUrl = rtrim($protocol . $host . BASE_URL, '/');
$canonicalUrl = $siteUrl . '/baca-artikel.php?slug=' . urlencode($article['slug']);

// SEO Metadata
$seoTitle = !empty($article['meta_title']) ? $article['meta_title'] : $article['judul'] . ' | Trashily Edukasi';
$seoDesc  = !empty($article['meta_description']) ? $article['meta_description'] : $article['ringkasan'];
$seoKeywords = !empty($article['meta_keywords']) ? $article['meta_keywords'] : $article['tags'];
$featuredImg = !empty($article['gambar']) ? (strpos($article['gambar'], 'http') === 0 ? $article['gambar'] : $siteUrl . '/' . ltrim($article['gambar'], '/')) : $siteUrl . '/assets/brand.png';

$breadcrumbs = [
    'Beranda' => $siteUrl . '/',
    'Artikel & Edukasi' => $siteUrl . '/artikel.php',
    $article['kategori'] => $siteUrl . '/artikel.php?kategori=' . urlencode($article['kategori']),
    $article['judul'] => $canonicalUrl
];

// Related Articles
$relStmt = $conn->prepare("SELECT id, judul, slug, gambar, gambar_caption, kategori, baca_menit, created_at FROM artikel WHERE kategori = ? AND id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
$relStmt->bind_param("si", $article['kategori'], $article['id']);
$relStmt->execute();
$related = $relStmt->get_result();

// If fewer than 3 in same category, get latest
if ($related->num_rows < 3) {
    $relStmt2 = $conn->prepare("SELECT id, judul, slug, gambar, gambar_caption, kategori, baca_menit, created_at FROM artikel WHERE id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    $relStmt2->bind_param("i", $article['id']);
    $relStmt2->execute();
    $related = $relStmt2->get_result();
}

// Generate Table of Contents automatically from <h2> & <h3> in content
preg_match_all('/<h([23])>(.*?)<\/h\1>/i', $article['konten'], $headings, PREG_SET_ORDER);
$tocList = [];
$contentWithAnchors = $article['konten'];
foreach ($headings as $index => $h) {
    $level = $h[1];
    $text = strip_tags($h[2]);
    $anchorId = 'heading-' . ($index + 1) . '-' . preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($text));
    $anchorId = trim($anchorId, '-');
    $tocList[] = ['level' => $level, 'text' => $text, 'id' => $anchorId];

    // Replace original tag with ID
    $oldTag = $h[0];
    $newTag = "<h{$level} id=\"{$anchorId}\" class=\"scroll-mt-24\">{$h[2]}</h{$level}>";
    $contentWithAnchors = preg_replace('/' . preg_quote($oldTag, '/') . '/', $newTag, $contentWithAnchors, 1);
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php 
    render_seo_tags([
        'title' => $seoTitle,
        'description' => $seoDesc,
        'keywords' => $seoKeywords,
        'canonical' => $canonicalUrl,
        'image' => $featuredImg,
        'og_type' => 'article',
        'published_at' => $article['created_at'],
        'modified_at' => $article['updated_at'],
        'author' => $article['penulis'],
        'breadcrumbs' => $breadcrumbs,
        'article' => [
            'judul' => $article['judul'],
            'kategori' => $article['kategori'],
            'penulis_role' => $article['penulis_role'],
            'konten' => $article['konten'],
            'baca_menit' => $article['baca_menit']
        ]
    ]); 
    ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

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
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        reading: ['"Lora"', 'Georgia', 'serif']
                    }
                }
            }
        }
    </script>
    <style>
        /* ================= Editorial Article Typography ================= */
        .article-content {
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 1.125rem; /* 18px */
            line-height: 1.88;
            color: #334155;
            word-break: break-word;
        }
        .article-content h2 {
            font-family: var(--font-display, 'Plus Jakarta Sans', sans-serif);
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            margin-top: 3rem;
            margin-bottom: 1.15rem;
            line-height: 1.3;
            letter-spacing: -0.025em;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
        }
        .article-content h2:first-child {
            margin-top: 0;
            padding-top: 0;
            border-top: none;
        }
        .article-content h3 {
            font-family: var(--font-display, 'Plus Jakarta Sans', sans-serif);
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 2.25rem;
            margin-bottom: 0.85rem;
            line-height: 1.35;
            letter-spacing: -0.015em;
        }
        .article-content h4 {
            font-family: var(--font-display, 'Plus Jakarta Sans', sans-serif);
            font-size: 1.15rem;
            font-weight: 700;
            color: #334155;
            margin-top: 1.75rem;
            margin-bottom: 0.65rem;
        }
        .article-content p {
            font-size: 1.125rem;
            line-height: 1.88;
            color: #334155;
            margin-bottom: 1.5rem;
            letter-spacing: -0.005em;
        }
        .article-content ul, .article-content ol {
            margin-bottom: 1.75rem;
            padding-left: 1.75rem;
            color: #334155;
            line-height: 1.85;
            font-size: 1.125rem;
        }
        .article-content ul {
            list-style-type: disc;
        }
        .article-content ol {
            list-style-type: decimal;
        }
        .article-content li {
            margin-bottom: 0.65rem;
            padding-left: 0.35rem;
        }
        .article-content blockquote {
            border-left: 4px solid #16a34a;
            background: #f8fafc;
            padding: 1.25rem 1.75rem;
            border-radius: 0 1rem 1rem 0;
            margin: 2.25rem 0;
            color: #1e293b;
            font-size: 1.15rem;
            line-height: 1.75;
            font-style: italic;
        }
        .article-content blockquote p {
            margin-bottom: 0;
            color: inherit;
        }
        .article-content strong, .article-content b {
            color: #0f172a;
            font-weight: 700;
        }
        .article-content a {
            color: #15803d;
            text-decoration: underline;
            text-decoration-thickness: 1.5px;
            text-underline-offset: 3px;
            transition: color 0.15s ease;
        }
        .article-content a:hover {
            color: #166534;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 1rem;
            margin: 2rem auto;
            display: block;
        }
        .article-content figure {
            margin: 2rem 0;
        }
        .article-content figcaption {
            text-align: center;
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 0.5rem;
            font-style: italic;
        }
        .article-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            font-size: 0.95rem;
        }
        .article-content th, .article-content td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem 1rem;
            text-align: left;
        }
        .article-content th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #0f172a;
        }
        .article-content code {
            font-size: 0.9em;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 0.2rem 0.4rem;
            border-radius: 0.375rem;
        }
        .article-content pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 1.25rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin: 1.75rem 0;
        }
        .article-content hr {
            border: 0;
            height: 1px;
            background: #e2e8f0;
            margin: 3rem 0;
        }
    </style>
</head>

<body class="bg-white text-slate-800 font-sans antialiased selection:bg-brand-500 selection:text-white">

    <?php
    $active_nav = 'artikel';
    include __DIR__ . '/includes/public_navbar.php';
    ?>

    <!-- ================= Semantic Breadcrumb ================= -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-8 pb-3">
        <nav class="flex items-center flex-wrap gap-2 text-xs text-slate-400" aria-label="Breadcrumb">
            <a href="index.php" class="hover:text-brand-700 transition flex items-center gap-1"><i class="fa-solid fa-house"></i> Beranda</a>
            <span>/</span>
            <a href="artikel.php" class="hover:text-brand-700 transition">Artikel</a>
            <span>/</span>
            <a href="artikel.php?kategori=<?= urlencode($article['kategori']) ?>" class="hover:text-brand-700 transition text-brand-700 font-semibold"><?= htmlspecialchars($article['kategori']) ?></a>
            <span>/</span>
            <span class="text-slate-600 font-medium truncate max-w-[200px] sm:max-w-xs"><?= htmlspecialchars($article['judul']) ?></span>
        </nav>
    </div>

    <!-- ================= Main Editorial Article ================= -->
    <main class="w-full">
        <!-- Article Header & Byline -->
        <header class="max-w-3xl mx-auto px-4 sm:px-6 pt-4 pb-2">
            <!-- Category Badge & Reading Time -->
            <div class="flex items-center gap-2 mb-4">
                <a href="artikel.php?kategori=<?= urlencode($article['kategori']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 text-brand-700 hover:bg-brand-100 font-bold text-xs uppercase tracking-wider border border-brand-200/60 transition">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                    <?= htmlspecialchars($article['kategori']) ?>
                </a>
            </div>

            <!-- Main Editorial Title (H1) -->
            <h1 class="font-display font-extrabold text-3xl sm:text-4xl md:text-[44px] text-slate-900 tracking-tight leading-[1.2] mb-5">
                <?= htmlspecialchars($article['judul']) ?>
            </h1>

            <!-- Article Excerpt / Lead Paragraph -->
            <?php if (!empty($article['ringkasan'])): ?>
                <p class="text-lg sm:text-xl text-slate-600 leading-relaxed font-normal mb-6">
                    <?= htmlspecialchars($article['ringkasan']) ?>
                </p>
            <?php endif; ?>

            <!-- Author & Publication Metadata Bar -->
            <div class="flex flex-wrap items-center justify-between gap-4 py-4 border-y border-slate-100 text-xs">
                <!-- Author Info -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-brand-600 to-emerald-700 text-white font-bold flex items-center justify-center text-sm shadow-sm ring-2 ring-brand-100 shrink-0">
                        <?= strtoupper(substr($article['penulis'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($article['penulis']) ?></span>
                            <i class="fa-solid fa-circle-check text-brand-600 text-xs" title="Penulis Terverifikasi"></i>
                        </div>
                        <div class="text-slate-500 text-[11px]"><?= htmlspecialchars($article['penulis_role']) ?> &bull; Trashily</div>
                    </div>
                </div>

                <!-- Meta Specs & Date -->
                <div class="flex items-center gap-3 sm:gap-5 text-slate-500">
                    <span title="Tanggal Publikasi"><i class="fa-regular fa-calendar mr-1 text-slate-400"></i> <?= date('d M Y', strtotime($article['created_at'])) ?></span>
                    <span title="Estimasi Waktu Membaca"><i class="fa-regular fa-clock mr-1 text-slate-400"></i> <?= $article['baca_menit'] ?> min baca</span>
                    <span title="Total Pembaca"><i class="fa-regular fa-eye mr-1 text-slate-400"></i> <?= number_format($article['views']) ?> views</span>
                </div>
            </div>

            <!-- Top Social Share Bar -->
            <div class="flex items-center justify-between py-3 my-2 text-xs text-slate-500">
                <span class="font-medium text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-share-nodes text-brand-600"></i> Bagikan:
                </span>
                <div class="flex items-center gap-2">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['judul'] . ' - ' . $canonicalUrl) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm transition" title="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text=<?= urlencode($article['judul']) ?>&url=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 flex items-center justify-center text-sm transition" title="X / Twitter">
                        <i class="fa-brands fa-x-twitter"></i>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-blue-50 hover:bg-blue-100 text-blue-700 flex items-center justify-center text-sm transition" title="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>
                    <button type="button" onclick="navigator.clipboard.writeText('<?= $canonicalUrl ?>'); alert('Tautan artikel berhasil disalin!');" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-sm transition" title="Salin Tautan">
                        <i class="fa-solid fa-link"></i>
                    </button>
                    <button type="button" onclick="window.print();" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center text-sm transition" title="Cetak Artikel">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Featured Hero Image (Full Editorial Width) -->
        <?php if (!empty($article['gambar'])): ?>
            <figure class="max-w-4xl mx-auto px-4 sm:px-6 my-6">
                <img src="<?= htmlspecialchars($featuredImg) ?>" alt="<?= htmlspecialchars($article['gambar_caption'] ?: $article['judul']) ?>" class="w-full rounded-2xl object-cover max-h-[500px]" width="1200" height="630">
                <?php if (!empty($article['gambar_caption'])): ?>
                    <figcaption class="mt-2.5 text-center text-xs text-slate-500 italic">
                        <i class="fa-solid fa-camera mr-1 text-slate-400"></i> <?= htmlspecialchars($article['gambar_caption']) ?>
                    </figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <!-- Table of Contents (Daftar Isi - Editorial Callout) -->
        <?php if (!empty($tocList)): ?>
            <div class="max-w-3xl mx-auto px-4 sm:px-6 mb-10">
                <div class="bg-slate-50/90 border-l-4 border-brand-600 rounded-r-2xl p-5 sm:p-6">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-list-ul text-brand-700"></i>
                        <h3 class="font-display font-bold text-sm text-slate-900 uppercase tracking-wider">Daftar Isi Artikel</h3>
                    </div>
                    <ul class="space-y-1.5 text-xs">
                        <?php foreach ($tocList as $toc): ?>
                            <li class="<?= $toc['level'] == '3' ? 'ml-4 list-circle text-slate-600' : 'font-semibold text-slate-800' ?>">
                                <a href="#<?= $toc['id'] ?>" class="hover:text-brand-700 transition hover:underline">
                                    <?= htmlspecialchars($toc['text']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Pure Editorial Article Content (No Card Container!) -->
        <article class="article-content max-w-3xl mx-auto px-4 sm:px-6 py-2">
            <?= $contentWithAnchors ?>
        </article>

        <!-- Article Tags -->
        <?php if (!empty($article['tags'])): ?>
            <div class="max-w-3xl mx-auto px-4 sm:px-6 mt-10 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 mr-2"><i class="fa-solid fa-tags"></i> Topik Terkait:</span>
                <?php 
                $tagArray = explode(',', $article['tags']);
                foreach ($tagArray as $t): 
                    $t = trim($t);
                    if ($t):
                ?>
                    <a href="artikel.php?q=<?= urlencode($t) ?>" class="px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-600 text-xs font-medium transition">
                        #<?= htmlspecialchars($t) ?>
                    </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>

        <!-- Bottom Share Callout -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 my-8 py-5 border-y border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
                <span class="font-display font-bold text-sm text-slate-900 block">Suka dengan panduan ini?</span>
                <span class="text-xs text-slate-500">Bagikan kepada teman dan keluarga agar makin banyak yang sadar pilah sampah.</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['judul'] . ' - ' . $canonicalUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                    <i class="fa-brands fa-whatsapp text-sm"></i> Bagikan
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('<?= $canonicalUrl ?>'); alert('Tautan artikel berhasil disalin!');" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    <i class="fa-solid fa-link"></i> Salin Link
                </button>
            </div>
        </div>

        <!-- Editorial Author Bio Box (Standard Publication Style) -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 my-10">
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-6 sm:p-7 flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-brand-600 to-emerald-700 text-white font-display font-extrabold text-2xl flex items-center justify-center shrink-0 shadow-sm ring-4 ring-brand-100">
                    <?= strtoupper(substr($article['penulis'], 0, 1)) ?>
                </div>
                <div class="text-center sm:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
                        <span class="font-display font-bold text-base text-slate-900"><?= htmlspecialchars($article['penulis']) ?></span>
                        <span class="px-2.5 py-0.5 rounded-full bg-brand-100 text-brand-800 text-[11px] font-bold">Penulis Redaksi</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-3">
                        <?= htmlspecialchars($article['penulis_role']) ?> di <strong>Trashily</strong>. Berdedikasi dalam riset daur ulang, edukasi pilah sampah rumah tangga, serta pengembangan ekosistem bank sampah berbasis teknologi di Bogor.
                    </p>
                    <div class="text-[11px] text-slate-400 flex items-center justify-center sm:justify-start gap-1">
                        <i class="fa-solid fa-shield-halved text-brand-600"></i>
                        <span>Artikel telah diverifikasi dan memenuhi standar editorial edukasi Trashily.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Callout Banner (Pilah Sampah & Poin) -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 mb-16">
            <div class="bg-gradient-to-br from-brand-700 via-brand-800 to-teal-900 text-white rounded-2xl p-8 sm:p-10 text-center shadow-lg relative overflow-hidden">
                <h3 class="font-display font-extrabold text-2xl sm:text-3xl mb-2 text-white">
                    Punya Sampah Daur Ulang di Rumah?
                </h3>
                <p class="text-sm text-emerald-100 max-w-lg mx-auto mb-6 leading-relaxed">
                    Ubah sampah plastik, kardus, minyak jelantah, dan logam Anda menjadi poin rupiah yang bisa ditarik tunai atau ditukar sembako.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="harga.php" class="px-6 py-3 rounded-full bg-white text-brand-900 font-bold text-xs hover:bg-emerald-50 transition shadow-md">
                        <i class="fa-solid fa-tags mr-1"></i> Cek Daftar Harga Sampah
                    </a>
                    <a href="auth/register.php" class="px-6 py-3 rounded-full bg-emerald-950/60 hover:bg-emerald-950 text-white font-bold text-xs border border-white/20 transition">
                        <i class="fa-solid fa-user-plus mr-1"></i> Daftar Gratis Sekarang
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Articles Section (Standard Editorial 3-Col Grid) -->
        <?php if ($related->num_rows > 0): ?>
            <section class="max-w-4xl mx-auto px-4 sm:px-6 pt-12 pb-20 border-t border-slate-200">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-700 block">Rekomendasi Bacaan</span>
                        <h3 class="font-display font-bold text-2xl text-slate-900">Artikel Terkait Lainnya</h3>
                    </div>
                    <a href="artikel.php?kategori=<?= urlencode($article['kategori']) ?>" class="text-xs font-bold text-brand-600 hover:text-brand-800 transition flex items-center gap-1">
                        Lihat Semua <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php while ($r = $related->fetch_assoc()): 
                        $rImg = !empty($r['gambar']) ? (strpos($r['gambar'], 'http') === 0 ? $r['gambar'] : $siteUrl . '/' . ltrim($r['gambar'], '/')) : 'assets/brand.png';
                    ?>
                        <article class="flex flex-col justify-between group">
                            <div>
                                <!-- Image Thumbnail -->
                                <a href="baca-artikel.php?slug=<?= urlencode($r['slug']) ?>" class="block rounded-xl overflow-hidden mb-3 aspect-video bg-slate-100">
                                    <img src="<?= htmlspecialchars($rImg) ?>" alt="<?= htmlspecialchars($r['judul']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>
                                <span class="inline-block text-[11px] font-bold text-brand-700 uppercase tracking-wide mb-1">
                                    <?= htmlspecialchars($r['kategori']) ?>
                                </span>
                                <h4 class="font-display font-bold text-sm text-slate-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug mb-2">
                                    <a href="baca-artikel.php?slug=<?= urlencode($r['slug']) ?>">
                                        <?= htmlspecialchars($r['judul']) ?>
                                    </a>
                                </h4>
                            </div>
                            <div class="pt-2 text-[11px] text-slate-400 flex items-center gap-3">
                                <span><i class="fa-regular fa-clock mr-1"></i> <?= $r['baca_menit'] ?> min baca</span>
                                <span>&bull;</span>
                                <span><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/public_footer.php'; ?>

</body>
</html>
