<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

// Verifikasi Login Customer
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Silakan masuk ke akun Trashily terlebih dahulu.']);
    exit;
}

$userId = intval($_SESSION['user_id']);
$action = trim($_GET['action'] ?? ($_POST['action'] ?? 'status'));
$deviceId = trim($_GET['device'] ?? ($_POST['device'] ?? 'PIRU-DEV-001'));

switch ($action) {
    case 'start':
        // Batalkan sesi aktif lama yang kadaluarsa (> 30 menit)
        $conn->query("
            UPDATE piru_sessions 
            SET status = 'cancelled' 
            WHERE user_id = $userId 
              AND status = 'active' 
              AND updated_at < NOW() - INTERVAL 30 MINUTE
        ");

        // Cari sesi aktif yang sedang berjalan untuk user & device ini
        $stmtCheck = $conn->prepare("
            SELECT id, session_token, device_kode, total_organik_gr, total_anorganik_gr, total_berat_gr, total_poin, item_count, status
            FROM piru_sessions
            WHERE user_id = ? AND device_kode = ? AND status = 'active'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtCheck->bind_param('is', $userId, $deviceId);
        $stmtCheck->execute();
        $existing = $stmtCheck->get_result()->fetch_assoc();

        if ($existing) {
            echo json_encode([
                'success' => true,
                'is_new'  => false,
                'session' => $existing,
                'message' => 'Melanjutkan sesi aktif.'
            ]);
            exit;
        }

        // Buat sesi baru
        $token = bin2hex(random_bytes(16));
        $stmtNew = $conn->prepare("
            INSERT INTO piru_sessions (session_token, device_kode, user_id, status, created_at, updated_at)
            VALUES (?, ?, ?, 'active', NOW(), NOW())
        ");
        $stmtNew->bind_param('ssi', $token, $deviceId, $userId);
        $stmtNew->execute();
        $newId = $conn->insert_id;

        echo json_encode([
            'success' => true,
            'is_new'  => true,
            'session' => [
                'id'                 => $newId,
                'session_token'      => $token,
                'device_kode'        => $deviceId,
                'total_organik_gr'   => 0,
                'total_anorganik_gr' => 0,
                'total_berat_gr'     => 0,
                'total_poin'         => 0,
                'item_count'         => 0,
                'status'             => 'active'
            ],
            'message' => 'Sesi memilah berhasil dimulai!'
        ]);
        break;

    case 'status':
        // Ambil sesi aktif terkini
        $stmtStatus = $conn->prepare("
            SELECT id, session_token, device_kode, total_organik_gr, total_anorganik_gr, total_berat_gr, total_poin, item_count, status, created_at
            FROM piru_sessions
            WHERE user_id = ? AND device_kode = ? AND status = 'active'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtStatus->bind_param('is', $userId, $deviceId);
        $stmtStatus->execute();
        $session = $stmtStatus->get_result()->fetch_assoc();

        if (!$session) {
            echo json_encode([
                'success' => false,
                'has_active_session' => false,
                'message' => 'Tidak ada sesi aktif. Silakan mulai sesi baru.'
            ]);
            exit;
        }

        // Ambil daftar item sampah yang sudah dipilah dalam sesi ini
        $sessionId = $session['id'];
        $session['count_organik'] = intval($conn->query("SELECT COUNT(*) as c FROM piru_session_items WHERE session_id = $sessionId AND category = 'ORGANIK'")->fetch_assoc()['c'] ?? 0);
        $session['count_anorganik'] = intval($conn->query("SELECT COUNT(*) as c FROM piru_session_items WHERE session_id = $sessionId AND category = 'ANORGANIK'")->fetch_assoc()['c'] ?? 0);

        $stmtItems = $conn->prepare("
            SELECT id, category, item_name, weight_gr, poin, DATE_FORMAT(created_at, '%H:%i:%s') as waktu
            FROM piru_session_items
            WHERE session_id = ?
            ORDER BY id DESC
        ");
        $stmtItems->bind_param('i', $sessionId);
        $stmtItems->execute();
        $itemsRes = $stmtItems->get_result();

        $items = [];
        while ($row = $itemsRes->fetch_assoc()) {
            $items[] = $row;
        }

        // Ambil telemetri hardware perangkat dari tabel trashsmart_devices
        $stmtDevInfo = $conn->prepare("
            SELECT id, kode_perangkat, nama_perangkat, lokasi, status, kapasitas_organik, kapasitas_anorganik, baterai, suhu_celsius, firmware_version, last_ping,
                   (last_ping >= NOW() - INTERVAL 5 MINUTE) as is_online,
                   TIMESTAMPDIFF(SECOND, last_ping, NOW()) as ping_age_seconds
            FROM trashsmart_devices
            WHERE kode_perangkat = ?
        ");
        $stmtDevInfo->bind_param('s', $deviceId);
        $stmtDevInfo->execute();
        $deviceInfo = $stmtDevInfo->get_result()->fetch_assoc();

        echo json_encode([
            'success'            => true,
            'has_active_session' => true,
            'session'            => $session,
            'items'              => $items,
            'device'             => $deviceInfo
        ]);
        break;

    case 'complete':
        // Selesaikan sesi & Klaim Poin
        $stmtAct = $conn->prepare("
            SELECT id, total_organik_gr, total_anorganik_gr, total_berat_gr, total_poin, item_count
            FROM piru_sessions
            WHERE user_id = ? AND device_kode = ? AND status = 'active'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtAct->bind_param('is', $userId, $deviceId);
        $stmtAct->execute();
        $session = $stmtAct->get_result()->fetch_assoc();

        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Tidak ada sesi aktif untuk diselesaikan.']);
            exit;
        }

        $sessionId   = $session['id'];
        $totalPoin   = intval($session['total_poin']);
        $totalBeratGr= floatval($session['total_berat_gr']);
        $totalBeratKg= round($totalBeratGr / 1000.0, 2);
        $itemCount   = intval($session['item_count']);

        if ($itemCount <= 0) {
            // Jika belum memasukkan sampah, tandai selesai tanpa transaksi
            $conn->query("UPDATE piru_sessions SET status = 'completed', completed_at = NOW() WHERE id = $sessionId");
            echo json_encode([
                'success' => true,
                'points_earned' => 0,
                'message' => 'Sesi selesai tanpa ada sampah yang dimasukkan.'
            ]);
            exit;
        }

        $conn->begin_transaction();

        try {
            // 1. Buat kode transaksi unik
            do {
                $kode = generateKode('PIRU');
                $chk = $conn->query("SELECT id FROM transaksi WHERE kode_transaksi = '$kode'");
            } while ($chk && $chk->num_rows > 0);

            $catatan = "Pemilahan Cerdas via PIRU Smart Bin ({$deviceId}) - {$itemCount} item";

            // 2. Simpan transaksi utama
            $stmtTrx = $conn->prepare("
                INSERT INTO transaksi (
                    kode_transaksi, customer_id, admin_id, total_poin, total_uang, total_berat, catatan, status, tipe_transaksi, created_at
                ) VALUES (?, ?, NULL, ?, 0.00, ?, ?, 'selesai', 'langsung', NOW())
            ");
            $stmtTrx->bind_param('siids', $kode, $userId, $totalPoin, $totalBeratKg, $catatan);
            $stmtTrx->execute();
            $transaksiId = $conn->insert_id;

            // 3. Masukkan ke detail_transaksi
            // Cari referensi ID jenis_sampah organik & anorganik
            $orgJenisId = 2; // Default organik
            $anorgJenisId = 3; // Default plastik

            $resOrg = $conn->query("SELECT id FROM jenis_sampah WHERE kategori = 'organik_kering' LIMIT 1");
            if ($resOrg && $r = $resOrg->fetch_assoc()) $orgJenisId = $r['id'];

            $resAnorg = $conn->query("SELECT id FROM jenis_sampah WHERE kategori IN ('plastik','anorganik') LIMIT 1");
            if ($resAnorg && $r = $resAnorg->fetch_assoc()) $anorgJenisId = $r['id'];

            $pOrg = intval($conn->query("SELECT COALESCE(SUM(poin),0) as p FROM piru_session_items WHERE session_id = $sessionId AND category = 'ORGANIK'")->fetch_assoc()['p'] ?? 0);
            $pAnorg = intval($conn->query("SELECT COALESCE(SUM(poin),0) as p FROM piru_session_items WHERE session_id = $sessionId AND category = 'ANORGANIK'")->fetch_assoc()['p'] ?? 0);

            if ($session['total_organik_gr'] > 0 || $pOrg > 0) {
                $bKg = round($session['total_organik_gr'] / 1000.0, 3);
                $stmtDet1 = $conn->prepare("
                    INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang)
                    VALUES (?, ?, ?, 100.00, 0.00, ?, 0.00)
                ");
                $stmtDet1->bind_param('iidi', $transaksiId, $orgJenisId, $bKg, $pOrg);
                $stmtDet1->execute();
            }

            if ($session['total_anorganik_gr'] > 0 || $pAnorg > 0) {
                $bKg = round($session['total_anorganik_gr'] / 1000.0, 3);
                $stmtDet2 = $conn->prepare("
                    INSERT INTO detail_transaksi (transaksi_id, jenis_sampah_id, berat, poin_per_kg, harga_per_kg, subtotal_poin, subtotal_uang)
                    VALUES (?, ?, ?, 250.00, 0.00, ?, 0.00)
                ");
                $stmtDet2->bind_param('iidi', $transaksiId, $anorgJenisId, $bKg, $pAnorg);
                $stmtDet2->execute();
            }

            // 4. Tambahkan Poin ke akun user
            $stmtUserPoin = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
            $stmtUserPoin->bind_param('ii', $totalPoin, $userId);
            $stmtUserPoin->execute();

            // 5. Update status piru_sessions
            $stmtSesDone = $conn->prepare("
                UPDATE piru_sessions 
                SET status = 'completed', completed_at = NOW(), transaksi_id = ?
                WHERE id = ?
            ");
            $stmtSesDone->bind_param('ii', $transaksiId, $sessionId);
            $stmtSesDone->execute();

            // Refresh session PHP
            $userRow = $conn->query("SELECT poin FROM users WHERE id = $userId")->fetch_assoc();
            $_SESSION['poin'] = $userRow['poin'];

            $conn->commit();

            echo json_encode([
                'success'        => true,
                'session_id'     => $sessionId,
                'transaksi_id'   => $transaksiId,
                'kode_transaksi' => $kode,
                'points_earned'  => $totalPoin,
                'current_poin'   => $userRow['poin'],
                'total_berat_gr' => $totalBeratGr,
                'total_berat_kg' => $totalBeratKg,
                'item_count'     => $itemCount,
                'message'        => "Selamat! Anda berhasil mengklaim +{$totalPoin} Poin Trashily."
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan sesi: ' . $e->getMessage()]);
        }
        break;

    case 'cancel':
        // Batalkan sesi
        $stmtCancel = $conn->prepare("
            UPDATE piru_sessions 
            SET status = 'cancelled', completed_at = NOW() 
            WHERE user_id = ? AND device_kode = ? AND status = 'active'
        ");
        $stmtCancel->bind_param('is', $userId, $deviceId);
        $stmtCancel->execute();

        echo json_encode(['success' => true, 'message' => 'Sesi berhasil dibatalkan.']);
        break;

    case 'simulate':
        // Simulasi pemicuan sensor IoT Baki 1 / Baki 2 langsung dari Web
        $catInput = strtoupper(trim($_POST['category'] ?? ($_GET['category'] ?? 'ORGANIK')));
        $simCategory = ($catInput === 'ANORGANIK') ? 'ANORGANIK' : 'ORGANIK';
        $rawLabel = trim($_POST['item_name'] ?? ($_GET['item_name'] ?? ''));
        $simWeight = floatval($_POST['weight_grams'] ?? ($_GET['weight_grams'] ?? ($simCategory === 'ORGANIK' ? 120.0 : 85.0)));
        if ($simWeight <= 0) $simWeight = 100.0;

        $labelMap = [
            'banana'    => '🍌 Kulit Pisang (banana)',
            'apple'     => '🍎 Sisa Buah Apel (apple)',
            'orange'    => '🍊 Kulit Jeruk (orange)',
            'sandwich'  => '🥪 Sisa Roti (sandwich)',
            'bottle'    => '🍾 Botol Plastik (bottle)',
            'cup'       => '🥤 Gelas Plastik / Cup',
            'cardboard' => '📦 Kardus Karton (cardboard)',
            'can'       => '🥫 Kaleng Minuman (can)',
        ];
        $key = strtolower($rawLabel);
        $itemName = $labelMap[$key] ?? (!empty($rawLabel) ? (($simCategory === 'ORGANIK' ? '🌿 ' : '📦 ') . ucwords($rawLabel)) : ($simCategory === 'ORGANIK' ? '🌿 Sampah Organik' : '📦 Sampah Anorganik'));
        $poinItem = ($simCategory === 'ORGANIK') ? 10 : 15;
        $beratKg = round($simWeight / 1000.0, 3);

        $stmtActSes = $conn->prepare("
            SELECT id, user_id FROM piru_sessions
            WHERE user_id = ? AND device_kode = ? AND status = 'active'
            ORDER BY id DESC LIMIT 1
        ");
        $stmtActSes->bind_param('is', $userId, $deviceId);
        $stmtActSes->execute();
        $actSes = $stmtActSes->get_result()->fetch_assoc();

        if (!$actSes) {
            $token = bin2hex(random_bytes(16));
            $stmtNew = $conn->prepare("
                INSERT INTO piru_sessions (session_token, device_kode, user_id, status, created_at, updated_at)
                VALUES (?, ?, ?, 'active', NOW(), NOW())
            ");
            $stmtNew->bind_param('ssi', $token, $deviceId, $userId);
            $stmtNew->execute();
            $sessionId = $conn->insert_id;
        } else {
            $sessionId = intval($actSes['id']);
        }

        $stmtInsItem = $conn->prepare("
            INSERT INTO piru_session_items (session_id, category, item_name, weight_gr, poin, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmtInsItem->bind_param('issdi', $sessionId, $simCategory, $itemName, $simWeight, $poinItem);
        $stmtInsItem->execute();

        if ($simCategory === 'ORGANIK') {
            $conn->query("
                UPDATE piru_sessions 
                SET total_organik_gr = total_organik_gr + $simWeight,
                    total_berat_gr   = total_berat_gr + $simWeight,
                    total_poin       = total_poin + $poinItem,
                    item_count       = item_count + 1,
                    updated_at       = NOW()
                WHERE id = $sessionId
            ");
            $conn->query("
                UPDATE trashsmart_devices 
                SET total_pilah_count = total_pilah_count + 1,
                    total_berat_kg    = total_berat_kg + $beratKg,
                    kapasitas_organik = LEAST(100, kapasitas_organik + 3),
                    status            = 'online',
                    last_ping         = NOW()
                WHERE kode_perangkat = '$deviceId'
            ");
        } else {
            $conn->query("
                UPDATE piru_sessions 
                SET total_anorganik_gr = total_anorganik_gr + $simWeight,
                    total_berat_gr     = total_berat_gr + $simWeight,
                    total_poin         = total_poin + $poinItem,
                    item_count         = item_count + 1,
                    updated_at         = NOW()
                WHERE id = $sessionId
            ");
            $conn->query("
                UPDATE trashsmart_devices 
                SET total_pilah_count   = total_pilah_count + 1,
                    total_berat_kg      = total_berat_kg + $beratKg,
                    kapasitas_anorganik = LEAST(100, kapasitas_anorganik + 3),
                    status              = 'online',
                    last_ping           = NOW()
                WHERE kode_perangkat = '$deviceId'
            ");
        }

        $userNama = $_SESSION['nama'] ?? 'Customer';
        $catDb = strtolower($simCategory);
        $pesanLog = "Simulasi Sensor IoT: '{$itemName}' ({$simCategory}) seberat {$simWeight}g pada {$deviceId}.";
        $stmtLog = $conn->prepare("
            INSERT INTO trashsmart_logs (
                device_id, tanggal, waktu, kategori_sampah, status_pilah, berat_kg, poin, pesan_suara, user_identifier, created_at
            ) VALUES (
                (SELECT id FROM trashsmart_devices WHERE kode_perangkat = ? LIMIT 1),
                CURDATE(), CURTIME(), ?, 'benar', ?, ?, ?, ?, NOW()
            )
        ");
        $stmtLog->bind_param('ssdiss', $deviceId, $catDb, $beratKg, $poinItem, $pesanLog, $userNama);
        $stmtLog->execute();

        echo json_encode([
            'success'       => true,
            'simulated'     => true,
            'category'      => $simCategory,
            'item_name'     => $itemName,
            'weight_grams'  => $simWeight,
            'points_earned' => $poinItem,
            'message'       => "Sensor IoT berhasil mendeteksi {$itemName}! (+{$poinItem} Poin)"
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
        break;
}
