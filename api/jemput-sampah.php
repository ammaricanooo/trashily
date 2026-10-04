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

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? 'buat';

// ======================================================
// ADMIN: update status permintaan jemput
// ======================================================
if ($_SESSION['role'] === 'admin' && $action === 'update_status') {
    $jemput_id = intval($data['jemput_id'] ?? 0);
    $status    = $data['status'] ?? '';
    $catatan   = trim($data['catatan'] ?? '');

    $allowed = ['dikonfirmasi', 'dijemput', 'selesai', 'batal'];
    if (!$jemput_id || !in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
        exit;
    }

    $admin_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("UPDATE jemput_sampah SET status = ?, admin_id = ?, catatan_admin = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('sisi', $status, $admin_id, $catatan, $jemput_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status.']);
    }
    exit;
}

// ======================================================
// ADMIN: catat selesai + input poin dari jemput
// ======================================================
if ($_SESSION['role'] === 'admin' && $action === 'selesaikan') {
    $jemput_id = intval($data['jemput_id'] ?? 0);
    $items     = $data['items'] ?? [];
    $catatan   = trim($data['catatan'] ?? '');

    if (!$jemput_id || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit;
    }

    // Ambil data jemput
    $stmt = $conn->prepare("SELECT * FROM jemput_sampah WHERE id = ?");
    $stmt->bind_param('i', $jemput_id);
    $stmt->execute();
    $jemput = $stmt->get_result()->fetch_assoc();

    if (!$jemput) {
        echo json_encode(['success' => false, 'message' => 'Permintaan tidak ditemukan.']);
        exit;
    }

    if ($jemput['status'] === 'selesai') {
        echo json_encode(['success' => false, 'message' => 'Permintaan sudah diselesaikan.']);
        exit;
    }

    $customer_id = $jemput['customer_id'];
    $admin_id    = $_SESSION['user_id'];

    $total_poin  = 0;
    $total_uang  = 0;
    $total_berat = 0;
    $items_data  = [];

    foreach ($items as $item) {
        $jenis_id = intval($item['jenis_id'] ?? 0);
        $berat    = floatval($item['berat'] ?? 0);
        if (!$jenis_id || $berat <= 0) continue;

        $s = $conn->prepare("SELECT id, poin_per_kg, harga_per_kg FROM jenis_sampah WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $jenis_id);
        $s->execute();
        $jenis = $s->get_result()->fetch_assoc();
        if (!$jenis) continue;

        $subtotal_poin = (int) round($berat * floatval($jenis['poin_per_kg']));
        $subtotal_uang = round($berat * floatval($jenis['harga_per_kg']), 2);

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

    if (empty($items_data)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada item valid.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        do {
            $kode = generateKode('TRX');
            $chk  = $conn->query("SELECT id FROM transaksi WHERE kode_transaksi = '$kode'");
        } while ($chk->num_rows > 0);

        $s3 = $conn->prepare("INSERT INTO transaksi (kode_transaksi, customer_id, admin_id, total_poin, total_uang, total_berat, catatan, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'selesai')");
        $s3->bind_param('siiidds', $kode, $customer_id, $admin_id, $total_poin, $total_uang, $total_berat, $catatan);
        $s3->execute();
        $transaksi_id = $conn->insert_id;

        foreach ($items_data as $it) {
            $s4 = $conn->prepare("INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $s4->bind_param('iidddid', $transaksi_id, $it['jenis_id'], $it['berat'], $it['poin_per_kg'], $it['harga_per_kg'], $it['subtotal_poin'], $it['subtotal_uang']);
            $s4->execute();
        }

        // Update poin customer
        $s5 = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
        $s5->bind_param('ii', $total_poin, $customer_id);
        $s5->execute();

        // Update status jemput selesai + simpan transaksi_id
        $s6 = $conn->prepare("UPDATE jemput_sampah SET status = 'selesai', admin_id = ?, transaksi_id = ?, catatan_admin = ?, updated_at = NOW() WHERE id = ?");
        $s6->bind_param('iisi', $admin_id, $transaksi_id, $catatan, $jemput_id);
        $s6->execute();

        $conn->commit();
        echo json_encode([
            'success'    => true,
            'message'    => 'Jemput sampah diselesaikan!',
            'kode'       => $kode,
            'total_poin' => number_format($total_poin, 0, ',', '.'),
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ======================================================
// CUSTOMER: buat permintaan jemput
// ======================================================
if ($_SESSION['role'] === 'customer' && $action === 'buat') {
    $customer_id  = $_SESSION['user_id'];
    $alamat       = trim($data['alamat'] ?? '');
    $jadwal       = trim($data['jadwal'] ?? '');
    $catatan      = trim($data['catatan'] ?? '');
    $items        = $data['items'] ?? [];

    $jarak_km     = max(0.1, floatval($data['jarak_km'] ?? 1.0));
    $tarif_per_km = 2000; // Rp 2.000 / km
    $biaya_ongkir = round($jarak_km * $tarif_per_km);

    if (empty($alamat) || empty($jadwal) || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Alamat, jadwal, dan jenis sampah wajib diisi.']);
        exit;
    }

    // Validasi format jadwal
    $jadwal_dt = strtotime($jadwal);
    if (!$jadwal_dt) {
        echo json_encode(['success' => false, 'message' => 'Format jadwal penjemputan tidak valid.']);
        exit;
    }

    // Validasi items
    $items_valid = [];
    foreach ($items as $it) {
        $jenis_id = intval($it['jenis_id'] ?? 0);
        $est_berat = floatval($it['est_berat'] ?? 0);
        if (!$jenis_id || $est_berat <= 0) continue;

        $s = $conn->prepare("SELECT id, nama FROM jenis_sampah WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $jenis_id);
        $s->execute();
        if ($s->get_result()->num_rows > 0) {
            $items_valid[] = ['jenis_id' => $jenis_id, 'est_berat' => $est_berat];
        }
    }

    if (empty($items_valid)) {
        echo json_encode(['success' => false, 'message' => 'Pilih minimal satu jenis sampah yang valid.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        do {
            $kode = generateKode('JMP');
            $chk  = $conn->query("SELECT id FROM jemput_sampah WHERE kode_jemput = '$kode'");
        } while ($chk->num_rows > 0);

        $jadwal_fmt = date('Y-m-d H:i:s', $jadwal_dt);

        $s1 = $conn->prepare("INSERT INTO jemput_sampah (kode_jemput, customer_id, alamat_jemput, jarak_km, biaya_ongkir, tarif_per_km, jadwal_jemput, catatan_customer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $s1->bind_param('sisdddss', $kode, $customer_id, $alamat, $jarak_km, $biaya_ongkir, $tarif_per_km, $jadwal_fmt, $catatan);
        $s1->execute();
        $jemput_id = $conn->insert_id;

        foreach ($items_valid as $it) {
            $s2 = $conn->prepare("INSERT INTO jemput_detail (jemput_id, jenis_sampah_id, est_berat) VALUES (?, ?, ?)");
            $s2->bind_param('iid', $jemput_id, $it['jenis_id'], $it['est_berat']);
            $s2->execute();
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Permintaan jemput sampah berhasil dibuat!',
            'kode'    => $kode,
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ======================================================
// CUSTOMER: batalkan permintaan
// ======================================================
if ($_SESSION['role'] === 'customer' && $action === 'batal') {
    $jemput_id   = intval($data['jemput_id'] ?? 0);
    $customer_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE jemput_sampah SET status = 'batal', updated_at = NOW() WHERE id = ? AND customer_id = ? AND status = 'menunggu'");
    $stmt->bind_param('ii', $jemput_id, $customer_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Permintaan berhasil dibatalkan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Permintaan tidak dapat dibatalkan.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
