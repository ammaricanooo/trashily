-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2026 at 07:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.7

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
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `jenis_sampah_id` int(11) NOT NULL,
  `berat` decimal(10,2) NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT 0.00,
  `subtotal_poin` int(11) NOT NULL,
  `subtotal_uang` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `everwear_jemput`
--

CREATE TABLE `everwear_jemput` (
  `id` int(11) NOT NULL,
  `kode_jemput` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `kategori_pakaian` enum('layak_pakai','recycle','campuran') NOT NULL,
  `estimasi_jumlah` varchar(100) DEFAULT NULL,
  `alamat_penjemputan` text NOT NULL,
  `catatan_lokasi` text DEFAULT NULL,
  `tanggal_jemput` date NOT NULL,
  `jam_jemput` time NOT NULL,
  `status` enum('pending','dikonfirmasi','dalam_perjalanan','selesai','dibatalkan') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `jarak_km` decimal(8,2) NOT NULL DEFAULT 0.00,
  `biaya_ongkir` int(11) NOT NULL DEFAULT 0,
  `pcs_layak` int(11) NOT NULL DEFAULT 0,
  `pcs_recycle` int(11) NOT NULL DEFAULT 0,
  `berat_kg` decimal(8,2) NOT NULL DEFAULT 0.00,
  `poin_diperoleh` int(11) NOT NULL DEFAULT 0,
  `admin_id` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `everwear_transaksi`
--

CREATE TABLE `everwear_transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `kategori_pakaian` enum('layak_pakai','recycle','campuran') NOT NULL,
  `deskripsi_pakaian` text DEFAULT NULL,
  `jumlah_pcs` int(11) DEFAULT 1,
  `berat_kg` decimal(8,2) DEFAULT 0.00,
  `poin_diperoleh` int(11) DEFAULT 0,
  `status` enum('pending','proses','selesai','dibatalkan') DEFAULT 'pending',
  `catatan_admin` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `catatan` text DEFAULT NULL,
  `jadwal_setor` datetime DEFAULT NULL,
  `pcs_layak` int(11) NOT NULL DEFAULT 0,
  `pcs_recycle` int(11) NOT NULL DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gembul_jadwal`
--

CREATE TABLE `gembul_jadwal` (
  `id` int(11) NOT NULL,
  `judul_kegiatan` varchar(200) NOT NULL,
  `kategori` varchar(50) DEFAULT 'Sekolah',
  `tanggal` date NOT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `lokasi` varchar(255) NOT NULL,
  `target_peserta` varchar(150) DEFAULT NULL,
  `kuota_peserta` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `status` enum('rencana','berjalan','selesai','dibatalkan') DEFAULT 'rencana',
  `link_pendaftaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hadiah`
--

CREATE TABLE `hadiah` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `poin_dibutuhkan` int(11) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
(9, 'Pulpen', 'Pulpen pilot warna hitam', 20, 99, NULL, 1, '2026-08-17 10:36:15'),
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
  `id` int(11) NOT NULL,
  `jemput_id` int(11) NOT NULL,
  `jenis_sampah_id` int(11) NOT NULL,
  `est_berat` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jemput_sampah`
--

CREATE TABLE `jemput_sampah` (
  `id` int(11) NOT NULL,
  `kode_jemput` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `alamat_jemput` text NOT NULL,
  `jarak_km` decimal(10,2) DEFAULT 0.00,
  `biaya_ongkir` decimal(10,2) DEFAULT 0.00,
  `tarif_per_km` decimal(10,2) DEFAULT 2000.00,
  `jadwal_jemput` datetime NOT NULL,
  `catatan_customer` text DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `status` enum('menunggu','dikonfirmasi','dijemput','selesai','batal') DEFAULT 'menunggu',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_sampah`
--

CREATE TABLE `jenis_sampah` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` enum('organik_kering','plastik','kertas','logam','kaca','elektronik','lainnya') NOT NULL,
  `poin_per_kg` decimal(10,2) NOT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT 0.00,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_sampah`
--

INSERT INTO `jenis_sampah` (`id`, `nama`, `kategori`, `poin_per_kg`, `harga_per_kg`, `deskripsi`, `is_active`, `created_at`) VALUES
(2, 'Ranting & Kayu Kecil', 'organik_kering', 5.00, 500.00, 'Ranting pohon dan kayu kecil kering', 1, '2026-08-14 11:00:17'),
(3, 'Botol Plastik PET', 'plastik', 20.00, 2000.00, 'Botol plastik bening bekas minuman', 1, '2026-08-14 11:00:17'),
(4, 'Plastik Kresek', 'plastik', 8.00, 800.00, 'Kantong plastik kresek', 1, '2026-08-14 11:00:17'),
(5, 'Kardus & Karton', 'kertas', 15.00, 1500.00, 'Kardus bekas dan karton', 1, '2026-08-14 11:00:17'),
(6, 'Kertas HVS', 'kertas', 12.00, 1200.00, 'Kertas putih bekas', 1, '2026-08-14 11:00:17'),
(7, 'Kaleng Aluminium', 'logam', 35.00, 3500.00, 'Kaleng minuman atau makanan', 1, '2026-08-14 11:00:17'),
(9, 'Botol Kaca', 'kaca', 10.00, 1000.00, 'Botol dan wadah kaca', 1, '2026-08-14 11:00:17'),
(11, 'Daun Kering', 'organik_kering', 5.00, 500.00, 'Daun-daun kering yang sudah gugur', 1, '2026-08-17 10:36:15'),
(12, 'Ranting & Kayu Kecil', 'organik_kering', 5.00, 500.00, 'Ranting pohon dan kayu kecil kering', 1, '2026-08-17 10:36:15'),
(13, 'Botol Plastik PET', 'plastik', 20.00, 2000.00, 'Botol plastik bening bekas minuman', 1, '2026-08-17 10:36:15'),
(14, 'Plastik Kresek', 'plastik', 8.00, 800.00, 'Kantong plastik kresek', 1, '2026-08-17 10:36:15'),
(15, 'Kardus & Karton', 'kertas', 15.00, 1500.00, 'Kardus bekas dan karton', 1, '2026-08-17 10:36:15'),
(16, 'Kertas HVS', 'kertas', 12.00, 1200.00, 'Kertas putih bekas', 1, '2026-08-17 10:36:15'),
(17, 'Kaleng Aluminium', 'logam', 35.00, 3500.00, 'Kaleng minuman atau makanan', 1, '2026-08-17 10:36:15'),
(18, 'Besi & Baja', 'logam', 25.00, 2500.00, 'Besi dan baja bekas', 1, '2026-08-17 10:36:15'),
(19, 'Botol Kaca', 'kaca', 10.00, 1000.00, 'Botol dan wadah kaca', 1, '2026-08-17 10:36:15'),
(20, 'Elektronik Bekas', 'elektronik', 50.00, 5000.00, 'Perangkat elektronik tidak terpakai', 1, '2026-08-17 10:36:15');

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
  `id` int(11) NOT NULL,
  `kode_penukaran` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `hadiah_id` int(11) NOT NULL,
  `poin_digunakan` int(11) NOT NULL,
  `status` enum('pending','diproses','selesai','batal') DEFAULT 'pending',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_transaksi` varchar(20) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `nama_non_member` varchar(100) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `total_poin` int(11) DEFAULT 0,
  `total_uang` decimal(12,2) DEFAULT 0.00,
  `total_berat` decimal(10,2) DEFAULT 0.00,
  `catatan` text DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  `status` enum('pending','selesai','batal') DEFAULT 'selesai',
  `tipe_transaksi` enum('langsung','setor_sendiri') DEFAULT 'langsung',
  `jadwal_setor` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trashsmart_devices`
--

CREATE TABLE `trashsmart_devices` (
  `id` int(11) NOT NULL,
  `kode_perangkat` varchar(50) NOT NULL,
  `nama_perangkat` varchar(100) NOT NULL,
  `lokasi` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('online','warning','offline','maintenance') DEFAULT 'online',
  `kapasitas_organik` int(11) DEFAULT 0,
  `kapasitas_anorganik` int(11) DEFAULT 0,
  `baterai` int(11) DEFAULT 100,
  `suhu_celsius` decimal(4,1) DEFAULT 28.0,
  `firmware_version` varchar(20) DEFAULT 'v2.4.1',
  `total_pilah_count` int(11) DEFAULT 0,
  `total_berat_kg` decimal(10,2) DEFAULT 0.00,
  `voice_nudge_count` int(11) DEFAULT 0,
  `terakhir_dikosongkan` datetime DEFAULT NULL,
  `last_ping` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trashsmart_devices`
--

INSERT INTO `trashsmart_devices` (`id`, `kode_perangkat`, `nama_perangkat`, `lokasi`, `latitude`, `longitude`, `status`, `kapasitas_organik`, `kapasitas_anorganik`, `baterai`, `suhu_celsius`, `firmware_version`, `total_pilah_count`, `total_berat_kg`, `voice_nudge_count`, `terakhir_dikosongkan`, `last_ping`, `created_at`) VALUES
(1, 'TS-BGR-001', 'TrashSmart Kantin SMA 1 Bogor', 'Kantin Sekolah Hijau, SMAN 1 Bogor (Area Depan Gazebo)', -6.59714700, 106.79947800, 'online', 48, 62, 94, 27.8, 'v2.4.2', 142, 18.75, 19, '2026-10-10 08:30:00', '2026-10-11 18:21:44', '2026-10-12 01:24:44'),
(2, 'TS-BGR-002', 'TrashSmart Taman Sempur EcoHub', 'Taman Sempur Bogor, Dekat Lapangan Basket & Jalur Jogging', -6.59123000, 106.79785000, 'warning', 65, 92, 82, 30.2, 'v2.4.2', 285, 34.60, 43, '2026-10-09 09:00:00', '2026-10-11 18:23:44', '2026-10-12 01:24:44'),
(3, 'TS-BGR-003', 'TrashSmart Stasiun Bogor Hall Barat', 'Stasiun Bogor, Pintu Masuk Tap-In Hall Barat', -6.59543000, 106.78912000, 'online', 38, 45, 100, 26.5, 'v2.4.1', 360, 42.10, 31, '2026-10-11 06:00:00', '2026-10-11 18:24:44', '2026-10-12 01:24:44'),
(4, 'TS-BGR-004', 'TrashSmart Botani Square Food Terrace', 'Mall Botani Square Lt. 2, Koridor Food Terrace & Atrium', -6.60145000, 106.80562000, 'online', 70, 58, 91, 25.4, 'v2.4.2', 198, 26.80, 24, '2026-10-11 07:15:00', '2026-10-11 18:19:44', '2026-10-12 01:24:44'),
(5, 'TS-BGR-005', 'TrashSmart Kantor DLH Kota Bogor', 'Lobi Utama Kantor Dinas Lingkungan Hidup, Jl. Paledang', -6.60381000, 106.79412000, 'online', 22, 31, 97, 26.8, 'v2.4.2', 86, 9.40, 7, '2026-10-11 08:00:00', '2026-10-11 18:22:44', '2026-10-12 01:24:44'),
(6, 'TS-BGR-006', 'TrashSmart Kebun Raya Pintu 3', 'Kebun Raya Bogor, Area Tourist Information & Pintu 3', -6.60251000, 106.80121000, 'maintenance', 15, 20, 45, 29.1, 'v2.4.0', 112, 14.20, 15, '2026-10-08 10:00:00', '2026-10-11 16:24:44', '2026-10-12 01:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `trashsmart_logs`
--

CREATE TABLE `trashsmart_logs` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `kategori_sampah` enum('organik','anorganik','residu','sistem') DEFAULT 'organik',
  `status_pilah` enum('benar','keliru','peringatan','sistem') DEFAULT 'benar',
  `berat_kg` decimal(8,3) DEFAULT 0.000,
  `poin` int(11) DEFAULT 0,
  `pesan_suara` varchar(255) DEFAULT NULL,
  `user_identifier` varchar(100) DEFAULT 'Guest / Siswa',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trashsmart_logs`
--

INSERT INTO `trashsmart_logs` (`id`, `device_id`, `tanggal`, `waktu`, `kategori_sampah`, `status_pilah`, `berat_kg`, `poin`, `pesan_suara`, `user_identifier`, `created_at`) VALUES
(1, 1, '2026-10-11', '19:00:44', 'anorganik', 'keliru', 0.140, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Staf Tenant Botani Square', '2026-10-11 19:00:44'),
(2, 1, '2026-10-11', '12:20:10', 'anorganik', 'benar', 0.220, 22, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Dewi Lestari (Member)', '2026-10-11 12:20:10'),
(3, 1, '2026-10-11', '12:20:10', 'anorganik', 'benar', 0.460, 46, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-11 12:20:10'),
(4, 1, '2026-10-11', '19:00:44', 'organik', 'benar', 0.700, 28, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-11 19:00:44'),
(5, 1, '2026-10-11', '16:45:12', 'organik', 'keliru', 0.480, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Pelari Pagi Taman Sempur', '2026-10-11 16:45:12'),
(6, 1, '2026-10-11', '15:10:05', 'organik', 'keliru', 0.490, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMKN 1', '2026-10-11 15:10:05'),
(7, 2, '2026-10-11', '11:58:30', 'anorganik', 'keliru', 0.420, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-11 11:58:30'),
(8, 2, '2026-10-11', '12:45:55', 'organik', 'benar', 0.800, 32, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pelari Pagi Taman Sempur', '2026-10-11 12:45:55'),
(9, 2, '2026-10-11', '12:45:55', 'organik', 'benar', 0.410, 16, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Rizky Pratama (App Trashily)', '2026-10-11 12:45:55'),
(10, 2, '2026-10-11', '14:25:18', 'organik', 'benar', 0.280, 11, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Staf Tenant Botani Square', '2026-10-11 14:25:18'),
(11, 2, '2026-10-11', '09:20:15', 'organik', 'keliru', 0.420, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Budi Santoso (Member)', '2026-10-11 09:20:15'),
(12, 2, '2026-10-11', '18:15:25', 'organik', 'benar', 0.580, 23, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Staf Tenant Botani Square', '2026-10-11 18:15:25'),
(13, 2, '2026-10-11', '08:45:33', 'anorganik', 'benar', 0.760, 76, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Rizky Pratama (App Trashily)', '2026-10-11 08:45:33'),
(14, 3, '2026-10-11', '12:20:10', 'anorganik', 'benar', 0.750, 75, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Staf Tenant Botani Square', '2026-10-11 12:20:10'),
(15, 3, '2026-10-11', '16:05:30', 'anorganik', 'benar', 0.160, 16, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pelari Pagi Taman Sempur', '2026-10-11 16:05:30'),
(16, 3, '2026-10-11', '16:45:12', 'organik', 'benar', 0.810, 32, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Budi Santoso (Member)', '2026-10-11 16:45:12'),
(17, 3, '2026-10-11', '14:25:18', 'organik', 'keliru', 0.380, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Pengunjung Umum', '2026-10-11 14:25:18'),
(18, 4, '2026-10-11', '09:20:15', 'organik', 'benar', 0.630, 25, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Pelari Pagi Taman Sempur', '2026-10-11 09:20:15'),
(19, 4, '2026-10-11', '08:02:11', 'organik', 'benar', 0.470, 18, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Staf Tenant Botani Square', '2026-10-11 08:02:11'),
(20, 4, '2026-10-11', '13:50:22', 'organik', 'keliru', 0.250, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Budi Santoso (Member)', '2026-10-11 13:50:22'),
(21, 4, '2026-10-11', '17:30:50', 'organik', 'keliru', 0.330, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Budi Santoso (Member)', '2026-10-11 17:30:50'),
(22, 5, '2026-10-11', '08:45:33', 'organik', 'benar', 0.710, 28, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Budi Santoso (Member)', '2026-10-11 08:45:33'),
(23, 5, '2026-10-11', '16:45:12', 'organik', 'benar', 0.380, 15, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMKN 1', '2026-10-11 16:45:12'),
(24, 5, '2026-10-11', '07:15:20', 'organik', 'benar', 0.760, 30, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-11 07:15:20'),
(25, 5, '2026-10-11', '17:30:50', 'anorganik', 'benar', 0.320, 32, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Dewi Lestari (Member)', '2026-10-11 17:30:50'),
(26, 5, '2026-10-11', '15:10:05', 'anorganik', 'benar', 0.210, 21, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-11 15:10:05'),
(27, 5, '2026-10-11', '11:58:30', 'organik', 'benar', 0.170, 6, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-11 11:58:30'),
(28, 6, '2026-10-11', '14:25:18', 'anorganik', 'benar', 0.830, 83, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Pengunjung Umum', '2026-10-11 14:25:18'),
(29, 6, '2026-10-11', '16:05:30', 'organik', 'benar', 0.830, 33, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pelari Pagi Taman Sempur', '2026-10-11 16:05:30'),
(30, 6, '2026-10-11', '16:05:30', 'organik', 'benar', 0.580, 23, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siswa SMKN 1', '2026-10-11 16:05:30'),
(31, 6, '2026-10-11', '13:10:40', 'anorganik', 'keliru', 0.180, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Staf Tenant Botani Square', '2026-10-11 13:10:40'),
(32, 1, '2026-10-10', '18:15:25', 'organik', 'benar', 0.580, 23, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-10 18:15:25'),
(33, 1, '2026-10-10', '12:45:55', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 12:45:55'),
(34, 1, '2026-10-10', '18:15:25', 'anorganik', 'benar', 0.310, 31, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Pengunjung Umum', '2026-10-10 18:15:25'),
(35, 1, '2026-10-10', '11:12:05', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 11:12:05'),
(36, 1, '2026-10-10', '18:15:25', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 18:15:25'),
(37, 2, '2026-10-10', '19:00:44', 'organik', 'benar', 0.250, 10, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Pengunjung Umum', '2026-10-10 19:00:44'),
(38, 2, '2026-10-10', '09:20:15', 'organik', 'keliru', 0.410, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Pengunjung Umum', '2026-10-10 09:20:15'),
(39, 2, '2026-10-10', '08:45:33', 'organik', 'benar', 0.670, 26, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siti Rahma (App Trashily)', '2026-10-10 08:45:33'),
(40, 2, '2026-10-10', '19:00:44', 'organik', 'benar', 0.600, 24, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-10 19:00:44'),
(41, 2, '2026-10-10', '12:45:55', 'anorganik', 'benar', 0.670, 67, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Budi Santoso (Member)', '2026-10-10 12:45:55'),
(42, 2, '2026-10-10', '10:05:42', 'organik', 'benar', 0.610, 24, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Pengunjung Umum', '2026-10-10 10:05:42'),
(43, 2, '2026-10-10', '16:45:12', 'organik', 'benar', 0.340, 13, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Rizky Pratama (App Trashily)', '2026-10-10 16:45:12'),
(44, 3, '2026-10-10', '08:02:11', 'organik', 'benar', 0.300, 12, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-10 08:02:11'),
(45, 3, '2026-10-10', '13:10:40', 'organik', 'benar', 0.450, 18, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-10 13:10:40'),
(46, 3, '2026-10-10', '14:25:18', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-10 14:25:18'),
(47, 3, '2026-10-10', '09:20:15', 'anorganik', 'benar', 0.840, 84, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMKN 1', '2026-10-10 09:20:15'),
(48, 3, '2026-10-10', '16:45:12', 'anorganik', 'benar', 0.600, 60, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Komuter KRL (NFC Tap)', '2026-10-10 16:45:12'),
(49, 3, '2026-10-10', '07:15:20', 'anorganik', 'keliru', 0.140, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Siswa SMKN 1', '2026-10-10 07:15:20'),
(50, 4, '2026-10-10', '13:10:40', 'organik', 'keliru', 0.330, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siswa SMKN 1', '2026-10-10 13:10:40'),
(51, 4, '2026-10-10', '16:05:30', 'organik', 'benar', 0.720, 28, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 16:05:30'),
(52, 4, '2026-10-10', '11:12:05', 'anorganik', 'benar', 0.280, 28, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Budi Santoso (Member)', '2026-10-10 11:12:05'),
(53, 4, '2026-10-10', '12:45:55', 'anorganik', 'keliru', 0.480, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Dewi Lestari (Member)', '2026-10-10 12:45:55'),
(54, 4, '2026-10-10', '17:30:50', 'anorganik', 'keliru', 0.310, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Dewi Lestari (Member)', '2026-10-10 17:30:50'),
(55, 4, '2026-10-10', '13:10:40', 'anorganik', 'benar', 0.440, 44, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 13:10:40'),
(56, 5, '2026-10-10', '10:05:42', 'organik', 'keliru', 0.210, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Dewi Lestari (Member)', '2026-10-10 10:05:42'),
(57, 5, '2026-10-10', '16:05:30', 'anorganik', 'benar', 0.540, 54, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-10 16:05:30'),
(58, 5, '2026-10-10', '07:15:20', 'organik', 'keliru', 0.240, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Siti Rahma (App Trashily)', '2026-10-10 07:15:20'),
(59, 5, '2026-10-10', '08:02:11', 'anorganik', 'benar', 0.260, 26, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-10 08:02:11'),
(60, 5, '2026-10-10', '18:15:25', 'organik', 'benar', 0.850, 34, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Rizky Pratama (App Trashily)', '2026-10-10 18:15:25'),
(61, 5, '2026-10-10', '09:20:15', 'anorganik', 'benar', 0.520, 52, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siti Rahma (App Trashily)', '2026-10-10 09:20:15'),
(62, 5, '2026-10-10', '08:02:11', 'anorganik', 'benar', 0.620, 62, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-10 08:02:11'),
(63, 6, '2026-10-10', '18:15:25', 'anorganik', 'benar', 0.550, 55, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Pelari Pagi Taman Sempur', '2026-10-10 18:15:25'),
(64, 6, '2026-10-10', '13:50:22', 'anorganik', 'benar', 0.280, 28, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-10 13:50:22'),
(65, 6, '2026-10-10', '10:35:19', 'organik', 'benar', 0.710, 28, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-10 10:35:19'),
(66, 6, '2026-10-10', '18:15:25', 'anorganik', 'keliru', 0.110, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-10 18:15:25'),
(67, 1, '2026-10-09', '13:10:40', 'anorganik', 'benar', 0.810, 81, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-09 13:10:40'),
(68, 1, '2026-10-09', '18:15:25', 'anorganik', 'benar', 0.610, 61, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-09 18:15:25'),
(69, 1, '2026-10-09', '14:25:18', 'anorganik', 'benar', 0.500, 50, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Budi Santoso (Member)', '2026-10-09 14:25:18'),
(70, 1, '2026-10-09', '08:02:11', 'organik', 'benar', 0.310, 12, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-09 08:02:11'),
(71, 1, '2026-10-09', '12:20:10', 'anorganik', 'benar', 0.410, 41, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Dewi Lestari (Member)', '2026-10-09 12:20:10'),
(72, 1, '2026-10-09', '13:10:40', 'anorganik', 'benar', 0.690, 69, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Siti Rahma (App Trashily)', '2026-10-09 13:10:40'),
(73, 2, '2026-10-09', '12:20:10', 'anorganik', 'benar', 0.560, 56, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Pengunjung Umum', '2026-10-09 12:20:10'),
(74, 2, '2026-10-09', '07:15:20', 'anorganik', 'keliru', 0.260, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 07:15:20'),
(75, 2, '2026-10-09', '10:35:19', 'organik', 'benar', 0.690, 27, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Pengunjung Umum', '2026-10-09 10:35:19'),
(76, 2, '2026-10-09', '08:45:33', 'organik', 'benar', 0.660, 26, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Rizky Pratama (App Trashily)', '2026-10-09 08:45:33'),
(77, 2, '2026-10-09', '10:35:19', 'organik', 'keliru', 0.370, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 10:35:19'),
(78, 2, '2026-10-09', '12:20:10', 'organik', 'benar', 0.450, 18, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Pengunjung Umum', '2026-10-09 12:20:10'),
(79, 3, '2026-10-09', '08:02:11', 'anorganik', 'keliru', 0.150, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siti Rahma (App Trashily)', '2026-10-09 08:02:11'),
(80, 3, '2026-10-09', '16:05:30', 'organik', 'benar', 0.630, 25, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siswa SMA 1 (Kartu RFID)', '2026-10-09 16:05:30'),
(81, 3, '2026-10-09', '12:20:10', 'organik', 'keliru', 0.250, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Staf Tenant Botani Square', '2026-10-09 12:20:10'),
(82, 3, '2026-10-09', '07:15:20', 'organik', 'keliru', 0.460, 0, 'Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!', 'Staf Tenant Botani Square', '2026-10-09 07:15:20'),
(83, 3, '2026-10-09', '09:20:15', 'anorganik', 'benar', 0.690, 69, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Siti Rahma (App Trashily)', '2026-10-09 09:20:15'),
(84, 3, '2026-10-09', '16:05:30', 'anorganik', 'benar', 0.150, 15, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Pelari Pagi Taman Sempur', '2026-10-09 16:05:30'),
(85, 3, '2026-10-09', '13:50:22', 'anorganik', 'benar', 0.690, 69, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Budi Santoso (Member)', '2026-10-09 13:50:22'),
(86, 4, '2026-10-09', '08:02:11', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 08:02:11'),
(87, 4, '2026-10-09', '12:20:10', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 12:20:10'),
(88, 4, '2026-10-09', '19:00:44', 'organik', 'benar', 0.370, 14, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Komuter KRL (NFC Tap)', '2026-10-09 19:00:44'),
(89, 4, '2026-10-09', '12:20:10', 'anorganik', 'benar', 0.170, 17, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Komuter KRL (NFC Tap)', '2026-10-09 12:20:10'),
(90, 4, '2026-10-09', '12:45:55', 'anorganik', 'benar', 0.320, 32, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siswa SMKN 1', '2026-10-09 12:45:55'),
(91, 5, '2026-10-09', '13:50:22', 'organik', 'keliru', 0.140, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Siswa SMKN 1', '2026-10-09 13:50:22'),
(92, 5, '2026-10-09', '12:45:55', 'organik', 'keliru', 0.420, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siti Rahma (App Trashily)', '2026-10-09 12:45:55'),
(93, 5, '2026-10-09', '16:45:12', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 16:45:12'),
(94, 5, '2026-10-09', '11:12:05', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 11:12:05'),
(95, 5, '2026-10-09', '17:30:50', 'anorganik', 'keliru', 0.280, 0, 'Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!', 'Dewi Lestari (Member)', '2026-10-09 17:30:50'),
(96, 5, '2026-10-09', '18:15:25', 'organik', 'benar', 0.840, 33, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Budi Santoso (Member)', '2026-10-09 18:15:25'),
(97, 5, '2026-10-09', '13:50:22', 'anorganik', 'keliru', 0.460, 0, 'Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali.', 'Siti Rahma (App Trashily)', '2026-10-09 13:50:22'),
(98, 6, '2026-10-09', '11:58:30', 'organik', 'benar', 0.770, 30, 'Terima kasih! Sampah organikmu masuk wadah yang tepat.', 'Siti Rahma (App Trashily)', '2026-10-09 11:58:30'),
(99, 6, '2026-10-09', '11:12:05', 'organik', 'benar', 0.690, 27, 'Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.', 'Staf Tenant Botani Square', '2026-10-09 11:12:05'),
(100, 6, '2026-10-09', '10:05:42', 'anorganik', 'benar', 0.790, 79, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Komuter KRL (NFC Tap)', '2026-10-09 10:05:42'),
(101, 6, '2026-10-09', '11:12:05', 'sistem', 'sistem', 0.000, 0, 'Status sistem normal. Baki inspeksi siap digunakan.', 'Sistem Otomatis IoT', '2026-10-09 11:12:05'),
(102, 6, '2026-10-09', '13:50:22', 'anorganik', 'benar', 0.770, 77, 'Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.', 'Siti Rahma (App Trashily)', '2026-10-09 13:50:22'),
(103, 6, '2026-10-09', '17:30:50', 'anorganik', 'benar', 0.560, 56, 'Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!', 'Staf Tenant Botani Square', '2026-10-09 17:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `komentar` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id`, `customer_id`, `nama`, `rating`, `komentar`, `status`, `admin_note`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 5, 'Siti Rahmawati', 5, 'Dulu sampah bekas cuma dibuang ke tempat sampah. Sekarang kumpulin, panggil kurir Trashily, langsung tukar poin jadi pulpen & alat tulis favorit!', 'approved', NULL, 1, '2026-10-11 14:12:00', '2026-08-17 10:00:00'),
(2, 3, 'Ahmad Hidayat', 5, 'Warkop kami menghasilkan puluhan kilo kardus & kaleng tiap minggu. Dengan Trashily, penimbangannya transparan banget dan poinnya bisa ditukar ke sabun untuk cuci piring!', 'approved', NULL, 1, '2026-10-11 14:12:00', '2026-08-17 10:05:00'),
(3, 4, 'Ammar Abdul Malik', 5, 'Sistem digitalnya keren banget! Riwayat setor tercatat rapi di dashboard. Sangat membantu program pemilahan sampah di lingkungan RT kami.', 'approved', NULL, 1, '2026-10-11 14:12:00', '2026-08-17 10:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `poin` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
(7, 'Abdul Kodir', 'kodirs@gmail.com', '$2y$10$mPWwN7dqXfrvD0Vpo8jjKePUAraccuoe8YQv1IGy/GphqD4o4NNby', '0895702633879', 'Pamoyanan', 'customer', 0, '2026-08-14 12:23:36'),
(12, 'Muhamad Elgy Fahri hidayat', 'muhamadelgyfahrihidayat@gmail.com', '$2y$10$hESSXwFAapiLQaqEiN9SEehPkdCa7mKVzwxCstmiHDe9hbSkJ6BmK', '0838111623746', 'Kp.bajarwar', 'customer', 58, '2026-10-11 14:48:28'),
(13, 'elgy aja', 'elgy@gmail.com', '$2y$10$DOdHNjTfMej16R2lqrBBV.U/L78OHWtMGuUYfRzA.Ct40ccLEC5O.', '083811623786', 'kp.ciwi', 'customer', 0, '2026-10-11 17:58:00'),
(15, 'diah teknik', 'muhamadelgyhidayat@gmail.com', '$2y$10$jRWxLzjKp2r0s46TwsOdJ.c6WEs.3QmefIGMDqZYP85kHsp0nommK', '085694944797', 'jalan raya badak putih', 'customer', 0, '2026-10-12 06:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `_t`
--

CREATE TABLE `_t` (
  `id` int(11) DEFAULT NULL,
  `v` int(11) DEFAULT NULL
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `everwear_jemput`
--
ALTER TABLE `everwear_jemput`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `everwear_transaksi`
--
ALTER TABLE `everwear_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `gembul_jadwal`
--
ALTER TABLE `gembul_jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hadiah`
--
ALTER TABLE `hadiah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jemput_detail`
--
ALTER TABLE `jemput_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jemput_sampah`
--
ALTER TABLE `jemput_sampah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jenis_sampah`
--
ALTER TABLE `jenis_sampah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `penukaran`
--
ALTER TABLE `penukaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `trashsmart_devices`
--
ALTER TABLE `trashsmart_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trashsmart_logs`
--
ALTER TABLE `trashsmart_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
-- Constraints for table `everwear_jemput`
--
ALTER TABLE `everwear_jemput`
  ADD CONSTRAINT `everwear_jemput_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `everwear_transaksi`
--
ALTER TABLE `everwear_transaksi`
  ADD CONSTRAINT `everwear_transaksi_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
