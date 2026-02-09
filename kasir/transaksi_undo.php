<?php
session_start();
include '../config/database.php';
header('Content-Type: application/json');

$id = (int)$_POST['id'];
$kasirId = $_SESSION['kasir_id'];

$conn->begin_transaction();

try {

  $q = $conn->prepare(
    "SELECT * FROM transaksi
     WHERE id=? AND jenis='BELANJA'
       AND merchant_id=?
       AND DATE(waktu)=CURDATE()
     FOR UPDATE"
  );
  $q->bind_param("ii",$id,$kasirId);
  $q->execute();
  $t = $q->get_result()->fetch_assoc();

  if (!$t) {
    throw new Exception('Undo tidak diizinkan');
  }

  // kembalikan saldo siswa
  $q = $conn->prepare(
    "UPDATE siswa SET saldo=saldo+? WHERE nis=?"
  );
  $q->bind_param("is",$t['nominal'],$t['nis']);
  $q->execute();

  // kurangi saldo merchant
  $q = $conn->prepare(
    "UPDATE merchant SET saldo=saldo-? WHERE id=?"
  );
  $q->bind_param("ii",$t['nominal'],$kasirId);
  $q->execute();

  // tandai transaksi dibatalkan
  $q = $conn->prepare(
    "UPDATE transaksi SET jenis='UNDO_BELANJA' WHERE id=?"
  );
  $q->bind_param("i",$id);
  $q->execute();

  $conn->commit();
  echo json_encode(['status'=>'ok']);

}catch(Exception $e){
  $conn->rollback();
  echo json_encode([
    'status'=>'error',
    'msg'=>$e->getMessage()
  ]);
}
