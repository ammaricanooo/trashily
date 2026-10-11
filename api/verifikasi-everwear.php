<?php
header('Content-Type: application/json');
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/database.php';
$data=json_decode(file_get_contents('php://input'),true)??[];
if(($data['action']??'')!=='verifikasi'){echo json_encode(['success'=>false,'message'=>'Aksi tidak valid.']);exit;}
$id=intval($data['transaksi_id']??0);
$catatan=trim($data['catatan_admin']??'');
$beratBaru=isset($data['berat_kg'])?floatval($data['berat_kg']):null;
$pcsLayakBaru=isset($data['pcs_layak'])?intval($data['pcs_layak']):null;
$pcsRecycleBaru=isset($data['pcs_recycle'])?intval($data['pcs_recycle']):null;
$stmt=$conn->prepare("SELECT * FROM everwear_transaksi WHERE id=?");
$stmt->bind_param('i',$id);$stmt->execute();$trx=$stmt->get_result()->fetch_assoc();
if(!$trx||$trx['status']!=='pending'){echo json_encode(['success'=>false,'message'=>'Transaksi tidak ditemukan / bukan pending.']);exit;}
$berat=$beratBaru!==null&&$beratBaru>0?$beratBaru:floatval($trx['berat_kg']);
$pcsLayak=$pcsLayakBaru!==null&&$pcsLayakBaru>=0?intval($pcsLayakBaru):intval($trx['pcs_layak']??0);
$pcsRecycle=$pcsRecycleBaru!==null&&$pcsRecycleBaru>=0?intval($pcsRecycleBaru):intval($trx['pcs_recycle']??0);
$pcs=$pcsLayak+$pcsRecycle;
$kategori = ($pcsLayak>0&&$pcsRecycle>0)?'campuran':($pcsLayak>0?'layak_pakai':'recycle');
$poin=max(1,round($berat*10));
$adminId=intval($_SESSION['user_id']);
$stmt=$conn->prepare("UPDATE everwear_transaksi SET kategori_pakaian=?,berat_kg=?,jumlah_pcs=?,pcs_layak=?,pcs_recycle=?,poin_diperoleh=?,status='selesai',catatan_admin=?,admin_id=?,verified_at=NOW() WHERE id=?");
$stmt->bind_param('sdiiiisii',$kategori,$berat,$pcs,$pcsLayak,$pcsRecycle,$poin,$catatan,$adminId,$id);
$stmt->execute();
$cid=intval($trx['customer_id']);
$stmt=$conn->prepare("UPDATE users SET poin=poin+? WHERE id=?");
$stmt->bind_param('ii',$poin,$cid);$stmt->execute();
echo json_encode(['success'=>true,'poin'=>$poin,'berat'=>$berat,'pcs_layak'=>$pcsLayak,'pcs_recycle'=>$pcsRecycle]);
