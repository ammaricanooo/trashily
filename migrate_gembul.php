<?php
require_once __DIR__ . '/config/database.php';

echo "Membuat tabel gembul_jadwal...\n";

$sql = "
CREATE TABLE IF NOT EXISTS gembul_jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul_kegiatan VARCHAR(200) NOT NULL,
    kategori VARCHAR(50) DEFAULT 'Sekolah',
    tanggal DATE NOT NULL,
    waktu_mulai TIME NULL,
    waktu_selesai TIME NULL,
    lokasi VARCHAR(255) NOT NULL,
    target_peserta VARCHAR(150) NULL,
    kuota_peserta INT DEFAULT 0,
    deskripsi TEXT NULL,
    status ENUM('rencana','berjalan','selesai','dibatalkan') DEFAULT 'rencana',
    link_pendaftaran VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql)) {
    echo "✔ Tabel 'gembul_jadwal' berhasil disiapkan.\n";
} else {
    die("❌ Gagal membuat gembul_jadwal: " . $conn->error . "\n");
}

echo "Migrasi GEMBUL selesai!\n";
