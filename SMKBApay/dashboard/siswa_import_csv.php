<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}

include "../config/database.php";

$file = $_FILES['file_csv']['tmp_name'];
$handle = fopen($file, "r");

if ($handle === false) {
  header("Location: siswa.php?msg=import_error");
  exit;
}

/* PREPARED STATEMENT INSERT */
$insert = mysqli_prepare(
  $conn,
  "INSERT INTO siswa (nis, nama_siswa, kelas) VALUES (?,?,?)"
);

$success = 0;
$duplicate = 0;
$rowNum = 0;

while (($row = fgetcsv($handle, 1000, ",")) !== false) {
  $rowNum++;

  /* SKIP HEADER */
  if ($rowNum == 1) continue;

  $nis   = trim($row[0] ?? '');
  $nama  = trim($row[1] ?? '');
  $kelas = trim($row[2] ?? '');

  if ($nis == '' || $nama == '' || $kelas == '') continue;

  /* CEK DUPLIKAT NIS */
  $cek = mysqli_prepare($conn, "SELECT id FROM siswa WHERE nis=?");
  mysqli_stmt_bind_param($cek, "s", $nis);
  mysqli_stmt_execute($cek);
  mysqli_stmt_store_result($cek);

  if (mysqli_stmt_num_rows($cek) > 0) {
    $duplicate++;
    mysqli_stmt_close($cek);
    continue;
  }
  mysqli_stmt_close($cek);

  mysqli_stmt_bind_param($insert, "sss", $nis, $nama, $kelas);
  if (mysqli_stmt_execute($insert)) {
    $success++;
  }
}

fclose($handle);
mysqli_stmt_close($insert);

header("Location: siswa.php?msg=import_ok&ok=$success&dup=$duplicate");
