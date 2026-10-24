<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Verifikasi sesi login (Customer atau Admin)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$currentUserId = intval($_SESSION['user_id']);
$currentUserRole = $_SESSION['role'] ?? 'customer';

$sessionId   = intval($_GET['session_id'] ?? 0);
$transaksiId = intval($_GET['transaksi_id'] ?? 0);
$token       = trim($_GET['token'] ?? '');

$session = null;
if ($sessionId > 0) {
    $stmt = $conn->prepare("
        SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
               t.kode_transaksi, t.created_at as tgl_transaksi, d.nama_perangkat, d.lokasi as device_lokasi
        FROM piru_sessions s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN transaksi t ON s.transaksi_id = t.id
        LEFT JOIN trashsmart_devices d ON s.device_kode = d.kode_perangkat
        WHERE s.id = ?
    ");
    $stmt->bind_param('i', $sessionId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
} elseif ($transaksiId > 0) {
    $stmt = $conn->prepare("
        SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
               t.kode_transaksi, t.created_at as tgl_transaksi, d.nama_perangkat, d.lokasi as device_lokasi
        FROM piru_sessions s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN transaksi t ON s.transaksi_id = t.id
        LEFT JOIN trashsmart_devices d ON s.device_kode = d.kode_perangkat
        WHERE s.transaksi_id = ?
    ");
    $stmt->bind_param('i', $transaksiId);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
} elseif ($token !== '') {
    $stmt = $conn->prepare("
        SELECT s.*, u.nama as customer_nama, u.email as customer_email, u.no_hp as customer_nohp,
               t.kode_transaksi, t.created_at as tgl_transaksi, d.nama_perangkat, d.lokasi as device_lokasi
        FROM piru_sessions s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN transaksi t ON s.transaksi_id = t.id
        LEFT JOIN trashsmart_devices d ON s.device_kode = d.kode_perangkat
        WHERE s.session_token = ?
    ");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
}

if (!$session) {
    die("<h3>Data sesi pemilahan PIRU tidak ditemukan.</h3><p><a href='dashboard.php'>Kembali</a></p>");
}

// Otorisasi: Hanya pemilik sesi atau admin yang boleh melihat
if ($currentUserRole !== 'admin' && intval($session['user_id']) !== $currentUserId) {
    http_response_code(403);
    die("<h3>Akses ditolak: Anda tidak memiliki izin melihat tanda terima sesi ini.</h3>");
}

$sessionId = intval($session['id']);

// Ambil item yang dipilah dalam sesi ini
$items_stmt = $conn->prepare("
    SELECT id, category, item_name, weight_gr, poin, DATE_FORMAT(created_at, '%H:%i:%s') as waktu 
    FROM piru_session_items 
    WHERE session_id = ? 
    ORDER BY id ASC
");
$items_stmt->bind_param('i', $sessionId);
$items_stmt->execute();
$items = $items_stmt->get_result();

$totalWeightKg = round(floatval($session['total_berat_gr']) / 1000, 2);
// Hitung dampak ekologis estimasi (1 kg sampah terpilah = ~1.85 kg pengurangan CO2e)
$co2Saved = round($totalWeightKg * 1.85, 2);

$qrUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . BASE_URL . "/customer/export-session-pdf.php?session_id=" . $sessionId;
$qrApi = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrUrl);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk_PIRU_<?= htmlspecialchars($session['kode_transaksi'] ?? substr($session['session_token'], 0, 8)) ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Action Bar */
        .no-print-bar {
            background: #064e29;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        .btn-print {
            background: #10b981;
            color: #fff;
            border: none;
            padding: 8px 18px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #059669; }
        .btn-back {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-back:hover { color: #fff; }

        /* Receipt Card Container */
        .receipt-card {
            max-width: 520px;
            margin: 24px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }

        /* Receipt Header */
        .receipt-header {
            background: linear-gradient(135deg, #064e29 0%, #006e2f 60%, #059669 100%);
            color: #fff;
            padding: 24px 24px 20px 24px;
            text-align: center;
            position: relative;
        }
        .receipt-header h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }
        .receipt-header p {
            font-size: 11px;
            color: #a7f3d0;
            font-weight: 500;
        }
        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,0.18);
            backdrop-filter: blur(4px);
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 700;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        /* Poin Reward Hero */
        .points-hero {
            background: #ecfdf5;
            border-bottom: 2px dashed #cbd5e1;
            padding: 16px 24px;
            text-align: center;
        }
        .points-val {
            font-size: 32px;
            font-weight: 800;
            color: #006e2f;
            line-height: 1;
        }
        .points-lbl {
            font-size: 11px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Meta List */
        .receipt-body {
            padding: 20px 24px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11.5px;
        }
        .meta-row:last-child { border-bottom: none; }
        .meta-lbl { color: #64748b; font-weight: 500; }
        .meta-val { font-weight: 700; color: #0f172a; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Items Table */
        .items-title {
            font-size: 11px;
            font-weight: 800;
            color: #006e2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 18px 0 8px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 16px;
        }
        .items-table th {
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 6px 8px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .cat-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 9.5px;
            font-weight: 700;
        }
        .cat-org { background: #dcfce7; color: #166534; }
        .cat-anorg { background: #e0f2fe; color: #0369a1; }

        /* Eco Impact Card */
        .eco-card {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 12px 16px;
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .eco-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #22c55e;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .eco-text h4 {
            font-size: 11px;
            font-weight: 800;
            color: #166534;
        }
        .eco-text p {
            font-size: 10px;
            color: #15803d;
            margin-top: 2px;
        }

        /* Receipt Footer */
        .receipt-footer {
            background: #f8fafc;
            border-top: 1px dashed #cbd5e1;
            padding: 18px 24px;
            text-align: center;
        }
        .receipt-footer img {
            width: 72px;
            height: 72px;
            margin: 0 auto 8px auto;
            display: block;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .footer-note {
            font-size: 9.5px;
            color: #94a3b8;
        }

        @media print {
            body { background: #fff; }
            .no-print-bar { display: none !important; }
            .receipt-card {
                box-shadow: none;
                margin: 0 auto;
                border: 1px solid #cbd5e1;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- ACTION BAR (Browser Only) -->
    <div class="no-print-bar">
        <a href="<?= $currentUserRole === 'admin' ? '../admin/monitoring-piru.php' : 'riwayat.php' ?>" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>

        <div style="font-weight:700; font-size:13px;">
            Bukti Sesi Pemilahan PIRU
        </div>

        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-file-pdf"></i> Unduh / Cetak PDF
        </button>
    </div>

    <!-- STRUK BUKTI RESMI -->
    <div class="receipt-card">
        
        <!-- HEADER -->
        <div class="receipt-header">
            <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-bottom:4px;">
                <img src="../assets/brand.png" alt="Trashily" style="height:26px; width:auto; filter:brightness(0) invert(1);">
                <span style="font-weight:800; font-size:16px;">Trashily.id</span>
            </div>
            <h1>PIRU Smart Bin</h1>
            <p>Bukti Pemilahan Sampah Berbasis IoT &amp; AI Computer Vision</p>

            <div class="badge-verified">
                <i class="fa-solid fa-circle-check text-emerald-300"></i> Transaksi Terverifikasi
            </div>
        </div>

        <!-- POIN REWARD -->
        <div class="points-hero">
            <div class="points-val">+<?= number_format($session['total_poin']) ?></div>
            <div class="points-lbl">Poin Reward Trashily Masuk ke Akun</div>
        </div>

        <!-- BODY -->
        <div class="receipt-body">
            
            <div class="meta-row">
                <span class="meta-lbl">Kode Transaksi:</span>
                <span class="meta-val font-mono" style="color:#006e2f;"><?= htmlspecialchars($session['kode_transaksi'] ?? 'PIRU-SESS-' . $session['id']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-lbl">Customer:</span>
                <span class="meta-val"><?= htmlspecialchars($session['customer_nama']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-lbl">Kontak:</span>
                <span class="meta-val"><?= htmlspecialchars($session['customer_nohp'] ?? $session['customer_email']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-lbl">Waktu Pemilahan:</span>
                <span class="meta-val"><?= date('d F Y, H:i', strtotime($session['created_at'])) ?> WIB</span>
            </div>
            <div class="meta-row">
                <span class="meta-lbl">Perangkat PIRU:</span>
                <span class="meta-val"><?= htmlspecialchars($session['nama_perangkat'] ?? $session['device_kode']) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-lbl">Lokasi Unit:</span>
                <span class="meta-val"><?= htmlspecialchars($session['device_lokasi'] ?? 'EcoHub Trashily') ?></span>
            </div>

            <!-- RINCIAN ITEM SAMPAH -->
            <div class="items-title">
                <span><i class="fa-solid fa-list-check"></i> Daftar Sampah Terpilah (<?= $session['item_count'] ?> Item)</span>
                <span>Bobot: <?= $totalWeightKg ?> kg</span>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width:30px;">#</th>
                        <th>Kategori</th>
                        <th>Waktu</th>
                        <th class="text-right">Bobot</th>
                        <th class="text-right">Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" class="text-center" style="color:#94a3b8; padding:12px;">
                            Tidak ada item terdaftar.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        while ($item = $items->fetch_assoc()): 
                            $isOrg = ($item['category'] === 'ORGANIK');
                            $itemName = $item['item_name'] ?? ($isOrg ? '🌿 Sampah Organik' : '📦 Sampah Anorganik');
                        ?>
                        <tr>
                            <td class="font-mono" style="color:#64748b;"><?= $no++ ?></td>
                            <td>
                                <div style="font-weight:700; color:#0f172a; margin-bottom:3px; font-size:11px;">
                                    <?= htmlspecialchars($itemName) ?>
                                </div>
                                <span class="cat-badge <?= $isOrg ? 'cat-org' : 'cat-anorg' ?>">
                                    <i class="fa-solid <?= $isOrg ? 'fa-leaf' : 'fa-bottle-water' ?>"></i>
                                    <?= htmlspecialchars($item['category']) ?>
                                </span>
                            </td>
                            <td class="font-mono" style="font-size:10px; color:#64748b;"><?= $item['waktu'] ?></td>
                            <td class="text-right font-mono"><?= number_format($item['weight_gr'], 0) ?> gr</td>
                            <td class="text-right font-bold" style="color:#006e2f;">+<?= $item['poin'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- DAMPAK EKOLOGIS -->
            <div class="eco-card">
                <div class="eco-icon">
                    <i class="fa-solid fa-earth-americas"></i>
                </div>
                <div class="eco-text">
                    <h4>Kontribusi Ekologis Anda</h4>
                    <p>Pemilahan sampah Anda menghemat estimasi <b><?= $co2Saved ?> kg CO2e</b> dan mendukung pengurangan timbulan sampah ke TPA Galuga!</p>
                </div>
            </div>

        </div>

        <!-- FOOTER & QR VERIFIKASI -->
        <div class="receipt-footer">
            <img src="<?= $qrApi ?>" alt="QR Verifikasi Sesi">
            <div class="font-mono" style="font-size:9.5px; font-weight:700; color:#334155; margin-bottom:4px;">
                TOKEN: <?= substr($session['session_token'], 0, 16) ?>...
            </div>
            <p class="footer-note">
                Terima kasih telah memilah sampah secara cerdas bersama Trashily PIRU.<br>
                Simpan struk ini sebagai bukti resmi perolehan poin reward Anda.
            </p>
        </div>

    </div>

</body>
</html>
