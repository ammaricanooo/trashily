<?php
require_once __DIR__ . '/config/database.php';

echo "<h2>Migrasi Database Everwear...</h2>";

// 1. Tabel everwear_transaksi
$sql_transaksi = "CREATE TABLE IF NOT EXISTS everwear_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    kategori_pakaian ENUM('layak_pakai', 'recycle', 'campuran') NOT NULL,
    deskripsi_pakaian TEXT,
    jumlah_pcs INT DEFAULT 1,
    pcs_layak INT DEFAULT 0,
    pcs_recycle INT DEFAULT 0,
    berat_kg DECIMAL(8,2) DEFAULT 0.00,
    poin_diperoleh INT DEFAULT 0,
    status ENUM('pending', 'proses', 'selesai', 'dibatalkan') DEFAULT 'pending',
    catatan TEXT,
    jadwal_setor DATETIME,
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_transaksi)) {
    echo "<p>✅ Tabel <code>everwear_transaksi</code> berhasil dibuat / sudah ada.</p>";
} else {
    echo "<p>❌ Gagal membuat tabel <code>everwear_transaksi</code>: " . $conn->error . "</p>";
}

// 2. Tabel everwear_jemput
$sql_jemput = "CREATE TABLE IF NOT EXISTS everwear_jemput (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_jemput VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    kategori_pakaian ENUM('layak_pakai', 'recycle', 'campuran') NOT NULL,
    estimasi_jumlah VARCHAR(100),
    alamat_penjemputan TEXT NOT NULL,
    catatan_lokasi TEXT,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    jarak_km DECIMAL(8,2) DEFAULT 0,
    biaya_ongkir INT DEFAULT 0,
    tanggal_jemput DATE NOT NULL,
    jam_jemput TIME NOT NULL,
    status ENUM('pending', 'dikonfirmasi', 'dalam_perjalanan', 'selesai', 'dibatalkan') DEFAULT 'pending',
    catatan_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql_jemput)) {
    echo "<p>✅ Tabel <code>everwear_jemput</code> berhasil dibuat / sudah ada.</p>";
} else {
    echo "<p>❌ Gagal membuat tabel <code>everwear_jemput</code>: " . $conn->error . "</p>";
}

$columns = [
    'everwear_transaksi' => [
        'catatan' => "ALTER TABLE everwear_transaksi ADD COLUMN catatan TEXT NULL",
        'jadwal_setor' => "ALTER TABLE everwear_transaksi ADD COLUMN jadwal_setor DATETIME NULL",
        'kategori_pakaian-campur' => "ALTER TABLE everwear_transaksi MODIFY COLUMN kategori_pakaian ENUM('layak_pakai', 'recycle', 'campuran') NOT NULL",
        'pcs_layak' => "ALTER TABLE everwear_transaksi ADD COLUMN pcs_layak INT NOT NULL DEFAULT 0",
        'pcs_recycle' => "ALTER TABLE everwear_transaksi ADD COLUMN pcs_recycle INT NOT NULL DEFAULT 0",
        'verified_at' => "ALTER TABLE everwear_transaksi ADD COLUMN verified_at DATETIME NULL",
        'admin_id' => "ALTER TABLE everwear_transaksi ADD COLUMN admin_id INT NULL"
    ],
    'everwear_jemput' => [
        'latitude' => "ALTER TABLE everwear_jemput ADD COLUMN latitude DECIMAL(10,7) NULL",
        'longitude' => "ALTER TABLE everwear_jemput ADD COLUMN longitude DECIMAL(10,7) NULL",
        'jarak_km' => "ALTER TABLE everwear_jemput ADD COLUMN jarak_km DECIMAL(8,2) NOT NULL DEFAULT 0",
        'biaya_ongkir' => "ALTER TABLE everwear_jemput ADD COLUMN biaya_ongkir INT NOT NULL DEFAULT 0",
        'pcs_layak' => "ALTER TABLE everwear_jemput ADD COLUMN pcs_layak INT NOT NULL DEFAULT 0",
        'pcs_recycle' => "ALTER TABLE everwear_jemput ADD COLUMN pcs_recycle INT NOT NULL DEFAULT 0",
        'berat_kg' => "ALTER TABLE everwear_jemput ADD COLUMN berat_kg DECIMAL(8,2) NOT NULL DEFAULT 0",
        'poin_diperoleh' => "ALTER TABLE everwear_jemput ADD COLUMN poin_diperoleh INT NOT NULL DEFAULT 0",
        'admin_id' => "ALTER TABLE everwear_jemput ADD COLUMN admin_id INT NULL",
        'verified_at' => "ALTER TABLE everwear_jemput ADD COLUMN verified_at DATETIME NULL"
    ]
];

foreach ($columns as $table => $table_columns) {
    foreach ($table_columns as $column => $sql) {
        $exists = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($exists && $exists->num_rows === 0 && !$conn->query($sql)) {
            echo "<p>Gagal menambah $table.$column: " . htmlspecialchars($conn->error) . "</p>";
        }
    }
}

echo "<br><a href='everwear.php'>Kembali ke Everwear</a>";
