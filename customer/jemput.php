<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];

// Refresh poin
$user = $conn->query("SELECT nama, poin, alamat FROM users WHERE id = $uid")->fetch_assoc();
$_SESSION['poin'] = $user['poin'];

// Pengaturan lokasi Trashily
$res_pengaturan = $conn->query("SELECT kunci, nilai FROM pengaturan");
$pengaturan = [
    'trashily_lat'    => '-6.200000',
    'trashily_lng'    => '106.816666',
    'trashily_alamat' => 'Trashily Central Waste Bank',
    'tarif_per_km'    => '2000'
];
if ($res_pengaturan) {
    while ($row = $res_pengaturan->fetch_assoc()) {
        $pengaturan[$row['kunci']] = $row['nilai'];
    }
}

// Daftar jenis sampah aktif
$jenis_list = $conn->query("SELECT id, nama, kategori, poin_per_kg FROM jenis_sampah WHERE is_active = 1 ORDER BY kategori, nama");
$jenis_arr = [];
while ($j = $jenis_list->fetch_assoc()) $jenis_arr[] = $j;

// Riwayat permintaan jemput
$page  = max(1, intval($_GET['page'] ?? 1));
$limit = 8;
$offset = ($page - 1) * $limit;

$total_rows  = $conn->query("SELECT COUNT(*) as c FROM jemput_sampah WHERE customer_id = $uid")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT js.id, js.kode_jemput, js.alamat_jemput, js.jarak_km, js.biaya_ongkir, js.jadwal_jemput,
           js.status, js.catatan_customer, js.catatan_admin, js.created_at,
           t.kode_transaksi, t.total_poin
    FROM jemput_sampah js
    LEFT JOIN transaksi t ON js.transaksi_id = t.id
    WHERE js.customer_id = $uid
    ORDER BY js.created_at DESC
    LIMIT $limit OFFSET $offset
");

