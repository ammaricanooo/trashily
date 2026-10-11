<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$id = intval($_GET['id'] ?? 0);
$uid = intval($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT kode_transaksi, jumlah_pcs, pcs_layak, pcs_recycle, berat_kg, poin_diperoleh, status, deskripsi_pakaian, jadwal_setor, created_at FROM everwear_transaksi WHERE id = ? AND customer_id = ?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();
$donasi = $stmt->get_result()->fetch_assoc();

if (!$donasi) {
    echo json_encode(['success' => false, 'message' => 'Data donasi tidak ditemukan.']);
    exit;
}

echo json_encode([
    'success' => true,
    'donasi' => [
        'kode' => $donasi['kode_transaksi'],
        'pcs_layak' => intval($donasi['pcs_layak']),
        'pcs_recycle' => intval($donasi['pcs_recycle']),
        'jumlah_pcs' => $donasi['jumlah_pcs'],
        'berat_kg' => $donasi['berat_kg'],
        'poin_diperoleh' => $donasi['poin_diperoleh'],
        'status' => $donasi['status'],
        'deskripsi' => $donasi['deskripsi_pakaian'],
        'jadwal_setor' => $donasi['jadwal_setor'] ? date('d M Y H:i', strtotime($donasi['jadwal_setor'])) : '—',
        'tanggal' => date('d M Y H:i', strtotime($donasi['created_at']))
    ]
]);
