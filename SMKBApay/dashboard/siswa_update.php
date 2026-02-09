<?php
include "../config/database.php";

$id    = $_POST['id'];
$nis   = trim($_POST['nis']);
$nama  = trim($_POST['nama']);
$kelas = trim($_POST['kelas']);

/* 1. CEK NIS DUPLIKAT (KECUALI DATA SENDIRI) */
$cek = mysqli_prepare(
  $conn,
  "SELECT id FROM siswa WHERE nis = ? AND id != ?"
);
mysqli_stmt_bind_param($cek, "si", $nis, $id);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
  header("Location: siswa.php?msg=nis_exist");
  exit;
}
mysqli_stmt_close($cek);

/* 2. UPDATE DATA (PREPARED STATEMENT) */
$stmt = mysqli_prepare(
  $conn,
  "UPDATE siswa SET nis=?, nama_siswa=?, kelas=? WHERE id=?"
);
mysqli_stmt_bind_param($stmt, "sssi", $nis, $nama, $kelas, $id);

if (mysqli_stmt_execute($stmt)) {
  header("Location: siswa.php?msg=update_success");
} else {
  header("Location: siswa.php?msg=update_error");
}

mysqli_stmt_close($stmt);
