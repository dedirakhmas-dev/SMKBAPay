<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status'=>'err','msg'=>'Unauthorized']);
  exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['status'=>'err','msg'=>'ID tidak valid']);
  exit;
}

$hariIni = date('Y-m-d');

$conn->begin_transaction();

try {
  // Ambil transaksi TOPUP + lock
  $q = $conn->prepare(
    "SELECT nis, nominal, keterangan, DATE(tanggal) AS tgl
     FROM transaksi
     WHERE id=? AND jenis='TOPUP'
     FOR UPDATE"
  );
  $q->bind_param("i", $id);
  $q->execute();
  $r = $q->get_result();

  if ($r->num_rows == 0) {
    throw new Exception("Transaksi tidak ditemukan");
  }

  $t = $r->fetch_assoc();

  // ❌ Sudah dibatalkan
  if ($t['keterangan'] === 'DIBATALKAN') {
    throw new Exception("Top up sudah dibatalkan");
  }

  // ❌ Bukan hari ini
  if ($t['tgl'] !== $hariIni) {
    throw new Exception("Undo hanya boleh di hari yang sama");
  }

  // Kurangi saldo siswa
  $u = $conn->prepare(
    "UPDATE siswa 
     SET saldo = saldo - ?
     WHERE nis=?"
  );
  $u->bind_param("is", $t['nominal'], $t['nis']);
  $u->execute();

  // Update status transaksi
  $s = $conn->prepare(
    "UPDATE transaksi 
     SET jenis='UNDO_TOPUP',keterangan='DIBATALKAN'
     WHERE id=?"
  );
  $s->bind_param("i", $id);
  $s->execute();

  $conn->commit();

  echo json_encode(['status'=>'ok']);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode([
    'status'=>'err',
    'msg'=>$e->getMessage()
  ]);
  exit;
}