$kategori_icon = [
    'organik_kering' => 'fa-seedling',
    'plastik'        => 'fa-bottle-water',
    'kertas'         => 'fa-file',
    'logam'          => 'fa-screwdriver-wrench',
    'kaca'           => 'fa-wine-bottle',
    'elektronik'     => 'fa-microchip',
    'lainnya'        => 'fa-box',
];
$status_badge = [
    'menunggu'     => ['badge-orange', 'Menunggu'],
    'dikonfirmasi' => ['badge-blue',   'Dikonfirmasi'],
    'dijemput'     => ['badge-blue',   'Sedang Dijemput'],
    'selesai'      => ['badge-green',  'Selesai'],
    'batal'        => ['badge-red',    'Dibatalkan'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jemput Sampah — Trashily</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="stylesheet" href="../assets/css/output.css">
    <style>
        .jenis-checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: .6rem;
        }
        .jenis-checkbox-item {
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: .65rem .875rem;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            display: flex;
            align-items: center;
            gap: .6rem;
            user-select: none;
        }
        .jenis-checkbox-item:hover { border-color: var(--primary); background: var(--primary-light); }
        .jenis-checkbox-item.checked { border-color: var(--primary); background: var(--primary-light); }
        .jenis-checkbox-item input[type=checkbox] { display: none; }
        .jenis-icon { width: 30px; height: 30px; border-radius: 8px; background: var(--bg-light); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .8rem; color: var(--primary); }
        .jenis-checkbox-item.checked .jenis-icon { background: var(--primary); color: #fff; }
        .jenis-label { font-size: .8rem; font-weight: 600; color: var(--text-dark); line-height: 1.3; }
        .jenis-poin { font-size: .7rem; color: var(--text-muted); }

        .status-timeline { display: flex; gap: .5rem; align-items: center; flex-wrap: wrap; }
        .timeline-step { display: flex; align-items: center; gap: .4rem; font-size: .75rem; color: var(--text-muted); }
        .timeline-step .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--border); flex-shrink: 0; }
        .timeline-step.done .dot { background: var(--primary); }
        .timeline-step.done { color: var(--primary-dark); font-weight: 600; }
        .timeline-step.active .dot { background: var(--accent); }
        .timeline-step.active { color: var(--accent); font-weight: 600; }
        .timeline-sep { width: 16px; height: 1px; background: var(--border); flex-shrink: 0; }

        #mapPicker {
            height: 260px;
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            margin-top: .5rem;
            z-index: 1;
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_customer.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Jemput Sampah</h1>
                    <p>Ajukan penjemputan sampah ke lokasi Anda dengan kalkulasi ongkir peta</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-star"></i>
                    <?= number_format($user['poin'], 0, ',', '.') ?> Poin
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Info Banner -->
            <div style="background:var(--info-light);border:1.5px solid #BBDEFB;border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.875rem">
                <div style="width:36px;height:36px;background:var(--info);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-map-location-dot" style="color:#fff;font-size:.9rem"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.875rem;color:var(--info);margin-bottom:.2rem">Peta Penjemputan Otomatis & Ongkir</div>
                    <div style="font-size:.8rem;color:#1565C0;line-height:1.6">
                        Pilih titik jemput Anda pada peta interaktif. Jarak ke <b>Pusat Trashily (<?= htmlspecialchars($pengaturan['trashily_alamat']) ?>)</b> 
                        dan ongkos kirim akan otomatis dihitung secara presisi (Tarif: Rp <?= number_format($pengaturan['tarif_per_km'], 0, ',', '.') ?> / km).
                    </div>
                </div>
            </div>

            <!-- Riwayat Permintaan -->
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Permintaan Jemput</h3>
                    <button class="btn btn-primary btn-sm" onclick="openModalJemput()">
                        <i class="fa-solid fa-truck"></i> Minta Dijemput
                    </button>
                </div>

                <?php if ($list->num_rows === 0): ?>
                <div class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa-solid fa-truck-ramp-box" style="font-size:3rem;opacity:.3"></i></div>
                    <p>Belum ada permintaan jemput sampah</p>
                    <button class="btn btn-primary" style="margin-top:1rem" onclick="openModalJemput()">
                        <i class="fa-solid fa-plus"></i> Buat Permintaan
                    </button>
                </div>

                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jadwal</th>
                                <th>Alamat</th>
                                <th>Jarak & Ongkir</th>
                                <th>Status</th>
                                <th>Poin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list->fetch_assoc()):
                                [$badge_cls, $badge_txt] = $status_badge[$row['status']] ?? ['badge-gray', ucfirst($row['status'])];
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-blue"><?= $row['kode_jemput'] ?></span>
                                    <div class="text-muted text-small"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:.875rem;font-weight:600"><?= date('d M Y', strtotime($row['jadwal_jemput'])) ?></div>
                                    <div class="text-muted text-small"><?= date('H:i', strtotime($row['jadwal_jemput'])) ?> WIB</div>
                                </td>
                                <td style="max-width:180px">
                                    <div style="font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($row['alamat_jemput']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:.85rem;font-weight:600"><?= number_format($row['jarak_km'] ?? 0, 1, ',', '.') ?> km</div>
                                    <div class="text-muted text-small">Rp <?= number_format($row['biaya_ongkir'] ?? 0, 0, ',', '.') ?></div>
                                </td>
                                <td><span class="badge <?= $badge_cls ?>"><?= $badge_txt ?></span></td>
                                <td>
                                    <?php if ($row['status'] === 'selesai' && $row['total_poin']): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['total_poin']) ?> poin</strong>
                                    <?php else: ?>
                                    <span class="text-muted text-small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.4rem">
                                        <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <?php if ($row['status'] === 'menunggu'): ?>
                                        <button class="btn btn-danger btn-sm" onclick="batalPermintaan(<?= $row['id'] ?>)" title="Batalkan">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <div style="padding:1rem 1.25rem;display:flex;justify-content:center">
                    <div class="pagination">
                        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a><?php endif; ?>
                        <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <a href="?page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- MODAL BUAT PERMINTAAN JEMPUT -->
