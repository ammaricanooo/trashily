<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT nama, poin, alamat FROM users WHERE id = $uid")->fetch_assoc();

$res_pengaturan = $conn->query("SELECT kunci, nilai FROM pengaturan");
$pengaturan = [
    'trashily_lat'    => '-6.200000',
    'trashily_lng'    => '106.816666',
    'trashily_alamat' => 'Trashily Central Waste Bank',
    'tarif_per_km'    => '2500'
];
if ($res_pengaturan) {
    while ($row = $res_pengaturan->fetch_assoc()) {
        $pengaturan[$row['kunci']] = $row['nilai'];
    }
}
$_SESSION['poin'] = $user['poin'];

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 8;
$offset = ($page - 1) * $limit;

$total_rows  = $conn->query("SELECT COUNT(*) as c FROM everwear_jemput WHERE customer_id = $uid")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT id, kode_jemput, pcs_layak, pcs_recycle, berat_kg, alamat_penjemputan, jarak_km, biaya_ongkir, tanggal_jemput, jam_jemput, status, created_at
    FROM everwear_jemput
    WHERE customer_id = $uid
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

$status_badge = [
    'pending' => ['badge-orange', 'Menunggu'],
    'dikonfirmasi' => ['badge-blue', 'Dikonfirmasi'],
    'dalam_perjalanan' => ['badge-blue', 'Perjalanan'],
    'selesai' => ['badge-green', 'Selesai'],
    'dibatalkan' => ['badge-red', 'Dibatalkan'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jemput Pakaian — Everwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
    <style>
        #mapPicker {
            height: 240px;
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            z-index: 1;
        }
    </style>
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_everwear.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Jemput Pakaian</h1>
                    <p>Ajukan penjemputan pakaian donasi ke lokasi Anda</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-coins text-xs"></i>
                    <?= number_format($user['poin'], 0, ',', '.') ?> Poin
                </div>
            </div>
        </div>

        <div class="content">
            <div style="background:var(--primary-light);border:1.5px solid var(--primary);border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.875rem">
                <div style="width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-map-location-dot" style="color:#fff;font-size:.9rem"></i></div>
                <div><div style="font-weight:700;font-size:.875rem;color:var(--primary-dark);margin-bottom:.2rem">Peta Penjemputan Otomatis & Ongkir</div><div style="font-size:.8rem;color:var(--primary-dark);line-height:1.6">Pilih titik jemput Anda pada peta interaktif. Jarak ke Pusat Trashily (<b>Gang Kosasih, Cikaret, Bogor Selatan, Kota Batu, Bogor, West Java, 16610, Indonesia</b>) dan ongkos kirim akan otomatis dihitung secara presisi (Tarif: <b>Rp 2.500 / km</b>).</div></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Penjemputan</h3>
                    <button class="btn btn-primary btn-sm" onclick="openModalJemput()">
                        <i class="fa-solid fa-plus"></i> Ajukan Jemput
                    </button>
                </div>

                <?php if ($list->num_rows === 0): ?>
                <div class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa-solid fa-truck" style="font-size:3rem;opacity:.3"></i></div>
                    <p>Belum ada permintaan penjemputan</p>
                    <button class="btn btn-primary" style="margin-top:1rem" onclick="openModalJemput()">
                        <i class="fa-solid fa-plus"></i> Ajukan Penjemputan
                    </button>
                </div>

                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jumlah Baju</th>
                                <th>Alamat</th>
                                <th>Jadwal</th>
                                <th>Pengiriman</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list->fetch_assoc()):
                                [$bcls, $btxt] = $status_badge[$row['status']] ?? ['badge-gray', 'Lainnya'];
                            ?>
                            <tr>
                                <td><span class="badge badge-blue"><?= $row['kode_jemput'] ?></span></td>
                                <td>
                                    1. Baju layak: <strong><?= intval($row['pcs_layak']) ?></strong> pcs<br>
                                    2. Recycle: <strong><?= intval($row['pcs_recycle']) ?></strong> pcs
                                </td>
                                <td><small class="text-muted"><?= substr($row['alamat_penjemputan'], 0, 40) ?>...</small></td>
                                <td><small><?= date('d M H:i', strtotime($row['tanggal_jemput'] . ' ' . $row['jam_jemput'])) ?></small></td>
                                <td><small>Jarak: <?= $row['jarak_km'] ?> km<br>Ongkir: Rp <?= number_format($row['biaya_ongkir'], 0, ',', '.') ?></small></td>
                                <td><span class="badge <?= $bcls ?>"><?= $btxt ?></span></td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                         <i class="fa-solid fa-eye"></i>
                                     </button>
                                     <?php if ($row['status'] === 'pending'): ?>
                                     <button class="btn btn-danger btn-sm" onclick="batalkanJemput(<?= $row['id'] ?>)" title="Batalkan">
                                         <i class="fa-solid fa-xmark"></i>
                                     </button>
                                     <?php endif; ?>
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

