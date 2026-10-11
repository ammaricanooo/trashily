<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
if (($_SESSION['role'] ?? '') === 'admin') requireAdmin();
else requireCustomer();
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $data['action'] ?? 'buat';
if ($action === 'batalkan') {
    $id = intval($data['id'] ?? 0);
    if (($_SESSION['role'] ?? '') === 'admin') {
        $stmt = $conn->prepare("UPDATE everwear_transaksi SET status='dibatalkan' WHERE id=? AND status='pending'");
        $stmt->bind_param('i', $id);
    } else {
        $uid = intval($_SESSION['user_id']);
        $stmt = $conn->prepare("UPDATE everwear_transaksi SET status='dibatalkan' WHERE id=? AND customer_id=? AND status='pending'");
        $stmt->bind_param('ii', $id, $uid);
    }
    $stmt->execute();
    echo json_encode($stmt->affected_rows ? ['success' => true] : ['success' => false, 'message' => 'Donasi tidak bisa dibatalkan.']);
    exit;
}
if ($action !== 'buat') {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

$pcs_layak = intval($data['pcs_layak'] ?? 0);
$pcs_recycle = intval($data['pcs_recycle'] ?? 0);
$berat_kg = floatval($data['berat_kg'] ?? 0);
$deskripsi = trim($data['deskripsi'] ?? '');
$jadwal_setor = str_replace('T', ' ', trim($data['jadwal_setor'] ?? ''));
$jadwal_ts = strtotime($jadwal_setor);

if (($pcs_layak < 0 || $pcs_recycle < 0) || ($pcs_layak === 0 && $pcs_recycle === 0)) {
    echo json_encode(['success' => false, 'message' => 'Isi jumlah layak atau recycle.']);
    exit;
}
if ($berat_kg <= 0 || !$jadwal_ts || $jadwal_ts < time()) {
    echo json_encode(['success' => false, 'message' => 'Berat dan jadwal tidak valid.']);
    exit;
}

if ($pcs_layak > 0 && $pcs_recycle > 0) $kategori = 'campuran';
elseif ($pcs_layak > 0) $kategori = 'layak_pakai';
else $kategori = 'recycle';
$jumlah_pcs = $pcs_layak + $pcs_recycle;

$uid = intval($_SESSION['user_id']);
$kode = generateKode('EVR');

$stmt = $conn->prepare("INSERT INTO everwear_transaksi (kode_transaksi, customer_id, kategori_pakaian, deskripsi_pakaian, jumlah_pcs, berat_kg, pcs_layak, pcs_recycle, catatan, jadwal_setor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sissidiiss', $kode, $uid, $kategori, $deskripsi, $jumlah_pcs, $berat_kg, $pcs_layak, $pcs_recycle, $deskripsi, $jadwal_setor);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Donasi gagal disimpan. Silakan coba lagi.']);
    exit;
}

$message = 'Donasi Anda menunggu pemeriksaan. Pakaian layak disalurkan ke komunitas, yang tidak layak didata untuk recycle. Keduanya dapat poin.';

echo json_encode(['success' => true, 'kode' => $kode, 'message' => $message]);
