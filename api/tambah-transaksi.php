<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$is_member       = isset($data['is_member']) ? (bool)$data['is_member'] : true;
$customer_id     = intval($data['customer_id'] ?? 0);
$nama_non_member = trim($data['nama_non_member'] ?? '');
$items           = $data['items'] ?? [];
$catatan         = trim($data['catatan'] ?? '');

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Item sampah tidak boleh kosong.']);
    exit;
}

$customer = null;
if ($is_member) {
    if (!$customer_id) {
        echo json_encode(['success' => false, 'message' => 'Pilih customer terdaftar terlebih dahulu.']);
        exit;
    }
    // Validasi customer
    $stmt = $conn->prepare("SELECT id, nama, poin FROM users WHERE id = ? AND role = 'customer'");
    $stmt->bind_param('i', $customer_id);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer tidak ditemukan.']);
        exit;
    }
} else {
    // Non-member / walk-in / gaptek
    $customer_id = null;
    if (empty($nama_non_member)) {
        $nama_non_member = 'Pelanggan Langsung (Tanpa Akun)';
    }
}

// Hitung total poin, uang, & berat
$total_poin  = 0;
$total_uang  = 0;
$total_berat = 0;
$items_data  = [];

foreach ($items as $item) {
    $jenis_id = intval($item['jenis_id'] ?? 0);
    $berat    = floatval($item['berat'] ?? 0);

    if (!$jenis_id || $berat <= 0) continue;

    $stmt2 = $conn->prepare("SELECT id, nama, poin_per_kg, harga_per_kg FROM jenis_sampah WHERE id = ? AND is_active = 1");
    $stmt2->bind_param('i', $jenis_id);
    $stmt2->execute();
    $jenis = $stmt2->get_result()->fetch_assoc();

    if (!$jenis) continue;

    $subtotal_uang = round($berat * floatval($jenis['harga_per_kg']), 2);
    $subtotal_poin = $is_member ? (int) round($berat * floatval($jenis['poin_per_kg'])) : 0;

    $total_poin  += $subtotal_poin;
    $total_uang  += $subtotal_uang;
    $total_berat += $berat;

    $items_data[] = [
        'jenis_id'      => $jenis_id,
        'berat'         => $berat,
        'poin_per_kg'   => floatval($jenis['poin_per_kg']),
        'harga_per_kg'  => floatval($jenis['harga_per_kg']),
        'subtotal_poin' => $subtotal_poin,
        'subtotal_uang' => $subtotal_uang
    ];
}

if (empty($items_data) || $total_berat <= 0) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada item sampah valid.']);
    exit;
}

$conn->begin_transaction();

try {
    // Buat kode unik
    do {
        $kode = generateKode('TRX');
        $check = $conn->query("SELECT id FROM transaksi WHERE kode_transaksi = '$kode'");
    } while ($check->num_rows > 0);

    $admin_id = $_SESSION['user_id'];

    if ($is_member) {
        $stmt3 = $conn->prepare("
            INSERT INTO transaksi (kode_transaksi, customer_id, nama_non_member, admin_id, total_poin, total_uang, total_berat, catatan, status, tipe_transaksi)
            VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'selesai', 'langsung')
        ");
        $stmt3->bind_param('siiidds', $kode, $customer_id, $admin_id, $total_poin, $total_uang, $total_berat, $catatan);
    } else {
        $stmt3 = $conn->prepare("
            INSERT INTO transaksi (kode_transaksi, customer_id, nama_non_member, admin_id, total_poin, total_uang, total_berat, catatan, status, tipe_transaksi)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, 'selesai', 'langsung')
        ");
        $stmt3->bind_param('ssiidds', $kode, $nama_non_member, $admin_id, $total_poin, $total_uang, $total_berat, $catatan);
    }
    $stmt3->execute();
    $transaksi_id = $conn->insert_id;

    foreach ($items_data as $item) {
        $stmt4 = $conn->prepare("
            INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt4->bind_param('iidddid', $transaksi_id, $item['jenis_id'], $item['berat'], $item['poin_per_kg'], $item['harga_per_kg'], $item['subtotal_poin'], $item['subtotal_uang']);
        $stmt4->execute();
    }

    // Update poin customer jika member
    if ($is_member && $customer_id && $total_poin > 0) {
        $stmt5 = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
        $stmt5->bind_param('ii', $total_poin, $customer_id);
        $stmt5->execute();
    }

    $conn->commit();

    echo json_encode([
        'success'        => true,
        'message'        => 'Transaksi berhasil disimpan!',
        'kode'           => $kode,
        'is_member'      => $is_member,
        'total_poin'     => number_format($total_poin, 0, ',', '.'),
        'total_uang'     => $total_uang,
        'total_uang_fmt' => formatRupiah($total_uang),
        'total_berat'    => number_format($total_berat, 2, ',', '.'),
        'nama_customer'  => $is_member ? $customer['nama'] : $nama_non_member
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
}