<!-- MODAL AJUKAN JEMPUT -->
<div class="modal-overlay" id="modalJemput">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-truck" style="color:var(--primary);margin-right:.5rem"></i>Ajukan Penjemputan</h3>
            <button class="modal-close" onclick="closeModalJemput()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Baju Layak Pakai (pcs) <span style="color:var(--danger)">*</span></label>
                <input type="number" class="form-control" id="pcsLayakJemput" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Recycle (pcs) <span style="color:var(--danger)">*</span></label>
                <input type="number" class="form-control" id="pcsRecycleJemput" min="0" value="0" required>
            </div>

            <div class="form-group">
                <label class="form-label">Estimasi Berat Total (kg) <span style="color:var(--danger)">*</span></label>
                <input type="number" class="form-control" id="beratJemput" min="0.1" step="0.1" placeholder="Contoh: 2.5" required>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal & Jam Penjemputan <span style="color:var(--danger)">*</span></label>
                <input type="datetime-local" class="form-control" id="jadwalJemput" required>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat &amp; Lokasi Penjemputan <span style="color:var(--danger)">*</span></label>
                <div class="text-muted text-small" style="margin:-.25rem 0 .5rem">Pin hijau = Pusat Trashily (<?= htmlspecialchars($pengaturan['trashily_alamat']) ?>). Geser pin biru ke lokasi penjemputan.</div>
                <div id="mapPicker"></div>
                <input type="hidden" id="latJemput" value="<?= $pengaturan['trashily_lat'] ?>">
                <input type="hidden" id="lngJemput" value="<?= $pengaturan['trashily_lng'] ?>">
                <textarea class="form-control" id="alamatJemput" placeholder="Masukkan alamat lengkap Anda..." rows="3" required style="margin-top:.5rem"></textarea>
                <div id="infoOngkir" style="margin-top:.5rem;font-size:.8rem;font-weight:600;color:var(--primary)"></div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan <span style="font-weight:400;color:var(--text-muted)">(opsional)</span></label>
                <textarea class="form-control" id="catatanJemput" placeholder="Contoh: Ada pagar, di belakang rumah..." rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModalJemput()">Batal</button>
            <button class="btn btn-primary" onclick="kirimJemput()">
                <i class="fa-solid fa-paper-plane"></i> Ajukan Penjemputan
            </button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-info-circle" style="color:var(--primary);margin-right:.5rem"></i>Detail Penjemputan</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>
        </div>
    </div>
</div>

<script>
const trashilyLat = parseFloat("<?= $pengaturan['trashily_lat'] ?>") || -6.2;
const trashilyLng = parseFloat("<?= $pengaturan['trashily_lng'] ?>") || 106.816666;
const tarifPerKm = parseInt("<?= $pengaturan['tarif_per_km'] ?>") || 2500;
let map, marker, routeLine;

function hitungJarakKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
    return (R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}

function updateOngkirInfo(lat, lng) {
    const km = hitungJarakKm(trashilyLat, trashilyLng, lat, lng);
    const ongkir = Math.round(km * tarifPerKm);
    document.getElementById('infoOngkir').textContent = `Estimasi Jarak: ${km.toFixed(1)} km | Biaya Ongkir: Rp ${ongkir.toLocaleString('id-ID')}`;
    if (routeLine) {
        routeLine.setLatLngs([[trashilyLat, trashilyLng], [lat, lng]]);
    }
}

function openModalJemput() {
    document.getElementById('modalJemput').classList.add('open');
    const now = new Date();
    const pad = value => String(value).padStart(2, '0');
    document.getElementById('jadwalJemput').min = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

    setTimeout(() => {
        if (!map) {
            map = L.map('mapPicker').setView([trashilyLat, trashilyLng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const trashIcon = L.divIcon({
                className: 'map-pin-central',
                html: `<div style="background:#4CAF50;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,.3);border:2px solid #fff;font-size:1rem"><i class="fa-solid fa-leaf"></i></div>`,
                iconSize: [34, 34], iconAnchor: [17, 17]
            });
            const userIcon = L.divIcon({
                className: 'map-pin-user',
                html: `<div style="background:#1E88E5;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,.3);border:2px solid #fff;font-size:1rem"><i class="fa-solid fa-house-user"></i></div>`,
                iconSize: [34, 34], iconAnchor: [17, 17]
            });

            L.marker([trashilyLat, trashilyLng], { icon: trashIcon }).addTo(map).bindPopup("<b>Pusat Trashily / Everwear</b>");
            const startLat = trashilyLat + 0.005;
            const startLng = trashilyLng + 0.005;

            marker = L.marker([startLat, startLng], { draggable: true, icon: userIcon }).addTo(map);
            marker.bindPopup("<b>Titik Jemput Pakaian</b>");

            routeLine = L.polyline([[trashilyLat, trashilyLng], [startLat, startLng]], {
                color: '#1E88E5',
                weight: 3,
                dashArray: '6, 6'
            }).addTo(map);

            const setPos = (lat, lng) => {
                document.getElementById('latJemput').value = lat;
                document.getElementById('lngJemput').value = lng;
                updateOngkirInfo(lat, lng);
            };

            marker.on('drag', function (e) {
                const pos = e.target.getLatLng();
                setPos(pos.lat, pos.lng);
            });

            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                setPos(pos.lat, pos.lng);
            });

            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                setPos(e.latlng.lat, e.latlng.lng);
            });

            setPos(startLat, startLng);
        } else {
            map.invalidateSize();
        }
    }, 200);
}

