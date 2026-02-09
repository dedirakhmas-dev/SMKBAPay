<?php
session_start();
include '../config/database.php';

header('Content-Type: application/json');

$nis = $_POST['nis'] ?? '';

$q = $conn->prepare(
  "SELECT nis, nama_siswa, saldo
   FROM siswa
   WHERE nis=?"
);
$q->bind_param("s", $nis);
$q->execute();
$r = $q->get_result();

if ($r->num_rows == 0) {
  echo json_encode([
    'status' => 'error',
    'msg'    => 'Siswa tidak ditemukan'
  ]);
  exit;
}

$s = $r->fetch_assoc();

echo json_encode([
  'status' => 'ok',
  'nis'    => $s['nis'],
  'nama'   => $s['nama_siswa'],
  'saldo'  => $s['saldo']
]);
