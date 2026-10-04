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
$action = $data['action'] ?? '';

// ======================================================
// CUSTOMER: buat pengajuan setor sendiri
// ======================================================
if ($_SESSION['role'] === 'customer' && $action === 'buat') {
    $customer_id  = $_SESSION['user_id'];
    $jadwal_setor = trim($data['jadwal_setor'] ?? '');
    $catatan      = trim($data['catatan'] ?? '');
    $items        = $data['items'] ?? [];

    if (empty($jadwal_setor)) {
        echo json_encode(['success' => false, 'message' => 'Pilih tanggal dan jam kedatangan terlebih dahulu.']);
        exit;
    }

    $jadwal_dt = strtotime($jadwal_setor);
    if (!$jadwal_dt) {
        echo json_encode(['success' => false, 'message' => 'Format jam kedatangan tidak valid.']);
        exit;
    }

    if (empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Pilih minimal satu jenis sampah.']);
        exit;
    }

    $items_valid = [];
    $total_est_poin  = 0;
    $total_est_uang  = 0;
    $total_est_berat = 0;

    foreach ($items as $it) {
        $jenis_id = intval($it['jenis_id'] ?? 0);
        $berat    = floatval($it['berat'] ?? 0);
        if (!$jenis_id || $berat <= 0) continue;

        $s = $conn->prepare("SELECT id, poin_per_kg, harga_per_kg FROM jenis_sampah WHERE id = ? AND is_active = 1");
        $s->bind_param('i', $jenis_id);
        $s->execute();
        $jenis = $s->get_result()->fetch_assoc();
        if (!$jenis) continue;

        $subtotal_poin = (int) round($berat * floatval($jenis['poin_per_kg']));
        $subtotal_uang = round($berat * floatval($jenis['harga_per_kg']), 2);

        $total_est_poin  += $subtotal_poin;
        $total_est_uang  += $subtotal_uang;
        $total_est_berat += $berat;
        $items_valid[] = [
            'jenis_id'      => $jenis_id,
            'berat'         => $berat,
            'poin_per_kg'   => floatval($jenis['poin_per_kg']),
            'harga_per_kg'  => floatval($jenis['harga_per_kg']),
            'subtotal_poin' => $subtotal_poin,
            'subtotal_uang' => $subtotal_uang
        ];
    }

    if (empty($items_valid)) {
        echo json_encode(['success' => false, 'message' => 'Pilih minimal satu jenis sampah yang valid.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        do {
            $kode = generateKode('STS');
            $chk  = $conn->query("SELECT id FROM transaksi WHERE kode_transaksi = '$kode'");
        } while ($chk->num_rows > 0);

        $jadwal_fmt = date('Y-m-d H:i:s', $jadwal_dt);

        $stmt = $conn->prepare("
            INSERT INTO transaksi (kode_transaksi, customer_id, admin_id, total_poin, total_uang, total_berat, catatan, status, tipe_transaksi, jadwal_setor)
            VALUES (?, ?, NULL, ?, ?, ?, ?, 'pending', 'setor_sendiri', ?)
        ");
        $stmt->bind_param('siiddss', $kode, $customer_id, $total_est_poin, $total_est_uang, $total_est_berat, $catatan, $jadwal_fmt);
        $stmt->execute();
        $transaksi_id = $conn->insert_id;

        foreach ($items_valid as $it) {
            $s2 = $conn->prepare("INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $s2->bind_param('iidddid', $transaksi_id, $it['jenis_id'], $it['berat'], $it['poin_per_kg'], $it['harga_per_kg'], $it['subtotal_poin'], $it['subtotal_uang']);
            $s2->execute();
        }

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Jadwal setor sendiri berhasil dibuat! Silakan datang ke Trashily sesuai jam pilihan Anda.',
            'kode'    => $kode,
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ======================================================
// ADMIN: verifikasi & periksa setor sendiri (input timbangan aktual)
// ======================================================
if ($_SESSION['role'] === 'admin' && $action === 'verifikasi') {
    $transaksi_id  = intval($data['transaksi_id'] ?? 0);
    $items         = $data['items'] ?? [];
    $catatan_admin = trim($data['catatan_admin'] ?? '');

    if (!$transaksi_id || empty($items)) {
        echo json_encode(['success' => false, 'message' => 'Data verifikasi tidak lengkap.']);
        exit;
    }

    // Ambil transaksi pending
    $stmt = $conn->prepare("SELECT * FROM transaksi WHERE id = ? AND tipe_transaksi = 'setor_sendiri'");
    $stmt->bind_param('i', $transaksi_id);
    $stmt->execute();
    $trx = $stmt->get_result()->fetch_assoc();

    if (!$trx) {
        echo json_encode(['success' => false, 'message' => 'Transaksi tidak ditemukan.']);
        exit;
    }

    if ($trx['status'] === 'selesai') {
        echo json_encode(['success' => false, 'message' => 'Transaksi sudah diverifikasi & selesai.']);
        exit;
    }

    $customer_id = $trx['customer_id'];
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
        echo json_encode(['success' => false, 'message' => 'Tidak ada item sampah valid hasil pemeriksaan.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Hapus detail lama dan ganti dengan hasil timbangan petugas
        $conn->query("DELETE FROM detail_transaksi WHERE transaksi_id = $transaksi_id");

        foreach ($items_data as $it) {
            $s2 = $conn->prepare("INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $s2->bind_param('iidddid', $transaksi_id, $it['jenis_id'], $it['berat'], $it['poin_per_kg'], $it['harga_per_kg'], $it['subtotal_poin'], $it['subtotal_uang']);
            $s2->execute();
        }

        // Update status transaksi ke 'selesai', set admin_id, total_poin, total_uang, total_berat
        $s3 = $conn->prepare("
            UPDATE transaksi 
            SET status = 'selesai', admin_id = ?, total_poin = ?, total_uang = ?, total_berat = ?, catatan_admin = ? 
            WHERE id = ?
        ");
        $s3->bind_param('iiddsi', $admin_id, $total_poin, $total_uang, $total_berat, $catatan_admin, $transaksi_id);
        $s3->execute();

        // Tambah poin ke user jika customer_id ada
        if ($customer_id && $total_poin > 0) {
            $s4 = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
            $s4->bind_param('ii', $total_poin, $customer_id);
            $s4->execute();
        }

        $conn->commit();
        echo json_encode([
            'success'        => true,
            'message'        => 'Pemeriksaan selesai! Poin telah dikreditkan ke customer.',
            'total_poin'     => number_format($total_poin, 0, ',', '.'),
            'total_uang'     => $total_uang,
            'total_uang_fmt' => formatRupiah($total_uang),
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ======================================================
// CUSTOMER / ADMIN: Batalkan pengajuan setor sendiri
// ======================================================
if ($action === 'batal') {
    $transaksi_id = intval($data['transaksi_id'] ?? 0);
    if (!$transaksi_id) {
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }

    if ($_SESSION['role'] === 'customer') {
        $stmt = $conn->prepare("UPDATE transaksi SET status = 'batal' WHERE id = ? AND customer_id = ? AND status = 'pending'");
        $stmt->bind_param('ii', $transaksi_id, $_SESSION['user_id']);
    } else {
        $stmt = $conn->prepare("UPDATE transaksi SET status = 'batal', admin_id = ? WHERE id = ? AND status = 'pending'");
        $stmt->bind_param('ii', $_SESSION['user_id'], $transaksi_id);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Pengajuan setor sendiri dibatalkan.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal membatalkan pengajuan.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenali.']);