function closeModalJemput() {
    document.getElementById('modalJemput').classList.remove('open');
}

function kirimJemput() {
    const pcs_layak = parseInt(document.getElementById('pcsLayakJemput').value || 0);
    const pcs_recycle = parseInt(document.getElementById('pcsRecycleJemput').value || 0);
    const berat_kg = parseFloat(document.getElementById('beratJemput').value || 0);
    const jadwal = document.getElementById('jadwalJemput').value;
    const [tanggal, jam] = jadwal.split('T');
    const lat = document.getElementById('latJemput').value;
    const lng = document.getElementById('lngJemput').value;
    const alamat = document.getElementById('alamatJemput').value.trim();
    const catatan = document.getElementById('catatanJemput').value.trim();

    if ((pcs_layak < 0 || pcs_recycle < 0) || (pcs_layak === 0 && pcs_recycle === 0) || !berat_kg || !jadwal || !tanggal || !jam || !alamat) {
        Swal.fire({ icon: 'warning', title: 'Data Lengkap', text: 'Isi semua field yang wajib.', confirmButtonColor: '#4CAF50' });
        return;
    }

    Swal.fire({
        title: 'Ajukan Penjemputan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Ajukan'
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(BASE_URL + '/api/everwear-jemput.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'buat', pcs_layak, pcs_recycle, berat_kg, tanggal, jam, alamat, catatan, lat, lng })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModalJemput();
                Swal.fire({
                    icon: 'success',
                    title: 'Penjemputan Diajukan!',
                    html: `Kode: <b>${res.kode}</b><br>Petugas akan menghubungi Anda.`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
    });
}

function batalkanJemput(id) {
    Swal.fire({ title: 'Batalkan penjemputan?', text: 'Permintaan yang belum dikonfirmasi akan dibatalkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, batalkan', cancelButtonText: 'Tidak', confirmButtonColor: '#d33' }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/everwear-jemput.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'batalkan', id }) })
            .then(r => r.json()).then(res => res.success ? Swal.fire('Berhasil', 'Penjemputan dibatalkan.', 'success').then(() => location.reload()) : Swal.fire('Gagal', res.message, 'error'))
            .catch(e => Swal.fire('Gagal', e.message, 'error'));
    });
}

function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div style="text-align:center;padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';
    fetch(BASE_URL + '/api/detail-everwear-jemput-customer.php?id=' + id)
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.message);
            const d = res.data;
            const status = { pending: 'Menunggu', dikonfirmasi: 'Dikonfirmasi', dalam_perjalanan: 'Dijemput', selesai: 'Selesai', dibatalkan: 'Dibatalkan' }[d.status] || d.status;
            document.getElementById('detailContent').innerHTML = `<div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem"><div style="background:var(--bg-light);border-radius:10px;padding:.75rem"><div class="text-muted text-small">Kode</div><strong>${d.kode}</strong></div><div style="background:var(--bg-light);border-radius:10px;padding:.75rem"><div class="text-muted text-small">Status</div><strong>${status}</strong></div><div style="background:var(--bg-light);border-radius:10px;padding:.75rem"><div class="text-muted text-small">Jadwal Jemput</div><strong>${d.jadwal} WIB</strong></div><div style="background:var(--bg-light);border-radius:10px;padding:.75rem"><div class="text-muted text-small">Jarak / Ongkir</div><strong>${d.jarak_km} km (Rp ${d.biaya_ongkir})</strong></div><div style="background:var(--bg-light);border-radius:10px;padding:.75rem;grid-column:1/-1"><div class="text-muted text-small">Jumlah Pakaian</div><strong>1. Baju layak: ${d.pcs_layak} pcs<br>2. Recycle: ${d.pcs_recycle} pcs<br>Berat: ${d.berat_kg} kg</strong></div><div style="background:var(--bg-light);border-radius:10px;padding:.75rem;grid-column:1/-1"><div class="text-muted text-small">Alamat Penjemputan</div><div>${d.alamat}</div></div>${d.catatan?`<div style="background:var(--accent-light);border-radius:10px;padding:.75rem;grid-column:1/-1"><div class="text-muted text-small">Catatan</div><div>${d.catatan}</div></div>`:''}${d.catatan_admin?`<div style="background:var(--primary-light);border-radius:10px;padding:.75rem;grid-column:1/-1"><div class="text-muted text-small">Catatan Admin</div><div>${d.catatan_admin}</div></div>`:''}${d.status==='selesai'?`<div style="background:var(--primary-light);border-radius:10px;padding:.875rem;grid-column:1/-1;text-align:center"><div class="text-muted text-small">Poin Diperoleh</div><strong style="font-size:1.5rem;color:var(--primary-dark)">+${d.poin} poin</strong></div>`:''}</div>`;
        })
        .catch(e => { document.getElementById('detailContent').innerHTML = `<div class="empty-state"><p>${e.message}</p></div>`; });
}

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
