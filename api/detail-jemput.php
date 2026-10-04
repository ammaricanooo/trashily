<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$uid  = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Admin bisa lihat semua, customer hanya miliknya
if ($role === 'admin') {
    $stmt = $conn->prepare("
        SELECT js.*, u.nama as customer_nama, u.no_hp,
               t.kode_transaksi, t.total_poin, t.total_berat
        FROM jemput_sampah js
        JOIN users u ON js.customer_id = u.id
        LEFT JOIN transaksi t ON js.transaksi_id = t.id
        WHERE js.id = ?
    ");
    $stmt->bind_param('i', $id);
} else {
    $stmt = $conn->prepare("
        SELECT js.*, u.nama as customer_nama, u.no_hp,
               t.kode_transaksi, t.total_poin, t.total_berat
        FROM jemput_sampah js
        JOIN users u ON js.customer_id = u.id
        LEFT JOIN transaksi t ON js.transaksi_id = t.id
        WHERE js.id = ? AND js.customer_id = ?
    ");
    $stmt->bind_param('ii', $id, $uid);
}

$stmt->execute();
$jemput = $stmt->get_result()->fetch_assoc();

if (!$jemput) {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    exit;
}

// Ambil item detail
$items_stmt = $conn->prepare("
    SELECT jd.est_berat, js.nama, js.poin_per_kg, js.kategori
    FROM jemput_detail jd
    JOIN jenis_sampah js ON jd.jenis_sampah_id = js.id
    WHERE jd.jemput_id = ?
");
$items_stmt->bind_param('i', $id);
$items_stmt->execute();
$items_res = $items_stmt->get_result();
$items = [];
while ($it = $items_res->fetch_assoc()) $items[] = $it;

echo json_encode([
    'success' => true,
    'data' => [
        'id'             => $jemput['id'],
        'kode'           => $jemput['kode_jemput'],
        'customer'       => $jemput['customer_nama'],
        'no_hp'          => $jemput['no_hp'],
        'alamat'         => htmlspecialchars($jemput['alamat_jemput']),
        'jadwal'         => date('d M Y H:i', strtotime($jemput['jadwal_jemput'])) . ' WIB',
        'status'         => $jemput['status'],
        'catatan_customer' => htmlspecialchars($jemput['catatan_customer'] ?? ''),
        'catatan_admin'  => htmlspecialchars($jemput['catatan_admin'] ?? ''),
        'jarak_km'       => number_format($jemput['jarak_km'] ?? 0, 1, ',', '.'),
        'biaya_ongkir'   => number_format($jemput['biaya_ongkir'] ?? 0, 0, ',', '.'),
        'tarif_per_km'   => number_format($jemput['tarif_per_km'] ?? 2000, 0, ',', '.'),
        'dibuat'         => date('d M Y H:i', strtotime($jemput['created_at'])),
        'kode_transaksi' => $jemput['kode_transaksi'] ?? null,
        'total_poin'     => $jemput['total_poin'] ? number_format($jemput['total_poin'], 0, ',', '.') : null,
        'total_berat'    => $jemput['total_berat'] ?? null,
    ],
    'items' => $items,
]);
