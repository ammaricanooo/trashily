-- phpMyAdmin SQL Dump — dimodifikasi untuk kompatibilitas production
-- Collation  : utf8mb4_unicode_ci (kompatibel MySQL 5.7+ & MariaDB)
-- CREATE TABLE pakai IF NOT EXISTS
-- INSERT pakai IGNORE (aman dijalankan berulang)
-- PRIMARY KEY, INDEX, AUTO_INCREMENT langsung di dalam CREATE TABLE

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------
-- users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         int            NOT NULL AUTO_INCREMENT,
  `nama`       varchar(100)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `email`      varchar(100)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `password`   varchar(255)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp`      varchar(20)    COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat`     text           COLLATE utf8mb4_unicode_ci,
  `role`       enum('admin','customer') COLLATE utf8mb4_unicode_ci DEFAULT 'customer',
  `poin`       int            DEFAULT '0',
  `created_at` timestamp      NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`, `poin`, `created_at`) VALUES
(1, 'Admin Utama',       'admin@trashily.id',   '$2y$10$npl9/vU8phcedK52LMjgqe8xf89r46Acjy7yNYF4Mwcqv7Y9.F0Ka', '081234567890',  NULL,                'admin',    0,   '2026-08-14 11:00:17'),
(2, 'Budi Santoso',      'budi@gmail.com',      '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '081111111111',  'Jl. Mawar No. 10',  'customer', 150, '2026-08-14 11:00:17'),
(3, 'Siti Rahayu',       'siti@gmail.com',      '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '082222222222',  'Jl. Melati No. 5',  'customer', 320, '2026-08-14 11:00:17'),
(4, 'Andi Wijaya',       'andi@gmail.com',      '$2y$10$PP3b6R8pxOQHrNxW92BLUeccczhBeRp/C0kUcWY0hj.UV.zmg0Dm6', '083333333333',  'Jl. Kenanga No. 8', 'customer', 80,  '2026-08-14 11:00:17'),
(5, 'Ammar Abdul Malik', 'ammarithm@gmail.com', '$2y$10$gp9KgL7pe.UYx1fsqYghZuvBaRQe428uABVX63bX79lkBwYla4d/a', '0895702633030', 'CIkaret',           'customer', 86,  '2026-08-14 12:21:53'),
(6, 'Rafli Al Bukhori',  'rafli@gmail.com',     '$2y$10$LrUSJmc1bBLDn2tlllq37uesrz24E7WnJF33ojgmvaKkrEvHiwfi6', '0895702633078', 'CIkaret',           'customer', 0,   '2026-08-14 12:22:53'),
(7, 'Abdul Kodir',       'kodirs@gmail.com',    '$2y$10$mPWwN7dqXfrvD0Vpo8jjKePUAraccuoe8YQv1IGy/GphqD4o4NNby', '0895702633879', 'Pamoyanan',         'customer', 0,   '2026-08-14 12:23:36');

-- --------------------------------------------------------
-- jenis_sampah
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jenis_sampah` (
  `id`           int            NOT NULL AUTO_INCREMENT,
  `nama`         varchar(100)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori`     enum('organik_kering','plastik','kertas','logam','kaca','elektronik','lainnya') COLLATE utf8mb4_unicode_ci NOT NULL,
  `poin_per_kg`  decimal(10,2)  NOT NULL,
  `harga_per_kg` decimal(10,2)  DEFAULT '0.00',
  `deskripsi`    text           COLLATE utf8mb4_unicode_ci,
  `is_active`    tinyint(1)     DEFAULT '1',
  `created_at`   timestamp      NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `jenis_sampah` (`id`, `nama`, `kategori`, `poin_per_kg`, `harga_per_kg`, `deskripsi`, `is_active`, `created_at`) VALUES
(2,  'Ranting & Kayu Kecil', 'organik_kering', '5.00',  '500.00',  'Ranting pohon dan kayu kecil kering',      1, '2026-08-14 11:00:17'),
(3,  'Botol Plastik PET',    'plastik',         '20.00', '2000.00', 'Botol plastik bening bekas minuman',       1, '2026-08-14 11:00:17'),
(4,  'Plastik Kresek',       'plastik',         '8.00',  '800.00',  'Kantong plastik kresek',                  1, '2026-08-14 11:00:17'),
(5,  'Kardus & Karton',      'kertas',          '15.00', '1500.00', 'Kardus bekas dan karton',                 1, '2026-08-14 11:00:17'),
(6,  'Kertas HVS',           'kertas',          '12.00', '1200.00', 'Kertas putih bekas',                      1, '2026-08-14 11:00:17'),
(7,  'Kaleng Aluminium',     'logam',           '35.00', '3500.00', 'Kaleng minuman atau makanan',             1, '2026-08-14 11:00:17'),
(9,  'Botol Kaca',           'kaca',            '10.00', '1000.00', 'Botol dan wadah kaca',                    1, '2026-08-14 11:00:17'),
(11, 'Daun Kering',          'organik_kering',  '5.00',  '500.00',  'Daun-daun kering yang sudah gugur',       1, '2026-08-17 10:36:15'),
(12, 'Ranting & Kayu Kecil', 'organik_kering',  '5.00',  '500.00',  'Ranting pohon dan kayu kecil kering',    1, '2026-08-17 10:36:15'),
(13, 'Botol Plastik PET',    'plastik',         '20.00', '2000.00', 'Botol plastik bening bekas minuman',      1, '2026-08-17 10:36:15'),
(14, 'Plastik Kresek',       'plastik',         '8.00',  '800.00',  'Kantong plastik kresek',                  1, '2026-08-17 10:36:15'),
(15, 'Kardus & Karton',      'kertas',          '15.00', '1500.00', 'Kardus bekas dan karton',                 1, '2026-08-17 10:36:15'),
(16, 'Kertas HVS',           'kertas',          '12.00', '1200.00', 'Kertas putih bekas',                      1, '2026-08-17 10:36:15'),
(17, 'Kaleng Aluminium',     'logam',           '35.00', '3500.00', 'Kaleng minuman atau makanan',             1, '2026-08-17 10:36:15'),
(18, 'Besi & Baja',          'logam',           '25.00', '2500.00', 'Besi dan baja bekas',                     1, '2026-08-17 10:36:15'),
(19, 'Botol Kaca',           'kaca',            '10.00', '1000.00', 'Botol dan wadah kaca',                    1, '2026-08-17 10:36:15'),
(20, 'Elektronik Bekas',     'elektronik',      '50.00', '5000.00', 'Perangkat elektronik tidak terpakai',    1, '2026-08-17 10:36:15');

-- --------------------------------------------------------
-- hadiah
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `hadiah` (
  `id`              int           NOT NULL AUTO_INCREMENT,
  `nama`            varchar(100)  COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi`       text          COLLATE utf8mb4_unicode_ci,
  `poin_dibutuhkan` int           NOT NULL,
  `stok`            int           DEFAULT '0',
  `gambar`          varchar(255)  COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active`       tinyint(1)    DEFAULT '1',
  `created_at`      timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `hadiah` (`id`, `nama`, `deskripsi`, `poin_dibutuhkan`, `stok`, `gambar`, `is_active`, `created_at`) VALUES
(1,  'Pulpen',              'Pulpen pilot warna hitam',          20,  100, NULL, 1, '2026-08-14 11:00:17'),
(2,  'Buku Tulis',          'Buku tulis 58 lembar',              50,  50,  NULL, 1, '2026-08-14 11:00:17'),
(3,  'Sabun Mandi',         'Sabun mandi batang',                75,  30,  NULL, 1, '2026-08-14 11:00:17'),
(4,  'Minyak Goreng 1L',    'Minyak goreng kemasan 1 liter',     150, 20,  NULL, 1, '2026-08-14 11:00:17'),
(5,  'Detergen 500gr',      'Detergen bubuk 500 gram',           100, 40,  NULL, 1, '2026-08-14 11:00:17'),
(6,  'Payung',              'Payung lipat anti hujan',           200, 15,  NULL, 1, '2026-08-14 11:00:17'),
(7,  'Tas Belanja',         'Tas belanja ramah lingkungan',      120, 25,  NULL, 1, '2026-08-14 11:00:17'),
(8,  'Voucher Belanja 10rb','Voucher belanja senilai Rp 10.000', 250, 10,  NULL, 1, '2026-08-14 11:00:17'),
(9,  'Pulpen',              'Pulpen pilot warna hitam',          20,  100, NULL, 1, '2026-08-17 10:36:15'),
(10, 'Buku Tulis',          'Buku tulis 58 lembar',              50,  50,  NULL, 1, '2026-08-17 10:36:15'),
(11, 'Sabun Mandi',         'Sabun mandi batang',                75,  30,  NULL, 1, '2026-08-17 10:36:15'),
(12, 'Minyak Goreng 1L',    'Minyak goreng kemasan 1 liter',     150, 20,  NULL, 1, '2026-08-17 10:36:15'),
(13, 'Detergen 500gr',      'Detergen bubuk 500 gram',           100, 40,  NULL, 1, '2026-08-17 10:36:15'),
(14, 'Payung',              'Payung lipat anti hujan',           200, 15,  NULL, 1, '2026-08-17 10:36:15'),
(15, 'Tas Belanja',         'Tas belanja ramah lingkungan',      120, 25,  NULL, 1, '2026-08-17 10:36:15'),
(16, 'Voucher Belanja 10rb','Voucher belanja senilai Rp 10.000', 250, 10,  NULL, 1, '2026-08-17 10:36:15');

-- --------------------------------------------------------
-- transaksi
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id`              int           NOT NULL AUTO_INCREMENT,
  `kode_transaksi`  varchar(20)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id`     int           DEFAULT NULL,
  `nama_non_member` varchar(100)  COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id`        int           DEFAULT NULL,
  `total_poin`      int           DEFAULT '0',
  `total_uang`      decimal(12,2) DEFAULT '0.00',
  `total_berat`     decimal(10,2) DEFAULT '0.00',
  `catatan`         text          COLLATE utf8mb4_unicode_ci,
  `catatan_admin`   text          COLLATE utf8mb4_unicode_ci,
  `status`          enum('pending','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'selesai',
  `tipe_transaksi`  enum('langsung','setor_sendiri') COLLATE utf8mb4_unicode_ci DEFAULT 'langsung',
  `jadwal_setor`    datetime      DEFAULT NULL,
  `created_at`      timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `transaksi` (`id`, `kode_transaksi`, `customer_id`, `nama_non_member`, `admin_id`, `total_poin`, `total_uang`, `total_berat`, `catatan`, `catatan_admin`, `status`, `tipe_transaksi`, `jadwal_setor`, `created_at`) VALUES
(1, 'TRX202608141111', 5, NULL, 1, 6,  '580.00',  '1.16', '', NULL, 'selesai', 'langsung', NULL, '2026-08-14 12:52:00'),
(2, 'TRX202608141264', 5, NULL, 1, 80, '8000.00', '4.00', '', NULL, 'selesai', 'langsung', NULL, '2026-08-14 13:06:38');

-- --------------------------------------------------------
-- detail_transaksi
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id`              int           NOT NULL AUTO_INCREMENT,
  `transaksi_id`    int           NOT NULL,
  `jenis_sampah_id` int           NOT NULL,
  `berat`           decimal(10,2) NOT NULL,
  `poin_per_kg`     decimal(10,2) NOT NULL,
  `harga_per_kg`    decimal(10,2) DEFAULT '0.00',
  `subtotal_poin`   int           NOT NULL,
  `subtotal_uang`   decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `transaksi_id` (`transaksi_id`),
  KEY `jenis_sampah_id` (`jenis_sampah_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `detail_transaksi` (`id`, `transaksi_id`, `jenis_sampah_id`, `berat`, `poin_per_kg`, `harga_per_kg`, `subtotal_poin`, `subtotal_uang`) VALUES
(1, 1, 2, '1.16', '5.00',  '500.00',  6,  '580.00'),
(2, 2, 3, '4.00', '20.00', '2000.00', 80, '8000.00');

-- --------------------------------------------------------
-- ulasan
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ulasan` (
  `id`          int           NOT NULL AUTO_INCREMENT,
  `customer_id` int           DEFAULT NULL,
  `nama`        varchar(100)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating`      tinyint        NOT NULL DEFAULT '5',
  `komentar`    text           COLLATE utf8mb4_unicode_ci NOT NULL,
  `status`      enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note`  text           COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_by` int           DEFAULT NULL,
  `reviewed_at` timestamp     NULL DEFAULT NULL,
  `created_at`  timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `ulasan` (`id`, `customer_id`, `nama`, `rating`, `komentar`, `status`, `admin_note`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(1, 5, 'Siti Rahmawati', 5, 'Dulu sampah bekas cuma dibuang ke tempat sampah. Sekarang kumpulin, panggil kurir Trashily, langsung tukar poin jadi pulpen & alat tulis favorit!', 'approved', NULL, 1, NOW(), '2026-08-17 10:00:00'),
(2, 3, 'Ahmad Hidayat', 5, 'Warkop kami menghasilkan puluhan kilo kardus & kaleng tiap minggu. Dengan Trashily, penimbangannya transparan banget dan poinnya bisa ditukar ke sabun untuk cuci piring!', 'approved', NULL, 1, NOW(), '2026-08-17 10:05:00'),
(3, 4, 'Ammar Abdul Malik', 5, 'Sistem digitalnya keren banget! Riwayat setor tercatat rapi di dashboard. Sangat membantu program pemilahan sampah di lingkungan RT kami.', 'approved', NULL, 1, NOW(), '2026-08-17 10:10:00');

-- --------------------------------------------------------
-- penukaran
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `penukaran` (
  `id`             int          NOT NULL AUTO_INCREMENT,
  `kode_penukaran` varchar(20)  COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id`    int          NOT NULL,
  `admin_id`       int          DEFAULT NULL,
  `hadiah_id`      int          NOT NULL,
  `poin_digunakan` int          NOT NULL,
  `status`         enum('pending','diproses','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `catatan`        text         COLLATE utf8mb4_unicode_ci,
  `created_at`     timestamp    NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     timestamp    NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_penukaran` (`kode_penukaran`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  KEY `hadiah_id` (`hadiah_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- jemput_sampah
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jemput_sampah` (
  `id`               int           NOT NULL AUTO_INCREMENT,
  `kode_jemput`      varchar(20)   COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id`      int           NOT NULL,
  `admin_id`         int           DEFAULT NULL,
  `transaksi_id`     int           DEFAULT NULL,
  `alamat_jemput`    text          COLLATE utf8mb4_unicode_ci NOT NULL,
  `jarak_km`         decimal(10,2) DEFAULT '0.00',
  `biaya_ongkir`     decimal(10,2) DEFAULT '0.00',
  `tarif_per_km`     decimal(10,2) DEFAULT '2000.00',
  `jadwal_jemput`    datetime      NOT NULL,
  `catatan_customer` text          COLLATE utf8mb4_unicode_ci,
  `catatan_admin`    text          COLLATE utf8mb4_unicode_ci,
  `status`           enum('menunggu','dikonfirmasi','dijemput','selesai','batal') COLLATE utf8mb4_unicode_ci DEFAULT 'menunggu',
  `created_at`       timestamp     NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       timestamp     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jemput` (`kode_jemput`),
  KEY `customer_id` (`customer_id`),
  KEY `admin_id` (`admin_id`),
  KEY `transaksi_id` (`transaksi_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `jemput_sampah` (`id`, `kode_jemput`, `customer_id`, `admin_id`, `transaksi_id`, `alamat_jemput`, `jarak_km`, `biaya_ongkir`, `tarif_per_km`, `jadwal_jemput`, `catatan_customer`, `catatan_admin`, `status`, `created_at`, `updated_at`) VALUES
(1, 'JMP202608140282', 5, 1, 1, 'Cikaret', '0.00', '0.00', '2000.00', '2026-08-14 20:50:00', '', '', 'selesai', '2026-08-14 12:42:05', '2026-08-14 12:52:00');

-- --------------------------------------------------------
-- jemput_detail
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jemput_detail` (
  `id`              int           NOT NULL AUTO_INCREMENT,
  `jemput_id`       int           NOT NULL,
  `jenis_sampah_id` int           NOT NULL,
  `est_berat`       decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jemput_id` (`jemput_id`),
  KEY `jenis_sampah_id` (`jenis_sampah_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `jemput_detail` (`id`, `jemput_id`, `jenis_sampah_id`, `est_berat`) VALUES
(1, 1, 2, '1.00');

-- --------------------------------------------------------
-- pengaturan
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan` (
  `kunci` varchar(50) NOT NULL,
  `nilai` text        NOT NULL,
  PRIMARY KEY (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pengaturan` (`kunci`, `nilai`) VALUES
('tarif_per_km',    '2500'),
('trashily_alamat', 'Gang Kosasih, Cikaret, Bogor Selatan, Kota Batu, Bogor, West Java, 16610, Indonesia'),
('trashily_lat',    '-6.624408'),
('trashily_lng',    '106.787575');

-- --------------------------------------------------------
-- Foreign key constraints
-- --------------------------------------------------------
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`transaksi_id`)    REFERENCES `transaksi`    (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah` (`id`);

ALTER TABLE `jemput_detail`
  ADD CONSTRAINT `jemput_detail_ibfk_1` FOREIGN KEY (`jemput_id`)       REFERENCES `jemput_sampah` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jemput_detail_ibfk_2` FOREIGN KEY (`jenis_sampah_id`) REFERENCES `jenis_sampah`  (`id`);

ALTER TABLE `jemput_sampah`
  ADD CONSTRAINT `jemput_sampah_ibfk_1` FOREIGN KEY (`customer_id`)  REFERENCES `users`     (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jemput_sampah_ibfk_2` FOREIGN KEY (`admin_id`)     REFERENCES `users`     (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jemput_sampah_ibfk_3` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE SET NULL;

ALTER TABLE `penukaran`
  ADD CONSTRAINT `penukaran_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users`  (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `penukaran_ibfk_2` FOREIGN KEY (`admin_id`)    REFERENCES `users`  (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `penukaran_ibfk_3` FOREIGN KEY (`hadiah_id`)   REFERENCES `hadiah` (`id`);

ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`admin_id`)    REFERENCES `users` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
