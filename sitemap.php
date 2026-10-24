<?php
header('Content-Type: application/xml; charset=utf-8');
require_once __DIR__ . '/config/database.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'trashily.id';
$base = defined('BASE_URL') ? BASE_URL : '';
$siteUrl = rtrim($protocol . $host . $base, '/');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <!-- Halaman Utama & Core Features -->
    <url>
        <loc><?= $siteUrl ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/artikel.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/harga.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/trashsmart.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/smartkolecer.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/gembul.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/everwear.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc><?= $siteUrl ?>/ulasan.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>

    <!-- Artikel & Edukasi Lingkungan Dinamis -->
    <?php
    $res = $conn->query("SELECT slug, judul, gambar, gambar_caption, updated_at, created_at FROM artikel WHERE status = 'published' ORDER BY created_at DESC");
    if ($res && $res->num_rows > 0):
        while ($row = $res->fetch_assoc()):
            $articleUrl = $siteUrl . '/baca-artikel.php?slug=' . urlencode($row['slug']);
            $modDate = !empty($row['updated_at']) ? date('Y-m-d', strtotime($row['updated_at'])) : date('Y-m-d', strtotime($row['created_at']));
            $imgUrl = !empty($row['gambar']) ? (strpos($row['gambar'], 'http') === 0 ? $row['gambar'] : $siteUrl . '/' . ltrim($row['gambar'], '/')) : $siteUrl . '/assets/brand.png';
            $caption = !empty($row['gambar_caption']) ? $row['gambar_caption'] : $row['judul'];
    ?>
    <url>
        <loc><?= htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8') ?></loc>
        <lastmod><?= $modDate ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.85</priority>
        <image:image>
            <image:loc><?= htmlspecialchars($imgUrl, ENT_QUOTES, 'UTF-8') ?></image:loc>
            <image:title><?= htmlspecialchars($row['judul'], ENT_QUOTES, 'UTF-8') ?></image:title>
            <image:caption><?= htmlspecialchars($caption, ENT_QUOTES, 'UTF-8') ?></image:caption>
        </image:image>
    </url>
    <?php 
        endwhile;
    endif; 
    ?>
</urlset>
