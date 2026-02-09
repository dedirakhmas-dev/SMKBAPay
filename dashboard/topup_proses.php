<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

$nis     = $_POST['nis'] ?? '';
$nominal = (int)($_POST['nominal'] ?? 0);
$admin   = 'admin';

$conn->begin_transaction();

try {

  // Ambil & lock data siswa
  $q = $conn->prepare(
    "SELECT nama_siswa, saldo FROM siswa WHERE nis=? FOR UPDATE"
  );
  $q->bind_param("s", $nis);
  $q->execute();
  $r = $q->get_result();

  if ($r->num_rows == 0) {
    throw new Exception("Siswa tidak ditemukan");
  }

  $s = $r->fetch_assoc();
  $saldoAwal = $s['saldo'];
  $saldoBaru = $saldoAwal + $nominal;

  // Update saldo siswa
  $u = $conn->prepare(
    "UPDATE siswa SET saldo=? WHERE nis=?"
  );
  $u->bind_param("is", $saldoBaru, $nis);
  $u->execute();

  // Catat transaksi TOPUP
  $t = $conn->prepare(
    "INSERT INTO transaksi (nis, jenis, nominal, admin)
     VALUES (?, 'TOPUP', ?, ?)"
  );
  $t->bind_param("sis", $nis, $nominal, $admin);
  $t->execute();

  $conn->commit();

  echo json_encode([
    'status'     => 'ok',
    'nis'        => $nis,
    'nama'       => $s['nama_siswa'],
    'saldo_awal' => $saldoAwal,
    'topup'      => $nominal,
    'saldo_akhir'=> $saldoBaru
  ]);
  exit;

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode([
    'status' => 'error',
    'msg'    => $e->getMessage()
  ]);
  exit;
}
