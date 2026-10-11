<?php
require_once __DIR__ . '/config/database.php';

echo "Membuat tabel trashsmart_devices & trashsmart_logs...\n";

// 1. Table trashsmart_devices
$sqlDevices = "
CREATE TABLE IF NOT EXISTS trashsmart_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_perangkat VARCHAR(50) UNIQUE NOT NULL,
    nama_perangkat VARCHAR(100) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    status ENUM('online', 'warning', 'offline', 'maintenance') DEFAULT 'online',
    kapasitas_organik INT DEFAULT 0,
    kapasitas_anorganik INT DEFAULT 0,
    baterai INT DEFAULT 100,
    suhu_celsius DECIMAL(4,1) DEFAULT 28.0,
    firmware_version VARCHAR(20) DEFAULT 'v2.4.1',
    total_pilah_count INT DEFAULT 0,
    total_berat_kg DECIMAL(10,2) DEFAULT 0.00,
    voice_nudge_count INT DEFAULT 0,
    terakhir_dikosongkan DATETIME NULL,
    last_ping DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sqlDevices)) {
    echo "✔ Tabel 'trashsmart_devices' berhasil disiapkan.\n";
} else {
    die("❌ Gagal membuat trashsmart_devices: " . $conn->error . "\n");
}

// 2. Table trashsmart_logs
$sqlLogs = "
CREATE TABLE IF NOT EXISTS trashsmart_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    kategori_sampah ENUM('organik', 'anorganik', 'residu', 'sistem') DEFAULT 'organik',
    status_pilah ENUM('benar', 'keliru', 'peringatan', 'sistem') DEFAULT 'benar',
    berat_kg DECIMAL(8,3) DEFAULT 0.000,
    poin INT DEFAULT 0,
    pesan_suara VARCHAR(255) NULL,
    user_identifier VARCHAR(100) DEFAULT 'Guest / Siswa',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_device_tanggal (device_id, tanggal),
    CONSTRAINT fk_trashsmart_device FOREIGN KEY (device_id) REFERENCES trashsmart_devices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sqlLogs)) {
    echo "✔ Tabel 'trashsmart_logs' berhasil disiapkan.\n";
} else {
    die("❌ Gagal membuat trashsmart_logs: " . $conn->error . "\n");
}

