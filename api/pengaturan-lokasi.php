<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Function helper ambil pengaturan
function getPengaturan($conn) {
    $res = $conn->query("SELECT kunci, nilai FROM pengaturan");
    $data = [
        'trashily_lat'    => '-6.200000',
        'trashily_lng'    => '106.816666',
        'trashily_alamat' => 'Trashily Central Waste Bank',
        'tarif_per_km'    => '2000'
    ];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[$row['kunci']] = $row['nilai'];
        }
    }
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = getPengaturan($conn);
    echo json_encode([
        'success' => true,
        'data'    => [
            'lat'          => floatval($data['trashily_lat']),
            'lng'          => floatval($data['trashily_lng']),
            'alamat'       => $data['trashily_alamat'],
            'tarif_per_km' => floatval($data['tarif_per_km'])
        ]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Hanya admin yang dapat mengedit lokasi.']);
        exit;
    }

    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $lat    = trim($input['lat'] ?? $_POST['lat'] ?? '');
    $lng    = trim($input['lng'] ?? $_POST['lng'] ?? '');
    $alamat = trim($input['alamat'] ?? $_POST['alamat'] ?? '');
    $tarif  = floatval($input['tarif_per_km'] ?? $_POST['tarif_per_km'] ?? 2000);

    if (empty($lat) || empty($lng) || empty($alamat)) {
        echo json_encode(['success' => false, 'message' => 'Koordinat dan alamat tidak boleh kosong.']);
        exit;
    }

    $updates = [
        'trashily_lat'    => $lat,
        'trashily_lng'    => $lng,
        'trashily_alamat' => $alamat,
        'tarif_per_km'    => (string)$tarif
    ];

    foreach ($updates as $kunci => $nilai) {
        $stmt = $conn->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
        $stmt->bind_param('ss', $kunci, $nilai);
        $stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Lokasi & tarif Trashily berhasil disimpan!']);
    exit;
}
