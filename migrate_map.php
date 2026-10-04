<?php
require_once 'config/database.php';

echo "Membuat tabel pengaturan...\n";

$sql = "
CREATE TABLE IF NOT EXISTS pengaturan (
    kunci VARCHAR(50) PRIMARY KEY,
    nilai TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql)) {
    echo "Tabel 'pengaturan' berhasil dibuat / disiapkan.\n";
} else {
    echo "Gagal membuat tabel pengaturan: " . $conn->error . "\n";
}

$defaults = [
    'trashily_lat'    => '-6.200000',
    'trashily_lng'    => '106.816666',
    'trashily_alamat' => 'Trashily Central Waste Bank, Jl. Green Eco No. 12',
    'tarif_per_km'    => '2000'
];

foreach ($defaults as $key => $val) {
    $stmt = $conn->prepare("INSERT IGNORE INTO pengaturan (kunci, nilai) VALUES (?, ?)");
    $stmt->bind_param('ss', $key, $val);
    $stmt->execute();
}

echo "Data lokasi default Trashily berhasil ditambahkan!\n";

// Cek data
$res = $conn->query("SELECT * FROM pengaturan");
while ($r = $res->fetch_assoc()) {
    echo " - " . $r['kunci'] . ": " . $r['nilai'] . "\n";
}
