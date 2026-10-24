<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

// Endpoint hanya menerima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Gunakan POST.']);
    exit;
}

// Baca body JSON
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format JSON tidak valid.']);
    exit;
}

$deviceId = trim($data['device_id'] ?? 'PIRU-DEV-001');
$action   = trim($data['action'] ?? 'log_item');

// Fungsi mapping nama objek YOLO ke label Indonesia ramah pengguna
function formatPiruItemName($rawName, $category) {
    $map = [
        'banana'       => '🍌 Kulit Pisang (banana)',
        'apple'        => '🍎 Sisa Buah Apel (apple)',
        'orange'       => '🍊 Kulit Jeruk (orange)',
        'sandwich'     => '🥪 Sisa Roti (sandwich)',
        'broccoli'     => '🥦 Sayuran Brokoli (broccoli)',
        'carrot'       => '🥕 Sisa Wortel (carrot)',
        'hot dog'      => '🌭 Sisa Makanan (hot dog)',
        'pizza'        => '🍕 Sisa Pizza (pizza)',
        'donut'        => '🍩 Sisa Donat (donut)',
        'cake'         => '🍰 Sisa Kue (cake)',
        'potted plant' => '🌿 Ranting / Daun Tanaman',
        'leaf'         => '🍂 Dedaunan Kering (leaf)',
        'food'         => '🍲 Sisa Makanan Organik (food)',
        'fruit'        => '🍎 Sisa Buah Organik (fruit)',
        'vegetable'    => '🥬 Sayuran Organik (vegetable)',
        'bread'        => '🍞 Sisa Roti (bread)',
        'meat'         => '🥩 Sisa Daging / Tulang (meat)',
        'sisa_makanan' => '🍛 Sisa Makanan Organik',
        'kulit_buah'   => '🍌 Kulit Buah Organik',
        'biological'   => '🍃 Sampah Organik Hayati',

        'bottle'       => '🍾 Botol Plastik (bottle)',
        'wine glass'   => '🍷 Botol / Gelas Kaca',
        'cup'          => '🥤 Gelas Plastik / Cup',
        'fork'         => '🍴 Sendok / Garpu Plastik',
        'spoon'        => '🥄 Sendok Plastik Sekali Pakai',
        'knife'        => '🔪 Pisau Plastik',
        'bowl'         => '🥣 Mangkuk Sekali Pakai',
        'can'          => '🥫 Kaleng Minuman (can)',
        'tin_can'      => '🥫 Kaleng Logam (tin can)',
        'box'          => '📦 Kardus Kemasan (box)',
        'cardboard'    => '📦 Kardus Karton (cardboard)',
        'paper'        => '📄 Kertas / Brosur (paper)',
        'plastic_bag'  => '🛍️ Kantong Plastik / Kresek',
        'toothbrush'   => '🪥 Sikat Gigi Bekas',
        'scissors'     => '✂️ Logam Gunting',
        'plastic'      => '🧴 Kemasan Plastik (plastic)',
        'metal'        => '⚙️ Kaleng / Logam (metal)',
        'battery'      => '🔋 Sampah Baterai Bekas',
        'cell phone'   => '📱 Sampah Elektronik (e-waste)',
        'remote'       => '🎛️ Remote / Plastik Keras',
        'keyboard'     => '⌨️ Aksesoris Komputer',
        'mouse'        => '🖱️ Mouse / E-Waste'
    ];

    $key = strtolower(trim((string)$rawName));
    if (isset($map[$key])) {
        return $map[$key];
    }
    if (!empty($rawName)) {
        return ($category === 'ORGANIK' ? '🌿 ' : '📦 ') . ucwords(str_replace(['_', '-'], ' ', $rawName));
    }
    return ($category === 'ORGANIK') ? '🌿 Sampah Organik' : '📦 Sampah Anorganik';
}

// Ambil ID device dari tabel trashsmart_devices
$stmtDev = $conn->prepare("SELECT id, nama_perangkat, status, kapasitas_organik, kapasitas_anorganik FROM trashsmart_devices WHERE kode_perangkat = ?");
$stmtDev->bind_param('s', $deviceId);
$stmtDev->execute();
$device = $stmtDev->get_result()->fetch_assoc();

