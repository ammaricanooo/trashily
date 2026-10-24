-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2026 at 12:18 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank_sampah`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int NOT NULL,
  `transaksi_id` int NOT NULL,
  `jenis_sampah_id` int NOT NULL,
  `berat` decimal(10,2) NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT '0.00',
  `subtotal_poin` int NOT NULL,
  `subtotal_uang` decimal(12,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id`, `transaksi_id`, `jenis_sampah_id`, `berat`, `poin_per_kg`, `harga_per_kg`, `subtotal_poin`, `subtotal_uang`) VALUES
(1, 1, 2, '1.16', '5.00', '500.00', 6, '580.00'),
(2, 2, 3, '4.00', '20.00', '2000.00', 80, '8000.00');

-- --------------------------------------------------------

--
-- Table structure for table `everwear_jemput`
--

CREATE TABLE `everwear_jemput` (
  `id` int NOT NULL,
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
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `everwear_transaksi`
--

CREATE TABLE `everwear_transaksi` (
  `id` int NOT NULL,
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
  `admin_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gembul_jadwal`
--

CREATE TABLE `gembul_jadwal` (
  `id` int NOT NULL,
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
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gembul_jadwal`
--

INSERT INTO `gembul_jadwal` (`id`, `judul_kegiatan`, `kategori`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `lokasi`, `target_peserta`, `kuota_peserta`, `deskripsi`, `status`, `link_pendaftaran`, `created_at`, `updated_at`) VALUES
(1, 'Simulasi Ular Tangga GEMBUL & Estafet Pilah di SMKN 4 Bogor', 'Sekolah', '2026-10-14', '08:30:00', '11:00:00', 'Aula & Koridor Kelas X, SMKN 4 Bogor (Jl. Raya Tajur, Muarasari)', 'Siswa Kelas X & Pengurus OSIS / Ekstrakurikuler Lingkungan', 60, 'Sesi interaktif permainan Ular Tangga 30 petak dan Estafet Pilah kartu. Siswa diajak berdiskusi tentang penanganan sampah kantin dan sisa makanan MBG serta pembagian Eco-Pass perdana.', 'rencana', 'https://wa.me/6281234567890?text=Halo%20GEMBUL%2C%20saya%20ingin%20info%20kegiatan%20SMKN%204', '2026-10-11 19:37:09', '2026-10-11 19:37:09'),
(2, 'Aksi Observasi & Bersih Sampah Sungai Ciliwung bersama Alkesa', 'Aksi Sungai', '2026-10-18', '07:00:00', '10:30:00', 'Titik Pantau Ciliwung Sempur & Kolaborasi Alkesa Bogor', 'Relawan Muda, Anggota GEMBUL & Komunitas Alkesa', 40, 'Aksi susur sungai dan audit kategori sampah riparian bersama Alkesa. Data temuan sampah plastik dan sisa konsumsi akan dijadikan studi kasus nyata pada Kartu Misi GEMBUL.', 'rencana', 'https://wa.me/6281234567890?text=Daftar%20Aksi%20Ciliwung%20GEMBUL', '2026-10-11 19:37:09', '2026-10-11 19:37:09'),
(3, 'Workshop Pembuatan Magnet Kulkas Pilah dari Kardus Bekas', 'Workshop', '2026-10-23', '13:30:00', '16:00:00', 'Ruang Kolaborasi EcoHub Trashily, Jl. Padjadjaran No. 12', 'Perwakilan Keluarga, Guru, dan Remaja Komunitas', 25, 'Praktik langsung merakit toolkit rumah: magnet kulkas dari limbah kardus kemasan, lembar magnet bekas kalender, dan kartu pilah mini sebagai pengingat pemilahan harian di dapur.', 'rencana', 'https://wa.me/6281234567890?text=Daftar%20Workshop%20Magnet%20Pilah', '2026-10-11 19:37:09', '2026-10-11 19:37:09'),
(4, 'Uji Coba Mini Tracker & Eco-Challenge Keluarga 7 Hari', 'Komunitas', '2026-10-09', '09:00:00', '17:00:00', 'Lingkungan RT 04 Kelurahan Baranangsiang (Rumah Pilot Anggota)', '15 Keluarga Percontohan GEMBUL', 15, 'Tantangan keluarga sukarela selama 7 hari memisahkan kemasan dan mencatat keberhasilan pada papan Mini Tracker kalender bekas. Evaluasi kendala pemilahan di tingkat dapur.', 'berjalan', '', '2026-10-11 19:37:09', '2026-10-11 19:37:09'),
(5, 'Observasi Lapangan Alur Pemrosesan Sampah di Bank Sampah WU', 'Riset & Prototipe', '2026-10-06', '10:00:00', '13:00:00', 'Fasilitas Pengelolaan Bank Sampah WU Bogor', 'Tim Riset & Edukasi GEMBUL', 10, 'Menyelaraskan kategori kartu edukasi dengan alur penanganan nyata yang berjalan di WU. Memastikan aturan pemilahan di Ular Tangga sesuai dengan fasilitas penerima daur ulang lokal.', 'selesai', '', '2026-10-11 19:37:09', '2026-10-11 19:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `hadiah`
--

CREATE TABLE `hadiah` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `poin_dibutuhkan` int NOT NULL,
  `stok` int DEFAULT '0',
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hadiah`
--

INSERT INTO `hadiah` (`id`, `nama`, `deskripsi`, `poin_dibutuhkan`, `stok`, `gambar`, `is_active`, `created_at`) VALUES
(1, 'Pulpen', 'Pulpen pilot warna hitam', 20, 100, NULL, 1, '2026-08-14 11:00:17'),
(2, 'Buku Tulis', 'Buku tulis 58 lembar', 50, 50, NULL, 1, '2026-08-14 11:00:17'),
(3, 'Sabun Mandi', 'Sabun mandi batang', 75, 30, NULL, 1, '2026-08-14 11:00:17'),
(4, 'Minyak Goreng 1L', 'Minyak goreng kemasan 1 liter', 150, 20, NULL, 1, '2026-08-14 11:00:17'),
(5, 'Detergen 500gr', 'Detergen bubuk 500 gram', 100, 40, NULL, 1, '2026-08-14 11:00:17'),
(6, 'Payung', 'Payung lipat anti hujan', 200, 15, NULL, 1, '2026-08-14 11:00:17'),
(7, 'Tas Belanja', 'Tas belanja ramah lingkungan', 120, 25, NULL, 1, '2026-08-14 11:00:17'),
(8, 'Voucher Belanja 10rb', 'Voucher belanja senilai Rp 10.000', 250, 10, NULL, 1, '2026-08-14 11:00:17'),
(9, 'Pulpen', 'Pulpen pilot warna hitam', 20, 100, NULL, 1, '2026-08-17 10:36:15'),
(10, 'Buku Tulis', 'Buku tulis 58 lembar', 50, 50, NULL, 1, '2026-08-17 10:36:15'),
(11, 'Sabun Mandi', 'Sabun mandi batang', 75, 30, NULL, 1, '2026-08-17 10:36:15'),
(12, 'Minyak Goreng 1L', 'Minyak goreng kemasan 1 liter', 150, 20, NULL, 1, '2026-08-17 10:36:15'),
(13, 'Detergen 500gr', 'Detergen bubuk 500 gram', 100, 40, NULL, 1, '2026-08-17 10:36:15'),
(14, 'Payung', 'Payung lipat anti hujan', 200, 15, NULL, 1, '2026-08-17 10:36:15'),
(15, 'Tas Belanja', 'Tas belanja ramah lingkungan', 120, 25, NULL, 1, '2026-08-17 10:36:15'),
(16, 'Voucher Belanja 10rb', 'Voucher belanja senilai Rp 10.000', 250, 10, NULL, 1, '2026-08-17 10:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `jemput_detail`
--

CREATE TABLE `jemput_detail` (
  `id` int NOT NULL,
  `jemput_id` int NOT NULL,
  `jenis_sampah_id` int NOT NULL,
  `est_berat` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jemput_detail`
--

INSERT INTO `jemput_detail` (`id`, `jemput_id`, `jenis_sampah_id`, `est_berat`) VALUES
(1, 1, 2, '1.00');

-- --------------------------------------------------------

--
-- Table structure for table `jemput_sampah`
--

CREATE TABLE `jemput_sampah` (
  `id` int NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jemput_sampah`
--

INSERT INTO `jemput_sampah` (`id`, `kode_jemput`, `customer_id`, `admin_id`, `transaksi_id`, `alamat_jemput`, `jarak_km`, `biaya_ongkir`, `tarif_per_km`, `jadwal_jemput`, `catatan_customer`, `catatan_admin`, `status`, `created_at`, `updated_at`) VALUES
(1, 'JMP202608140282', 5, 1, 1, 'Cikaret', '0.00', '0.00', '2000.00', '2026-08-14 20:50:00', '', '', 'selesai', '2026-08-14 12:42:05', '2026-08-14 12:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_sampah`
--

CREATE TABLE `jenis_sampah` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('organik_kering','plastik','kertas','logam','kaca','elektronik','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT '0.00',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_sampah`
--

INSERT INTO `jenis_sampah` (`id`, `nama`, `kategori`, `poin_per_kg`, `harga_per_kg`, `deskripsi`, `is_active`, `created_at`) VALUES
(2, 'Ranting & Kayu Kecil', 'organik_kering', '5.00', '500.00', 'Ranting pohon dan kayu kecil kering', 1, '2026-08-14 11:00:17'),
(3, 'Botol Plastik PET', 'plastik', '20.00', '2000.00', 'Botol plastik bening bekas minuman', 1, '2026-08-14 11:00:17'),
(4, 'Plastik Kresek', 'plastik', '8.00', '800.00', 'Kantong plastik kresek', 1, '2026-08-14 11:00:17'),
(5, 'Kardus & Karton', 'kertas', '15.00', '1500.00', 'Kardus bekas dan karton', 1, '2026-08-14 11:00:17'),
(6, 'Kertas HVS', 'kertas', '12.00', '1200.00', 'Kertas putih bekas', 1, '2026-08-14 11:00:17'),
(7, 'Kaleng Aluminium', 'logam', '35.00', '3500.00', 'Kaleng minuman atau makanan', 1, '2026-08-14 11:00:17'),
(9, 'Botol Kaca', 'kaca', '10.00', '1000.00', 'Botol dan wadah kaca', 1, '2026-08-14 11:00:17'),
(11, 'Daun Kering', 'organik_kering', '5.00', '500.00', 'Daun-daun kering yang sudah gugur', 1, '2026-08-17 10:36:15'),
(12, 'Ranting & Kayu Kecil', 'organik_kering', '5.00', '500.00', 'Ranting pohon dan kayu kecil kering', 1, '2026-08-17 10:36:15'),
(13, 'Botol Plastik PET', 'plastik', '20.00', '2000.00', 'Botol plastik bening bekas minuman', 1, '2026-08-17 10:36:15'),
(14, 'Plastik Kresek', 'plastik', '8.00', '800.00', 'Kantong plastik kresek', 1, '2026-08-17 10:36:15'),
(15, 'Kardus & Karton', 'kertas', '15.00', '1500.00', 'Kardus bekas dan karton', 1, '2026-08-17 10:36:15'),
(16, 'Kertas HVS', 'kertas', '12.00', '1200.00', 'Kertas putih bekas', 1, '2026-08-17 10:36:15'),
(17, 'Kaleng Aluminium', 'logam', '35.00', '3500.00', 'Kaleng minuman atau makanan', 1, '2026-08-17 10:36:15'),
(18, 'Besi & Baja', 'logam', '25.00', '2500.00', 'Besi dan baja bekas', 1, '2026-08-17 10:36:15'),
(19, 'Botol Kaca', 'kaca', '10.00', '1000.00', 'Botol dan wadah kaca', 1, '2026-08-17 10:36:15'),
(20, 'Elektronik Bekas', 'elektronik', '50.00', '5000.00', 'Perangkat elektronik tidak terpakai', 1, '2026-08-17 10:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan`
--

CREATE TABLE `pengaturan` (
  `kunci` varchar(50) NOT NULL,
  `nilai` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturan`
--

INSERT INTO `pengaturan` (`kunci`, `nilai`) VALUES
('tarif_per_km', '2500'),
('trashily_alamat', 'Gang Kosasih, Cikaret, Bogor Selatan, Kota Batu, Bogor, West Java, 16610, Indonesia'),
('trashily_lat', '-6.624408'),
('trashily_lng', '106.787575');

-- --------------------------------------------------------

--
-- Table structure for table `penukaran`
--

CREATE TABLE `penukaran` (
  `id` int NOT NULL,
  `kode_penukaran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `admin_id` int DEFAULT NULL,
  `hadiah_id` int NOT NULL,
  `poin_digunakan` int NOT NULL,
  `status` enum('pending','diproses','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_transaksi`, `customer_id`, `nama_non_member`, `admin_id`, `total_poin`, `total_uang`, `total_berat`, `catatan`, `catatan_admin`, `status`, `tipe_transaksi`, `jadwal_setor`, `created_at`) VALUES
(1, 'TRX202608141111', 5, NULL, 1, 6, '580.00', '1.16', '', NULL, 'selesai', 'langsung', NULL, '2026-08-14 12:52:00'),
(2, 'TRX202608141264', 5, NULL, 1, 80, '8000.00', '4.00', '', NULL, 'selesai', 'langsung', NULL, '2026-08-14 13:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `trashsmart_devices`
--

CREATE TABLE `trashsmart_devices` (
  `id` int NOT NULL,
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
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trashsmart_devices`
--

INSERT INTO `trashsmart_devices` (`id`, `kode_perangkat`, `nama_perangkat`, `lokasi`, `latitude`, `longitude`, `status`, `kapasitas_organik`, `kapasitas_anorganik`, `baterai`, `suhu_celsius`, `firmware_version`, `total_pilah_count`, `total_berat_kg`, `voice_nudge_count`, `terakhir_dikosongkan`, `last_ping`, `created_at`) VALUES
(1, 'TS-BGR-001', 'TrashSmart Kantin SMA 1 Bogor', 'Kantin Sekolah Hijau, SMAN 1 Bogor (Area Depan Gazebo)', '-6.59714700', '106.79947800', 'online', 48, 62, 94, '27.8', 'v2.4.2', 142, '18.75', 19, '2026-10-09 08:30:00', '2026-10-10 10:05:52', '2026-10-10 10:08:52'),
(2, 'TS-BGR-002', 'TrashSmart Taman Sempur EcoHub', 'Taman Sempur Bogor, Dekat Lapangan Basket & Jalur Jogging', '-6.59123000', '106.79785000', 'warning', 65, 92, 82, '30.2', 'v2.4.2', 285, '34.60', 43, '2026-10-08 09:00:00', '2026-10-10 10:07:52', '2026-10-10 10:08:52'),
(3, 'TS-BGR-003', 'TrashSmart Stasiun Bogor Hall Barat', 'Stasiun Bogor, Pintu Masuk Tap-In Hall Barat', '-6.59543000', '106.78912000', 'online', 38, 45, 100, '26.5', 'v2.4.1', 360, '42.10', 31, '2026-10-10 06:00:00', '2026-10-10 10:08:52', '2026-10-10 10:08:52'),
(4, 'TS-BGR-004', 'TrashSmart Botani Square Food Terrace', 'Mall Botani Square Lt. 2, Koridor Food Terrace & Atrium', '-6.60145000', '106.80562000', 'online', 70, 58, 91, '25.4', 'v2.4.2', 198, '26.80', 24, '2026-10-10 07:15:00', '2026-10-10 10:03:52', '2026-10-10 10:08:52'),
(5, 'TS-BGR-005', 'TrashSmart Kantor DLH Kota Bogor', 'Lobi Utama Kantor Dinas Lingkungan Hidup, Jl. Paledang', '-6.60381000', '106.79412000', 'online', 22, 31, 97, '26.8', 'v2.4.2', 86, '9.40', 7, '2026-10-10 08:00:00', '2026-10-10 10:06:52', '2026-10-10 10:08:52'),
(6, 'TS-BGR-006', 'TrashSmart Kebun Raya Pintu 3', 'Kebun Raya Bogor, Area Tourist Information & Pintu 3', '-6.60251000', '106.80121000', 'maintenance', 15, 20, 45, '29.1', 'v2.4.0', 112, '14.20', 15, '2026-10-07 10:00:00', '2026-10-10 08:08:52', '2026-10-10 10:08:52');

-- --------------------------------------------------------

--
-- Table structure for table `trashsmart_logs`
--

CREATE TABLE `trashsmart_logs` (
  `id` int NOT NULL,
  `device_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `kategori_sampah` enum('organik','anorganik','residu','sistem') DEFAULT 'organik',
  `status_pilah` enum('benar','keliru','peringatan','sistem') DEFAULT 'benar',
  `berat_kg` decimal(8,3) DEFAULT '0.000',
  `poin` int DEFAULT '0',
  `pesan_suara` varchar(255) DEFAULT NULL,
  `user_identifier` varchar(100) DEFAULT 'Guest / Siswa',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trashsmart_logs`
--

INSERT INTO `trashsmart_logs` (`id`, `device_id`, `tanggal`, `waktu`, `kategori_sampah`, `status_pilah`, `berat_kg`, `poin`, `pesan_suara`, `user_identifier`, `created_at`) VALUES
(1, 1, '2026-10-10', '07:15:20', 'organik', 'benar', '0.600', 24, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Komuter KRL (NFC Tap)', '2026-10-10 07:15:20'),
(2, 1, '2026-10-10', '15:10:05', 'anorganik', 'benar', '0.750', 75, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 15:10:05'),
(3, 1, '2026-10-10', '15:10:05', 'organik', 'benar', '0.170', 6, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Staf Tenant Botani Square', '2026-10-10 15:10:05'),
(4, 1, '2026-10-10', '17:30:50', 'anorganik', 'benar', '0.830', 83, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-10 17:30:50'),
(5, 1, '2026-10-10', '09:20:15', 'anorganik', 'keliru', '0.430', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 09:20:15'),
(6, 1, '2026-10-10', '18:15:25', 'organik', 'keliru', '0.120', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Budi Santoso (Member)', '2026-10-10 18:15:25'),
(7, 2, '2026-10-10', '15:10:05', 'anorganik', 'benar', '0.470', 47, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pengunjung Umum', '2026-10-10 15:10:05'),
(8, 2, '2026-10-10', '07:15:20', 'sistem', 'peringatan', '0.000', 0, 'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.', 'Sistem Otomatis IoT', '2026-10-10 07:15:20'),
(9, 2, '2026-10-10', '09:20:15', 'sistem', 'peringatan', '0.000', 0, 'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.', 'Sistem Otomatis IoT', '2026-10-10 09:20:15'),
(10, 2, '2026-10-10', '10:35:19', 'anorganik', 'keliru', '0.460', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Rizky Pratama (App Trashily)', '2026-10-10 10:35:19'),
(11, 2, '2026-10-10', '17:30:50', 'anorganik', 'benar', '0.290', 28, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Dewi Lestari (Member)', '2026-10-10 17:30:50'),
(12, 2, '2026-10-10', '12:20:10', 'organik', 'keliru', '0.260', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMKN 1', '2026-10-10 12:20:10'),
(13, 3, '2026-10-10', '16:45:12', 'anorganik', 'benar', '0.630', 63, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Dewi Lestari (Member)', '2026-10-10 16:45:12'),
(14, 3, '2026-10-10', '12:45:55', 'organik', 'benar', '0.360', 14, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-10 12:45:55'),
(15, 3, '2026-10-10', '17:30:50', 'anorganik', 'benar', '0.600', 60, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-10 17:30:50'),
(16, 3, '2026-10-10', '10:05:42', 'organik', 'benar', '0.380', 15, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-10 10:05:42'),
(17, 4, '2026-10-10', '11:58:30', 'anorganik', 'benar', '0.690', 69, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Staf Tenant Botani Square', '2026-10-10 11:58:30'),
(18, 4, '2026-10-10', '19:00:44', 'anorganik', 'benar', '0.700', 70, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Rizky Pratama (App Trashily)', '2026-10-10 19:00:44'),
(19, 4, '2026-10-10', '07:15:20', 'organik', 'keliru', '0.370', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Rizky Pratama (App Trashily)', '2026-10-10 07:15:20'),
(20, 4, '2026-10-10', '11:58:30', 'anorganik', 'keliru', '0.350', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Budi Santoso (Member)', '2026-10-10 11:58:30'),
(21, 4, '2026-10-10', '16:05:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 16:05:30'),
(22, 4, '2026-10-10', '12:20:10', 'organik', 'benar', '0.490', 19, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siswa SMKN 1', '2026-10-10 12:20:10'),
(23, 4, '2026-10-10', '10:35:19', 'anorganik', 'benar', '0.310', 31, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 10:35:19'),
(24, 5, '2026-10-10', '16:45:12', 'organik', 'benar', '0.620', 24, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pengunjung Umum', '2026-10-10 16:45:12'),
(25, 5, '2026-10-10', '15:10:05', 'anorganik', 'benar', '0.650', 65, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-10 15:10:05'),
(26, 5, '2026-10-10', '12:45:55', 'organik', 'keliru', '0.120', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Staf Tenant Botani Square', '2026-10-10 12:45:55'),
(27, 5, '2026-10-10', '11:58:30', 'organik', 'benar', '0.340', 13, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-10 11:58:30'),
(28, 6, '2026-10-10', '11:12:05', 'anorganik', 'benar', '0.610', 61, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 11:12:05'),
(29, 6, '2026-10-10', '11:58:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 11:58:30'),
(30, 6, '2026-10-10', '12:45:55', 'anorganik', 'benar', '0.210', 21, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 12:45:55'),
(31, 6, '2026-10-10', '10:05:42', 'anorganik', 'benar', '0.460', 46, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Komuter KRL (NFC Tap)', '2026-10-10 10:05:42'),
(32, 6, '2026-10-10', '19:00:44', 'anorganik', 'benar', '0.640', 64, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Dewi Lestari (Member)', '2026-10-10 19:00:44'),
(33, 6, '2026-10-10', '11:12:05', 'organik', 'benar', '0.420', 16, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-10 11:12:05'),
(34, 1, '2026-10-09', '13:10:40', 'anorganik', 'benar', '0.800', 80, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Dewi Lestari (Member)', '2026-10-09 13:10:40'),
(35, 1, '2026-10-09', '07:15:20', 'anorganik', 'keliru', '0.280', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 07:15:20'),
(36, 1, '2026-10-09', '08:02:11', 'organik', 'keliru', '0.160', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Staf Tenant Botani Square', '2026-10-09 08:02:11'),
(37, 1, '2026-10-09', '08:45:33', 'organik', 'keliru', '0.310', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Rizky Pratama (App Trashily)', '2026-10-09 08:45:33'),
(38, 1, '2026-10-09', '15:10:05', 'anorganik', 'benar', '0.700', 70, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMKN 1', '2026-10-09 15:10:05'),
(39, 2, '2026-10-09', '19:00:44', 'anorganik', 'keliru', '0.210', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Dewi Lestari (Member)', '2026-10-09 19:00:44'),
(40, 2, '2026-10-09', '14:25:18', 'organik', 'benar', '0.790', 31, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 14:25:18'),
(41, 2, '2026-10-09', '19:00:44', 'anorganik', 'keliru', '0.140', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siti Rahma (App Trashily)', '2026-10-09 19:00:44'),
(42, 2, '2026-10-09', '12:45:55', 'anorganik', 'benar', '0.820', 82, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Staf Tenant Botani Square', '2026-10-09 12:45:55'),
(43, 3, '2026-10-09', '18:15:25', 'organik', 'benar', '0.750', 30, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-09 18:15:25'),
(44, 3, '2026-10-09', '16:45:12', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 16:45:12'),
(45, 3, '2026-10-09', '11:58:30', 'anorganik', 'keliru', '0.260', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Pengunjung Umum', '2026-10-09 11:58:30'),
(46, 3, '2026-10-09', '13:50:22', 'organik', 'benar', '0.710', 28, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 13:50:22'),
(47, 3, '2026-10-09', '15:10:05', 'organik', 'benar', '0.660', 26, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Pelari Pagi Taman Sempur', '2026-10-09 15:10:05'),
(48, 3, '2026-10-09', '16:05:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 16:05:30'),
(49, 3, '2026-10-09', '16:05:30', 'anorganik', 'benar', '0.260', 26, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Pelari Pagi Taman Sempur', '2026-10-09 16:05:30'),
(50, 4, '2026-10-09', '08:45:33', 'anorganik', 'benar', '0.170', 17, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 08:45:33'),
(51, 4, '2026-10-09', '11:12:05', 'anorganik', 'keliru', '0.240', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Rizky Pratama (App Trashily)', '2026-10-09 11:12:05'),
(52, 4, '2026-10-09', '10:05:42', 'organik', 'keliru', '0.310', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 10:05:42'),
(53, 4, '2026-10-09', '13:50:22', 'organik', 'benar', '0.560', 22, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pelari Pagi Taman Sempur', '2026-10-09 13:50:22'),
(54, 4, '2026-10-09', '09:20:15', 'anorganik', 'keliru', '0.360', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 09:20:15'),
(55, 4, '2026-10-09', '09:20:15', 'anorganik', 'benar', '0.380', 38, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-09 09:20:15'),
(56, 4, '2026-10-09', '12:45:55', 'organik', 'benar', '0.750', 30, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siti Rahma (App Trashily)', '2026-10-09 12:45:55'),
(57, 5, '2026-10-09', '14:25:18', 'organik', 'keliru', '0.200', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Dewi Lestari (Member)', '2026-10-09 14:25:18'),
(58, 5, '2026-10-09', '13:10:40', 'organik', 'benar', '0.380', 15, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMKN 1', '2026-10-09 13:10:40'),
(59, 5, '2026-10-09', '08:45:33', 'organik', 'benar', '0.320', 12, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Dewi Lestari (Member)', '2026-10-09 08:45:33'),
(60, 5, '2026-10-09', '19:00:44', 'anorganik', 'keliru', '0.310', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Rizky Pratama (App Trashily)', '2026-10-09 19:00:44'),
(61, 5, '2026-10-09', '12:20:10', 'anorganik', 'benar', '0.500', 50, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siti Rahma (App Trashily)', '2026-10-09 12:20:10'),
(62, 5, '2026-10-09', '12:45:55', 'organik', 'benar', '0.380', 15, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-09 12:45:55'),
(63, 6, '2026-10-09', '13:50:22', 'organik', 'keliru', '0.480', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Komuter KRL (NFC Tap)', '2026-10-09 13:50:22'),
(64, 6, '2026-10-09', '11:12:05', 'organik', 'keliru', '0.400', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Staf Tenant Botani Square', '2026-10-09 11:12:05'),
(65, 6, '2026-10-09', '19:00:44', 'organik', 'keliru', '0.440', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Staf Tenant Botani Square', '2026-10-09 19:00:44'),
(66, 6, '2026-10-09', '13:50:22', 'organik', 'benar', '0.720', 28, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siti Rahma (App Trashily)', '2026-10-09 13:50:22'),
(67, 6, '2026-10-09', '11:12:05', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 11:12:05'),
(68, 6, '2026-10-09', '13:10:40', 'anorganik', 'keliru', '0.110', 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Pelari Pagi Taman Sempur', '2026-10-09 13:10:40'),
(69, 6, '2026-10-09', '12:45:55', 'anorganik', 'benar', '0.850', 85, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Staf Tenant Botani Square', '2026-10-09 12:45:55'),
(70, 1, '2026-10-08', '08:02:11', 'organik', 'benar', '0.500', 20, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Staf Tenant Botani Square', '2026-10-08 08:02:11'),
(71, 1, '2026-10-08', '16:45:12', 'organik', 'keliru', '0.480', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siti Rahma (App Trashily)', '2026-10-08 16:45:12'),
(72, 1, '2026-10-08', '16:05:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 16:05:30'),
(73, 1, '2026-10-08', '15:10:05', 'organik', 'benar', '0.840', 33, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Komuter KRL (NFC Tap)', '2026-10-08 15:10:05'),
(74, 1, '2026-10-08', '11:58:30', 'organik', 'benar', '0.710', 28, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-08 11:58:30'),
(75, 1, '2026-10-08', '16:45:12', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 16:45:12'),
(76, 1, '2026-10-08', '07:15:20', 'organik', 'benar', '0.750', 30, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Budi Santoso (Member)', '2026-10-08 07:15:20'),
(77, 2, '2026-10-08', '15:10:05', 'organik', 'keliru', '0.350', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Pelari Pagi Taman Sempur', '2026-10-08 15:10:05'),
(78, 2, '2026-10-08', '11:58:30', 'anorganik', 'benar', '0.160', 16, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siti Rahma (App Trashily)', '2026-10-08 11:58:30'),
(79, 2, '2026-10-08', '08:02:11', 'sistem', 'peringatan', '0.000', 0, 'Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.', 'Sistem Otomatis IoT', '2026-10-08 08:02:11'),
(80, 2, '2026-10-08', '18:15:25', 'organik', 'benar', '0.440', 17, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-08 18:15:25'),
(81, 3, '2026-10-08', '10:05:42', 'anorganik', 'benar', '0.760', 76, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pelari Pagi Taman Sempur', '2026-10-08 10:05:42'),
(82, 3, '2026-10-08', '16:05:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 16:05:30'),
(83, 3, '2026-10-08', '16:05:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 16:05:30'),
(84, 3, '2026-10-08', '11:58:30', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 11:58:30'),
(85, 3, '2026-10-08', '08:02:11', 'anorganik', 'benar', '0.340', 34, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Rizky Pratama (App Trashily)', '2026-10-08 08:02:11'),
(86, 3, '2026-10-08', '19:00:44', 'organik', 'benar', '0.460', 18, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-08 19:00:44'),
(87, 4, '2026-10-08', '07:15:20', 'organik', 'benar', '0.830', 33, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Pengunjung Umum', '2026-10-08 07:15:20'),
(88, 4, '2026-10-08', '08:45:33', 'anorganik', 'benar', '0.660', 66, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Dewi Lestari (Member)', '2026-10-08 08:45:33'),
(89, 4, '2026-10-08', '08:45:33', 'organik', 'benar', '0.700', 28, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Staf Tenant Botani Square', '2026-10-08 08:45:33'),
(90, 4, '2026-10-08', '10:35:19', 'organik', 'benar', '0.590', 23, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-08 10:35:19'),
(91, 4, '2026-10-08', '11:12:05', 'anorganik', 'benar', '0.230', 23, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMKN 1', '2026-10-08 11:12:05'),
(92, 5, '2026-10-08', '18:15:25', 'organik', 'benar', '0.670', 26, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMKN 1', '2026-10-08 18:15:25'),
(93, 5, '2026-10-08', '13:50:22', 'anorganik', 'benar', '0.440', 44, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Dewi Lestari (Member)', '2026-10-08 13:50:22'),
(94, 5, '2026-10-08', '09:20:15', 'organik', 'benar', '0.630', 25, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Staf Tenant Botani Square', '2026-10-08 09:20:15'),
(95, 5, '2026-10-08', '15:10:05', 'organik', 'keliru', '0.200', 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Pelari Pagi Taman Sempur', '2026-10-08 15:10:05'),
(96, 5, '2026-10-08', '08:45:33', 'anorganik', 'benar', '0.770', 77, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Pengunjung Umum', '2026-10-08 08:45:33'),
(97, 5, '2026-10-08', '15:10:05', 'organik', 'keliru', '0.340', 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Rizky Pratama (App Trashily)', '2026-10-08 15:10:05'),
(98, 5, '2026-10-08', '12:45:55', 'organik', 'benar', '0.320', 12, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Budi Santoso (Member)', '2026-10-08 12:45:55'),
(99, 6, '2026-10-08', '12:45:55', 'organik', 'benar', '0.850', 34, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Rizky Pratama (App Trashily)', '2026-10-08 12:45:55'),
(100, 6, '2026-10-08', '11:12:05', 'sistem', 'sistem', '0.000', 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-08 11:12:05'),
(101, 6, '2026-10-08', '13:10:40', 'anorganik', 'benar', '0.770', 77, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-08 13:10:40'),
(102, 6, '2026-10-08', '08:45:33', 'anorganik', 'benar', '0.630', 63, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-08 08:45:33'),
(103, 6, '2026-10-08', '16:45:12', 'anorganik', 'benar', '0.830', 83, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-08 16:45:12'),
(104, 6, '2026-10-08', '16:45:12', 'anorganik', 'benar', '0.390', 39, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-08 16:45:12'),
(105, 6, '2026-10-08', '16:45:12', 'anorganik', 'benar', '0.240', 24, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-08 16:45:12');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint NOT NULL DEFAULT '5',
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id`, `customer_id`, `nama`, `rating`, `komentar`, `status`, `admin_note`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 5, 'Siti Rahmawati', 5, 'Dulu sampah bekas cuma dibuang ke tempat sampah. Sekarang kumpulin, panggil kurir Trashily, langsung tukar poin jadi pulpen & alat tulis favorit!', 'approved', NULL, 1, '2026-08-17 12:28:32', '2026-08-17 03:00:00'),
(2, 3, 'Ahmad Hidayat', 5, 'Warkop kami menghasilkan puluhan kilo kardus & kaleng tiap minggu. Dengan Trashily, penimbangannya transparan banget dan poinnya bisa ditukar ke sabun untuk cuci piring!', 'approved', NULL, 1, '2026-08-17 12:28:32', '2026-08-17 03:05:00'),
(3, 4, 'Ammar Abdul Malik', 5, 'Sistem digitalnya keren banget! Riwayat setor tercatat rapi di dashboard. Sangat membantu program pemilahan sampah di lingkungan RT kami.', 'approved', NULL, 1, '2026-08-17 12:28:32', '2026-08-17 03:10:00'),
(4, NULL, 'tes', 4, 'bagus', 'pending', NULL, NULL, NULL, '2026-08-17 12:40:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `role` enum('admin','customer') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `poin` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`, `poin`, `created_at`) VALUES
(1, 'Admin Utama', 'admin@trashily.id', '$2y$10$npl9/vU8phcedK52LMjgqe8xf89r46Acjy7yNYF4Mwcqv7Y9.F0Ka', '081234567890', NULL, 'admin', 0, '2026-08-14 11:00:17'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '081111111111', 'Jl. Mawar No. 10', 'customer', 150, '2026-08-14 11:00:17'),
(3, 'Siti Rahayu', 'siti@gmail.com', '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '082222222222', 'Jl. Melati No. 5', 'customer', 320, '2026-08-14 11:00:17'),
(4, 'Andi Wijaya', 'andi@gmail.com', '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '083333333333', 'Jl. Kenanga No. 8', 'customer', 80, '2026-08-14 11:00:17'),
(5, 'Ammar Abdul Malik', 'ammarithm@gmail.com', '$2y$10$gp9KgL7pe.UYx1fsqYghZuvBaRQe428uABVX63bX79lkBwYla4d/a', '0895702633030', 'CIkaret', 'customer', 86, '2026-08-14 12:21:53'),
(6, 'Rafli Al Bukhori', 'rafli@gmail.com', '$2y$10$LrUSJmc1bBLDn2tlllq37uesrz24E7WnJF33ojgmvaKkrEvHiwfi6', '0895702633078', 'CIkaret', 'customer', 0, '2026-08-14 12:22:53'),
(7, 'Abdul Kodir', 'kodirs@gmail.com', '$2y$10$mPWwN7dqXfrvD0Vpo8jjKePUAraccuoe8YQv1IGy/GphqD4o4NNby', '0895702633879', 'Pamoyanan', 'customer', 0, '2026-08-14 12:23:36');

-- --------------------------------------------------------

--
-- Table structure for table `_t`
--

CREATE TABLE `_t` (
  `id` int DEFAULT NULL,
  `v` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `_t`
--

INSERT INTO `_t` (`id`, `v`) VALUES
(1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_id` (`transaksi_id`),
  ADD KEY `jenis_sampah_id` (`jenis_sampah_id`);

--
-- Indexes for table `everwear_jemput`
--
ALTER TABLE `everwear_jemput`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jemput` (`kode_jemput`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `everwear_transaksi`
--
ALTER TABLE `everwear_transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `gembul_jadwal`
--
ALTER TABLE `gembul_jadwal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hadiah`
--
ALTER TABLE `hadiah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jemput_detail`
--
ALTER TABLE `jemput_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jemput_id` (`jemput_id`),
  ADD KEY `jenis_sampah_id` (`jenis_sampah_id`);

--
-- Indexes for table `jemput_sampah`
--
ALTER TABLE `jemput_sampah`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_jemput` (`kode_jemput`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `transaksi_id` (`transaksi_id`);

--
-- Indexes for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`kunci`);

--
-- Indexes for table `penukaran`
--
ALTER TABLE `penukaran`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_penukaran` (`kode_penukaran`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `hadiah_id` (`hadiah_id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `trashsmart_devices`
--
ALTER TABLE `trashsmart_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_perangkat` (`kode_perangkat`);

--
-- Indexes for table `trashsmart_logs`
--
ALTER TABLE `trashsmart_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_tanggal` (`device_id`,`tanggal`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `everwear_jemput`
--
ALTER TABLE `everwear_jemput`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `everwear_transaksi`
--
ALTER TABLE `everwear_transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `gembul_jadwal`
--
ALTER TABLE `gembul_jadwal`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hadiah`
--
ALTER TABLE `hadiah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jemput_detail`
--
ALTER TABLE `jemput_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jemput_sampah`
--
ALTER TABLE `jemput_sampah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `penukaran`
--
ALTER TABLE `penukaran`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trashsmart_devices`
--
ALTER TABLE `trashsmart_devices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trashsmart_logs`
--
ALTER TABLE `trashsmart_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah` (`id`);

--
-- Constraints for table `jemput_detail`
--
ALTER TABLE `jemput_detail`
  ADD CONSTRAINT `jemput_detail_ibfk_1` FOREIGN KEY (`jemput_id`) REFERENCES `jemput_sampah` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jemput_detail_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah` (`id`);

--
-- Constraints for table `jemput_sampah`
--
ALTER TABLE `jemput_sampah`
  ADD CONSTRAINT `jemput_sampah_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jemput_sampah_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jemput_sampah_ibfk_3` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `penukaran`
--
ALTER TABLE `penukaran`
  ADD CONSTRAINT `penukaran_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penukaran_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penukaran_ibfk_3` FOREIGN KEY (`hadiah_id`) REFERENCES `hadiah` (`id`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trashsmart_logs`
--
ALTER TABLE `trashsmart_logs`
  ADD CONSTRAINT `fk_trashsmart_device` FOREIGN KEY (`device_id`) REFERENCES `trashsmart_devices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
