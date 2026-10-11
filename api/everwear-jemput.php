<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $data['action'] ?? 'buat';
if ($action === 'batalkan') {
    $id = intval($data['id'] ?? 0);
    $uid = intval($_SESSION['user_id']);
    $stmt = $conn->prepare("UPDATE everwear_jemput SET status='dibatalkan' WHERE id=? AND customer_id=? AND status='pending'");
    $stmt->bind_param('ii', $id, $uid); $stmt->execute();
    echo json_encode($stmt->affected_rows ? ['success'=>true] : ['success'=>false,'message'=>'Permintaan sudah dikonfirmasi atau tidak ditemukan.']);
    exit;
}
if ($action !== 'buat') {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

$pcsLayak = intval($data['pcs_layak'] ?? 0);
$pcsRecycle = intval($data['pcs_recycle'] ?? 0);
$berat = floatval($data['berat_kg'] ?? 0);
$kategori = ($pcsLayak > 0 && $pcsRecycle > 0) ? 'campuran' : ($pcsLayak > 0 ? 'layak_pakai' : 'recycle');
$estimasi = ($pcsLayak + $pcsRecycle) . ' pcs';
$tanggal = $data['tanggal'] ?? '';
$jam = $data['jam'] ?? '';
$alamat = trim($data['alamat'] ?? '');
$catatan = trim($data['catatan'] ?? '');

if (($pcsLayak < 0 || $pcsRecycle < 0 || $berat <= 0) || ($pcsLayak === 0 && $pcsRecycle === 0) || !$tanggal || !$jam || !$alamat) {
    echo json_encode(['success' => false, 'message' => 'Data penjemputan tidak lengkap.']);
    exit;
}

if (strtotime($tanggal . ' ' . $jam) < time()) {
    echo json_encode(['success' => false, 'message' => 'Jadwal penjemputan harus di masa depan.']);
    exit;
}

$res = $conn->query("SELECT kunci, nilai FROM pengaturan WHERE kunci IN ('trashily_lat', 'trashily_lng', 'tarif_per_km')");
$settings = ['trashily_lat' => -6.2, 'trashily_lng' => 106.816666, 'tarif_per_km' => 2500];
if ($res) while ($row = $res->fetch_assoc()) $settings[$row['kunci']] = $row['nilai'];

$lat = floatval($data['lat'] ?? 0);
$lng = floatval($data['lng'] ?? 0);

function haversine_km($lat1, $lon1, $lat2, $lon2) {
    $R = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
}

$km = haversine_km(floatval($settings['trashily_lat']), floatval($settings['trashily_lng']), $lat, $lng);
$ongkir = (int) round($km * intval($settings['tarif_per_km']));

$uid = intval($_SESSION['user_id']);
$kode = generateKode('EVJ');

$stmt = $conn->prepare("INSERT INTO everwear_jemput (kode_jemput, customer_id, kategori_pakaian, estimasi_jumlah, pcs_layak, pcs_recycle, berat_kg, alamat_penjemputan, catatan_lokasi, latitude, longitude, jarak_km, biaya_ongkir, tanggal_jemput, jam_jemput) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sissiidssdddiss', $kode, $uid, $kategori, $estimasi, $pcsLayak, $pcsRecycle, $berat, $alamat, $catatan, $lat, $lng, $km, $ongkir, $tanggal, $jam);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Permintaan jemput gagal disimpan.']);
    exit;
}

echo json_encode(['success' => true, 'kode' => $kode, 'jarak_km' => round($km, 1), 'biaya_ongkir' => $ongkir]);