if (!$device) {
    // Daftarkan otomatis jika belum ada di database
    $stmtAddDev = $conn->prepare("
        INSERT INTO trashsmart_devices (kode_perangkat, nama_perangkat, lokasi, status, kapasitas_organik, kapasitas_anorganik, baterai, suhu_celsius, firmware_version, last_ping)
        VALUES (?, 'PIRU Smart Bin #001', 'Area Utama / EcoHub Trashily', 'online', 20, 25, 100, 28.5, 'v2.0.0-YOLO', NOW())
    ");
    $stmtAddDev->bind_param('s', $deviceId);
    $stmtAddDev->execute();
    $internalDevId = $conn->insert_id;
} else {
    $internalDevId = intval($device['id']);
}

// ----------------------------------------------------------------------------
// HANDLER 1: PING / TELEMETRY UPDATE (Sensor Ultrasonik, Jarak, Suhu, Baterai)
// ----------------------------------------------------------------------------
if ($action === 'ping' || $action === 'telemetry') {
    $capOrg   = isset($data['kapasitas_organik']) ? intval($data['kapasitas_organik']) : null;
    $capAnorg = isset($data['kapasitas_anorganik']) ? intval($data['kapasitas_anorganik']) : null;
    $baterai  = isset($data['baterai']) ? intval($data['baterai']) : null;
    $suhu     = isset($data['suhu']) ? floatval($data['suhu']) : null;

    $updates = ["status = 'online'", "last_ping = NOW()"];
    $types = '';
    $params = [];

    if ($capOrg !== null) {
        $updates[] = "kapasitas_organik = ?";
        $types .= 'i';
        $params[] = max(0, min(100, $capOrg));
    }
    if ($capAnorg !== null) {
        $updates[] = "kapasitas_anorganik = ?";
        $types .= 'i';
        $params[] = max(0, min(100, $capAnorg));
    }
    if ($baterai !== null) {
        $updates[] = "baterai = ?";
        $types .= 'i';
        $params[] = max(0, min(100, $baterai));
    }
    if ($suhu !== null) {
        $updates[] = "suhu_celsius = ?";
        $types .= 'd';
        $params[] = $suhu;
    }

    $sql = "UPDATE trashsmart_devices SET " . implode(', ', $updates) . " WHERE id = ?";
    $types .= 'i';
    $params[] = $internalDevId;

    $stmtPing = $conn->prepare($sql);
    $stmtPing->bind_param($types, ...$params);
    $stmtPing->execute();

    echo json_encode([
        'success'   => true,
        'action'    => 'ping_acknowledged',
        'device_id' => $deviceId,
        'message'   => 'Telemetri IoT berhasil disinkronkan ke Trashily.'
    ]);
    exit;
}

// ----------------------------------------------------------------------------
// HANDLER 2: LOG TRANSAKSI PEMILAHAN SAMPAH (YOLO AI + TIMBANGAN/ITEM)
// ----------------------------------------------------------------------------
$category    = strtoupper(trim($data['category'] ?? 'ORGANIK'));
$weightGrams = floatval($data['weight_grams'] ?? ($data['weight_gr'] ?? 0.0));
$rawLabel    = trim($data['item_name'] ?? ($data['label'] ?? ''));

if ($weightGrams <= 0) {
    // Bobot default nominal per pemilahan jika load cell fisik belum aktif
    $weightGrams = 100.0;
}

// Normalisasi kategori
if ($category !== 'ORGANIK' && $category !== 'ANORGANIK') {
    $category = 'ANORGANIK';
}

$itemName = formatPiruItemName($rawLabel, $category);

// Periksa apakah ada SESI AKTIF pengguna di perangkat ini (dalam 15 menit terakhir)
$stmtSession = $conn->prepare("
    SELECT ps.id, ps.session_token, ps.user_id, ps.status, u.nama, u.email, u.poin
    FROM piru_sessions ps
    JOIN users u ON ps.user_id = u.id
    WHERE ps.device_kode = ? 
      AND ps.status = 'active'
      AND ps.updated_at >= NOW() - INTERVAL 15 MINUTE
    ORDER BY ps.id DESC LIMIT 1
");
$stmtSession->bind_param('s', $deviceId);
$stmtSession->execute();
$activeSession = $stmtSession->get_result()->fetch_assoc();

$beratKg = round($weightGrams / 1000.0, 3);
$poinItem = 0;

// Kalibrasi Poin Trashily PIRU
$poinPerItemOrganik   = 10; // +10 Poin per 1 item sampah organik
$poinPerItemAnorganik = 15; // +15 Poin per 1 item sampah anorganik

if ($category === 'ORGANIK') {
    $poinItem = $poinPerItemOrganik;
} else {
    $poinItem = $poinPerItemAnorganik;
}

$conn->begin_transaction();

try {
    $userName  = 'Guest / Tanpa Akun';
    $sessionId = null;

    if ($activeSession) {
        $sessionId = intval($activeSession['id']);
        $userName  = $activeSession['nama'];

        // 1. Catat item ke tabel piru_session_items (dengan nama item terdeteksi AI)
        $stmtItem = $conn->prepare("
            INSERT INTO piru_session_items (session_id, category, item_name, weight_gr, poin, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmtItem->bind_param('issdi', $sessionId, $category, $itemName, $weightGrams, $poinItem);
        $stmtItem->execute();

        // 2. Update akumulasi sesi di piru_sessions
        if ($category === 'ORGANIK') {
            $stmtUpdSes = $conn->prepare("
                UPDATE piru_sessions
                SET total_organik_gr = total_organik_gr + ?,
                    total_berat_gr   = total_berat_gr + ?,
                    total_poin       = total_poin + ?,
                    item_count       = item_count + 1,
                    updated_at       = NOW()
                WHERE id = ?
            ");
            $stmtUpdSes->bind_param('ddii', $weightGrams, $weightGrams, $poinItem, $sessionId);
        } else {
            $stmtUpdSes = $conn->prepare("
                UPDATE piru_sessions
                SET total_anorganik_gr = total_anorganik_gr + ?,
                    total_berat_gr     = total_berat_gr + ?,
                    total_poin         = total_poin + ?,
                    item_count         = item_count + 1,
                    updated_at         = NOW()
                WHERE id = ?
            ");
            $stmtUpdSes->bind_param('ddii', $weightGrams, $weightGrams, $poinItem, $sessionId);
        }
        $stmtUpdSes->execute();
    }

    // 3. Update status & telemetri kapasitas perangkat di trashsmart_devices
    // Tambah estimasi kapasitas +3% tiap pemilahan masuk (maksimal 100%)
    if ($category === 'ORGANIK') {
        $stmtDevUpd = $conn->prepare("
            UPDATE trashsmart_devices
            SET total_pilah_count = total_pilah_count + 1,
                total_berat_kg    = total_berat_kg + ?,
                kapasitas_organik = LEAST(100, kapasitas_organik + 3),
                status            = 'online',
                last_ping         = NOW()
            WHERE id = ?
        ");
    } else {
        $stmtDevUpd = $conn->prepare("
            UPDATE trashsmart_devices
            SET total_pilah_count   = total_pilah_count + 1,
                total_berat_kg      = total_berat_kg + ?,
                kapasitas_anorganik = LEAST(100, kapasitas_anorganik + 3),
                status              = 'online',
                last_ping           = NOW()
            WHERE id = ?
        ");
    }
    $stmtDevUpd->bind_param('di', $beratKg, $internalDevId);
    $stmtDevUpd->execute();

    // 4. Catat log event ke trashsmart_logs untuk telemetry dashboard admin
    $catDb = strtolower($category);
    $pesanLog = "AI YOLO memvalidasi '{$itemName}' ({$category}) seberat {$weightGrams}g pada {$deviceId}.";
    $stmtLog = $conn->prepare("
        INSERT INTO trashsmart_logs (
            device_id, tanggal, waktu, kategori_sampah, status_pilah, berat_kg, poin, pesan_suara, user_identifier, created_at
        ) VALUES (?, CURDATE(), CURTIME(), ?, 'benar', ?, ?, ?, ?, NOW())
    ");
    $stmtLog->bind_param('isdiss', $internalDevId, $catDb, $beratKg, $poinItem, $pesanLog, $userName);
    $stmtLog->execute();

    $conn->commit();

    echo json_encode([
        'success'           => true,
        'session_active'    => ($activeSession !== null),
        'session_id'        => $sessionId,
        'user_name'         => $userName,
        'device_id'         => $deviceId,
        'category'          => $category,
        'item_name'         => $itemName,
        'weight_grams'      => $weightGrams,
        'points_earned'     => $poinItem,
        'message'           => $activeSession 
            ? "Sampah {$itemName} berhasil dicatat untuk {$userName}! (+{$poinItem} Poin)"
            : "Sampah {$itemName} dicatat sebagai tamu."
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal memproses data pemilahan: ' . $e->getMessage()
    ]);
}
