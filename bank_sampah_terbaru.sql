-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: bank_sampah
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `artikel`
--

DROP TABLE IF EXISTS `artikel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artikel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Edukasi Lingkungan',
  `ringkasan` text COLLATE utf8mb4_unicode_ci,
  `konten` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gambar_caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penulis` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tim Edukasi Trashily',
  `penulis_role` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Environmental & Waste Specialist',
  `penulis_avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `baca_menit` int NOT NULL DEFAULT '4',
  `views` int NOT NULL DEFAULT '0',
  `status` enum('published','draft','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_kategori` (`kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artikel`
--

LOCK TABLES `artikel` WRITE;
/*!40000 ALTER TABLE `artikel` DISABLE KEYS */;
INSERT INTO `artikel` VALUES (1,'Panduan Lengkap Memilah Sampah Rumah Tangga Agar Bernilai Rupiah Tinggi','panduan-lengkap-memilah-sampah-rumah-tangga-bernilai-tinggi','Panduan Daur Ulang','Pelajari cara memilah sampah plastik, kardus, dan kaleng aluminium dari rumah tangga dengan benar agar mendapatkan nilai tukar poin maksimal di Bank Sampah Trashily.','<h2>Mengapa Memilah Sampah dari Rumah Sangat Menguntungkan?</h2>\n<p>Banyak masyarakat beranggapan bahwa sampah adalah barang sisa yang tidak memiliki harga. Faktanya, di era ekonomi sirkular modern, sampah yang terpilah dengan bersih adalah bahan baku industri manufaktur yang sangat dicari. Dengan memilah sampah secara tepat, Anda tidak hanya menyelamatkan lingkungan dari tumpukan TPA Galuga Bogor, tetapi juga menghasilkan passive income berupa poin dan uang tunai melalui <strong>Trashily</strong>.</p>\n\n<h3>1. Kategori Sampah Bernilai Ekonomi Tertinggi</h3>\n<p>Tidak semua sampah memiliki harga beli yang sama. Berikut adalah jenis-jenis sampah bernilai tinggi yang wajib Anda prioritaskan:</p>\n<ul>\n    <li><strong>Kaleng Aluminium & Logam (Rp 3.500/kg / 35 poin):</strong> Kaleng minuman soda, kopi, dan aerosol berbahan aluminium memiliki nilai lebur tinggi dan 100% dapat didaur ulang tanpa menurunkan kualitas.</li>\n    <li><strong>Botol Plastik PET Bening (Rp 2.000/kg / 20 poin):</strong> Botol air mineral bening (Aqua, Le Minerale) yang telah dilepas labelnya memiliki nilai daur ulang tertinggi dibanding plastik berwarna.</li>\n    <li><strong>Kardus & Karton Tebal (Rp 1.500/kg / 15 poin):</strong> Kardus packing e-commerce dan boks karton yang kering dan terlipat rapi.</li>\n    <li><strong>Kertas HVS & Arsip (Rp 1.200/kg / 12 poin):</strong> Kertas dokumen kantor, buku tulis bekas, dan majalah yang bersih dari staples.</li>\n</ul>\n\n<h3>2. Teknik Membersihkan Sampah 3R (Bilas, Keringkan, Pipihkan)</h3>\n<p>Salah satu kesalahan fatal yang sering menurunkan harga sampah adalah sisa cairan dan kotoran minyak. Ikuti prinsip 3 langkah mudah ini:</p>\n<ol>\n    <li><strong>Bilas Ringkas:</strong> Kocok botol atau wadah plastik bekas sirup/jus dengan sedikit air untuk menghilangkan sisa gula dan aroma yang mengundang semut.</li>\n    <li><strong>Keringkan:</strong> Angin-anginkan sebelum dimasukkan ke dalam karung penampungan.</li>\n    <li><strong>Pipihkan (Remas):</strong> Injak atau pipihkan botol dan kardus agar menghemat ruang penyimpanan hingga 70%.</li>\n</ol>\n\n<blockquote>\n    <strong>Tips Pro Trashily:</strong> Pisahkan tutup botol (berbahan HDPE) dari botolnya (berbahan PET) karena keduanya memiliki proses lebur dan kategori daur ulang yang berbeda.\n</blockquote>\n\n<h3>3. Manfaatkan Fitur Jemput Sampah Trashily</h3>\n<p>Jika timbulan sampah Anda sudah mencapai minimal 5 kg, Anda tidak perlu repot mengantar sendiri ke drop point. Cukup buka aplikasi web Trashily, pilih menu <em>Jemput Sampah</em>, dan armada Trashily akan mengambil langsung di alamat Anda di area Kota & Kabupaten Bogor.</p>','assets/brand.png','Panduan memilah sampah bernilai tinggi di bank sampah Trashily Bogor','Aulia Ramadhani','Lead Eco-Educator Trashily',NULL,'memilah sampah, bank sampah bogor, daur ulang plastik, poin sampah, harga sampah','Panduan Memilah Sampah Rumah Tangga Bernilai Tinggi | Trashily','Panduan praktis cara memilah sampah plastik PET, kaleng aluminium, dan kardus dari rumah agar bernilai rupiah tinggi di Bank Sampah Digital Trashily Bogor.','memilah sampah rumah tangga, bank sampah bogor, harga sampah daur ulang, cara memilah sampah plastik, trashily bogor',4,147,'published','2026-10-21 11:12:14','2026-10-21 17:07:46');
INSERT INTO `artikel` VALUES (2,'Mengenal TrashSmart IoT: Tong Sampah Pintar Berbasis Sensor Pemilah Otomatis','mengenal-trashsmart-iot-tong-sampah-pintar-pemilah-otomatis','Inovasi & IoT','TrashSmart menghadirkan revolusi smart city di Bogor dengan sensor ultrasonik, pemantauan volume realtime, dan integrasi cloud untuk efisiensi rute penjemputan sampah.','<h2>Era Baru Pengelolaan Sampah Kota Berbasis Internet of Things (IoT)</h2>\n<p>Pengelolaan sampah konvensional di perkotaan sering kali menghadapi masalah tong sampah yang meluber, rute pengangkutan truk sampah yang tidak efisien, serta ketidaktepatan warga dalam memisahkan jenis sampah. <strong>TrashSmart IoT</strong> hadir sebagai solusi perangkat keras pintar yang terhubung langsung ke ekosistem digital Trashily.</p>\n\n<h3>Fitur Unggulan TrashSmart</h3>\n<p>Perangkat TrashSmart dirancang dengan komponen industri tangguh dan terintegrasi sistem telemetri canggih:</p>\n<ul>\n    <li><strong>Dual Ultrasonic Sensor HC-SR04:</strong> Mengukur ketinggian volume sampah secara presisi hingga 100 cm dengan toleransi error di bawah 1%.</li>\n    <li><strong>Microcontroller ESP32 / Arduino LoRa:</strong> Memproses data lokal dan mengirimkan telemetri status kapasitas baterai dan volume sampah setiap 15 menit via jaringan WiFi/Cellular/LoRaWAN.</li>\n    <li><strong>Smart Lid Actuator:</strong> Penutup tong sampah terbuka otomatis tanpa sentuhan (touchless) menggunakan sensor infra merah, menjaga higienitas pengguna fasilitas publik.</li>\n    <li><strong>Indikator LED RGB Status:</strong> Hijau untuk kapasitas aman (&lt;60%), Kuning untuk status siaga (60-84%), dan Merah berkedip saat kapasitas penuh (&gt;85%).</li>\n</ul>\n\n<h3>Dampak Pengurangan Emisi Karbon</h3>\n<p>Dengan adanya dashboard monitoring TrashSmart di ruang kendali admin Trashily, rute penjemputan sampah dapat dioptimalkan secara dinamis (Dynamic Route Planning). Truk pengangkut hanya mendatangi titik tong sampah yang sudah mencapai 85% kapasitas. Hal ini telah terbukti <strong>memangkas konsumsi bahan bakar armada hingga 34%</strong> dan mengurangi emisi CO2 di wilayah uji coba Kota Bogor.</p>','assets/brand.png','Inovasi Smart Bin TrashSmart IoT Trashily','M. Ilham Pratama','IoT & Hardware Systems Engineer',NULL,'trashsmart, iot smart bin, tong sampah pintar, smart city bogor, inovasi daur ulang','Mengenal TrashSmart IoT: Tong Sampah Pintar Masa Depan | Trashily','Eksplorasi teknologi TrashSmart IoT dari Trashily, tong sampah pintar dengan sensor ultrasonik dan telemetry cloud untuk kota hijau yang lebih efisien.','trashsmart iot, tong sampah pintar, smart bin indonesia, teknologi bank sampah, internet of things bogor',5,220,'published','2026-10-21 11:12:14','2026-10-21 17:04:56');
INSERT INTO `artikel` VALUES (3,'Gerakan GEMBUL: Solusi Kreatif Mengubah Perilaku Anak Muda Terhadap Sampah','gerakan-gembul-solusi-kreatif-mengubah-perilaku-anak-muda','Program GEMBUL','GEMBUL (Gemar Berdampak untuk Lingkungan) mengubah sosialisasi lingkungan yang membosankan menjadi tantangan seru, games sekolah, dan Eco-Pass interaktif.','<h2>Dari Ceramah Menjadi Permainan Nyata</h2>\n<p>Berdasarkan data RPJMD Kota Bogor, timbulan sampah mencapai lebih dari 650 ton per hari, dengan proporsi terbesar berasal dari generasi muda dan area permukiman. Sayangnya, edukasi konvensional berupa seminar dan poster sering kali diabaikan. Inilah alasan lahirnya <strong>GEMBUL (Gemar Berdampak untuk Lingkungan)</strong>.</p>\n\n<h3>Tiga Pilar Utama Creative Eco-Kit GEMBUL</h3>\n<ol>\n    <li><strong>Eco-Games di Sekolah & Kampus:</strong> Permainan simulasi interaktif seperti Dadu Pemilah Sampah, Tebak Kategori Sampah, dan Eco-Quiz yang dimainkan beregu untuk melatih kebiasaan pilah dalam hitungan detik.</li>\n    <li><strong>Toolkit Pemilah Rumah Tangga:</strong> Paket wadah pilah modular dengan kode warna standar nasional yang dirancang estetis dan cocok diletakkan di kamar anak kos maupun dapur minimalis.</li>\n    <li><strong>Eco-Pass Digital:</strong> Kartu paspor hijau yang mencatat setiap aksi baik pilah sampah, ditukar menjadi stempel digital, badge komunitas, dan diskon merchandise lokal.</li>\n</ol>\n\n<h3>Jadwal Roadshow Sekolah & Komunitas</h3>\n<p>Sepanjang tahun 2026, GEMBUL telah menjadwalkan kunjungan ke puluhan SMA, SMK, dan kampus di Bogor dan sekitarnya. Siswa dan relawan yang bergabung dapat melihat langsung live dashboard penimbangan dan menguji langsung purwarupa TrashSmart dan SmartKolecer.</p>','assets/brand.png','Gerakan anak muda peduli lingkungan GEMBUL Trashily','Farhan Alamsyah','Youth Campaign Director GEMBUL',NULL,'gembul, gemar berdampak untuk lingkungan, eco-kit, edukasi sampah anak muda, bogor hijau','Gerakan GEMBUL: Aksi Kreatif Anak Muda Sayang Lingkungan | Trashily','Program GEMBUL Trashily mengedukasi generasi muda memilah sampah melalui game simulasi, Creative Eco-Kit, dan Eco-Pass digital di sekolah-sekolah Bogor.','gerakan gembul, eco-kit sekolah, pemuda peduli sampah, bank sampah generasi muda bogor, edukasi lingkungan',3,95,'published','2026-10-21 11:12:14','2026-10-21 11:12:14');
INSERT INTO `artikel` VALUES (4,'Daftar Harga Sampah Daur Ulang Terbaru di Bogor 2026: Mana yang Paling Menguntungkan?','daftar-harga-sampah-daur-ulang-terbaru-bogor-2026','Edukasi Lingkungan','Update lengkap nilai tukar rupiah dan poin per kilogram sampah plastik, kardus, kaleng, kertas, dan kaca di Bank Sampah Digital Trashily.','<h2>Mengubah Sampah Menjadi Tabungan Nyata</h2>\n<p>Banyak masyarakat yang belum mengetahui nilai ekonomi sampah yang mereka hasilkan setiap hari. Dengan sistem transparansi harga di Trashily, setiap kilogram sampah yang Anda setorkan ditimbang secara digital dan langsung dikonversi menjadi poin yang dapat dicairkan atau ditukar hadiah menarik.</p>\n\n<h3>Daftar Harga & Nilai Poin Terkini</h3>\n<p>Berikut adalah tabel ringkas harga sampah per kilogram di drop point Trashily Bogor per tahun 2026:</p>\n<ul>\n    <li><strong>Kaleng Minuman Aluminium:</strong> Rp 3.500 / kg &rarr; 35 Poin Trashily</li>\n    <li><strong>Botol Plastik PET (Bening):</strong> Rp 2.000 / kg &rarr; 20 Poin Trashily</li>\n    <li><strong>Kardus / Karton Cokelat:</strong> Rp 1.500 / kg &rarr; 15 Poin Trashily</li>\n    <li><strong>Kertas HVS / Buku Bekas:</strong> Rp 1.200 / kg &rarr; 12 Poin Trashily</li>\n    <li><strong>Botol Kaca Utuh / Pecah:</strong> Rp 1.000 / kg &rarr; 10 Poin Trashily</li>\n    <li><strong>Plastik Kresek / Campuran:</strong> Rp 800 / kg &rarr; 8 Poin Trashily</li>\n    <li><strong>Sampah Organik / Kompos:</strong> Rp 500 / kg &rarr; 5 Poin Trashily</li>\n</ul>\n\n<h3>Hadiah & Reward yang Bisa Ditukar</h3>\n<p>Poin yang Anda kumpulkan di akun Trashily tidak memiliki masa kedaluwarsa. Anda dapat menukarkannya kapan saja dengan produk sembako (minyak goreng, beras, gula), perlengkapan sekolah ramah lingkungan, tumbler stainless, hingga saldo e-wallet.</p>','assets/brand.png','Daftar harga sampah daur ulang terbaru Bank Sampah Trashily Bogor','Tim Riset Pasar Trashily','Market Price Analyst',NULL,'harga sampah, harga rongsokan bogor, tukar poin sampah, bank sampah terdekat, jual botol plastik','Daftar Harga Sampah Daur Ulang Bogor 2026 Terbaru | Trashily','Cek daftar harga sampah daur ulang terbaru 2026 di Bogor: plastik PET, kaleng aluminium, kardus, dan kertas. Setor dan dapatkan poin uang tunai di Trashily.','harga sampah daur ulang bogor, harga rongsokan 2026, jual botol plastik per kg, harga kardus bekas, bank sampah bogor harga',4,311,'published','2026-10-21 11:12:14','2026-10-21 11:22:54');
/*!40000 ALTER TABLE `artikel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transaksi_id` int NOT NULL,
  `jenis_sampah_id` int NOT NULL,
  `berat` decimal(10,2) NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT '0.00',
  `subtotal_poin` int NOT NULL,
  `subtotal_uang` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `jenis_sampah_id` (`jenis_sampah_id`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
INSERT INTO `detail_transaksi` VALUES (1,1,2,1.16,5.00,500.00,6,580.00);
INSERT INTO `detail_transaksi` VALUES (2,2,3,4.00,20.00,2000.00,80,8000.00);
INSERT INTO `detail_transaksi` VALUES (12,5,2,0.15,100.00,0.00,15,0.00);
INSERT INTO `detail_transaksi` VALUES (13,5,3,0.08,250.00,0.00,20,0.00);
INSERT INTO `detail_transaksi` VALUES (14,6,2,0.10,100.00,0.00,10,0.00);
INSERT INTO `detail_transaksi` VALUES (15,6,3,0.10,250.00,0.00,15,0.00);
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `everwear_jemput`
--

DROP TABLE IF EXISTS `everwear_jemput`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `everwear_jemput` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_jemput` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int NOT NULL,
  `kategori_pakaian` enum('layak_pakai','recycle','campuran') COLLATE utf8mb4_general_ci NOT NULL,
  `estimasi_jumlah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_penjemputan` text COLLATE utf8mb4_general_ci NOT NULL,
  `catatan_lokasi` text COLLATE utf8mb4_general_ci,
  `tanggal_jemput` date NOT NULL,
  `jam_jemput` time NOT NULL,
  `status` enum('pending','dikonfirmasi','dalam_perjalanan','selesai','dibatalkan') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `catatan_admin` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `jarak_km` decimal(8,2) NOT NULL DEFAULT '0.00',
  `biaya_ongkir` int NOT NULL DEFAULT '0',
  `pcs_layak` int NOT NULL DEFAULT '0',
  `pcs_recycle` int NOT NULL DEFAULT '0',
  `berat_kg` decimal(8,2) NOT NULL DEFAULT '0.00',
  `poin_diperoleh` int NOT NULL DEFAULT '0',
  `admin_id` int DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jemput` (`kode_jemput`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `everwear_jemput`
--

LOCK TABLES `everwear_jemput` WRITE;
/*!40000 ALTER TABLE `everwear_jemput` DISABLE KEYS */;
/*!40000 ALTER TABLE `everwear_jemput` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `everwear_transaksi`
--

DROP TABLE IF EXISTS `everwear_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `everwear_transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int NOT NULL,
  `kategori_pakaian` enum('layak_pakai','recycle','campuran') COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi_pakaian` text COLLATE utf8mb4_general_ci,
  `jumlah_pcs` int DEFAULT '1',
  `berat_kg` decimal(8,2) DEFAULT '0.00',
  `poin_diperoleh` int DEFAULT '0',
  `status` enum('pending','proses','selesai','dibatalkan') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `catatan_admin` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `catatan` text COLLATE utf8mb4_general_ci,
  `jadwal_setor` datetime DEFAULT NULL,
  `pcs_layak` int NOT NULL DEFAULT '0',
  `pcs_recycle` int NOT NULL DEFAULT '0',
  `verified_at` datetime DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `everwear_transaksi`
--

LOCK TABLES `everwear_transaksi` WRITE;
/*!40000 ALTER TABLE `everwear_transaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `everwear_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gembul_jadwal`
--

DROP TABLE IF EXISTS `gembul_jadwal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gembul_jadwal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul_kegiatan` varchar(255) NOT NULL,
  `kategori` enum('Sekolah','Komunitas','Aksi Sungai','Workshop','Riset & Prototipe') DEFAULT 'Sekolah',
  `tanggal` date NOT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `target_peserta` varchar(150) DEFAULT NULL,
  `kuota_peserta` int DEFAULT '0',
  `deskripsi` text NOT NULL,
  `status` enum('rencana','berjalan','selesai','dibatalkan') DEFAULT 'rencana',
  `link_pendaftaran` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gembul_jadwal`
--

LOCK TABLES `gembul_jadwal` WRITE;
/*!40000 ALTER TABLE `gembul_jadwal` DISABLE KEYS */;
INSERT INTO `gembul_jadwal` VALUES (1,'Simulasi Ular Tangga GEMBUL & Estafet Pilah di SMKN 4 Bogor','Sekolah','2026-10-14','08:30:00','11:00:00','Aula & Koridor Kelas X, SMKN 4 Bogor (Jl. Raya Tajur, Muarasari)','Siswa Kelas X & Pengurus OSIS / Ekstrakurikuler Lingkungan',60,'Sesi interaktif permainan Ular Tangga 30 petak dan Estafet Pilah kartu. Siswa diajak berdiskusi tentang penanganan sampah kantin dan sisa makanan MBG serta pembagian Eco-Pass perdana.','rencana','https://wa.me/6281234567890?text=Halo%20GEMBUL%2C%20saya%20ingin%20info%20kegiatan%20SMKN%204','2026-10-11 19:37:09','2026-10-11 19:37:09');
INSERT INTO `gembul_jadwal` VALUES (2,'Aksi Observasi & Bersih Sampah Sungai Ciliwung bersama Alkesa','Aksi Sungai','2026-10-18','07:00:00','10:30:00','Titik Pantau Ciliwung Sempur & Kolaborasi Alkesa Bogor','Relawan Muda, Anggota GEMBUL & Komunitas Alkesa',40,'Aksi susur sungai dan audit kategori sampah riparian bersama Alkesa. Data temuan sampah plastik dan sisa konsumsi akan dijadikan studi kasus nyata pada Kartu Misi GEMBUL.','rencana','https://wa.me/6281234567890?text=Daftar%20Aksi%20Ciliwung%20GEMBUL','2026-10-11 19:37:09','2026-10-11 19:37:09');
INSERT INTO `gembul_jadwal` VALUES (3,'Workshop Pembuatan Magnet Kulkas Pilah dari Kardus Bekas','Workshop','2026-10-23','13:30:00','16:00:00','Ruang Kolaborasi EcoHub Trashily, Jl. Padjadjaran No. 12','Perwakilan Keluarga, Guru, dan Remaja Komunitas',25,'Praktik langsung merakit toolkit rumah: magnet kulkas dari limbah kardus kemasan, lembar magnet bekas kalender, dan kartu pilah mini sebagai pengingat pemilahan harian di dapur.','rencana','https://wa.me/6281234567890?text=Daftar%20Workshop%20Magnet%20Pilah','2026-10-11 19:37:09','2026-10-11 19:37:09');
INSERT INTO `gembul_jadwal` VALUES (4,'Uji Coba Mini Tracker & Eco-Challenge Keluarga 7 Hari','Komunitas','2026-10-09','09:00:00','17:00:00','Lingkungan RT 04 Kelurahan Baranangsiang (Rumah Pilot Anggota)','15 Keluarga Percontohan GEMBUL',15,'Tantangan keluarga sukarela selama 7 hari memisahkan kemasan dan mencatat keberhasilan pada papan Mini Tracker kalender bekas. Evaluasi kendala pemilahan di tingkat dapur.','berjalan','','2026-10-11 19:37:09','2026-10-11 19:37:09');
INSERT INTO `gembul_jadwal` VALUES (5,'Observasi Lapangan Alur Pemrosesan Sampah di Bank Sampah WU','Riset & Prototipe','2026-10-06','10:00:00','13:00:00','Fasilitas Pengelolaan Bank Sampah WU Bogor','Tim Riset & Edukasi GEMBUL',10,'Menyelaraskan kategori kartu edukasi dengan alur penanganan nyata yang berjalan di WU. Memastikan aturan pemilahan di Ular Tangga sesuai dengan fasilitas penerima daur ulang lokal.','selesai','','2026-10-11 19:37:09','2026-10-11 19:37:09');
/*!40000 ALTER TABLE `gembul_jadwal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hadiah`
--

DROP TABLE IF EXISTS `hadiah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hadiah` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `poin_dibutuhkan` int NOT NULL,
  `stok` int DEFAULT '0',
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hadiah`
--

LOCK TABLES `hadiah` WRITE;
/*!40000 ALTER TABLE `hadiah` DISABLE KEYS */;
INSERT INTO `hadiah` VALUES (1,'Pulpen','Pulpen pilot warna hitam',20,100,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (2,'Buku Tulis','Buku tulis 58 lembar',50,50,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (3,'Sabun Mandi','Sabun mandi batang',75,30,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (4,'Minyak Goreng 1L','Minyak goreng kemasan 1 liter',150,20,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (5,'Detergen 500gr','Detergen bubuk 500 gram',100,40,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (6,'Payung','Payung lipat anti hujan',200,15,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (7,'Tas Belanja','Tas belanja ramah lingkungan',120,25,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (8,'Voucher Belanja 10rb','Voucher belanja senilai Rp 10.000',250,10,NULL,1,'2026-08-14 11:00:17');
INSERT INTO `hadiah` VALUES (9,'Pulpen','Pulpen pilot warna hitam',20,100,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (10,'Buku Tulis','Buku tulis 58 lembar',50,50,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (11,'Sabun Mandi','Sabun mandi batang',75,30,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (12,'Minyak Goreng 1L','Minyak goreng kemasan 1 liter',150,20,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (13,'Detergen 500gr','Detergen bubuk 500 gram',100,40,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (14,'Payung','Payung lipat anti hujan',200,15,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (15,'Tas Belanja','Tas belanja ramah lingkungan',120,25,NULL,1,'2026-08-17 10:36:15');
INSERT INTO `hadiah` VALUES (16,'Voucher Belanja 10rb','Voucher belanja senilai Rp 10.000',250,10,NULL,1,'2026-08-17 10:36:15');
/*!40000 ALTER TABLE `hadiah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemput_detail`
--

DROP TABLE IF EXISTS `jemput_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemput_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jemput_id` int NOT NULL,
  `jenis_sampah_id` int NOT NULL,
  `est_berat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jemput_id` (`jemput_id`),
  KEY `jenis_sampah_id` (`jenis_sampah_id`),
  CONSTRAINT `jemput_detail_ibfk_1` FOREIGN KEY (`jemput_id`) REFERENCES `jemput_sampah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jemput_detail_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemput_detail`
--

LOCK TABLES `jemput_detail` WRITE;
/*!40000 ALTER TABLE `jemput_detail` DISABLE KEYS */;
INSERT INTO `jemput_detail` VALUES (1,1,2,1.00);
/*!40000 ALTER TABLE `jemput_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jemput_sampah`
--

DROP TABLE IF EXISTS `jemput_sampah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jemput_sampah` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_jemput` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `transaksi_id` int DEFAULT NULL,
  `alamat_jemput` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jarak_km` decimal(10,2) DEFAULT '0.00',
  `biaya_ongkir` decimal(10,2) DEFAULT '0.00',
  `tarif_per_km` decimal(10,2) DEFAULT '2000.00',
  `jadwal_jemput` datetime NOT NULL,
  `catatan_customer` text COLLATE utf8mb4_unicode_ci,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `status` enum('menunggu','dikonfirmasi','dijemput','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jemput` (`kode_jemput`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  KEY `transaksi_id` (`transaksi_id`),
  CONSTRAINT `jemput_sampah_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jemput_sampah_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jemput_sampah_ibfk_3` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jemput_sampah`
--

LOCK TABLES `jemput_sampah` WRITE;
/*!40000 ALTER TABLE `jemput_sampah` DISABLE KEYS */;
INSERT INTO `jemput_sampah` VALUES (1,'JMP202608140282',5,1,1,'Cikaret',0.00,0.00,2000.00,'2026-08-14 20:50:00','','','selesai','2026-08-14 12:42:05','2026-08-14 12:52:00');
/*!40000 ALTER TABLE `jemput_sampah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_sampah`
--

DROP TABLE IF EXISTS `jenis_sampah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_sampah` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('organik_kering','plastik','kertas','logam','kaca','elektronik','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT '0.00',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_sampah`
--

LOCK TABLES `jenis_sampah` WRITE;
/*!40000 ALTER TABLE `jenis_sampah` DISABLE KEYS */;
INSERT INTO `jenis_sampah` VALUES (2,'Ranting & Kayu Kecil','organik_kering',5.00,500.00,'Ranting pohon dan kayu kecil kering',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (3,'Botol Plastik PET','plastik',20.00,2000.00,'Botol plastik bening bekas minuman',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (4,'Plastik Kresek','plastik',8.00,800.00,'Kantong plastik kresek',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (5,'Kardus & Karton','kertas',15.00,1500.00,'Kardus bekas dan karton',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (6,'Kertas HVS','kertas',12.00,1200.00,'Kertas putih bekas',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (7,'Kaleng Aluminium','logam',35.00,3500.00,'Kaleng minuman atau makanan',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (9,'Botol Kaca','kaca',10.00,1000.00,'Botol dan wadah kaca',1,'2026-08-14 11:00:17');
INSERT INTO `jenis_sampah` VALUES (11,'Daun Kering','organik_kering',5.00,500.00,'Daun-daun kering yang sudah gugur',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (12,'Ranting & Kayu Kecil','organik_kering',5.00,500.00,'Ranting pohon dan kayu kecil kering',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (13,'Botol Plastik PET','plastik',20.00,2000.00,'Botol plastik bening bekas minuman',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (14,'Plastik Kresek','plastik',8.00,800.00,'Kantong plastik kresek',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (15,'Kardus & Karton','kertas',15.00,1500.00,'Kardus bekas dan karton',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (16,'Kertas HVS','kertas',12.00,1200.00,'Kertas putih bekas',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (17,'Kaleng Aluminium','logam',35.00,3500.00,'Kaleng minuman atau makanan',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (18,'Besi & Baja','logam',25.00,2500.00,'Besi dan baja bekas',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (19,'Botol Kaca','kaca',10.00,1000.00,'Botol dan wadah kaca',1,'2026-08-17 10:36:15');
INSERT INTO `jenis_sampah` VALUES (20,'Elektronik Bekas','elektronik',50.00,5000.00,'Perangkat elektronik tidak terpakai',1,'2026-08-17 10:36:15');
/*!40000 ALTER TABLE `jenis_sampah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan`
--

DROP TABLE IF EXISTS `pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan` (
  `kunci` varchar(50) NOT NULL,
  `nilai` text NOT NULL,
  PRIMARY KEY (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan`
--

LOCK TABLES `pengaturan` WRITE;
/*!40000 ALTER TABLE `pengaturan` DISABLE KEYS */;
INSERT INTO `pengaturan` VALUES ('tarif_per_km','2500');
INSERT INTO `pengaturan` VALUES ('trashily_alamat','Gang Kosasih, Cikaret, Bogor Selatan, Kota Batu, Bogor, West Java, 16610, Indonesia');
INSERT INTO `pengaturan` VALUES ('trashily_lat','-6.624408');
INSERT INTO `pengaturan` VALUES ('trashily_lng','106.787575');
/*!40000 ALTER TABLE `pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penukaran`
--

DROP TABLE IF EXISTS `penukaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penukaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_penukaran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `hadiah_id` int NOT NULL,
  `poin_digunakan` int NOT NULL,
  `status` enum('pending','diproses','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_penukaran` (`kode_penukaran`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  KEY `hadiah_id` (`hadiah_id`),
  CONSTRAINT `penukaran_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penukaran_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penukaran_ibfk_3` FOREIGN KEY (`hadiah_id`) REFERENCES `hadiah` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penukaran`
--

LOCK TABLES `penukaran` WRITE;
/*!40000 ALTER TABLE `penukaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `penukaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piru_session_items`
--

DROP TABLE IF EXISTS `piru_session_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `piru_session_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `category` enum('ORGANIK','ANORGANIK') NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `weight_gr` decimal(10,2) NOT NULL,
  `poin` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session` (`session_id`),
  CONSTRAINT `fk_piru_item_session` FOREIGN KEY (`session_id`) REFERENCES `piru_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piru_session_items`
--

LOCK TABLES `piru_session_items` WRITE;
/*!40000 ALTER TABLE `piru_session_items` DISABLE KEYS */;
INSERT INTO `piru_session_items` VALUES (3,2,'ORGANIK',NULL,100.00,10,'2026-10-23 16:27:44');
INSERT INTO `piru_session_items` VALUES (4,2,'ANORGANIK',NULL,100.00,15,'2026-10-23 16:27:44');
INSERT INTO `piru_session_items` VALUES (5,4,'ORGANIK','🍎 Sisa Buah Apel (apple)',125.50,10,'2026-10-24 15:40:42');
INSERT INTO `piru_session_items` VALUES (6,5,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 15:41:32');
INSERT INTO `piru_session_items` VALUES (7,5,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 15:41:33');
INSERT INTO `piru_session_items` VALUES (8,5,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 15:41:35');
INSERT INTO `piru_session_items` VALUES (9,5,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 15:41:36');
INSERT INTO `piru_session_items` VALUES (10,7,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:23:27');
INSERT INTO `piru_session_items` VALUES (11,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:28');
INSERT INTO `piru_session_items` VALUES (12,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:28');
INSERT INTO `piru_session_items` VALUES (13,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:29');
INSERT INTO `piru_session_items` VALUES (14,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:29');
INSERT INTO `piru_session_items` VALUES (15,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:29');
INSERT INTO `piru_session_items` VALUES (16,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:29');
INSERT INTO `piru_session_items` VALUES (17,7,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:23:29');
INSERT INTO `piru_session_items` VALUES (18,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:31');
INSERT INTO `piru_session_items` VALUES (19,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:31');
INSERT INTO `piru_session_items` VALUES (20,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:31');
INSERT INTO `piru_session_items` VALUES (21,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:32');
INSERT INTO `piru_session_items` VALUES (22,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:32');
INSERT INTO `piru_session_items` VALUES (23,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:32');
INSERT INTO `piru_session_items` VALUES (24,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:32');
INSERT INTO `piru_session_items` VALUES (25,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:32');
INSERT INTO `piru_session_items` VALUES (26,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:33');
INSERT INTO `piru_session_items` VALUES (27,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:33');
INSERT INTO `piru_session_items` VALUES (28,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:33');
INSERT INTO `piru_session_items` VALUES (29,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:33');
INSERT INTO `piru_session_items` VALUES (30,7,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:23:33');
INSERT INTO `piru_session_items` VALUES (31,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:35');
INSERT INTO `piru_session_items` VALUES (32,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:35');
INSERT INTO `piru_session_items` VALUES (33,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:35');
INSERT INTO `piru_session_items` VALUES (34,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:35');
INSERT INTO `piru_session_items` VALUES (35,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:35');
INSERT INTO `piru_session_items` VALUES (36,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:36');
INSERT INTO `piru_session_items` VALUES (37,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:36');
INSERT INTO `piru_session_items` VALUES (38,7,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:23:36');
INSERT INTO `piru_session_items` VALUES (39,8,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:25:57');
INSERT INTO `piru_session_items` VALUES (40,8,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:40:09');
INSERT INTO `piru_session_items` VALUES (41,9,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:48:54');
INSERT INTO `piru_session_items` VALUES (42,9,'ANORGANIK','📦 Kardus Karton (cardboard)',150.00,15,'2026-10-24 16:53:35');
INSERT INTO `piru_session_items` VALUES (43,9,'ORGANIK','🍌 Kulit Pisang (banana)',100.00,10,'2026-10-24 16:53:37');
INSERT INTO `piru_session_items` VALUES (44,9,'ORGANIK','🍎 Sisa Buah Apel (apple)',120.00,10,'2026-10-24 16:53:38');
INSERT INTO `piru_session_items` VALUES (45,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:40');
INSERT INTO `piru_session_items` VALUES (46,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:41');
INSERT INTO `piru_session_items` VALUES (47,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:42');
INSERT INTO `piru_session_items` VALUES (48,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:43');
INSERT INTO `piru_session_items` VALUES (49,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:43');
INSERT INTO `piru_session_items` VALUES (50,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:43');
INSERT INTO `piru_session_items` VALUES (51,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:43');
INSERT INTO `piru_session_items` VALUES (52,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:43');
INSERT INTO `piru_session_items` VALUES (53,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:44');
INSERT INTO `piru_session_items` VALUES (54,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:44');
INSERT INTO `piru_session_items` VALUES (55,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:44');
INSERT INTO `piru_session_items` VALUES (56,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:44');
INSERT INTO `piru_session_items` VALUES (57,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:44');
INSERT INTO `piru_session_items` VALUES (58,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:45');
INSERT INTO `piru_session_items` VALUES (59,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:45');
INSERT INTO `piru_session_items` VALUES (60,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:45');
INSERT INTO `piru_session_items` VALUES (61,9,'ANORGANIK','🍾 Botol Plastik (bottle)',85.00,15,'2026-10-24 16:53:46');
/*!40000 ALTER TABLE `piru_session_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `piru_sessions`
--

DROP TABLE IF EXISTS `piru_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `piru_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_token` varchar(64) NOT NULL,
  `device_kode` varchar(50) NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `total_organik_gr` decimal(10,2) DEFAULT '0.00',
  `total_anorganik_gr` decimal(10,2) DEFAULT '0.00',
  `total_berat_gr` decimal(10,2) DEFAULT '0.00',
  `total_poin` int DEFAULT '0',
  `item_count` int DEFAULT '0',
  `transaksi_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `idx_device_status` (`device_kode`,`status`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_piru_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `piru_sessions`
--

LOCK TABLES `piru_sessions` WRITE;
/*!40000 ALTER TABLE `piru_sessions` DISABLE KEYS */;
INSERT INTO `piru_sessions` VALUES (2,'6e455667ade9b6cae0ef516b0710a2e7','PIRU-DEV-001',2,'completed',100.00,100.00,200.00,25,2,6,'2026-10-23 16:27:44','2026-10-23 16:27:44','2026-10-23 16:27:44');
INSERT INTO `piru_sessions` VALUES (3,'8cd1d6d85413b0cb9fa04ca31dab94df','PIRU-DEV-001',2,'completed',0.00,0.00,0.00,0,0,NULL,'2026-10-24 13:46:56','2026-10-24 13:47:20','2026-10-24 13:47:20');
INSERT INTO `piru_sessions` VALUES (4,'3ecff93077b38477568056be053a4a5b','PIRU-DEV-001',2,'cancelled',125.50,0.00,125.50,10,1,NULL,'2026-10-24 15:35:52','2026-10-24 15:41:23','2026-10-24 15:41:23');
INSERT INTO `piru_sessions` VALUES (5,'471f8bf08b5b9cda6efab253ca3974b4','PIRU-DEV-001',2,'cancelled',220.00,235.00,455.00,50,4,NULL,'2026-10-24 15:41:28','2026-10-24 15:41:43','2026-10-24 15:41:43');
INSERT INTO `piru_sessions` VALUES (6,'bb04ef87d355b94d427154572abd8458','PIRU-DEV-001',2,'cancelled',0.00,0.00,0.00,0,0,NULL,'2026-10-24 15:44:13',NULL,'2026-10-24 16:23:18');
INSERT INTO `piru_sessions` VALUES (7,'0a64d52b355399dc3ed8341d753025d6','PIRU-DEV-001',2,'cancelled',2140.00,1285.00,3425.00,335,29,NULL,'2026-10-24 16:23:18','2026-10-24 16:23:43','2026-10-24 16:23:43');
INSERT INTO `piru_sessions` VALUES (8,'79170af67f94a884c7520944e383ae1d','PIRU-DEV-001',2,'cancelled',120.00,85.00,205.00,25,2,NULL,'2026-10-24 16:23:45','2026-10-24 16:40:25','2026-10-24 16:40:25');
INSERT INTO `piru_sessions` VALUES (9,'bf7eeef300ce08c9e01b6dfa5c013491','PIRU-DEV-001',2,'cancelled',320.00,1595.00,1915.00,300,21,NULL,'2026-10-24 16:48:22','2026-10-24 16:53:50','2026-10-24 16:53:50');
/*!40000 ALTER TABLE `piru_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int DEFAULT NULL,
  `nama_non_member` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` int DEFAULT NULL,
  `total_poin` int DEFAULT '0',
  `total_uang` decimal(12,2) DEFAULT '0.00',
  `total_berat` decimal(10,2) DEFAULT '0.00',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'selesai',
  `tipe_transaksi` enum('langsung','setor_sendiri') COLLATE utf8mb4_unicode_ci DEFAULT 'langsung',
  `jadwal_setor` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
INSERT INTO `transaksi` VALUES (1,'TRX202608141111',5,NULL,1,6,580.00,1.16,'',NULL,'selesai','langsung',NULL,'2026-08-14 12:52:00');
INSERT INTO `transaksi` VALUES (2,'TRX202608141264',5,NULL,1,80,8000.00,4.00,'',NULL,'selesai','langsung',NULL,'2026-08-14 13:06:38');
INSERT INTO `transaksi` VALUES (5,'PIRU202610230894',2,NULL,NULL,35,0.00,0.23,'Pemilahan Cerdas via PIRU Smart Bin (PIRU-DEV-001) - 2 item',NULL,'selesai','langsung',NULL,'2026-10-23 04:01:03');
INSERT INTO `transaksi` VALUES (6,'PIRU202610238483',2,NULL,NULL,25,0.00,0.20,'Pemilahan Cerdas via PIRU Smart Bin (PIRU-DEV-001) - 2 item',NULL,'selesai','langsung',NULL,'2026-10-23 09:27:44');
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trashsmart_devices`
--

DROP TABLE IF EXISTS `trashsmart_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trashsmart_devices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_perangkat` varchar(50) NOT NULL,
  `nama_perangkat` varchar(100) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('online','warning','offline','maintenance') DEFAULT 'online',
  `kapasitas_organik` int DEFAULT '0',
  `kapasitas_anorganik` int DEFAULT '0',
  `baterai` int DEFAULT '100',
  `suhu_celsius` decimal(4,1) DEFAULT '28.0',
  `firmware_version` varchar(20) DEFAULT 'v2.4.1',
  `total_pilah_count` int DEFAULT '0',
  `total_berat_kg` decimal(10,2) DEFAULT '0.00',
  `voice_nudge_count` int DEFAULT '0',
  `terakhir_dikosongkan` datetime DEFAULT NULL,
  `last_ping` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_perangkat` (`kode_perangkat`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trashsmart_devices`
--

LOCK TABLES `trashsmart_devices` WRITE;
/*!40000 ALTER TABLE `trashsmart_devices` DISABLE KEYS */;
INSERT INTO `trashsmart_devices` VALUES (1,'TS-BGR-001','TrashSmart Kantin SMA 1 Bogor','Kantin Sekolah Hijau, SMAN 1 Bogor (Area Depan Gazebo)',-6.59714700,106.79947800,'online',48,62,94,27.8,'v2.4.2',142,18.75,19,'2026-10-09 08:30:00','2026-10-10 10:05:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (2,'TS-BGR-002','TrashSmart Taman Sempur EcoHub','Taman Sempur Bogor, Dekat Lapangan Basket & Jalur Jogging',-6.59123000,106.79785000,'warning',65,92,82,30.2,'v2.4.2',285,34.60,43,'2026-10-08 09:00:00','2026-10-10 10:07:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (3,'TS-BGR-003','TrashSmart Stasiun Bogor Hall Barat','Stasiun Bogor, Pintu Masuk Tap-In Hall Barat',-6.59543000,106.78912000,'online',38,45,100,26.5,'v2.4.1',360,42.10,31,'2026-10-10 06:00:00','2026-10-10 10:08:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (4,'TS-BGR-004','TrashSmart Botani Square Food Terrace','Mall Botani Square Lt. 2, Koridor Food Terrace & Atrium',-6.60145000,106.80562000,'online',70,58,91,25.4,'v2.4.2',198,26.80,24,'2026-10-10 07:15:00','2026-10-10 10:03:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (5,'TS-BGR-005','TrashSmart Kantor DLH Kota Bogor','Lobi Utama Kantor Dinas Lingkungan Hidup, Jl. Paledang',-6.60381000,106.79412000,'online',22,31,97,26.8,'v2.4.2',86,9.40,7,'2026-10-10 08:00:00','2026-10-10 10:06:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (6,'TS-BGR-006','TrashSmart Kebun Raya Pintu 3','Kebun Raya Bogor, Area Tourist Information & Pintu 3',-6.60251000,106.80121000,'maintenance',15,20,45,29.1,'v2.4.0',112,14.20,15,'2026-10-07 10:00:00','2026-10-10 08:08:52','2026-10-10 10:08:52');
INSERT INTO `trashsmart_devices` VALUES (7,'PIRU-DEV-001','PIRU Smart Bin #001','Area Kantin Utama / EcoHub',-6.59714700,106.79947800,'online',100,100,99,28.7,'v2.0.0-YOLO',61,6.66,0,NULL,'2026-10-24 16:53:46','2026-10-23 10:55:42');
/*!40000 ALTER TABLE `trashsmart_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trashsmart_logs`
--

DROP TABLE IF EXISTS `trashsmart_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trashsmart_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `device_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `kategori_sampah` enum('organik','anorganik','residu','sistem') DEFAULT 'organik',
  `status_pilah` enum('benar','keliru','peringatan','sistem') DEFAULT 'benar',
  `berat_kg` decimal(8,3) DEFAULT '0.000',
  `poin` int DEFAULT '0',
  `pesan_suara` varchar(255) DEFAULT NULL,
  `user_identifier` varchar(100) DEFAULT 'Guest / Siswa',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_tanggal` (`device_id`,`tanggal`),
  CONSTRAINT `fk_trashsmart_device` FOREIGN KEY (`device_id`) REFERENCES `trashsmart_devices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=167 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trashsmart_logs`
--

LOCK TABLES `trashsmart_logs` WRITE;
/*!40000 ALTER TABLE `trashsmart_logs` DISABLE KEYS */;
INSERT INTO `trashsmart_logs` VALUES (1,1,'2026-10-10','07:15:20','organik','benar',0.600,24,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Komuter KRL (NFC Tap)','2026-10-10 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (2,1,'2026-10-10','15:10:05','anorganik','benar',0.750,75,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siswa SMA 1 (Kartu RFID)','2026-10-10 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (3,1,'2026-10-10','15:10:05','organik','benar',0.170,6,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Staf Tenant Botani Square','2026-10-10 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (4,1,'2026-10-10','17:30:50','anorganik','benar',0.830,83,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Komuter KRL (NFC Tap)','2026-10-10 17:30:50');
INSERT INTO `trashsmart_logs` VALUES (5,1,'2026-10-10','09:20:15','anorganik','keliru',0.430,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siswa SMA 1 (Kartu RFID)','2026-10-10 09:20:15');
INSERT INTO `trashsmart_logs` VALUES (6,1,'2026-10-10','18:15:25','organik','keliru',0.120,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Budi Santoso (Member)','2026-10-10 18:15:25');
INSERT INTO `trashsmart_logs` VALUES (7,2,'2026-10-10','15:10:05','anorganik','benar',0.470,47,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Pengunjung Umum','2026-10-10 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (8,2,'2026-10-10','07:15:20','sistem','peringatan',0.000,0,'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.','Sistem Otomatis IoT','2026-10-10 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (9,2,'2026-10-10','09:20:15','sistem','peringatan',0.000,0,'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.','Sistem Otomatis IoT','2026-10-10 09:20:15');
INSERT INTO `trashsmart_logs` VALUES (10,2,'2026-10-10','10:35:19','anorganik','keliru',0.460,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Rizky Pratama (App Trashily)','2026-10-10 10:35:19');
INSERT INTO `trashsmart_logs` VALUES (11,2,'2026-10-10','17:30:50','anorganik','benar',0.290,28,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Dewi Lestari (Member)','2026-10-10 17:30:50');
INSERT INTO `trashsmart_logs` VALUES (12,2,'2026-10-10','12:20:10','organik','keliru',0.260,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siswa SMKN 1','2026-10-10 12:20:10');
INSERT INTO `trashsmart_logs` VALUES (13,3,'2026-10-10','16:45:12','anorganik','benar',0.630,63,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Dewi Lestari (Member)','2026-10-10 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (14,3,'2026-10-10','12:45:55','organik','benar',0.360,14,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Budi Santoso (Member)','2026-10-10 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (15,3,'2026-10-10','17:30:50','anorganik','benar',0.600,60,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMKN 1','2026-10-10 17:30:50');
INSERT INTO `trashsmart_logs` VALUES (16,3,'2026-10-10','10:05:42','organik','benar',0.380,15,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siti Rahma (App Trashily)','2026-10-10 10:05:42');
INSERT INTO `trashsmart_logs` VALUES (17,4,'2026-10-10','11:58:30','anorganik','benar',0.690,69,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Staf Tenant Botani Square','2026-10-10 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (18,4,'2026-10-10','19:00:44','anorganik','benar',0.700,70,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Rizky Pratama (App Trashily)','2026-10-10 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (19,4,'2026-10-10','07:15:20','organik','keliru',0.370,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Rizky Pratama (App Trashily)','2026-10-10 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (20,4,'2026-10-10','11:58:30','anorganik','keliru',0.350,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Budi Santoso (Member)','2026-10-10 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (21,4,'2026-10-10','16:05:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-10 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (22,4,'2026-10-10','12:20:10','organik','benar',0.490,19,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siswa SMKN 1','2026-10-10 12:20:10');
INSERT INTO `trashsmart_logs` VALUES (23,4,'2026-10-10','10:35:19','anorganik','benar',0.310,31,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siswa SMA 1 (Kartu RFID)','2026-10-10 10:35:19');
INSERT INTO `trashsmart_logs` VALUES (24,5,'2026-10-10','16:45:12','organik','benar',0.620,24,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Pengunjung Umum','2026-10-10 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (25,5,'2026-10-10','15:10:05','anorganik','benar',0.650,65,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siti Rahma (App Trashily)','2026-10-10 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (26,5,'2026-10-10','12:45:55','organik','keliru',0.120,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Staf Tenant Botani Square','2026-10-10 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (27,5,'2026-10-10','11:58:30','organik','benar',0.340,13,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Komuter KRL (NFC Tap)','2026-10-10 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (28,6,'2026-10-10','11:12:05','anorganik','benar',0.610,61,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMA 1 (Kartu RFID)','2026-10-10 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (29,6,'2026-10-10','11:58:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-10 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (30,6,'2026-10-10','12:45:55','anorganik','benar',0.210,21,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMA 1 (Kartu RFID)','2026-10-10 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (31,6,'2026-10-10','10:05:42','anorganik','benar',0.460,46,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Komuter KRL (NFC Tap)','2026-10-10 10:05:42');
INSERT INTO `trashsmart_logs` VALUES (32,6,'2026-10-10','19:00:44','anorganik','benar',0.640,64,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Dewi Lestari (Member)','2026-10-10 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (33,6,'2026-10-10','11:12:05','organik','benar',0.420,16,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siti Rahma (App Trashily)','2026-10-10 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (34,1,'2026-10-09','13:10:40','anorganik','benar',0.800,80,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Dewi Lestari (Member)','2026-10-09 13:10:40');
INSERT INTO `trashsmart_logs` VALUES (35,1,'2026-10-09','07:15:20','anorganik','keliru',0.280,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siswa SMA 1 (Kartu RFID)','2026-10-09 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (36,1,'2026-10-09','08:02:11','organik','keliru',0.160,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Staf Tenant Botani Square','2026-10-09 08:02:11');
INSERT INTO `trashsmart_logs` VALUES (37,1,'2026-10-09','08:45:33','organik','keliru',0.310,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Rizky Pratama (App Trashily)','2026-10-09 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (38,1,'2026-10-09','15:10:05','anorganik','benar',0.700,70,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siswa SMKN 1','2026-10-09 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (39,2,'2026-10-09','19:00:44','anorganik','keliru',0.210,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Dewi Lestari (Member)','2026-10-09 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (40,2,'2026-10-09','14:25:18','organik','benar',0.790,31,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siswa SMA 1 (Kartu RFID)','2026-10-09 14:25:18');
INSERT INTO `trashsmart_logs` VALUES (41,2,'2026-10-09','19:00:44','anorganik','keliru',0.140,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siti Rahma (App Trashily)','2026-10-09 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (42,2,'2026-10-09','12:45:55','anorganik','benar',0.820,82,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Staf Tenant Botani Square','2026-10-09 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (43,3,'2026-10-09','18:15:25','organik','benar',0.750,30,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siti Rahma (App Trashily)','2026-10-09 18:15:25');
INSERT INTO `trashsmart_logs` VALUES (44,3,'2026-10-09','16:45:12','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-09 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (45,3,'2026-10-09','11:58:30','anorganik','keliru',0.260,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Pengunjung Umum','2026-10-09 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (46,3,'2026-10-09','13:50:22','organik','benar',0.710,28,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siswa SMA 1 (Kartu RFID)','2026-10-09 13:50:22');
INSERT INTO `trashsmart_logs` VALUES (47,3,'2026-10-09','15:10:05','organik','benar',0.660,26,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Pelari Pagi Taman Sempur','2026-10-09 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (48,3,'2026-10-09','16:05:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-09 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (49,3,'2026-10-09','16:05:30','anorganik','benar',0.260,26,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Pelari Pagi Taman Sempur','2026-10-09 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (50,4,'2026-10-09','08:45:33','anorganik','benar',0.170,17,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siswa SMA 1 (Kartu RFID)','2026-10-09 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (51,4,'2026-10-09','11:12:05','anorganik','keliru',0.240,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Rizky Pratama (App Trashily)','2026-10-09 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (52,4,'2026-10-09','10:05:42','organik','keliru',0.310,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siswa SMA 1 (Kartu RFID)','2026-10-09 10:05:42');
INSERT INTO `trashsmart_logs` VALUES (53,4,'2026-10-09','13:50:22','organik','benar',0.560,22,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Pelari Pagi Taman Sempur','2026-10-09 13:50:22');
INSERT INTO `trashsmart_logs` VALUES (54,4,'2026-10-09','09:20:15','anorganik','keliru',0.360,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Siswa SMA 1 (Kartu RFID)','2026-10-09 09:20:15');
INSERT INTO `trashsmart_logs` VALUES (55,4,'2026-10-09','09:20:15','anorganik','benar',0.380,38,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siti Rahma (App Trashily)','2026-10-09 09:20:15');
INSERT INTO `trashsmart_logs` VALUES (56,4,'2026-10-09','12:45:55','organik','benar',0.750,30,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siti Rahma (App Trashily)','2026-10-09 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (57,5,'2026-10-09','14:25:18','organik','keliru',0.200,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Dewi Lestari (Member)','2026-10-09 14:25:18');
INSERT INTO `trashsmart_logs` VALUES (58,5,'2026-10-09','13:10:40','organik','benar',0.380,15,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siswa SMKN 1','2026-10-09 13:10:40');
INSERT INTO `trashsmart_logs` VALUES (59,5,'2026-10-09','08:45:33','organik','benar',0.320,12,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Dewi Lestari (Member)','2026-10-09 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (60,5,'2026-10-09','19:00:44','anorganik','keliru',0.310,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Rizky Pratama (App Trashily)','2026-10-09 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (61,5,'2026-10-09','12:20:10','anorganik','benar',0.500,50,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siti Rahma (App Trashily)','2026-10-09 12:20:10');
INSERT INTO `trashsmart_logs` VALUES (62,5,'2026-10-09','12:45:55','organik','benar',0.380,15,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Siti Rahma (App Trashily)','2026-10-09 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (63,6,'2026-10-09','13:50:22','organik','keliru',0.480,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Komuter KRL (NFC Tap)','2026-10-09 13:50:22');
INSERT INTO `trashsmart_logs` VALUES (64,6,'2026-10-09','11:12:05','organik','keliru',0.400,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Staf Tenant Botani Square','2026-10-09 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (65,6,'2026-10-09','19:00:44','organik','keliru',0.440,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Staf Tenant Botani Square','2026-10-09 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (66,6,'2026-10-09','13:50:22','organik','benar',0.720,28,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siti Rahma (App Trashily)','2026-10-09 13:50:22');
INSERT INTO `trashsmart_logs` VALUES (67,6,'2026-10-09','11:12:05','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-09 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (68,6,'2026-10-09','13:10:40','anorganik','keliru',0.110,0,'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!','Pelari Pagi Taman Sempur','2026-10-09 13:10:40');
INSERT INTO `trashsmart_logs` VALUES (69,6,'2026-10-09','12:45:55','anorganik','benar',0.850,85,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Staf Tenant Botani Square','2026-10-09 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (70,1,'2026-10-08','08:02:11','organik','benar',0.500,20,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Staf Tenant Botani Square','2026-10-08 08:02:11');
INSERT INTO `trashsmart_logs` VALUES (71,1,'2026-10-08','16:45:12','organik','keliru',0.480,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Siti Rahma (App Trashily)','2026-10-08 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (72,1,'2026-10-08','16:05:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (73,1,'2026-10-08','15:10:05','organik','benar',0.840,33,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Komuter KRL (NFC Tap)','2026-10-08 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (74,1,'2026-10-08','11:58:30','organik','benar',0.710,28,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMKN 1','2026-10-08 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (75,1,'2026-10-08','16:45:12','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (76,1,'2026-10-08','07:15:20','organik','benar',0.750,30,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Budi Santoso (Member)','2026-10-08 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (77,2,'2026-10-08','15:10:05','organik','keliru',0.350,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Pelari Pagi Taman Sempur','2026-10-08 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (78,2,'2026-10-08','11:58:30','anorganik','benar',0.160,16,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siti Rahma (App Trashily)','2026-10-08 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (79,2,'2026-10-08','08:02:11','sistem','peringatan',0.000,0,'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.','Sistem Otomatis IoT','2026-10-08 08:02:11');
INSERT INTO `trashsmart_logs` VALUES (80,2,'2026-10-08','18:15:25','organik','benar',0.440,17,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siti Rahma (App Trashily)','2026-10-08 18:15:25');
INSERT INTO `trashsmart_logs` VALUES (81,3,'2026-10-08','10:05:42','anorganik','benar',0.760,76,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Pelari Pagi Taman Sempur','2026-10-08 10:05:42');
INSERT INTO `trashsmart_logs` VALUES (82,3,'2026-10-08','16:05:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (83,3,'2026-10-08','16:05:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 16:05:30');
INSERT INTO `trashsmart_logs` VALUES (84,3,'2026-10-08','11:58:30','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 11:58:30');
INSERT INTO `trashsmart_logs` VALUES (85,3,'2026-10-08','08:02:11','anorganik','benar',0.340,34,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Rizky Pratama (App Trashily)','2026-10-08 08:02:11');
INSERT INTO `trashsmart_logs` VALUES (86,3,'2026-10-08','19:00:44','organik','benar',0.460,18,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Komuter KRL (NFC Tap)','2026-10-08 19:00:44');
INSERT INTO `trashsmart_logs` VALUES (87,4,'2026-10-08','07:15:20','organik','benar',0.830,33,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Pengunjung Umum','2026-10-08 07:15:20');
INSERT INTO `trashsmart_logs` VALUES (88,4,'2026-10-08','08:45:33','anorganik','benar',0.660,66,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Dewi Lestari (Member)','2026-10-08 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (89,4,'2026-10-08','08:45:33','organik','benar',0.700,28,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Staf Tenant Botani Square','2026-10-08 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (90,4,'2026-10-08','10:35:19','organik','benar',0.590,23,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Komuter KRL (NFC Tap)','2026-10-08 10:35:19');
INSERT INTO `trashsmart_logs` VALUES (91,4,'2026-10-08','11:12:05','anorganik','benar',0.230,23,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siswa SMKN 1','2026-10-08 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (92,5,'2026-10-08','18:15:25','organik','benar',0.670,26,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Siswa SMKN 1','2026-10-08 18:15:25');
INSERT INTO `trashsmart_logs` VALUES (93,5,'2026-10-08','13:50:22','anorganik','benar',0.440,44,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Dewi Lestari (Member)','2026-10-08 13:50:22');
INSERT INTO `trashsmart_logs` VALUES (94,5,'2026-10-08','09:20:15','organik','benar',0.630,25,'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.','Staf Tenant Botani Square','2026-10-08 09:20:15');
INSERT INTO `trashsmart_logs` VALUES (95,5,'2026-10-08','15:10:05','organik','keliru',0.200,0,'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.','Pelari Pagi Taman Sempur','2026-10-08 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (96,5,'2026-10-08','08:45:33','anorganik','benar',0.770,77,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Pengunjung Umum','2026-10-08 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (97,5,'2026-10-08','15:10:05','organik','keliru',0.340,0,'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!','Rizky Pratama (App Trashily)','2026-10-08 15:10:05');
INSERT INTO `trashsmart_logs` VALUES (98,5,'2026-10-08','12:45:55','organik','benar',0.320,12,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Budi Santoso (Member)','2026-10-08 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (99,6,'2026-10-08','12:45:55','organik','benar',0.850,34,'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.','Rizky Pratama (App Trashily)','2026-10-08 12:45:55');
INSERT INTO `trashsmart_logs` VALUES (100,6,'2026-10-08','11:12:05','sistem','sistem',0.000,0,'Status sistem normal. Baki inspeksi siap digunakan.','Sistem Otomatis IoT','2026-10-08 11:12:05');
INSERT INTO `trashsmart_logs` VALUES (101,6,'2026-10-08','13:10:40','anorganik','benar',0.770,77,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Komuter KRL (NFC Tap)','2026-10-08 13:10:40');
INSERT INTO `trashsmart_logs` VALUES (102,6,'2026-10-08','08:45:33','anorganik','benar',0.630,63,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Komuter KRL (NFC Tap)','2026-10-08 08:45:33');
INSERT INTO `trashsmart_logs` VALUES (103,6,'2026-10-08','16:45:12','anorganik','benar',0.830,83,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMKN 1','2026-10-08 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (104,6,'2026-10-08','16:45:12','anorganik','benar',0.390,39,'Terima kasih! Sampah organikmu masuk wadah yang tepat.','Siswa SMA 1 (Kartu RFID)','2026-10-08 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (105,6,'2026-10-08','16:45:12','anorganik','benar',0.240,24,'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!','Siswa SMKN 1','2026-10-08 16:45:12');
INSERT INTO `trashsmart_logs` VALUES (106,7,'2026-10-23','11:00:47','organik','benar',0.150,15,NULL,'Budi Santoso','2026-10-23 11:00:47');
INSERT INTO `trashsmart_logs` VALUES (107,7,'2026-10-23','11:00:48','anorganik','benar',0.080,20,NULL,'Budi Santoso','2026-10-23 11:00:48');
INSERT INTO `trashsmart_logs` VALUES (108,7,'2026-10-23','16:27:44','organik','benar',0.100,10,NULL,'Budi Santoso','2026-10-23 16:27:44');
INSERT INTO `trashsmart_logs` VALUES (109,7,'2026-10-23','16:27:44','anorganik','benar',0.100,15,NULL,'Budi Santoso','2026-10-23 16:27:44');
INSERT INTO `trashsmart_logs` VALUES (110,7,'2026-10-24','15:40:42','organik','benar',0.126,10,'AI YOLO memvalidasi \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 125.5g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 15:40:42');
INSERT INTO `trashsmart_logs` VALUES (111,7,'2026-10-24','15:41:32','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 15:41:32');
INSERT INTO `trashsmart_logs` VALUES (112,7,'2026-10-24','15:41:33','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 15:41:33');
INSERT INTO `trashsmart_logs` VALUES (113,7,'2026-10-24','15:41:35','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 15:41:35');
INSERT INTO `trashsmart_logs` VALUES (114,7,'2026-10-24','15:41:36','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 15:41:36');
INSERT INTO `trashsmart_logs` VALUES (115,7,'2026-10-24','16:23:27','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:27');
INSERT INTO `trashsmart_logs` VALUES (116,7,'2026-10-24','16:23:28','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:28');
INSERT INTO `trashsmart_logs` VALUES (117,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (118,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (119,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (120,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (121,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (122,7,'2026-10-24','16:23:29','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:29');
INSERT INTO `trashsmart_logs` VALUES (123,7,'2026-10-24','16:23:31','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:31');
INSERT INTO `trashsmart_logs` VALUES (124,7,'2026-10-24','16:23:31','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:31');
INSERT INTO `trashsmart_logs` VALUES (125,7,'2026-10-24','16:23:31','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:31');
INSERT INTO `trashsmart_logs` VALUES (126,7,'2026-10-24','16:23:32','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:32');
INSERT INTO `trashsmart_logs` VALUES (127,7,'2026-10-24','16:23:32','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:32');
INSERT INTO `trashsmart_logs` VALUES (128,7,'2026-10-24','16:23:32','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:32');
INSERT INTO `trashsmart_logs` VALUES (129,7,'2026-10-24','16:23:32','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:32');
INSERT INTO `trashsmart_logs` VALUES (130,7,'2026-10-24','16:23:33','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:33');
INSERT INTO `trashsmart_logs` VALUES (131,7,'2026-10-24','16:23:33','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:33');
INSERT INTO `trashsmart_logs` VALUES (132,7,'2026-10-24','16:23:33','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:33');
INSERT INTO `trashsmart_logs` VALUES (133,7,'2026-10-24','16:23:33','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:33');
INSERT INTO `trashsmart_logs` VALUES (134,7,'2026-10-24','16:23:33','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:33');
INSERT INTO `trashsmart_logs` VALUES (135,7,'2026-10-24','16:23:34','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:34');
INSERT INTO `trashsmart_logs` VALUES (136,7,'2026-10-24','16:23:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:35');
INSERT INTO `trashsmart_logs` VALUES (137,7,'2026-10-24','16:23:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:35');
INSERT INTO `trashsmart_logs` VALUES (138,7,'2026-10-24','16:23:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:35');
INSERT INTO `trashsmart_logs` VALUES (139,7,'2026-10-24','16:23:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:35');
INSERT INTO `trashsmart_logs` VALUES (140,7,'2026-10-24','16:23:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:35');
INSERT INTO `trashsmart_logs` VALUES (141,7,'2026-10-24','16:23:36','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:36');
INSERT INTO `trashsmart_logs` VALUES (142,7,'2026-10-24','16:23:36','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:36');
INSERT INTO `trashsmart_logs` VALUES (143,7,'2026-10-24','16:23:36','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:23:36');
INSERT INTO `trashsmart_logs` VALUES (144,7,'2026-10-24','16:25:57','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:25:57');
INSERT INTO `trashsmart_logs` VALUES (145,7,'2026-10-24','16:40:09','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:40:09');
INSERT INTO `trashsmart_logs` VALUES (146,7,'2026-10-24','16:48:54','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:48:54');
INSERT INTO `trashsmart_logs` VALUES (147,7,'2026-10-24','16:53:35','anorganik','benar',0.150,15,'Simulasi Sensor IoT: \'📦 Kardus Karton (cardboard)\' (ANORGANIK) seberat 150g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:35');
INSERT INTO `trashsmart_logs` VALUES (148,7,'2026-10-24','16:53:37','organik','benar',0.100,10,'Simulasi Sensor IoT: \'🍌 Kulit Pisang (banana)\' (ORGANIK) seberat 100g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:37');
INSERT INTO `trashsmart_logs` VALUES (149,7,'2026-10-24','16:53:38','organik','benar',0.120,10,'Simulasi Sensor IoT: \'🍎 Sisa Buah Apel (apple)\' (ORGANIK) seberat 120g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:38');
INSERT INTO `trashsmart_logs` VALUES (150,7,'2026-10-24','16:53:40','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:40');
INSERT INTO `trashsmart_logs` VALUES (151,7,'2026-10-24','16:53:41','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:41');
INSERT INTO `trashsmart_logs` VALUES (152,7,'2026-10-24','16:53:42','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:42');
INSERT INTO `trashsmart_logs` VALUES (153,7,'2026-10-24','16:53:43','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:43');
INSERT INTO `trashsmart_logs` VALUES (154,7,'2026-10-24','16:53:43','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:43');
INSERT INTO `trashsmart_logs` VALUES (155,7,'2026-10-24','16:53:43','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:43');
INSERT INTO `trashsmart_logs` VALUES (156,7,'2026-10-24','16:53:43','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:43');
INSERT INTO `trashsmart_logs` VALUES (157,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (158,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (159,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (160,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (161,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (162,7,'2026-10-24','16:53:44','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:44');
INSERT INTO `trashsmart_logs` VALUES (163,7,'2026-10-24','16:53:45','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:45');
INSERT INTO `trashsmart_logs` VALUES (164,7,'2026-10-24','16:53:45','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:45');
INSERT INTO `trashsmart_logs` VALUES (165,7,'2026-10-24','16:53:45','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:45');
INSERT INTO `trashsmart_logs` VALUES (166,7,'2026-10-24','16:53:46','anorganik','benar',0.085,15,'Simulasi Sensor IoT: \'🍾 Botol Plastik (bottle)\' (ANORGANIK) seberat 85g pada PIRU-DEV-001.','Budi Santoso','2026-10-24 16:53:46');
/*!40000 ALTER TABLE `trashsmart_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ulasan`
--

DROP TABLE IF EXISTS `ulasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ulasan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ulasan`
--

LOCK TABLES `ulasan` WRITE;
/*!40000 ALTER TABLE `ulasan` DISABLE KEYS */;
INSERT INTO `ulasan` VALUES (1,5,'Siti Rahmawati',5,'Dulu sampah bekas cuma dibuang ke tempat sampah. Sekarang kumpulin, panggil kurir Trashily, langsung tukar poin jadi pulpen & alat tulis favorit!','approved',NULL,1,'2026-08-17 12:28:32','2026-08-17 03:00:00');
INSERT INTO `ulasan` VALUES (2,3,'Ahmad Hidayat',5,'Warkop kami menghasilkan puluhan kilo kardus & kaleng tiap minggu. Dengan Trashily, penimbangannya transparan banget dan poinnya bisa ditukar ke sabun untuk cuci piring!','approved',NULL,1,'2026-08-17 12:28:32','2026-08-17 03:05:00');
INSERT INTO `ulasan` VALUES (3,4,'Ammar Abdul Malik',5,'Sistem digitalnya keren banget! Riwayat setor tercatat rapi di dashboard. Sangat membantu program pemilahan sampah di lingkungan RT kami.','approved',NULL,1,'2026-08-17 12:28:32','2026-08-17 03:10:00');
INSERT INTO `ulasan` VALUES (4,NULL,'tes',4,'bagus','pending',NULL,NULL,NULL,'2026-08-17 12:40:24');
/*!40000 ALTER TABLE `ulasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `poin` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Utama','admin@trashily.id','$2y$10$npl9/vU8phcedK52LMjgqe8xf89r46Acjy7yNYF4Mwcqv7Y9.F0Ka','081234567890',NULL,'admin',0,'2026-08-14 11:00:17');
INSERT INTO `users` VALUES (2,'Budi Santoso','budi@gmail.com','$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6','081111111111','Jl. Mawar No. 10','customer',210,'2026-08-14 11:00:17');
INSERT INTO `users` VALUES (3,'Siti Rahayu','siti@gmail.com','$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6','082222222222','Jl. Melati No. 5','customer',320,'2026-08-14 11:00:17');
INSERT INTO `users` VALUES (4,'Andi Wijaya','andi@gmail.com','$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6','083333333333','Jl. Kenanga No. 8','customer',80,'2026-08-14 11:00:17');
INSERT INTO `users` VALUES (5,'Ammar Abdul Malik','ammarithm@gmail.com','$2y$10$gp9KgL7pe.UYx1fsqYghZuvBaRQe428uABVX63bX79lkBwYla4d/a','0895702633030','CIkaret','customer',86,'2026-08-14 12:21:53');
INSERT INTO `users` VALUES (6,'Rafli Al Bukhori','rafli@gmail.com','$2y$10$LrUSJmc1bBLDn2tlllq37uesrz24E7WnJF33ojgmvaKkrEvHiwfi6','0895702633078','CIkaret','customer',0,'2026-08-14 12:22:53');
INSERT INTO `users` VALUES (7,'Abdul Kodir','kodirs@gmail.com','$2y$10$mPWwN7dqXfrvD0Vpo8jjKePUAraccuoe8YQv1IGy/GphqD4o4NNby','0895702633879','Pamoyanan','customer',0,'2026-08-14 12:23:36');
INSERT INTO `users` VALUES (10,'Administrator','admin@trashily.com','$2y$10$Q.XOW9ray4lMxxVteHbnjOgmJd0LHufygThJcZJCFn7bgaCNt.akG','081234567890','','customer',0,'2026-10-21 11:30:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-24 20:01:10