<div class="modal-overlay" id="modalJemput">
    <div class="modal" style="max-width:680px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-truck" style="color:var(--primary);margin-right:.5rem"></i>Minta Jemput Sampah</h3>
            <button class="modal-close" onclick="closeModalJemput()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" style="max-height:80vh;overflow-y:auto">
            <!-- Peta Pemilihan Titik Jemput -->
            <div class="form-group">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.3rem">
                    <label class="form-label" style="margin:0">Titik Jemput pada Peta <span style="color:var(--danger)">*</span></label>
                    <button type="button" class="btn btn-ghost btn-sm" onclick="getUserGPS()" style="padding:.2rem .6rem;font-size:.75rem">
                        <i class="fa-solid fa-location-crosshairs" style="color:var(--primary)"></i> Lokasi GPS Saya
                    </button>
                </div>
                <div class="input-wrap" style="position:relative">
                    <input type="text" id="searchCustomerMap" class="form-control" placeholder="Cari nama jalan / kelurahan..." autocomplete="off">
                    <button type="button" class="btn btn-primary btn-sm" onclick="cariLokasiCustomer()" style="position:absolute;right:6px;top:50%;transform:translateY(-50%)">
                        <i class="fa-solid fa-magnifying-glass"></i> Cari
                    </button>
                </div>
                <div id="mapPicker"></div>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.3rem;display:flex;justify-content:space-between">
                    <span><i class="fa-solid fa-hand-pointer"></i> Klik peta atau geser pin biru ke lokasi Anda</span>
                    <span id="custMapStatus" style="font-weight:600;color:var(--primary-dark)">Siap</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Lengkap Penjemputan</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-location-dot input-icon" style="top:1rem;transform:none;position:absolute;left:1rem;color:var(--text-muted)"></i>
                    <textarea class="form-control" id="alamatJemput" style="padding-left:2.75rem" placeholder="Jl. Contoh No. 1, RT 01/02, Kelurahan..." rows="2"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Jarak & Ongkir Box -->
            <div class="form-group">
                <div style="background:var(--primary-light);border:1.5px dashed var(--primary);border-radius:12px;padding:.875rem 1.25rem;display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:center">
                    <div>
                        <div style="font-size:.75rem;color:var(--text-muted)">Jarak dari Pusat Trashily:</div>
                        <div style="font-size:1.15rem;font-weight:800;color:var(--primary-dark)" id="jarakDisplay">0.0 km</div>
                        <input type="hidden" id="jarakJemput" value="0.0">
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:.75rem;color:var(--text-muted)">Perkiraan Ongkir (Rp <?= number_format($pengaturan['tarif_per_km'], 0, ',', '.') ?>/km):</div>
                        <div style="font-size:1.25rem;font-weight:800;color:var(--primary-dark)" id="ongkirPreview">Rp 0</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Jadwal Penjemputan <span style="color:var(--danger)">*</span></label>
                <div class="input-wrap">
                    <i class="fa-regular fa-calendar input-icon" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                    <input type="datetime-local" class="form-control" id="jadwalJemput" style="padding-left:2.75rem" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Jenis Sampah</label>
                <div class="jenis-checkbox-grid" id="jenisGrid">
                    <?php foreach ($jenis_arr as $j):
                        $icon = $kategori_icon[$j['kategori']] ?? 'fa-box';
                    ?>
                    <div class="jenis-checkbox-item" data-jenis-id="<?= $j['id'] ?>" onclick="toggleJenis(this, event)">
                        <input type="checkbox" value="<?= $j['id'] ?>">
                        <div class="jenis-icon"><i class="fa-solid <?= $icon ?>"></i></div>
                        <div>
                            <div class="jenis-label"><?= htmlspecialchars($j['nama']) ?></div>
                            <div class="jenis-poin"><?= $j['poin_per_kg'] ?> poin/kg</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Est. Berat per Jenis -->
            <div id="jenisBeratSection" style="display:none">
                <div class="form-group">
                    <label class="form-label">Estimasi Berat per Jenis</label>
                    <div id="jenisBeratRows"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(opsional)</span></label>
                <textarea class="form-control" id="catatanJemput" placeholder="Mis: sampah sudah dikumpulkan di depan pintu..." rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModalJemput()">Batal</button>
            <button class="btn btn-primary" onclick="kirimPermintaan()">
                <i class="fa-solid fa-paper-plane"></i> Kirim Permintaan
            </button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-truck" style="color:var(--primary);margin-right:.5rem"></i>Detail Permintaan</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>
        </div>
    </div>
