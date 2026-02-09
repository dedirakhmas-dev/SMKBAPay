<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

$nis     = $_POST['nis'];
$nominal = (int)$_POST['nominal'];
$kasir   = $_SESSION['kasir_nama'];
$kasirId = $_SESSION['kasir_id'];

$conn->begin_transaction();

try {

  $q = $conn->prepare(
    "SELECT nama_siswa, saldo FROM siswa WHERE nis=? FOR UPDATE"
  );
  $q->bind_param("s",$nis);
  $q->execute();
  $s = $q->get_result()->fetch_assoc();

  if (!$s) {
    throw new Exception("Siswa tidak ditemukan");
  }

  if ($s['saldo'] < $nominal) {
    throw new Exception("Saldo tidak cukup");
  }

  // kurangi saldo siswa
  $q = $conn->prepare(
    "UPDATE siswa SET saldo=saldo-? WHERE nis=?"
  );
  $q->bind_param("is",$nominal,$nis);
  $q->execute();

  // tambah saldo kasir
  $q = $conn->prepare(
    "UPDATE merchant SET saldo=saldo+? WHERE id=?"
  );
  $q->bind_param("ii",$nominal,$kasirId);
  $q->execute();

  // catat transaksi
  $q = $conn->prepare(
    "INSERT INTO transaksi(nis,jenis,nominal,waktu,admin,merchant_id)
     VALUES(?, 'BELANJA', ?, NOW(), ?, ?)"
  );
  $q->bind_param("sisi",$nis,$nominal,$kasir,$kasirId);
  $q->execute();

  $conn->commit();

  echo json_encode([
    'status' => 'ok',
    'nis'    => $nis,
    'nama'   => $s['nama_siswa'],
    'total'  => $nominal,
    'saldo'  => $s['saldo'] - $nominal
  ]);
  exit;

} catch(Exception $e) {
  $conn->rollback();
  echo json_encode([
    'status' => 'error',
    'msg'    => $e->getMessage()
  ]);
  exit;
}
