<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];

// Refresh poin user
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

// Riwayat pengajuan setor sendiri
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 8;
$offset = ($page - 1) * $limit;

$total_rows  = $conn->query("SELECT COUNT(*) as c FROM transaksi WHERE customer_id = $uid AND tipe_transaksi = 'setor_sendiri'")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT t.id, t.kode_transaksi, t.total_poin, t.total_berat, t.status, t.catatan, t.catatan_admin, t.jadwal_setor, t.created_at
    FROM transaksi t
    WHERE t.customer_id = $uid AND t.tipe_transaksi = 'setor_sendiri'
    ORDER BY t.created_at DESC
    LIMIT $limit OFFSET $offset
");

$status_badge = [
    'pending' => ['badge-orange', 'Menunggu Pemeriksaan'],
    'selesai' => ['badge-green',  'Selesai Diverifikasi'],
    'batal'   => ['badge-red',    'Dibatalkan'],
];

$kategori_icon = [
    'organik_kering' => 'fa-seedling',
    'plastik'        => 'fa-bottle-water',
    'kertas'         => 'fa-file',
    'logam'          => 'fa-screwdriver-wrench',
    'kaca'           => 'fa-wine-bottle',
    'elektronik'     => 'fa-microchip',
    'lainnya'        => 'fa-box',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sendiri — Trashily</title>
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

        #mapTrashilyView {
            height: 320px;
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid var(--border);
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
                    <h1>Setor Sendiri</h1>
                    <p>Jadwalkan kedatangan Anda untuk menyetor sampah ke lokasi Trashily</p>
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
            <div style="background:var(--primary-light);border:1.5px solid var(--primary);border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.875rem">
                <div style="width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#fff;font-size:.9rem"></i>
                </div>
                <div style="flex:1">
                    <div style="font-weight:700;font-size:.875rem;color:var(--primary-dark);margin-bottom:.2rem">Cara Kerja Setor Sendiri</div>
                    <div style="font-size:.8rem;color:var(--primary-dark);line-height:1.6">
                        Pilih <b>tanggal & jam kedatangan</b> Anda ke lokasi Trashily (<b><?= htmlspecialchars($pengaturan['trashily_alamat']) ?></b>). 
                        Petugas kami akan <b>standby di lokasi</b> pada jam tersebut untuk memeriksa & menimbang ulang sampah Anda. 
                    </div>
                </div>
                <button class="btn btn-ghost btn-sm" onclick="openModalLokasi()" style="background:#fff;white-space:nowrap;border:1px solid var(--primary);color:var(--primary-dark);font-weight:700">
                    <i class="fa-solid fa-map-location-dot"></i> Lihat Peta Lokasi
                </button>
            </div>

            <!-- Tabel Riwayat Setor Sendiri -->
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Setor Sendiri</h3>
                    <button class="btn btn-primary btn-sm" onclick="openModalSetor()">
                        <i class="fa-solid fa-calendar-plus"></i> Ajukan Setor Sendiri
                    </button>
                </div>

                <?php if ($list->num_rows === 0): ?>
                <div class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa-solid fa-person-walking-luggage" style="font-size:3rem;opacity:.3"></i></div>
                    <p>Belum ada pengajuan setor sendiri</p>
                    <button class="btn btn-primary" style="margin-top:1rem" onclick="openModalSetor()">
                        <i class="fa-solid fa-plus"></i> Buat Jadwal Setor
                    </button>
                </div>

                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jam Rencana Datang</th>
                                <th>Est. Berat</th>
                                <th>Status</th>
                                <th>Poin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list->fetch_assoc()):
                                [$bcls, $btxt] = $status_badge[$row['status']] ?? ['badge-gray', ucfirst($row['status'])];
                                $jadwal_dt = strtotime($row['jadwal_setor']);
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-blue"><?= $row['kode_transaksi'] ?></span>
                                    <div class="text-muted text-small"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div style="font-size:.875rem;font-weight:700;color:var(--primary-dark)">
                                        <i class="fa-regular fa-clock" style="margin-right:.25rem"></i>
                                        <?= date('d M Y', $jadwal_dt) ?> - <?= date('H:i', $jadwal_dt) ?> WIB
                                    </div>
                                    <div class="text-muted text-small">Petugas standby di lokasi</div>
                                </td>
                                <td><?= number_format($row['total_berat'], 2) ?> kg</td>
                                <td><span class="badge <?= $bcls ?>"><?= $btxt ?></span></td>
                                <td>
                                    <?php if ($row['status'] === 'selesai' && $row['total_poin']): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['total_poin']) ?> poin</strong>
                                    <?php elseif ($row['status'] === 'pending'): ?>
                                    <span class="text-muted text-small">Est. +<?= number_format($row['total_poin']) ?> poin</span>
                                    <?php else: ?>
                                    <span class="text-muted text-small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.4rem">
                                        <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <?php if ($row['status'] === 'pending'): ?>
                                        <button class="btn btn-danger btn-sm" onclick="batalSetor(<?= $row['id'] ?>)" title="Batalkan">
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

