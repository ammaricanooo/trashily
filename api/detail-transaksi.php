<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['success' => false]); exit; }

// Customer hanya bisa lihat transaksi miliknya
$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];
if ($role === 'customer') {
    $own = $conn->query("SELECT id FROM transaksi WHERE id=$id AND customer_id=$uid");
    if ($own->num_rows === 0) { echo json_encode(['success' => false, 'message' => 'Tidak ditemukan']); exit; }
}

$stmt = $conn->prepare("
    SELECT t.id, t.kode_transaksi, t.customer_id, t.nama_non_member, t.admin_id, t.total_poin, t.total_uang, t.total_berat,
           t.catatan, t.catatan_admin, t.status, t.tipe_transaksi, t.jadwal_setor, t.created_at,
           u.nama as customer_nama, u.no_hp as customer_hp, a.nama as admin_nama
    FROM transaksi t
    LEFT JOIN users u ON t.customer_id = u.id
    LEFT JOIN users a ON t.admin_id = a.id
    WHERE t.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$trx = $stmt->get_result()->fetch_assoc();

if (!$trx) { echo json_encode(['success' => false]); exit; }

$stmt2 = $conn->prepare("
    SELECT dt.jenis_sampah_id, dt.berat, dt.poin_per_kg, dt.harga_per_kg, dt.subtotal_poin, dt.subtotal_uang,
           js.nama, js.kategori
    FROM detail_transaksi dt
    JOIN jenis_sampah js ON dt.jenis_sampah_id = js.id
    WHERE dt.transaksi_id = ?
");
$stmt2->bind_param('i', $id);
$stmt2->execute();
$details = $stmt2->get_result();

$items = [];
while ($d = $details->fetch_assoc()) {
    $items[] = [
        'jenis_sampah_id'   => $d['jenis_sampah_id'],
        'nama'              => $d['nama'],
        'kategori'          => $d['kategori'],
        'berat'             => floatval($d['berat']),
        'berat_fmt'         => number_format($d['berat'], 2),
        'poin_per_kg'       => number_format($d['poin_per_kg'], 0),
        'harga_per_kg'      => floatval($d['harga_per_kg']),
        'harga_per_kg_fmt'  => formatRupiah($d['harga_per_kg']),
        'subtotal_poin'     => number_format($d['subtotal_poin'], 0, ',', '.'),
        'subtotal_uang'     => floatval($d['subtotal_uang']),
        'subtotal_uang_fmt' => formatRupiah($d['subtotal_uang'])
    ];
}

$is_member = !empty($trx['customer_id']);
$cust_name = $is_member ? $trx['customer_nama'] : ($trx['nama_non_member'] ?: 'Pelanggan Langsung (Tanpa Akun)');

echo json_encode([
    'success' => true,
    'transaksi' => [
        'id'             => $trx['id'],
        'kode'           => $trx['kode_transaksi'],
        'is_member'      => $is_member,
        'customer_id'    => $trx['customer_id'],
        'customer'       => $cust_name,
        'customer_hp'    => $trx['customer_hp'] ?: '-',
        'admin'          => $trx['admin_nama'] ?: 'Belum diperiksa',
        'total_berat'    => number_format($trx['total_berat'], 2),
        'total_poin'     => number_format($trx['total_poin'], 0, ',', '.'),
        'total_uang'     => floatval($trx['total_uang']),
        'total_uang_fmt' => formatRupiah($trx['total_uang']),
        'status'         => $trx['status'],
        'tipe_transaksi' => $trx['tipe_transaksi'],
        'jadwal_setor'   => $trx['jadwal_setor'] ? date('d M Y H:i', strtotime($trx['jadwal_setor'])) . ' WIB' : '-',
        'catatan'        => htmlspecialchars($trx['catatan'] ?? ''),
        'catatan_admin'  => htmlspecialchars($trx['catatan_admin'] ?? ''),
        'tanggal'        => date('d M Y H:i', strtotime($trx['created_at']))
    ],
    'items' => $items
]);