</div>

<script>
const trashilyLat = parseFloat("<?= $pengaturan['trashily_lat'] ?>") || -6.200000;
const trashilyLng = parseFloat("<?= $pengaturan['trashily_lng'] ?>") || 106.816666;
const tarifPerKm  = parseFloat("<?= $pengaturan['tarif_per_km'] ?>") || 2000;

let custMap, trashilyMarker, userMarker, routeLine;
let currentCustLat = trashilyLat + 0.01;
let currentCustLng = trashilyLng + 0.01;

const jenisList = <?= json_encode($jenis_arr) ?>;
const jenisMeta = {};
jenisList.forEach(j => { jenisMeta[j.id] = j; });

const kategoriIcon = {
    organik_kering: 'fa-seedling',
    plastik: 'fa-bottle-water',
    kertas: 'fa-file',
    logam: 'fa-screwdriver-wrench',
    kaca: 'fa-wine-bottle',
    elektronik: 'fa-microchip',
    lainnya: 'fa-box'
};

const statusInfo = {
    menunggu:     { cls: 'badge-orange', txt: 'Menunggu Konfirmasi' },
    dikonfirmasi: { cls: 'badge-blue',   txt: 'Dikonfirmasi' },
    dijemput:     { cls: 'badge-blue',   txt: 'Sedang Dijemput' },
    selesai:      { cls: 'badge-green',  txt: 'Selesai' },
    batal:        { cls: 'badge-red',    txt: 'Dibatalkan' },
};

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // radius bumi km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function initCustomerMap() {
    if (custMap) {
        custMap.invalidateSize();
        return;
    }

    custMap = L.map('mapPicker').setView([trashilyLat, trashilyLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(custMap);

    // Icon Trashily
    const trashIcon = L.divIcon({
        className: 'trashily-map-icon',
        html: `<div style="background:#4CAF50;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,.3);border:2px solid #fff;font-size:1rem"><i class="fa-solid fa-leaf"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    // Icon Customer Pin
    const userIcon = L.divIcon({
        className: 'user-map-icon',
        html: `<div style="background:#1E88E5;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,.3);border:2px solid #fff;font-size:1rem"><i class="fa-solid fa-house-user"></i></div>`,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    trashilyMarker = L.marker([trashilyLat, trashilyLng], { icon: trashIcon }).addTo(custMap);
    trashilyMarker.bindPopup("<b>Pusat Trashily</b>").openPopup();

    userMarker = L.marker([currentCustLat, currentCustLng], { draggable: true, icon: userIcon }).addTo(custMap);
    userMarker.bindPopup("<b>Titik Jemput Anda</b>");

    routeLine = L.polyline([[trashilyLat, trashilyLng], [currentCustLat, currentCustLng]], { color: '#1E88E5', weight: 3, dashArray: '6, 6' }).addTo(custMap);

    userMarker.on('drag', function(e) {
        const pos = e.target.getLatLng();
        updateDistanceAndRoute(pos.lat, pos.lng);
    });

    userMarker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        reverseGeocodeCustomer(pos.lat, pos.lng);
    });

    custMap.on('click', function(e) {
        userMarker.setLatLng(e.latlng);
        updateDistanceAndRoute(e.latlng.lat, e.latlng.lng);
        reverseGeocodeCustomer(e.latlng.lat, e.latlng.lng);
    });

    updateDistanceAndRoute(currentCustLat, currentCustLng);
}

function updateDistanceAndRoute(lat, lng) {
    currentCustLat = lat;
    currentCustLng = lng;
    if (routeLine) {
        routeLine.setLatLngs([[trashilyLat, trashilyLng], [lat, lng]]);
    }
    const dist = calculateDistance(trashilyLat, trashilyLng, lat, lng);
    const distFmt = Math.max(0.1, dist).toFixed(1);
    const ongkir = Math.round(parseFloat(distFmt) * tarifPerKm);

    document.getElementById('jarakDisplay').textContent = `${distFmt} km`;
    document.getElementById('jarakJemput').value = distFmt;
    document.getElementById('ongkirPreview').textContent = `Rp ${ongkir.toLocaleString('id-ID')}`;
}

function reverseGeocodeCustomer(lat, lng) {
    document.getElementById('custMapStatus').textContent = 'Mencari alamat...';
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(r => r.json())
        .then(res => {
            if (res && res.display_name) {
                document.getElementById('alamatJemput').value = res.display_name;
            }
            document.getElementById('custMapStatus').textContent = 'Titik terpilih';
        })
        .catch(() => {
            document.getElementById('custMapStatus').textContent = 'Titik terpilih';
        });
}

function cariLokasiCustomer() {
    const q = document.getElementById('searchCustomerMap').value.trim();
    if (!q) return;
    document.getElementById('custMapStatus').textContent = 'Mencari...';
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(res => {
            if (res && res.length > 0) {
                const item = res[0];
                const lat = parseFloat(item.lat);
                const lon = parseFloat(item.lon);
                custMap.setView([lat, lon], 15);
                userMarker.setLatLng([lat, lon]);
                updateDistanceAndRoute(lat, lon);
                document.getElementById('alamatJemput').value = item.display_name;
                document.getElementById('custMapStatus').textContent = 'Ketemu!';
            } else {
                Swal.fire({ icon: 'warning', title: 'Tidak ditemukan', text: 'Lokasi tidak ditemukan.', confirmButtonColor: '#4CAF50' });
                document.getElementById('custMapStatus').textContent = 'Tidak ditemukan';
            }
        });
}

function getUserGPS() {
    if (!navigator.geolocation) {
        Swal.fire({ icon: 'error', title: 'GPS Tidak Didukung', text: 'Browser tidak mendukung GPS.', confirmButtonColor: '#4CAF50' });
        return;
    }
    document.getElementById('custMapStatus').textContent = 'Mengambil GPS...';
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        custMap.setView([lat, lng], 15);
        userMarker.setLatLng([lat, lng]);
        updateDistanceAndRoute(lat, lng);
        reverseGeocodeCustomer(lat, lng);
    }, err => {
        Swal.fire({ icon: 'error', title: 'Gagal GPS', text: err.message, confirmButtonColor: '#4CAF50' });
        document.getElementById('custMapStatus').textContent = 'Gagal GPS';
    });
}

function setMinDatetime() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const val = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    document.getElementById('jadwalJemput').min = val;
}

function openModalJemput() {
    document.getElementById('modalJemput').classList.add('open');
    setMinDatetime();
    document.querySelectorAll('.jenis-checkbox-item').forEach(el => {
        el.classList.remove('checked');
        el.querySelector('input').checked = false;
    });
    document.getElementById('jenisBeratSection').style.display = 'none';
    document.getElementById('jenisBeratRows').innerHTML = '';
    document.getElementById('catatanJemput').value = '';
    document.getElementById('jadwalJemput').value = '';

    setTimeout(() => {
        initCustomerMap();
    }, 200);
}

function closeModalJemput() {
    document.getElementById('modalJemput').classList.remove('open');
}

function toggleJenis(el, event) {
    if (event) event.stopPropagation();
    const cb = el.querySelector('input[type="checkbox"]');
    if (event && event.target === cb) {
        el.classList.toggle('checked', cb.checked);
    } else {
        cb.checked = !cb.checked;
        el.classList.toggle('checked', cb.checked);
    }
    updateJenisBeratRows();
}

function updateJenisBeratRows() {
    const checked = [...document.querySelectorAll('.jenis-checkbox-item.checked')];
    const section = document.getElementById('jenisBeratSection');
    const rows    = document.getElementById('jenisBeratRows');

    if (checked.length === 0) {
        section.style.display = 'none';
        rows.innerHTML = '';
        return;
    }

    section.style.display = 'block';
    const existing = {};
    rows.querySelectorAll('.jenis-berat-row').forEach(r => {
        existing[r.dataset.jenisId] = r.querySelector('input').value;
    });

    rows.innerHTML = '';
    checked.forEach(el => {
        const id   = el.dataset.jenisId;
        const meta = jenisMeta[id];
        if (!meta) return;
        const icon = kategoriIcon[meta.kategori] || 'fa-box';
        const prevVal = existing[id] || '';

        const div = document.createElement('div');
        div.className = 'jenis-berat-row';
        div.dataset.jenisId = id;
        div.style.cssText = 'display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;background:var(--bg-light);border:1.5px solid var(--border);border-radius:12px;margin-bottom:.5rem';
        div.innerHTML = `
            <div style="width:32px;height:32px;background:var(--primary-light);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fa-solid ${icon}" style="color:var(--primary);font-size:.8rem"></i>
            </div>
            <div style="flex:1">
                <div style="font-size:.85rem;font-weight:600;color:var(--text-dark)">${meta.nama}</div>
                <div style="font-size:.72rem;color:var(--text-muted)">${meta.poin_per_kg} poin/kg</div>
            </div>
            <input type="number" class="form-control" placeholder="Est. kg" min="0.1" step="0.1" value="${prevVal}"
                   style="width:110px;text-align:center" required>
        `;
        rows.appendChild(div);
    });
}

function kirimPermintaan() {
    const alamat   = document.getElementById('alamatJemput').value.trim();
    const jadwal   = document.getElementById('jadwalJemput').value;
    const jarak_km = parseFloat(document.getElementById('jarakJemput').value || 0);
    const catatan  = document.getElementById('catatanJemput').value.trim();

    if (!alamat) {
        Swal.fire({ icon: 'warning', title: 'Alamat kosong', text: 'Isi alamat penjemputan atau pilih pada peta.', confirmButtonColor: '#4CAF50' });
        return;
    }
    if (!jadwal) {
        Swal.fire({ icon: 'warning', title: 'Jadwal belum diisi', text: 'Pilih jadwal penjemputan.', confirmButtonColor: '#4CAF50' });
        return;
    }
    if (!jarak_km || jarak_km <= 0) {
        Swal.fire({ icon: 'warning', title: 'Jarak belum dihitung', text: 'Pilih titik penjemputan pada peta.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const beratRows = document.querySelectorAll('.jenis-berat-row');
    if (beratRows.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Pilih jenis sampah', text: 'Pilih minimal satu jenis sampah.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const items = [];
    let allFilled = true;
    beratRows.forEach(row => {
        const jenisId  = parseInt(row.dataset.jenisId);
        const estBerat = parseFloat(row.querySelector('input').value || 0);
        if (!estBerat || estBerat <= 0) { allFilled = false; return; }
        items.push({ jenis_id: jenisId, est_berat: estBerat });
    });

    if (!allFilled || items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Estimasi berat belum diisi', text: 'Isi estimasi berat untuk semua jenis sampah yang dipilih.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const estOngkir = Math.round(jarak_km * tarifPerKm).toLocaleString('id-ID');

    Swal.fire({
        title: 'Kirim Permintaan?',
        html: `<b>${items.length}</b> jenis sampah<br>Jadwal: <b>${new Date(jadwal).toLocaleString('id-ID')}</b><br>Jarak: <b>${jarak_km} km</b> (Ongkir: Rp ${estOngkir})`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(BASE_URL + '/api/jemput-sampah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'buat', alamat, jadwal, jarak_km, catatan, items })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModalJemput();
                Swal.fire({
                    icon: 'success',
                    title: 'Permintaan Terkirim!',
                    html: `Kode: <b>${res.kode}</b><br>Petugas akan segera mengkonfirmasi jadwal Anda.`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
    });
}

function batalPermintaan(id) {
    Swal.fire({
        title: 'Batalkan Permintaan?',
        text: 'Permintaan yang sudah dibatalkan tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Batalkan',
        cancelButtonText: 'Tidak'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/jemput-sampah.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'batal', jemput_id: id })
        })
        .then(r => r.json())
        .then(res => {
            Swal.fire({ icon: res.success ? 'success' : 'error', title: res.success ? 'Dibatalkan' : 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' })
            .then(() => { if (res.success) location.reload(); });
        });
    });
}

function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div class="empty-state" style="padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';

    fetch(BASE_URL + `/api/detail-jemput.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                document.getElementById('detailContent').innerHTML = '<div class="empty-state"><p>Gagal memuat data</p></div>';
                return;
            }
            const d = res.data;
            const si = statusInfo[d.status] || { cls: 'badge-gray', txt: d.status };

            const stepsMap = ['menunggu','dikonfirmasi','dijemput','selesai'];
            const stepLabels = ['Menunggu','Dikonfirmasi','Dijemput','Selesai'];
            const curIdx = stepsMap.indexOf(d.status);

            let timelineHtml = '<div class="status-timeline" style="margin-bottom:1rem">';
            stepsMap.forEach((s, i) => {
                if (i > 0) timelineHtml += '<div class="timeline-sep"></div>';
                let cls = i < curIdx ? 'done' : (i === curIdx ? 'active' : '');
                timelineHtml += `<div class="timeline-step ${cls}"><div class="dot"></div>${stepLabels[i]}</div>`;
            });
            timelineHtml += '</div>';

            let itemsHtml = (res.items || []).map(it => `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.875rem">${it.nama}</div>
                        <div class="text-muted text-small">Est. ${it.est_berat} kg</div>
                    </div>
                    <div style="font-size:.75rem;color:var(--text-muted)">${it.poin_per_kg} poin/kg</div>
                </div>`).join('');

            document.getElementById('detailContent').innerHTML = `
                ${timelineHtml}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:1rem">
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Kode</div>
                        <div style="font-weight:700;font-size:.9rem">${d.kode}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Status</div>
                        <span class="badge ${si.cls}" style="margin-top:.2rem">${si.txt}</span>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Jadwal Jemput</div>
                        <div style="font-weight:600;font-size:.85rem">${d.jadwal}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem">
                        <div class="text-muted text-small">Jarak / Ongkir</div>
                        <div style="font-weight:600;font-size:.85rem">${d.jarak_km} km (Rp ${d.biaya_ongkir})</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Alamat Penjemputan</div>
                        <div style="font-size:.875rem">${d.alamat}</div>
                    </div>
                    ${d.catatan_admin ? `<div style="background:var(--primary-light);border-radius:10px;padding:.75rem;grid-column:1/-1">
                        <div class="text-muted text-small">Catatan Admin</div>
                        <div style="font-size:.875rem;color:var(--primary-dark)">${d.catatan_admin}</div>
                    </div>` : ''}
                    ${d.status === 'selesai' && d.total_poin ? `<div style="background:var(--primary-light);border-radius:10px;padding:.75rem;grid-column:1/-1;text-align:center">
                        <div class="text-muted text-small">Poin Didapat</div>
                        <div style="font-size:1.4rem;font-weight:700;color:var(--primary-dark)">+${d.total_poin} poin</div>
                    </div>` : ''}
                </div>
                <div style="font-weight:600;font-size:.875rem;margin-bottom:.5rem">Jenis Sampah:</div>
                ${itemsHtml || '<p class="text-muted text-small">Tidak ada item</p>'}
            `;
        });
}
</script>
<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}
</script>
<?php include_once '../includes/notif_scripts.php'; ?>
</body>
</html>
