<?php
require_once __DIR__ . '/config/database.php';

echo "=== Memulai Migrasi Database PIRU (Pilah Runtah) ===\n";

// 1. Buat Tabel piru_sessions
$sqlSessions = "
CREATE TABLE IF NOT EXISTS piru_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    device_kode VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    total_organik_gr DECIMAL(10,2) DEFAULT 0.00,
    total_anorganik_gr DECIMAL(10,2) DEFAULT 0.00,
    total_berat_gr DECIMAL(10,2) DEFAULT 0.00,
    total_poin INT DEFAULT 0,
    item_count INT DEFAULT 0,
    transaksi_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_device_status (device_kode, status),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_piru_session_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sqlSessions)) {
    echo "✓ Tabel 'piru_sessions' siap.\n";
} else {
    die("✗ Gagal membuat tabel piru_sessions: " . $conn->error . "\n");
}

// 2. Buat Tabel piru_session_items
$sqlItems = "
CREATE TABLE IF NOT EXISTS piru_session_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    category ENUM('ORGANIK', 'ANORGANIK') NOT NULL,
    weight_gr DECIMAL(10,2) NOT NULL,
    poin INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    CONSTRAINT fk_piru_item_session FOREIGN KEY (session_id) REFERENCES piru_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sqlItems)) {
    echo "✓ Tabel 'piru_session_items' siap.\n";
} else {
    die("✗ Gagal membuat tabel piru_session_items: " . $conn->error . "\n");
}

// 3. Pastikan Perangkat PIRU-DEV-001 terdaftar di trashsmart_devices
$checkDevice = $conn->query("SELECT id FROM trashsmart_devices WHERE kode_perangkat = 'PIRU-DEV-001'");
if ($checkDevice && $checkDevice->num_rows == 0) {
    $insertDevice = "
    INSERT INTO trashsmart_devices (
        kode_perangkat, nama_perangkat, lokasi, latitude, longitude, status,
        kapasitas_organik, kapasitas_anorganik, baterai, suhu_celsius,
        firmware_version, total_pilah_count, total_berat_kg, last_ping
    ) VALUES (
        'PIRU-DEV-001', 'PIRU Smart Bin #001', 'Area Kantin Utama / EcoHub',
        -6.597147, 106.799478, 'online', 25, 30, 100, 28.5,
        'v2.0.0-YOLO', 0, 0.00, NOW()
    );
    ";
    if ($conn->query($insertDevice)) {
        echo "✓ Perangkat 'PIRU-DEV-001' berhasil didaftarkan ke trashsmart_devices.\n";
    } else {
        echo "! Gagal mendaftarkan perangkat default: " . $conn->error . "\n";
    }
} else {
    echo "ℹ Perangkat 'PIRU-DEV-001' sudah ada di sistem.\n";
}

echo "=== Migrasi Database PIRU Selesai! ===\n";
