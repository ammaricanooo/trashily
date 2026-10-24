<?php
require_once __DIR__ . '/config/database.php';

echo "Membuat tabel 'artikel' untuk fitur blog/edukasi Trashily...\n";

$sql = "
CREATE TABLE IF NOT EXISTS artikel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    kategori VARCHAR(100) NOT NULL DEFAULT 'Edukasi Lingkungan',
    ringkasan TEXT NULL,
    konten LONGTEXT NOT NULL,
    gambar VARCHAR(255) NULL,
    gambar_caption VARCHAR(255) NULL,
    penulis VARCHAR(100) NOT NULL DEFAULT 'Tim Edukasi Trashily',
    penulis_role VARCHAR(100) NOT NULL DEFAULT 'Environmental & Waste Specialist',
    penulis_avatar VARCHAR(255) NULL,
    tags VARCHAR(255) NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    meta_keywords VARCHAR(255) NULL,
    baca_menit INT NOT NULL DEFAULT 4,
    views INT NOT NULL DEFAULT 0,
    status ENUM('published','draft','archived') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_kategori (kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($sql)) {
    echo "✔ Tabel 'artikel' berhasil disiapkan.\n";
} else {
    die("❌ Gagal membuat tabel artikel: " . $conn->error . "\n");
}

// Cek apakah sudah ada artikel, jika belum kita insert seed artikel berkualitas tinggi
$check = $conn->query("SELECT COUNT(*) as c FROM artikel")->fetch_assoc()['c'] ?? 0;
if ($check == 0) {
    echo "Menambahkan seed artikel edukasi SEO tinggi...\n";
    
    $articles = [
        [
            'judul' => 'Panduan Lengkap Memilah Sampah Rumah Tangga Agar Bernilai Rupiah Tinggi',
            'slug' => 'panduan-lengkap-memilah-sampah-rumah-tangga-bernilai-tinggi',
            'kategori' => 'Panduan Daur Ulang',
            'ringkasan' => 'Pelajari cara memilah sampah plastik, kardus, dan kaleng aluminium dari rumah tangga dengan benar agar mendapatkan nilai tukar poin maksimal di Bank Sampah Trashily.',
            'konten' => '<h2>Mengapa Memilah Sampah dari Rumah Sangat Menguntungkan?</h2>
<p>Banyak masyarakat beranggapan bahwa sampah adalah barang sisa yang tidak memiliki harga. Faktanya, di era ekonomi sirkular modern, sampah yang terpilah dengan bersih adalah bahan baku industri manufaktur yang sangat dicari. Dengan memilah sampah secara tepat, Anda tidak hanya menyelamatkan lingkungan dari tumpukan TPA Galuga Bogor, tetapi juga menghasilkan passive income berupa poin dan uang tunai melalui <strong>Trashily</strong>.</p>

<h3>1. Kategori Sampah Bernilai Ekonomi Tertinggi</h3>
<p>Tidak semua sampah memiliki harga beli yang sama. Berikut adalah jenis-jenis sampah bernilai tinggi yang wajib Anda prioritaskan:</p>
<ul>
    <li><strong>Kaleng Aluminium & Logam (Rp 3.500/kg / 35 poin):</strong> Kaleng minuman soda, kopi, dan aerosol berbahan aluminium memiliki nilai lebur tinggi dan 100% dapat didaur ulang tanpa menurunkan kualitas.</li>
    <li><strong>Botol Plastik PET Bening (Rp 2.000/kg / 20 poin):</strong> Botol air mineral bening (Aqua, Le Minerale) yang telah dilepas labelnya memiliki nilai daur ulang tertinggi dibanding plastik berwarna.</li>
    <li><strong>Kardus & Karton Tebal (Rp 1.500/kg / 15 poin):</strong> Kardus packing e-commerce dan boks karton yang kering dan terlipat rapi.</li>
    <li><strong>Kertas HVS & Arsip (Rp 1.200/kg / 12 poin):</strong> Kertas dokumen kantor, buku tulis bekas, dan majalah yang bersih dari staples.</li>
</ul>

<h3>2. Teknik Membersihkan Sampah 3R (Bilas, Keringkan, Pipihkan)</h3>
<p>Salah satu kesalahan fatal yang sering menurunkan harga sampah adalah sisa cairan dan kotoran minyak. Ikuti prinsip 3 langkah mudah ini:</p>
<ol>
    <li><strong>Bilas Ringkas:</strong> Kocok botol atau wadah plastik bekas sirup/jus dengan sedikit air untuk menghilangkan sisa gula dan aroma yang mengundang semut.</li>
    <li><strong>Keringkan:</strong> Angin-anginkan sebelum dimasukkan ke dalam karung penampungan.</li>
    <li><strong>Pipihkan (Remas):</strong> Injak atau pipihkan botol dan kardus agar menghemat ruang penyimpanan hingga 70%.</li>
</ol>

<blockquote>
    <strong>Tips Pro Trashily:</strong> Pisahkan tutup botol (berbahan HDPE) dari botolnya (berbahan PET) karena keduanya memiliki proses lebur dan kategori daur ulang yang berbeda.
</blockquote>

<h3>3. Manfaatkan Fitur Jemput Sampah Trashily</h3>
<p>Jika timbulan sampah Anda sudah mencapai minimal 5 kg, Anda tidak perlu repot mengantar sendiri ke drop point. Cukup buka aplikasi web Trashily, pilih menu <em>Jemput Sampah</em>, dan armada Trashily akan mengambil langsung di alamat Anda di area Kota & Kabupaten Bogor.</p>',
            'gambar' => 'assets/brand.png',
            'gambar_caption' => 'Panduan memilah sampah bernilai tinggi di bank sampah Trashily Bogor',
            'penulis' => 'Aulia Ramadhani',
            'penulis_role' => 'Lead Eco-Educator Trashily',
            'tags' => 'memilah sampah, bank sampah bogor, daur ulang plastik, poin sampah, harga sampah',
            'meta_title' => 'Panduan Memilah Sampah Rumah Tangga Bernilai Tinggi | Trashily',
            'meta_description' => 'Panduan praktis cara memilah sampah plastik PET, kaleng aluminium, dan kardus dari rumah agar bernilai rupiah tinggi di Bank Sampah Digital Trashily Bogor.',
            'meta_keywords' => 'memilah sampah rumah tangga, bank sampah bogor, harga sampah daur ulang, cara memilah sampah plastik, trashily bogor',
            'baca_menit' => 4,
            'views' => 142
        ],
        [
            'judul' => 'Mengenal TrashSmart IoT: Tong Sampah Pintar Berbasis Sensor Pemilah Otomatis',
            'slug' => 'mengenal-trashsmart-iot-tong-sampah-pintar-pemilah-otomatis',
            'kategori' => 'Inovasi & IoT',
            'ringkasan' => 'TrashSmart menghadirkan revolusi smart city di Bogor dengan sensor ultrasonik, pemantauan volume realtime, dan integrasi cloud untuk efisiensi rute penjemputan sampah.',
            'konten' => '<h2>Era Baru Pengelolaan Sampah Kota Berbasis Internet of Things (IoT)</h2>
<p>Pengelolaan sampah konvensional di perkotaan sering kali menghadapi masalah tong sampah yang meluber, rute pengangkutan truk sampah yang tidak efisien, serta ketidaktepatan warga dalam memisahkan jenis sampah. <strong>TrashSmart IoT</strong> hadir sebagai solusi perangkat keras pintar yang terhubung langsung ke ekosistem digital Trashily.</p>

<h3>Fitur Unggulan TrashSmart</h3>
<p>Perangkat TrashSmart dirancang dengan komponen industri tangguh dan terintegrasi sistem telemetri canggih:</p>
<ul>
    <li><strong>Dual Ultrasonic Sensor HC-SR04:</strong> Mengukur ketinggian volume sampah secara presisi hingga 100 cm dengan toleransi error di bawah 1%.</li>
    <li><strong>Microcontroller ESP32 / Arduino LoRa:</strong> Memproses data lokal dan mengirimkan telemetri status kapasitas baterai dan volume sampah setiap 15 menit via jaringan WiFi/Cellular/LoRaWAN.</li>
    <li><strong>Smart Lid Actuator:</strong> Penutup tong sampah terbuka otomatis tanpa sentuhan (touchless) menggunakan sensor infra merah, menjaga higienitas pengguna fasilitas publik.</li>
    <li><strong>Indikator LED RGB Status:</strong> Hijau untuk kapasitas aman (&lt;60%), Kuning untuk status siaga (60-84%), dan Merah berkedip saat kapasitas penuh (&gt;85%).</li>
</ul>

<h3>Dampak Pengurangan Emisi Karbon</h3>
<p>Dengan adanya dashboard monitoring TrashSmart di ruang kendali admin Trashily, rute penjemputan sampah dapat dioptimalkan secara dinamis (Dynamic Route Planning). Truk pengangkut hanya mendatangi titik tong sampah yang sudah mencapai 85% kapasitas. Hal ini telah terbukti <strong>memangkas konsumsi bahan bakar armada hingga 34%</strong> dan mengurangi emisi CO2 di wilayah uji coba Kota Bogor.</p>',
            'gambar' => 'assets/brand.png',
            'gambar_caption' => 'Inovasi Smart Bin TrashSmart IoT Trashily',
            'penulis' => 'M. Ilham Pratama',
            'penulis_role' => 'IoT & Hardware Systems Engineer',
            'tags' => 'trashsmart, iot smart bin, tong sampah pintar, smart city bogor, inovasi daur ulang',
            'meta_title' => 'Mengenal TrashSmart IoT: Tong Sampah Pintar Masa Depan | Trashily',
            'meta_description' => 'Eksplorasi teknologi TrashSmart IoT dari Trashily, tong sampah pintar dengan sensor ultrasonik dan telemetry cloud untuk kota hijau yang lebih efisien.',
            'meta_keywords' => 'trashsmart iot, tong sampah pintar, smart bin indonesia, teknologi bank sampah, internet of things bogor',
            'baca_menit' => 5,
            'views' => 218
        ],
        [
            'judul' => 'Gerakan GEMBUL: Solusi Kreatif Mengubah Perilaku Anak Muda Terhadap Sampah',
            'slug' => 'gerakan-gembul-solusi-kreatif-mengubah-perilaku-anak-muda',
            'kategori' => 'Program GEMBUL',
            'ringkasan' => 'GEMBUL (Gemar Berdampak untuk Lingkungan) mengubah sosialisasi lingkungan yang membosankan menjadi tantangan seru, games sekolah, dan Eco-Pass interaktif.',
            'konten' => '<h2>Dari Ceramah Menjadi Permainan Nyata</h2>
<p>Berdasarkan data RPJMD Kota Bogor, timbulan sampah mencapai lebih dari 650 ton per hari, dengan proporsi terbesar berasal dari generasi muda dan area permukiman. Sayangnya, edukasi konvensional berupa seminar dan poster sering kali diabaikan. Inilah alasan lahirnya <strong>GEMBUL (Gemar Berdampak untuk Lingkungan)</strong>.</p>

<h3>Tiga Pilar Utama Creative Eco-Kit GEMBUL</h3>
<ol>
    <li><strong>Eco-Games di Sekolah & Kampus:</strong> Permainan simulasi interaktif seperti Dadu Pemilah Sampah, Tebak Kategori Sampah, dan Eco-Quiz yang dimainkan beregu untuk melatih kebiasaan pilah dalam hitungan detik.</li>
    <li><strong>Toolkit Pemilah Rumah Tangga:</strong> Paket wadah pilah modular dengan kode warna standar nasional yang dirancang estetis dan cocok diletakkan di kamar anak kos maupun dapur minimalis.</li>
    <li><strong>Eco-Pass Digital:</strong> Kartu paspor hijau yang mencatat setiap aksi baik pilah sampah, ditukar menjadi stempel digital, badge komunitas, dan diskon merchandise lokal.</li>
</ol>

<h3>Jadwal Roadshow Sekolah & Komunitas</h3>
<p>Sepanjang tahun 2026, GEMBUL telah menjadwalkan kunjungan ke puluhan SMA, SMK, dan kampus di Bogor dan sekitarnya. Siswa dan relawan yang bergabung dapat melihat langsung live dashboard penimbangan dan menguji langsung purwarupa TrashSmart dan SmartKolecer.</p>',
            'gambar' => 'assets/brand.png',
            'gambar_caption' => 'Gerakan anak muda peduli lingkungan GEMBUL Trashily',
            'penulis' => 'Farhan Alamsyah',
            'penulis_role' => 'Youth Campaign Director GEMBUL',
            'tags' => 'gembul, gemar berdampak untuk lingkungan, eco-kit, edukasi sampah anak muda, bogor hijau',
            'meta_title' => 'Gerakan GEMBUL: Aksi Kreatif Anak Muda Sayang Lingkungan | Trashily',
            'meta_description' => 'Program GEMBUL Trashily mengedukasi generasi muda memilah sampah melalui game simulasi, Creative Eco-Kit, dan Eco-Pass digital di sekolah-sekolah Bogor.',
            'meta_keywords' => 'gerakan gembul, eco-kit sekolah, pemuda peduli sampah, bank sampah generasi muda bogor, edukasi lingkungan',
            'baca_menit' => 3,
            'views' => 95
        ],
        [
            'judul' => 'Daftar Harga Sampah Daur Ulang Terbaru di Bogor 2026: Mana yang Paling Menguntungkan?',
            'slug' => 'daftar-harga-sampah-daur-ulang-terbaru-bogor-2026',
            'kategori' => 'Edukasi Lingkungan',
            'ringkasan' => 'Update lengkap nilai tukar rupiah dan poin per kilogram sampah plastik, kardus, kaleng, kertas, dan kaca di Bank Sampah Digital Trashily.',
            'konten' => '<h2>Mengubah Sampah Menjadi Tabungan Nyata</h2>
<p>Banyak masyarakat yang belum mengetahui nilai ekonomi sampah yang mereka hasilkan setiap hari. Dengan sistem transparansi harga di Trashily, setiap kilogram sampah yang Anda setorkan ditimbang secara digital dan langsung dikonversi menjadi poin yang dapat dicairkan atau ditukar hadiah menarik.</p>

<h3>Daftar Harga & Nilai Poin Terkini</h3>
<p>Berikut adalah tabel ringkas harga sampah per kilogram di drop point Trashily Bogor per tahun 2026:</p>
<ul>
    <li><strong>Kaleng Minuman Aluminium:</strong> Rp 3.500 / kg &rarr; 35 Poin Trashily</li>
    <li><strong>Botol Plastik PET (Bening):</strong> Rp 2.000 / kg &rarr; 20 Poin Trashily</li>
    <li><strong>Kardus / Karton Cokelat:</strong> Rp 1.500 / kg &rarr; 15 Poin Trashily</li>
    <li><strong>Kertas HVS / Buku Bekas:</strong> Rp 1.200 / kg &rarr; 12 Poin Trashily</li>
    <li><strong>Botol Kaca Utuh / Pecah:</strong> Rp 1.000 / kg &rarr; 10 Poin Trashily</li>
    <li><strong>Plastik Kresek / Campuran:</strong> Rp 800 / kg &rarr; 8 Poin Trashily</li>
    <li><strong>Sampah Organik / Kompos:</strong> Rp 500 / kg &rarr; 5 Poin Trashily</li>
</ul>

<h3>Hadiah & Reward yang Bisa Ditukar</h3>
<p>Poin yang Anda kumpulkan di akun Trashily tidak memiliki masa kedaluwarsa. Anda dapat menukarkannya kapan saja dengan produk sembako (minyak goreng, beras, gula), perlengkapan sekolah ramah lingkungan, tumbler stainless, hingga saldo e-wallet.</p>',
            'gambar' => 'assets/brand.png',
            'gambar_caption' => 'Daftar harga sampah daur ulang terbaru Bank Sampah Trashily Bogor',
            'penulis' => 'Tim Riset Pasar Trashily',
            'penulis_role' => 'Market Price Analyst',
            'tags' => 'harga sampah, harga rongsokan bogor, tukar poin sampah, bank sampah terdekat, jual botol plastik',
            'meta_title' => 'Daftar Harga Sampah Daur Ulang Bogor 2026 Terbaru | Trashily',
            'meta_description' => 'Cek daftar harga sampah daur ulang terbaru 2026 di Bogor: plastik PET, kaleng aluminium, kardus, dan kertas. Setor dan dapatkan poin uang tunai di Trashily.',
            'meta_keywords' => 'harga sampah daur ulang bogor, harga rongsokan 2026, jual botol plastik per kg, harga kardus bekas, bank sampah bogor harga',
            'baca_menit' => 4,
            'views' => 310
        ]
    ];

    $stmt = $conn->prepare("INSERT INTO artikel (judul, slug, kategori, ringkasan, konten, gambar, gambar_caption, penulis, penulis_role, tags, meta_title, meta_description, meta_keywords, baca_menit, views, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')");

    foreach ($articles as $a) {
        $stmt->bind_param("sssssssssssssii", 
            $a['judul'], 
            $a['slug'], 
            $a['kategori'], 
            $a['ringkasan'], 
            $a['konten'], 
            $a['gambar'], 
            $a['gambar_caption'], 
            $a['penulis'], 
            $a['penulis_role'], 
            $a['tags'], 
            $a['meta_title'], 
            $a['meta_description'], 
            $a['meta_keywords'], 
            $a['baca_menit'], 
            $a['views']
        );
        $stmt->execute();
    }
    echo "✔ 4 Seed artikel edukasi berhasil diinput.\n";
} else {
    echo "Tabel artikel sudah memiliki $check data.\n";
}

echo "Migrasi artikel selesai dengan sukses!\n";
