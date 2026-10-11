<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';
$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id = intval($data['id'] ?? 0);
$action = $data['action'] ?? '';
if (!$id) { echo json_encode(['success' => false, 'message' => 'Data tidak valid.']); exit; }
if ($action === 'batalkan') {
    $stmt = $conn->prepare("UPDATE everwear_jemput SET status='dibatalkan' WHERE id=? AND status='pending'");
    $stmt->bind_param('i', $id); $stmt->execute();
    echo json_encode($stmt->affected_rows ? ['success'=>true] : ['success'=>false,'message'=>'Permintaan sudah dikonfirmasi atau tidak ditemukan.']); exit;
}
$stmt = $conn->prepare('SELECT * FROM everwear_jemput WHERE id = ?');
$stmt->bind_param('i', $id); $stmt->execute(); $jemput = $stmt->get_result()->fetch_assoc();
if (!$jemput) { echo json_encode(['success' => false, 'message' => 'Permintaan tidak ditemukan.']); exit; }
if ($action === 'konfirmasi' && $jemput['status'] === 'pending') {
    $status = 'dikonfirmasi';
    $stmt = $conn->prepare('UPDATE everwear_jemput SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id); $stmt->execute();
    echo json_encode(['success' => true]); exit;
}
if ($action === 'dijemput' && $jemput['status'] === 'dikonfirmasi') {
    $status = 'dalam_perjalanan';
    $stmt = $conn->prepare('UPDATE everwear_jemput SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $id); $stmt->execute();
    echo json_encode(['success' => true]); exit;
}
if ($action === 'verifikasi' && in_array($jemput['status'], ['dalam_perjalanan', 'dikonfirmasi'], true)) {
    $layak = intval($data['pcs_layak'] ?? 0); $recycle = intval($data['pcs_recycle'] ?? 0); $berat = floatval($data['berat_kg'] ?? 0);
    if (($layak < 0 || $recycle < 0) || ($layak === 0 && $recycle === 0) || $berat <= 0) { echo json_encode(['success' => false, 'message' => 'Jumlah baju dan berat wajib valid.']); exit; }
    $catatan = trim($data['catatan_admin'] ?? '');
    $poin = max(1, round($berat * 10)); $status = 'selesai'; $cid = intval($jemput['customer_id']);
    $adminId = intval($_SESSION['user_id']);
    $stmt = $conn->prepare('UPDATE everwear_jemput SET pcs_layak = ?, pcs_recycle = ?, berat_kg = ?, poin_diperoleh = ?, status = ?, catatan_admin = ?, admin_id = ?, verified_at = NOW() WHERE id = ?');
    $stmt->bind_param('iidissii', $layak, $recycle, $berat, $poin, $status, $catatan, $adminId, $id); $stmt->execute();
    $stmt = $conn->prepare('UPDATE users SET poin = poin + ? WHERE id = ?'); $stmt->bind_param('ii', $poin, $cid); $stmt->execute();
    echo json_encode(['success' => true, 'poin' => $poin]); exit;
}
echo json_encode(['success' => false, 'message' => 'Status tidak sesuai alur.']);
