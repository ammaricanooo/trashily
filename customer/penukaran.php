<?php
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';

$uid = $_SESSION['user_id'];

// Refresh poin
$user = $conn->query("SELECT poin FROM users WHERE id = $uid")->fetch_assoc();
$_SESSION['poin'] = $user['poin'];
$poin_user = $user['poin'];

// Hadiah aktif
$hadiah_list = $conn->query("SELECT * FROM hadiah WHERE is_active = 1 ORDER BY poin_dibutuhkan ASC");
$hadiah_arr = [];
while ($h = $hadiah_list->fetch_assoc()) $hadiah_arr[] = $h;

// Riwayat penukaran
$riwayat = $conn->query("
    SELECT p.kode_penukaran, p.poin_digunakan, p.status, p.created_at,
           h.nama as hadiah_nama
    FROM penukaran p
    JOIN hadiah h ON p.hadiah_id = h.id
    WHERE p.customer_id = $uid
    ORDER BY p.created_at DESC
    LIMIT 10
");

$hadiah_emoji_map = ['Pulpen'=>'🖊️','Buku'=>'📚','Sabun'=>'🧼','Minyak'=>'🫙','Detergen'=>'🧺','Payung'=>'☂️','Tas'=>'👜','Voucher'=>'🎟️'];
function getEmoji2($nama) {
    global $hadiah_emoji_map;
    foreach ($hadiah_emoji_map as $key => $emoji) {
        if (stripos($nama, $key) !== false) return $emoji;
    }
    return '🎁';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar Poin — Trashily</title>
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
    <link rel="stylesheet" href="../assets/css/output.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar_customer.php'; ?>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
                <div>
                    <h1>Tukar Poin</h1>
                    <p>Pilih hadiah yang ingin Anda tukarkan</p>
                </div>
            </div>
            <div class="topbar-right">
                <?php include '../includes/notif_customer.php'; ?>
                <div class="poin-badge">
                    <i class="fa-solid fa-star"></i>
                    <span id="poinDisplay"><?= number_format($poin_user, 0, ',', '.') ?></span> Poin
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Info poin -->
            <div style="background:linear-gradient(135deg,var(--primary) 0%,#2E7D32 100%);border-radius:16px;padding:1.25rem 1.5rem;color:#fff;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between">
                <div>
                    <div style="font-size:.8rem;opacity:.8">Poin Tersedia</div>
                    <div style="font-size:1.8rem;font-weight:700"><?= number_format($poin_user) ?> <span style="font-size:1rem;opacity:.8">poin</span></div>
                </div>
                <i class="fa-solid fa-star" style="font-size:2.5rem;opacity:.2"></i>
            </div>

            <!-- Grid Hadiah -->
            <div class="card mb-3">
                <div class="card-header">
                    <h3>Pilih Hadiah</h3>
                    <span class="text-muted text-small"><?= count($hadiah_arr) ?> hadiah tersedia</span>
                </div>
                <div class="card-body">
                    <?php if (empty($hadiah_arr)): ?>
                    <div class="empty-state"><div class="empty-icon">🎁</div><p>Belum ada hadiah tersedia</p></div>
                    <?php else: ?>
                    <div class="hadiah-grid">
                        <?php foreach ($hadiah_arr as $h):
                            $bisa = $poin_user >= $h['poin_dibutuhkan'] && $h['stok'] > 0;
                        ?>
                        <div class="hadiah-card <?= !$bisa ? 'disabled' : '' ?>"
                             onclick="<?= $bisa ? "pilihHadiah({$h['id']}, '{$h['nama']}', {$h['poin_dibutuhkan']})" : "Swal.fire({icon:'warning',title:'Poin Kurang',text:'Poin Anda tidak mencukupi untuk hadiah ini.',confirmButtonColor:'#4CAF50'})" ?>"
                             style="<?= !$bisa ? 'opacity:.6;cursor:not-allowed' : '' ?>">
                            <div class="hadiah-img"><?= getEmoji2($h['nama']) ?></div>
                            <div class="hadiah-info">
                                <div class="hadiah-name"><?= htmlspecialchars($h['nama']) ?></div>
                                <div class="hadiah-poin"><i class="fa-solid fa-star" style="font-size:.7rem"></i> <?= number_format($h['poin_dibutuhkan']) ?> poin</div>
                                <div class="hadiah-stok">Stok: <?= $h['stok'] ?></div>
                                <?php if (!$bisa && $h['stok'] > 0): ?>
                                <div style="font-size:.7rem;color:var(--danger);margin-top:.25rem">Kurang <?= number_format($h['poin_dibutuhkan'] - $poin_user) ?> poin</div>
                                <?php elseif ($h['stok'] <= 0): ?>
                                <div style="font-size:.7rem;color:var(--danger);margin-top:.25rem">Stok habis</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Riwayat Penukaran -->
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Penukaran</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Hadiah</th>
                                <th>Poin</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($riwayat->num_rows === 0): ?>
                            <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🎁</div><p>Belum ada riwayat penukaran</p></div></td></tr>
                            <?php else: ?>
                            <?php while ($r = $riwayat->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-blue"><?= $r['kode_penukaran'] ?></span></td>
                                <td><?= htmlspecialchars($r['hadiah_nama']) ?></td>
                                <td><strong style="color:var(--accent)">-<?= number_format($r['poin_digunakan']) ?> poin</strong></td>
                                <td>
                                    <?php $b = ['pending'=>'badge-orange','diproses'=>'badge-blue','selesai'=>'badge-green','batal'=>'badge-red']; ?>
                                    <span class="badge <?= $b[$r['status']] ?>"><?= ucfirst($r['status']) ?></span>
                                </td>
                                <td class="text-muted text-small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function pilihHadiah(id, nama, poin) {
    Swal.fire({
        title: `Tukar "${nama}"?`,
        html: `Poin Anda akan berkurang <strong style="color:#FF8F00">${poin.toLocaleString('id-ID')} poin</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4CAF50',
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Tukar',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (!result.isConfirmed) return;

        fetch(BASE_URL + '/api/tukar-poin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ hadiah_id: id })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Penukaran Berhasil!',
                    html: `Kode penukaran: <b>${res.kode}</b><br>Sisa poin: <b style="color:#4CAF50">${res.sisa_poin} poin</b><br><small style="color:#666">Silakan tunjukkan kode ke admin</small>`,
                    confirmButtonColor: '#4CAF50'
                }).then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message, confirmButtonColor: '#4CAF50' });
            }
        });
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