// 3. Seed Dummy Devices jika kosong
$cekDevices = $conn->query("SELECT COUNT(*) as c FROM trashsmart_devices")->fetch_assoc()['c'];
if ($cekDevices == 0) {
    echo "Menambahkan dummy data perangkat TrashSmart...\n";

    $dummyDevices = [
        [
            'kode_perangkat'     => 'TS-BGR-001',
            'nama_perangkat'     => 'TrashSmart Kantin SMA 1 Bogor',
            'lokasi'             => 'Kantin Sekolah Hijau, SMAN 1 Bogor (Area Depan Gazebo)',
            'latitude'           => -6.597147,
            'longitude'          => 106.799478,
            'status'             => 'online',
            'kapasitas_organik'   => 48,
            'kapasitas_anorganik' => 62,
            'baterai'            => 94,
            'suhu_celsius'       => 27.8,
            'firmware_version'   => 'v2.4.2',
            'total_pilah_count'  => 142,
            'total_berat_kg'     => 18.75,
            'voice_nudge_count'  => 19,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('-1 day 08:30:00')),
            'last_ping'          => date('Y-m-d H:i:s', strtotime('-3 minutes'))
        ],
        [
            'kode_perangkat'     => 'TS-BGR-002',
            'nama_perangkat'     => 'TrashSmart Taman Sempur EcoHub',
            'lokasi'             => 'Taman Sempur Bogor, Dekat Lapangan Basket & Jalur Jogging',
            'latitude'           => -6.591230,
            'longitude'          => 106.797850,
            'status'             => 'warning', // Wadah anorganik 92%
            'kapasitas_organik'   => 65,
            'kapasitas_anorganik' => 92,
            'baterai'            => 82,
            'suhu_celsius'       => 30.2,
            'firmware_version'   => 'v2.4.2',
            'total_pilah_count'  => 285,
            'total_berat_kg'     => 34.60,
            'voice_nudge_count'  => 43,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('-2 days 09:00:00')),
            'last_ping'          => date('Y-m-d H:i:s', strtotime('-1 minute'))
        ],
        [
            'kode_perangkat'     => 'TS-BGR-003',
            'nama_perangkat'     => 'TrashSmart Stasiun Bogor Hall Barat',
            'lokasi'             => 'Stasiun Bogor, Pintu Masuk Tap-In Hall Barat',
            'latitude'           => -6.595430,
            'longitude'          => 106.789120,
            'status'             => 'online',
            'kapasitas_organik'   => 38,
            'kapasitas_anorganik' => 45,
            'baterai'            => 100, // Sambungan adaptor DC
            'suhu_celsius'       => 26.5,
            'firmware_version'   => 'v2.4.1',
            'total_pilah_count'  => 360,
            'total_berat_kg'     => 42.10,
            'voice_nudge_count'  => 31,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('today 06:00:00')),
            'last_ping'          => date('Y-m-d H:i:s')
        ],
        [
            'kode_perangkat'     => 'TS-BGR-004',
            'nama_perangkat'     => 'TrashSmart Botani Square Food Terrace',
            'lokasi'             => 'Mall Botani Square Lt. 2, Koridor Food Terrace & Atrium',
            'latitude'           => -6.601450,
            'longitude'          => 106.805620,
            'status'             => 'online',
            'kapasitas_organik'   => 70,
            'kapasitas_anorganik' => 58,
            'baterai'            => 91,
            'suhu_celsius'       => 25.4,
            'firmware_version'   => 'v2.4.2',
            'total_pilah_count'  => 198,
            'total_berat_kg'     => 26.80,
            'voice_nudge_count'  => 24,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('today 07:15:00')),
            'last_ping'          => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ],
        [
            'kode_perangkat'     => 'TS-BGR-005',
            'nama_perangkat'     => 'TrashSmart Kantor DLH Kota Bogor',
            'lokasi'             => 'Lobi Utama Kantor Dinas Lingkungan Hidup, Jl. Paledang',
            'latitude'           => -6.603810,
            'longitude'          => 106.794120,
            'status'             => 'online',
            'kapasitas_organik'   => 22,
            'kapasitas_anorganik' => 31,
            'baterai'            => 97,
            'suhu_celsius'       => 26.8,
            'firmware_version'   => 'v2.4.2',
            'total_pilah_count'  => 86,
            'total_berat_kg'     => 9.40,
            'voice_nudge_count'  => 7,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('today 08:00:00')),
            'last_ping'          => date('Y-m-d H:i:s', strtotime('-2 minutes'))
        ],
        [
            'kode_perangkat'     => 'TS-BGR-006',
            'nama_perangkat'     => 'TrashSmart Kebun Raya Pintu 3',
            'lokasi'             => 'Kebun Raya Bogor, Area Tourist Information & Pintu 3',
            'latitude'           => -6.602510,
            'longitude'          => 106.801210,
            'status'             => 'maintenance',
            'kapasitas_organik'   => 15,
            'kapasitas_anorganik' => 20,
            'baterai'            => 45,
            'suhu_celsius'       => 29.1,
            'firmware_version'   => 'v2.4.0',
            'total_pilah_count'  => 112,
            'total_berat_kg'     => 14.20,
            'voice_nudge_count'  => 15,
            'terakhir_dikosongkan' => date('Y-m-d H:i:s', strtotime('-3 days 10:00:00')),
            'last_ping'          => date('Y-m-d H:i:s', strtotime('-2 hours'))
        ]
    ];

    $stmt = $conn->prepare("
        INSERT INTO trashsmart_devices (
            kode_perangkat, nama_perangkat, lokasi, latitude, longitude,
            status, kapasitas_organik, kapasitas_anorganik, baterai, suhu_celsius,
            firmware_version, total_pilah_count, total_berat_kg, voice_nudge_count,
            terakhir_dikosongkan, last_ping
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($dummyDevices as $d) {
        $stmt->bind_param(
            'sssddsiiidsidiss',
            $d['kode_perangkat'],
            $d['nama_perangkat'],
            $d['lokasi'],
            $d['latitude'],
            $d['longitude'],
            $d['status'],
            $d['kapasitas_organik'],
            $d['kapasitas_anorganik'],
            $d['baterai'],
            $d['suhu_celsius'],
            $d['firmware_version'],
            $d['total_pilah_count'],
            $d['total_berat_kg'],
            $d['voice_nudge_count'],
            $d['terakhir_dikosongkan'],
            $d['last_ping']
        );
        $stmt->execute();
    }
    echo "✔ 6 Perangkat dummy TrashSmart berhasil ditambahkan.\n";
}

// 4. Seed Dummy Logs untuk 3 hari terakhir (Hari ini, kemarin, lusa kemarin)
$cekLogs = $conn->query("SELECT COUNT(*) as c FROM trashsmart_logs")->fetch_assoc()['c'];
if ($cekLogs == 0) {
    echo "Menambahkan dummy log telemetri dan aktivitas TrashSmart...\n";

    $deviceIds = [];
    $resDev = $conn->query("SELECT id, kode_perangkat FROM trashsmart_devices");
    while ($row = $resDev->fetch_assoc()) {
        $deviceIds[$row['kode_perangkat']] = $row['id'];
    }

    $voiceSuccess = [
        "Terima kasih! Sampah organikmu masuk wadah yang tepat.",
        "Hebat! Botol plastikmu telah tercatat dan poin ditambahkan ke akun Trashily.",
        "Pemilahan sempurna! Wadah terbuka dan poin berhasil diberikan.",
        "Terima kasih telah menjaga kebersihan lingkungan bersama TrashSmart!"
    ];

    $voiceNudge = [
        "Oops! Itu botol plastik, silakan masukkan ke pintu kuning anorganik ya!",
        "Perhatian: Wadah hijau khusus sisa makanan & organik. Yuk pindahkan ke kuning!",
        "Mohon maaf, terdeteksi wadah keliru. Baki inspeksi dibuka kembali."
    ];

    $voiceSystem = [
        "Status sistem normal. Baki inspeksi siap digunakan.",
        "Peringatan: Wadah anorganik mendekati kapasitas maksimal (90%). Segera kosongkan.",
        "Kalibrasi sensor beban otomatis selesai."
    ];

    $stmtLog = $conn->prepare("
        INSERT INTO trashsmart_logs (
            device_id, tanggal, waktu, kategori_sampah, status_pilah,
            berat_kg, poin, pesan_suara, user_identifier, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $dates = [
        date('Y-m-d'),                     // Hari ini
        date('Y-m-d', strtotime('-1 day')), // Kemarin
        date('Y-m-d', strtotime('-2 days')) // 2 hari lalu
    ];

    $times = [
        '07:15:20', '08:02:11', '08:45:33', '09:20:15', '10:05:42',
        '10:35:19', '11:12:05', '11:58:30', '12:20:10', '12:45:55',
        '13:10:40', '13:50:22', '14:25:18', '15:10:05', '16:05:30',
        '16:45:12', '17:30:50', '18:15:25', '19:00:44'
    ];

    $users = [
        'Siswa SMA 1 (Kartu RFID)', 'Rizky Pratama (App Trashily)',
        'Siti Rahma (App Trashily)', 'Pengunjung Umum', 'Budi Santoso (Member)',
        'Dewi Lestari (Member)', 'Siswa SMKN 1', 'Pelari Pagi Taman Sempur',
        'Komuter KRL (NFC Tap)', 'Staf Tenant Botani Square'
    ];

    $countInserted = 0;
    foreach ($dates as $tgl) {
        foreach ($deviceIds as $kode => $devId) {
            // Generate 4-7 log per perangkat per hari
            $logsPerDay = rand(4, 7);
            for ($i = 0; $i < $logsPerDay; $i++) {
                $waktu = $times[array_rand($times)];
                $rType = rand(1, 100);

                if ($rType <= 65) {
                    // Pemilahan Benar
                    $kat = (rand(1, 10) > 5) ? 'anorganik' : 'organik';
                    $status = 'benar';
                    $berat = round(mt_rand(15, 85) / 100, 3); // 0.15 - 0.85 kg
                    $poin = ($kat === 'anorganik') ? intval($berat * 100) : intval($berat * 40);
                    $pesan = $voiceSuccess[array_rand($voiceSuccess)];
                    $user = $users[array_rand($users)];
                } elseif ($rType <= 88) {
                    // Pemilahan Keliru (Voice Nudge!)
                    $kat = (rand(1, 10) > 5) ? 'anorganik' : 'organik';
                    $status = 'keliru';
                    $berat = round(mt_rand(10, 50) / 100, 3);
                    $poin = 0; // Tidak dapat poin saat keliru
                    $pesan = $voiceNudge[array_rand($voiceNudge)];
                    $user = $users[array_rand($users)];
                } else {
                    // Peringatan / Sistem Ping
                    $kat = 'sistem';
                    $status = ($kode === 'TS-BGR-002') ? 'peringatan' : 'sistem';
                    $berat = 0;
                    $poin = 0;
                    $pesan = ($status === 'peringatan') ? $voiceSystem[1] : $voiceSystem[0];
                    $user = 'Sistem Otomatis IoT';
                }

                $createdAt = $tgl . ' ' . $waktu;
                $stmtLog->bind_param(
                    'issssdisss',
                    $devId,
                    $tgl,
                    $waktu,
                    $kat,
                    $status,
                    $berat,
                    $poin,
                    $pesan,
                    $user,
                    $createdAt
                );
                $stmtLog->execute();
                $countInserted++;
            }
        }
    }
    echo "✔ Total $countInserted data log telemetri TrashSmart berhasil di-seed.\n";
}

echo "Migrasi & seeding TrashSmart selesai!\n";
?>
