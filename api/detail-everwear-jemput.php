<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';
$id = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare('SELECT j.*, u.nama, u.no_hp, u.email, a.nama AS admin_nama FROM everwear_jemput j JOIN users u ON u.id=j.customer_id LEFT JOIN users a ON a.id=j.admin_id WHERE j.id=?');
$stmt->bind_param('i', $id); $stmt->execute(); $d = $stmt->get_result()->fetch_assoc();
if (!$d) { echo json_encode(['success'=>false,'message'=>'Permintaan tidak ditemukan.']); exit; }
echo json_encode(['success'=>true,'data'=>[
    'kode'=>$d['kode_jemput'], 'customer'=>$d['nama'], 'no_hp'=>$d['no_hp'] ?: '-',
    'status'=>$d['status'], 'jadwal'=>date('d M Y H:i', strtotime($d['tanggal_jemput'].' '.$d['jam_jemput'])),
    'alamat'=>$d['alamat_penjemputan'], 'jarak_km'=>$d['jarak_km'], 'biaya_ongkir'=>number_format($d['biaya_ongkir'],0,',','.'),
    'pcs_layak'=>intval($d['pcs_layak']), 'pcs_recycle'=>intval($d['pcs_recycle']), 'berat_kg'=>$d['berat_kg'],
    'poin_diperoleh'=>intval($d['poin_diperoleh']), 'catatan_lokasi'=>$d['catatan_lokasi'], 'catatan_admin'=>$d['catatan_admin'], 'dicatat'=>($d['verified_at'] ? date('d M Y H:i', strtotime($d['verified_at'])) . ' oleh ' . ($d['admin_nama'] ?: 'Admin') : null)
]]);