<!-- MODAL AJUKAN SETOR SENDIRI -->
<div class="modal-overlay" id="modalSetor">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-person-walking-luggage" style="color:var(--primary);margin-right:.5rem"></i>Form Setor Sendiri</h3>
            <button class="modal-close" onclick="closeModalSetor()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Tanggal & Jam Kedatangan Anda <span style="color:var(--danger)">*</span></label>
                <div class="input-wrap">
                    <i class="fa-regular fa-clock input-icon" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                    <input type="datetime-local" class="form-control" id="jadwalSetor" style="padding-left:2.75rem" required>
                </div>
                <div style="font-size:.72rem;color:var(--text-muted);margin-top:.25rem">
                    Pilih jam kedatangan agar petugas kami dapat mempersiapkan timbangan & standby di lokasi.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Jenis Sampah yang Akan Disetor</label>
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
                    <label class="form-label">Estimasi Berat per Jenis (kg)</label>
                    <div id="jenisBeratRows"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(opsional)</span></label>
                <textarea class="form-control" id="catatanSetor" placeholder="Misal: Saya datang menggunakan motor jam 2 siang..." rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModalSetor()">Batal</button>
            <button class="btn btn-primary" onclick="kirimPengajuanSetor()">
                <i class="fa-solid fa-paper-plane"></i> Kirim Jadwal Setor
            </button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT PETA LOKASI TRASHILY -->
<div class="modal-overlay" id="modalLokasi">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-map-location-dot" style="color:var(--primary);margin-right:.5rem"></i>Lokasi Trashily</h3>
            <button class="modal-close" onclick="closeModalLokasi()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="background:var(--bg-light);border-radius:10px;padding:.875rem 1rem;margin-bottom:1rem">
                <div class="text-muted text-small">Alamat Trashily:</div>
                <div style="font-weight:700;color:var(--text-dark)"><?= htmlspecialchars($pengaturan['trashily_alamat']) ?></div>
            </div>
            <div id="mapTrashilyView"></div>
        </div>
        <div class="modal-footer">
            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($pengaturan['trashily_lat'] . ',' . $pengaturan['trashily_lng']) ?>" target="_blank" class="btn btn-primary">
                <i class="fa-solid fa-diamond-turn-right"></i> Buka Petunjuk Arah (Google Maps)
            </a>
            <button class="btn btn-ghost" onclick="closeModalLokasi()">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL TRANSAKSI -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:.5rem"></i>Detail Setor Sendiri</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>
        </div>
    </div>
</div>

<script>
const tLat = parseFloat("<?= $pengaturan['trashily_lat'] ?>") || -6.200000;
const tLng = parseFloat("<?= $pengaturan['trashily_lng'] ?>") || 106.816666;
let viewMap;

