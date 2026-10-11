<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];

$user = $conn->query("SELECT nama, poin FROM users WHERE id = $uid")->fetch_assoc();
$res_pengaturan = $conn->query("SELECT kunci, nilai FROM pengaturan");
$pengaturan = ['trashily_lat' => '-6.200000', 'trashily_lng' => '106.816666', 'trashily_alamat' => 'Lokasi Trashily'];
if ($res_pengaturan) while ($setting = $res_pengaturan->fetch_assoc()) $pengaturan[$setting['kunci']] = $setting['nilai'];
$_SESSION['poin'] = $user['poin'];

$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 8;
$offset = ($page - 1) * $limit;

$total_rows  = $conn->query("SELECT COUNT(*) as c FROM everwear_transaksi WHERE customer_id = $uid")->fetch_assoc()['c'];
$total_pages = ceil($total_rows / $limit);

$list = $conn->query("
    SELECT id, kode_transaksi, kategori_pakaian, jumlah_pcs, pcs_layak, pcs_recycle, berat_kg, poin_diperoleh, status, created_at
    FROM everwear_transaksi
    WHERE customer_id = $uid
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
");

$status_badge = [
    'pending' => ['badge-orange', 'Menunggu Pemeriksaan'],
    'proses' => ['badge-blue', 'Dalam Proses'],
    'selesai' => ['badge-green',  'Selesai'],
    'dibatalkan' => ['badge-red',    'Dibatalkan'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setor Sendiri — Everwear</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_everwear.php'; ?>

    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Setor Sendiri</h1>
                    <p>Setor pakaian layak pakai atau tekstil recycle Anda</p>
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
            <div style="background:var(--primary-light);border:1.5px solid var(--primary);border-radius:14px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:flex-start;gap:.875rem">
                <div style="width:36px;height:36px;background:var(--primary);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-clock-rotate-left" style="color:#fff;font-size:.9rem"></i>
                </div>
                <div style="flex:1">
                    <div style="font-weight:700;font-size:.875rem;color:var(--primary-dark);margin-bottom:.2rem">Cara Kerja Setor Sendiri</div>
                    <div style="font-size:.8rem;color:var(--primary-dark);line-height:1.6">
                        Pilih <b>tanggal & jam kedatangan</b> Anda ke lokasi Trashily (<b>Gang Kosasih, Cikaret, Bogor Selatan, Kota Batu, Bogor, West Java, 16610, Indonesia</b>).
                        Petugas kami akan <b>standby di lokasi</b> pada jam tersebut untuk memeriksa & menimbang ulang sampah Anda.
                    </div>
                </div>
                <button class="btn btn-ghost btn-sm" onclick="lihatLokasiSetor()" style="background:#fff;white-space:nowrap;border:1px solid var(--primary);color:var(--primary-dark);font-weight:700">
                    <i class="fa-solid fa-map-location-dot"></i> Lihat Peta Lokasi
                </button>
            </div>

            <!-- Tabel Riwayat Donasi -->
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Donasi Pakaian</h3>
                    <button class="btn btn-primary btn-sm" onclick="openModalSetor()">
                        <i class="fa-solid fa-plus"></i> Donasi Pakaian
                    </button>
                </div>

                <?php if ($list->num_rows === 0): ?>
                <div class="empty-state" style="padding:3rem">
                    <div class="empty-icon"><i class="fa-solid fa-shirt" style="font-size:3rem;opacity:.3"></i></div>
                    <p>Belum ada donasi pakaian</p>
                    <button class="btn btn-primary" style="margin-top:1rem" onclick="openModalSetor()">
                        <i class="fa-solid fa-plus"></i> Donasi Pakaian Sekarang
                    </button>
                </div>

                <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Jumlah Baju</th>
                                <th>Berat</th>
                                <th>Status</th>
                                <th>Poin</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $list->fetch_assoc()):
                                [$bcls, $btxt] = $status_badge[$row['status']] ?? ['badge-gray', ucfirst($row['status'])];
                            ?>
                            <tr>
                                <td>
                                    <span class="badge badge-blue"><?= $row['kode_transaksi'] ?></span>
                                    <div class="text-muted text-small"><?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td>
                                    1. Baju layak: <strong><?= intval($row['pcs_layak']) ?></strong> pcs<br>
                                    2. Recycle: <strong><?= intval($row['pcs_recycle']) ?></strong> pcs
                                </td>
                                <td><?= number_format($row['berat_kg'], 2) ?> kg</td>
                                <td><span class="badge <?= $bcls ?>"><?= $btxt ?></span></td>
                                <td>
                                    <?php if ($row['status'] === 'selesai' && $row['poin_diperoleh']): ?>
                                    <strong style="color:var(--primary-dark)">+<?= number_format($row['poin_diperoleh']) ?> poin</strong>
                                    <?php else: ?>
                                    <span class="text-muted text-small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-sm" onclick="lihatDetail(<?= $row['id'] ?>)" title="Detail">
                                         <i class="fa-solid fa-eye"></i>
                                     </button>
                                     <?php if ($row['status'] === 'pending'): ?>
                                     <button class="btn btn-danger btn-sm" onclick="batalkanDonasi(<?= $row['id'] ?>)" title="Batalkan">
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

<!-- MODAL DONASI PAKAIAN -->
<div class="modal-overlay" id="modalSetor">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
                    <h3><i class="fa-solid fa-person-walking-luggage" style="color:var(--primary);margin-right:.5rem"></i>Form Setor Sendiri</h3>
            <button class="modal-close" onclick="closeModalSetor()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Tanggal &amp; Jam Kedatangan Anda <span style="color:var(--danger)">*</span></label>
                <input type="datetime-local" class="form-control" id="jadwalSetor" required>
                <div class="text-muted text-small" style="margin-top:.25rem">Datang ke <?= htmlspecialchars($pengaturan['trashily_alamat']) ?> sesuai jadwal.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Pakaian Layak Pakai (pcs)</label>
                <input type="number" class="form-control" id="pcsLayak" placeholder="Contoh: 3" min="0">
                <div class="text-small text-muted" style="margin-top:0.25rem">Baju/celana dalam kondisi baik untuk disalurkan.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Pakaian Tidak Layak/Recycle (pcs)</label>
                <input type="number" class="form-control" id="pcsRecycle" placeholder="Contoh: 2" min="0">
            </div>

            <div class="form-group">
                <label class="form-label">Estimasi Berat Total (kg) <span style="color:var(--danger)">*</span></label>
                <input type="number" class="form-control" id="beratKg" placeholder="Contoh: 2.5" min="0.1" step="0.1" required>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan <span style="font-weight:400;text-transform:none;color:var(--text-muted)">(opsional)</span></label>
                <textarea class="form-control" id="deskripsi" placeholder="Informasi tambahan..." rows="2"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeModalSetor()">Batal</button>
            <button class="btn btn-primary" onclick="kirimDonasi()">
                <i class="fa-solid fa-paper-plane"></i> Kirim Donasi
            </button>
        </div>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:.5rem"></i>Detail Donasi</h3>
            <button class="modal-close" onclick="document.getElementById('modalDetail').classList.remove('open')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body" id="detailContent">
            <div class="empty-state"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>
        </div>
    </div>
</div>

<script>
function openModalSetor() {
    document.getElementById('modalSetor').classList.add('open');
    const now = new Date();
    const pad = value => String(value).padStart(2, '0');
    document.getElementById('jadwalSetor').min = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    document.getElementById('jadwalSetor').value = '';
    document.getElementById('pcsLayak').value = '';
    document.getElementById('pcsRecycle').value = '';
    document.getElementById('beratKg').value = '';
    document.getElementById('deskripsi').value = '';
}

function lihatLokasiSetor() {
    window.open('https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($pengaturan['trashily_lat'] . ',' . $pengaturan['trashily_lng']) ?>', '_blank');
}

function closeModalSetor() {
    document.getElementById('modalSetor').classList.remove('open');
}

function kirimDonasi() {
    const jadwalSetor = document.getElementById('jadwalSetor').value;
    const pcsLayak = parseInt(document.getElementById('pcsLayak').value || 0);
    const pcsRecycle = parseInt(document.getElementById('pcsRecycle').value || 0);
    const beratKg = parseFloat(document.getElementById('beratKg').value || 0);
    const deskripsi = document.getElementById('deskripsi').value.trim();

    if (!jadwalSetor || new Date(jadwalSetor) < new Date()) {
        Swal.fire({ icon: 'warning', title: 'Jadwal Belum Valid', text: 'Pilih tanggal dan jam kedatangan yang belum lewat.', confirmButtonColor: '#4CAF50' });
        return;
    }

    const totalPcs = pcsLayak + pcsRecycle;
    if (totalPcs <= 0) {
        Swal.fire({ icon: 'warning', title: 'Jumlah Kosong', text: 'Isi jumlah pakaian layak atau tidak layak minimal 1 pcs.', confirmButtonColor: '#4CAF50' });
        return;
    }

    if (!beratKg || beratKg <= 0) {
        Swal.fire({ icon: 'warning', title: 'Berat Kosong', text: 'Masukkan estimasi berat total.', confirmButtonColor: '#4CAF50' });
        return;
    }

    Swal.fire({
        title: 'Kirim Donasi Pakaian?',
        html: `Layak: <b>${pcsLayak} pcs</b> | Tidak Layak: <b>${pcsRecycle} pcs</b><br>Berat: <b>${beratKg} kg</b><br>Jadwal: <b>${new Date(jadwalSetor).toLocaleString('id-ID')}</b>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Kirim',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;

        fetch(BASE_URL + '/api/everwear-setor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'buat', pcs_layak: pcsLayak, pcs_recycle: pcsRecycle, berat_kg: beratKg, deskripsi, jadwal_setor: jadwalSetor })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                closeModalSetor();
                Swal.fire({
                    icon: 'success',
                    title: 'Donasi Terkirim!',
                    html: `Kode: <b>${res.kode}</b><br>${res.message}`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
    });
}

function batalkanDonasi(id) {
    Swal.fire({ title: 'Batalkan donasi?', text: 'Donasi pending akan dibatalkan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, batalkan', cancelButtonText: 'Tidak', confirmButtonColor: '#d33' }).then(r => {
        if (!r.isConfirmed) return;
        fetch(BASE_URL + '/api/everwear-setor.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'batalkan', id }) })
            .then(r => r.json()).then(res => res.success ? Swal.fire('Berhasil', 'Donasi dibatalkan.', 'success').then(() => location.reload()) : Swal.fire('Gagal', res.message, 'error'))
            .catch(e => Swal.fire('Gagal', e.message, 'error'));
    });
}

function lihatDetail(id) {
    document.getElementById('modalDetail').classList.add('open');
    document.getElementById('detailContent').innerHTML = '<div class="empty-state" style="padding:2rem"><i class="fa-solid fa-spinner fa-spin" style="font-size:2rem;color:var(--text-muted)"></i></div>';

    fetch(BASE_URL + `/api/detail-everwear.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const t = res.donasi;

            document.getElementById('detailContent').innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1rem">
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Kode Donasi</div>
                        <div style="font-weight:700">${t.kode}</div>
                    </div>
                    <div style="background:var(--primary-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Status</div>
                        <div style="font-weight:700;color:var(--primary-dark)">${t.status === 'pending' ? 'Menunggu' : (t.status === 'selesai' ? 'Selesai' : (t.status === 'proses' ? 'Proses' : 'Dibatalkan'))}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem;grid-column:1/-1">
                        <div class="text-muted text-small">Jumlah Baju</div>
                        <div style="font-weight:700;color:var(--primary-dark)">
                            1. Baju layak: ${t.pcs_layak} pcs<br>
                            2. Recycle: ${t.pcs_recycle} pcs
                        </div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Jadwal Kedatangan</div>
                        <div style="font-weight:700">${t.jadwal_setor}</div>
                    </div>
                    <div style="background:var(--bg-light);border-radius:10px;padding:.875rem">
                        <div class="text-muted text-small">Berat</div>
                        <div style="font-weight:700">${t.berat_kg} kg</div>
                    </div>
                    ${t.poin_diperoleh > 0 ? `<div style="background:var(--primary-light);border-radius:10px;padding:.875rem;grid-column:1/-1">
                        <div class="text-muted text-small">Poin Diperoleh</div>
                        <div style="font-weight:700;color:var(--primary-dark)">+${t.poin_diperoleh} poin</div>
                    </div>` : ''}
                </div>
                <div style="margin-top:.75rem" class="text-muted text-small">Tanggal: ${t.tanggal}</div>`;
        });
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
