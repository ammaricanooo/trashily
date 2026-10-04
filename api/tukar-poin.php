<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data      = json_decode(file_get_contents('php://input'), true);
$hadiah_id = intval($data['hadiah_id'] ?? 0);

// Jika admin memproses penukaran (update status)
if ($_SESSION['role'] === 'admin' && isset($data['penukaran_id'])) {
    $penukaran_id = intval($data['penukaran_id']);
    $status       = in_array($data['status'] ?? '', ['diproses', 'selesai', 'batal']) ? $data['status'] : 'diproses';
    $admin_id     = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("SELECT p.*, h.stok FROM penukaran p JOIN hadiah h ON p.hadiah_id = h.id WHERE p.id = ?");
        $stmt->bind_param('i', $penukaran_id);
        $stmt->execute();
        $penukaran = $stmt->get_result()->fetch_assoc();

        if (!$penukaran) throw new Exception('Penukaran tidak ditemukan.');

        if ($status === 'batal' && $penukaran['status'] !== 'batal') {
            // Kembalikan poin customer
            $stmt2 = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
            $stmt2->bind_param('ii', $penukaran['poin_digunakan'], $penukaran['customer_id']);
            $stmt2->execute();
            // Kembalikan stok
            $stmt3 = $conn->prepare("UPDATE hadiah SET stok = stok + 1 WHERE id = ?");
            $stmt3->bind_param('i', $penukaran['hadiah_id']);
            $stmt3->execute();
        }

        $stmt4 = $conn->prepare("UPDATE penukaran SET status = ?, admin_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt4->bind_param('sii', $status, $admin_id, $penukaran_id);
        $stmt4->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Status penukaran diperbarui.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Customer mengajukan penukaran
if (!$hadiah_id) {
    echo json_encode(['success' => false, 'message' => 'Pilih hadiah terlebih dahulu.']);
    exit;
}

$customer_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id, nama, poin_dibutuhkan, stok FROM hadiah WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $hadiah_id);
$stmt->execute();
$hadiah = $stmt->get_result()->fetch_assoc();

if (!$hadiah) {
    echo json_encode(['success' => false, 'message' => 'Hadiah tidak ditemukan.']);
    exit;
}

if ($hadiah['stok'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Stok hadiah habis.']);
    exit;
}

$stmt2 = $conn->prepare("SELECT poin FROM users WHERE id = ?");
$stmt2->bind_param('i', $customer_id);
$stmt2->execute();
$user = $stmt2->get_result()->fetch_assoc();

if ($user['poin'] < $hadiah['poin_dibutuhkan']) {
    echo json_encode(['success' => false, 'message' => 'Poin Anda tidak mencukupi.']);
    exit;
}

$conn->begin_transaction();
try {
    do {
        $kode = generateKode('TKR');
        $check = $conn->query("SELECT id FROM penukaran WHERE kode_penukaran = '$kode'");
    } while ($check->num_rows > 0);

    $poin_digunakan = $hadiah['poin_dibutuhkan'];

    $stmt3 = $conn->prepare("INSERT INTO penukaran (kode_penukaran, customer_id, hadiah_id, poin_digunakan, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt3->bind_param('siii', $kode, $customer_id, $hadiah_id, $poin_digunakan);
    $stmt3->execute();

    $stmt4 = $conn->prepare("UPDATE users SET poin = poin - ? WHERE id = ?");
    $stmt4->bind_param('ii', $poin_digunakan, $customer_id);
    $stmt4->execute();

    $stmt5 = $conn->prepare("UPDATE hadiah SET stok = stok - 1 WHERE id = ?");
    $stmt5->bind_param('i', $hadiah_id);
    $stmt5->execute();

    // Update session poin
    $_SESSION['poin'] = $user['poin'] - $poin_digunakan;

    $conn->commit();
    echo json_encode([
        'success'  => true,
        'message'  => 'Penukaran berhasil diajukan! Kode: ' . $kode,
        'kode'     => $kode,
        'sisa_poin' => number_format($_SESSION['poin'], 0, ',', '.')
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