function openModalLokasi() {
    document.getElementById('modalLokasi').classList.add('open');
    setTimeout(() => {
        if (!viewMap) {
            viewMap = L.map('mapTrashilyView').setView([tLat, tLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(viewMap);

            const trashIcon = L.divIcon({
                className: 'custom-pin',
                html: `<div style="background:#4CAF50;color:#fff;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(0,0,0,.3);border:3px solid #fff;font-size:1.1rem"><i class="fa-solid fa-leaf"></i></div>`,
                iconSize: [38, 38],
                iconAnchor: [19, 19]
            });
            L.marker([tLat, tLng], { icon: trashIcon }).addTo(viewMap)
                .bindPopup("<b>Trashily Central</b><br><?= htmlspecialchars($pengaturan['trashily_alamat']) ?>").openPopup();
        } else {
            viewMap.invalidateSize();
        }
    }, 200);
}

function closeModalLokasi() {
    document.getElementById('modalLokasi').classList.remove('open');
}

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

function setMinDatetime() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    const val = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    document.getElementById('jadwalSetor').min = val;
}

function openModalSetor() {
    document.getElementById('modalSetor').classList.add('open');
    setMinDatetime();
    document.querySelectorAll('.jenis-checkbox-item').forEach(el => {
        el.classList.remove('checked');
        el.querySelector('input').checked = false;
    });
    document.getElementById('jenisBeratSection').style.display = 'none';
    document.getElementById('jenisBeratRows').innerHTML = '';
    document.getElementById('catatanSetor').value = '';
    document.getElementById('jadwalSetor').value = '';
}

function closeModalSetor() {
    document.getElementById('modalSetor').classList.remove('open');
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

function kirimPengajuanSetor() {
    const jadwal_setor = document.getElementById('jadwalSetor').value;
    const catatan      = document.getElementById('catatanSetor').value.trim();

    if (!jadwal_setor) {
        Swal.fire({ icon: 'warning', title: 'Jam Belum Diisi', text: 'Pilih tanggal dan jam kedatangan Anda.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const beratRows = document.querySelectorAll('.jenis-berat-row');
    if (beratRows.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Pilih Sampah', text: 'Pilih minimal satu jenis sampah yang akan disetor.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const items = [];
    let allFilled = true;
    beratRows.forEach(row => {
        const jenisId = parseInt(row.dataset.jenisId);
        const berat   = parseFloat(row.querySelector('input').value || 0);
        if (!berat || berat <= 0) { allFilled = false; return; }
        items.push({ jenis_id: jenisId, berat });
    });

    if (!allFilled || items.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Estimasi Berat Kosong', text: 'Isi estimasi berat untuk semua jenis sampah yang dipilih.', confirmButtonColor: '#4CAF50' });
        return;
    }

    Swal.fire({
        title: 'Kirim Jadwal Setor?',
        html: `Jam Kedatangan: <b>${new Date(jadwal_setor).toLocaleString('id-ID')}</b><br>Petugas akan standby di lokasi.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(BASE_URL + '/api/setor-sendiri.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'buat', jadwal_setor, catatan, items })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModalSetor();
                Swal.fire({
                    icon: 'success',
                    title: 'Jadwal Terkirim!',
                    html: `Kode: <b>${res.kode}</b><br>${res.message}`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
    });
}

function batalSetor(id) {
    Swal.fire({
        title: 'Batalkan Pengajuan Setor?',
        text: 'Pengajuan yang dibatalkan tidak dapat dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#E53935',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Batalkan'
    }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/setor-sendiri.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'batal', transaksi_id: id })
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

    fetch(BASE_URL + `/api/detail-transaksi.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const t = res.transaksi;
            let itemsHtml = res.items.map(i => `
                <div style="display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.875rem">${i.nama}</div>
                        <div class="text-muted text-small">${i.berat_fmt} kg × ${i.poin_per_kg} poin/kg</div>
                    </div>
                    <div style="font-weight:700;color:var(--primary-dark)">+${i.subtotal_poin} poin</div>
                </div>`).join('');

            document.getElementById('detailContent').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Kode Transaksi</div>
                        <div style="font-weight:700">${t.kode}</div>
                    </div>
                    <div style="background:var(--primary-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Status</div>
                        <div style="font-weight:700;color:var(--primary-dark)">${t.status === 'pending' ? 'Menunggu Pemeriksaan' : (t.status === 'selesai' ? 'Selesai Diverifikasi' : 'Dibatalkan')}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem;grid-column:1/-1">
                        <div class="text-muted text-small">Jam Kedatangan Anda</div>
                        <div style="font-weight:700;color:var(--primary-dark)"><i class="fa-regular fa-clock" style="margin-right:.25rem"></i> ${t.jadwal_setor}</div>
                    </div>
                    ${t.catatan_admin ? `<div style="background:var(--primary-light);border-radius:10px;padding:.875rem;grid-column:1/-1">
                        <div class="text-muted text-small">Catatan Pemeriksaan Petugas</div>
                        <div style="font-size:.875rem;color:var(--primary-dark)">${t.catatan_admin}</div>
                    </div>` : ''}
                </div>
                <div style="font-weight:600;margin-bottom:.5rem;font-size:.875rem">Estimasi / Hasil Sampah:</div>
                ${itemsHtml}
                <div style="margin-top:.75rem" class="text-muted text-small">Tanggal dibuat: ${t.tanggal}</div>`;
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
