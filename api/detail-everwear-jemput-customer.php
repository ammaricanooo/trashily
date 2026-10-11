<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireCustomer();
require_once '../config/database.php';
$id = intval($_GET['id'] ?? 0);
$uid = intval($_SESSION['user_id']);
$stmt = $conn->prepare('SELECT * FROM everwear_jemput WHERE id=? AND customer_id=?');
$stmt->bind_param('ii', $id, $uid); $stmt->execute(); $d = $stmt->get_result()->fetch_assoc();
if (!$d) { echo json_encode(['success'=>false,'message'=>'Detail penjemputan tidak ditemukan.']); exit; }
echo json_encode(['success'=>true,'data'=>[
    'kode'=>$d['kode_jemput'], 'status'=>$d['status'],
    'jadwal'=>date('d M Y H:i', strtotime($d['tanggal_jemput'].' '.$d['jam_jemput'])),
    'alamat'=>$d['alamat_penjemputan'], 'jarak_km'=>$d['jarak_km'],
    'biaya_ongkir'=>number_format($d['biaya_ongkir'],0,',','.'),
    'pcs_layak'=>intval($d['pcs_layak']), 'pcs_recycle'=>intval($d['pcs_recycle']),
    'berat_kg'=>$d['berat_kg'], 'poin'=>intval($d['poin_diperoleh']),
    'catatan'=>$d['catatan_lokasi'], 'catatan_admin'=>$d['catatan_admin']
]]);
