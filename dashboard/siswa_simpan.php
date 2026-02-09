<?php
include "../config/database.php";

$nis   = trim($_POST['nis']);
$nama  = trim($_POST['nama']);
$kelas = trim($_POST['kelas']);

/* CEK NIS DUPLIKAT */
$cek = mysqli_prepare($conn, "SELECT id FROM siswa WHERE nis=?");
mysqli_stmt_bind_param($cek, "s", $nis);
mysqli_stmt_execute($cek);
mysqli_stmt_store_result($cek);

if (mysqli_stmt_num_rows($cek) > 0) {
  header("Location: siswa.php?msg=nis_exist");
  exit;
}
mysqli_stmt_close($cek);

/* SIMPAN */
$stmt = mysqli_prepare(
  $conn,
  "INSERT INTO siswa (nis, nama_siswa, kelas) VALUES (?,?,?)"
);
mysqli_stmt_bind_param($stmt, "sss", $nis, $nama, $kelas);

if (mysqli_stmt_execute($stmt)) {
  header("Location: siswa.php?msg=insert_success");
} else {
  header("Location: siswa.php?msg=insert_error");
}
