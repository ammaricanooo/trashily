<?php
/**
 * Trashily Ultra SEO Helper
 * Menghasilkan metadata, Open Graph, Twitter Cards, Geotargeting, dan JSON-LD Schema
 * yang sesuai standar Google Search Central & Core Web Vitals untuk menduduki peringkat Top 1 Google.
 */

if (!function_exists('render_seo_tags')) {
    function render_seo_tags($options = []) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = defined('BASE_URL') ? BASE_URL : '';
        $siteUrl = rtrim($protocol . $host . $base, '/');
        
        // Defaults
        $defaultTitle = 'Trashily — Bank Sampah Digital & Solusi Daur Ulang Poin Terlengkap';
        $defaultDesc  = 'Trashily adalah platform bank sampah digital dan ekosistem daur ulang modern di Bogor. Pilah sampah plastik, kardus, dan kaleng, jemput ke rumah, serta tukarkan dengan poin dan uang tunai.';
        $defaultKeywords = 'bank sampah, bank sampah digital, bank sampah bogor, daur ulang sampah, pilah sampah dapat uang, jemput sampah gratis, trashsmart iot, smartkolecer, gembul eco-kit, harga sampah plastik per kg, trashily';
        $defaultImage = $siteUrl . '/assets/brand.png';
        
        $title       = !empty($options['title']) ? $options['title'] : $defaultTitle;
        $description = !empty($options['description']) ? $options['description'] : $defaultDesc;
        $keywords    = !empty($options['keywords']) ? $options['keywords'] : $defaultKeywords;
        $canonical   = !empty($options['canonical']) ? $options['canonical'] : $siteUrl . ($_SERVER['REQUEST_URI'] ?? '');
        $ogType      = !empty($options['og_type']) ? $options['og_type'] : 'website';
        $image       = !empty($options['image']) ? $options['image'] : $defaultImage;
        $publishedAt = !empty($options['published_at']) ? $options['published_at'] : null;
        $modifiedAt  = !empty($options['modified_at']) ? $options['modified_at'] : null;
        $author      = !empty($options['author']) ? $options['author'] : 'Tim Trashily Indonesia';
        $breadcrumbs = !empty($options['breadcrumbs']) ? $options['breadcrumbs'] : [];
        $faqList     = !empty($options['faqs']) ? $options['faqs'] : [];
        $articleData = !empty($options['article']) ? $options['article'] : null;
        
        // Ensure image is absolute URL
        if (strpos($image, 'http') !== 0) {
            $image = $siteUrl . '/' . ltrim($image, '/');
        }

        // Clean & truncate description to ideal Google SERP length (155-160 chars)
        $cleanDesc = strip_tags($description);
        $cleanDesc = preg_replace('/\s+/', ' ', trim($cleanDesc));
        if (mb_strlen($cleanDesc) > 165) {
            $cleanDesc = mb_substr($cleanDesc, 0, 160) . '...';
        }
        ?>
    <!-- Core Primary Meta Tags -->
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars($cleanDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Local SEO Geo Targeting (Bogor, Jawa Barat, Indonesia) -->
    <meta name="geo.region" content="ID-JB">
    <meta name="geo.placename" content="Bogor">
    <meta name="geo.position" content="-6.5971;106.8060">
    <meta name="ICBM" content="-6.5971, 106.8060">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($cleanDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Trashily">
    <?php if ($articleData && $publishedAt): ?>
    <meta property="article:published_time" content="<?= date('c', strtotime($publishedAt)) ?>">
    <?php if ($modifiedAt): ?>
    <meta property="article:modified_time" content="<?= date('c', strtotime($modifiedAt)) ?>">
    <?php endif; ?>
    <meta property="article:author" content="<?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="article:section" content="<?= htmlspecialchars($articleData['kategori'] ?? 'Edukasi Lingkungan', ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($cleanDesc, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:creator" content="@TrashilyID">
    <meta name="twitter:site" content="@TrashilyID">

    <!-- Favicon & Mobile Icons -->
    <link rel="icon" type="image/png" href="<?= $siteUrl ?>/assets/brand.png">
    <link rel="apple-touch-icon" href="<?= $siteUrl ?>/assets/brand.png">
    <meta name="theme-color" content="#166534">

    <!-- ==================== JSON-LD STRUCTURED DATA SCHEMAS (GOOGLE TOP 1 RANKING) ==================== -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "<?= $siteUrl ?>/#organization",
          "name": "Trashily Indonesia",
          "alternateName": "Bank Sampah Trashily",
          "url": "<?= $siteUrl ?>",
          "logo": {
            "@type": "ImageObject",
            "@id": "<?= $siteUrl ?>/#logo",
            "url": "<?= $siteUrl ?>/assets/brand.png",
            "caption": "Trashily Logo"
          },
          "image": "<?= $siteUrl ?>/assets/brand.png",
          "description": "Ecosystem Bank Sampah Digital & Solusi Daur Ulang Poin Terintegrasi IoT di Bogor.",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl. Pajajaran No. 128",
            "addressLocality": "Bogor",
            "addressRegion": "Jawa Barat",
            "postalCode": "16128",
            "addressCountry": "ID"
          },
          "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+62-812-3456-7890",
            "contactType": "customer service",
            "areaServed": "ID",
            "availableLanguage": ["Indonesian", "English"]
          },
          "sameAs": [
            "https://facebook.com/trashily.id",
            "https://instagram.com/trashily.id",
            "https://twitter.com/TrashilyID",
            "https://linkedin.com/company/trashily"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "<?= $siteUrl ?>/#website",
          "url": "<?= $siteUrl ?>",
          "name": "Trashily",
          "description": "Platform Bank Sampah Digital & Rewards Terbesar di Bogor",
          "publisher": {
            "@id": "<?= $siteUrl ?>/#organization"
          },
          "inLanguage": "id-ID",
          "potentialAction": {
            "@type": "SearchAction",
            "target": {
              "@type": "EntryPoint",
              "urlTemplate": "<?= $siteUrl ?>/artikel.php?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "RecyclingCenter",
          "@id": "<?= $siteUrl ?>/#localbusiness",
          "name": "Bank Sampah Digital Trashily Bogor",
          "image": "<?= $siteUrl ?>/assets/brand.png",
          "telephone": "+62-812-3456-7890",
          "priceRange": "Rp",
          "address": {
            "@type": "PostalAddress",
            "streetAddress": "Jl. Pajajaran No. 128",
            "addressLocality": "Bogor",
            "addressRegion": "Jawa Barat",
            "postalCode": "16128",
            "addressCountry": "ID"
          },
          "geo": {
            "@type": "GeoCoordinates",
            "latitude": -6.5971,
            "longitude": 106.8060
          },
          "openingHoursSpecification": [
            {
              "@type": "OpeningHoursSpecification",
              "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
              "opens": "08:00",
              "closes": "17:00"
            }
          ]
        }
        <?php if (!empty($breadcrumbs)): ?>
        ,
        {
          "@type": "BreadcrumbList",
          "@id": "<?= $canonical ?>/#breadcrumb",
          "itemListElement": [
            <?php 
            $bList = [];
            $pos = 1;
            foreach ($breadcrumbs as $bName => $bUrl) {
                $bList[] = json_encode([
                    "@type" => "ListItem",
                    "position" => $pos++,
                    "name" => $bName,
                    "item" => $bUrl
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            echo implode(",\n            ", $bList);
            ?>
          ]
        }
        <?php endif; ?>
        <?php if (!empty($faqList)): ?>
        ,
        {
          "@type": "FAQPage",
          "@id": "<?= $canonical ?>/#faq",
          "mainEntity": [
            <?php
            $fItems = [];
            foreach ($faqList as $q => $a) {
                $fItems[] = json_encode([
                    "@type" => "Question",
                    "name" => $q,
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => strip_tags($a)
                    ]
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            echo implode(",\n            ", $fItems);
            ?>
          ]
        }
        <?php endif; ?>
        <?php if ($articleData): ?>
        ,
        {
          "@type": "Article",
          "@id": "<?= $canonical ?>/#article",
          "headline": "<?= addslashes($articleData['judul']) ?>",
          "name": "<?= addslashes($articleData['judul']) ?>",
          "description": "<?= addslashes($cleanDesc) ?>",
          "image": [
            "<?= $image ?>"
          ],
          "datePublished": "<?= date('c', strtotime($publishedAt ?? 'now')) ?>",
          "dateModified": "<?= date('c', strtotime($modifiedAt ?? ($publishedAt ?? 'now'))) ?>",
          "author": {
            "@type": "Person",
            "name": "<?= addslashes($author) ?>",
            "jobTitle": "<?= addslashes($articleData['penulis_role'] ?? 'Writer') ?>",
            "worksFor": {
              "@id": "<?= $siteUrl ?>/#organization"
            }
          },
          "publisher": {
            "@id": "<?= $siteUrl ?>/#organization"
          },
          "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "<?= $canonical ?>"
          },
          "articleSection": "<?= addslashes($articleData['kategori'] ?? 'Edukasi') ?>",
          "wordCount": <?= str_word_count(strip_tags($articleData['konten'] ?? '')) ?>,
          "timeRequired": "PT<?= intval($articleData['baca_menit'] ?? 4) ?>M",
          "inLanguage": "id-ID"
        }
        <?php endif; ?>
      ]
    }
    </script>
        <?php
    }
}
