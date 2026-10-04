<?php
require_once __DIR__ . '/config/database.php';

echo "Running database migration...\n";

// 1. Modify admin_id in transaksi to allow NULL
$sql1 = "ALTER TABLE transaksi MODIFY COLUMN admin_id INT NULL";
if ($conn->query($sql1)) {
    echo "✔ admin_id in transaksi modified to NULLable.\n";
} else {
    echo "ℹ admin_id modify note: " . $conn->error . "\n";
}

// 2. Add jadwal_setor, tipe_transaksi, catatan_admin to transaksi table if not exist
$checkCol1 = $conn->query("SHOW COLUMNS FROM transaksi LIKE 'jadwal_setor'");
if ($checkCol1->num_rows === 0) {
    $sql2 = "ALTER TABLE transaksi ADD COLUMN jadwal_setor DATETIME NULL AFTER status";
    if ($conn->query($sql2)) {
        echo "✔ Column jadwal_setor added to transaksi.\n";
    } else {
        echo "❌ Failed adding jadwal_setor: " . $conn->error . "\n";
    }
}

$checkCol2 = $conn->query("SHOW COLUMNS FROM transaksi LIKE 'tipe_transaksi'");
if ($checkCol2->num_rows === 0) {
    $sql3 = "ALTER TABLE transaksi ADD COLUMN tipe_transaksi ENUM('langsung', 'setor_sendiri') DEFAULT 'langsung' AFTER status";
    if ($conn->query($sql3)) {
        echo "✔ Column tipe_transaksi added to transaksi.\n";
    } else {
        echo "❌ Failed adding tipe_transaksi: " . $conn->error . "\n";
    }
}

$checkCol3 = $conn->query("SHOW COLUMNS FROM transaksi LIKE 'catatan_admin'");
if ($checkCol3->num_rows === 0) {
    $sql4 = "ALTER TABLE transaksi ADD COLUMN catatan_admin TEXT NULL AFTER catatan";
    if ($conn->query($sql4)) {
        echo "✔ Column catatan_admin added to transaksi.\n";
    } else {
        echo "❌ Failed adding catatan_admin: " . $conn->error . "\n";
    }
}

// 3. Add jarak_km, biaya_ongkir, tarif_per_km to jemput_sampah table if not exist
$checkCol4 = $conn->query("SHOW COLUMNS FROM jemput_sampah LIKE 'jarak_km'");
if ($checkCol4->num_rows === 0) {
    $sql5 = "ALTER TABLE jemput_sampah ADD COLUMN jarak_km DECIMAL(10,2) DEFAULT 0.00 AFTER alamat_jemput";
    if ($conn->query($sql5)) {
        echo "✔ Column jarak_km added to jemput_sampah.\n";
    } else {
        echo "❌ Failed adding jarak_km: " . $conn->error . "\n";
    }
}

$checkCol5 = $conn->query("SHOW COLUMNS FROM jemput_sampah LIKE 'biaya_ongkir'");
if ($checkCol5->num_rows === 0) {
    $sql6 = "ALTER TABLE jemput_sampah ADD COLUMN biaya_ongkir DECIMAL(10,2) DEFAULT 0.00 AFTER jarak_km";
    if ($conn->query($sql6)) {
        echo "✔ Column biaya_ongkir added to jemput_sampah.\n";
    } else {
        echo "❌ Failed adding biaya_ongkir: " . $conn->error . "\n";
    }
}

$checkCol6 = $conn->query("SHOW COLUMNS FROM jemput_sampah LIKE 'tarif_per_km'");
if ($checkCol6->num_rows === 0) {
    $sql7 = "ALTER TABLE jemput_sampah ADD COLUMN tarif_per_km DECIMAL(10,2) DEFAULT 2000.00 AFTER biaya_ongkir";
    if ($conn->query($sql7)) {
        echo "✔ Column tarif_per_km added to jemput_sampah.\n";
    } else {
        echo "❌ Failed adding tarif_per_km: " . $conn->error . "\n";
    }
}

$checkTableUlasan = $conn->query("SHOW TABLES LIKE 'ulasan'");
if ($checkTableUlasan->num_rows === 0) {
    $sql8 = "CREATE TABLE ulasan (
        id INT NOT NULL AUTO_INCREMENT,
        customer_id INT DEFAULT NULL,
        nama VARCHAR(100) NOT NULL,
        rating TINYINT NOT NULL DEFAULT 5,
        komentar TEXT NOT NULL,
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        admin_note TEXT DEFAULT NULL,
        reviewed_by INT DEFAULT NULL,
        reviewed_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY customer_id (customer_id),
        KEY status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    if ($conn->query($sql8)) {
        echo "✔ Table ulasan created.\n";
    } else {
        echo "❌ Failed creating ulasan table: " . $conn->error . "\n";
    }
} else {
    echo "✔ Table ulasan already exists.\n";
}

echo "Migration script completed successfully!\n";
