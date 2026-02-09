<?php
session_start();
include '../config/database.php';

$username = trim($_POST['username']);
$password = $_POST['password'];

$stmt = $conn->prepare(
  "SELECT id, nama_merchant, password
   FROM merchant
   WHERE username=? AND status='AKTIF'
   LIMIT 1"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
  $stmt->bind_result($id, $nama, $hash);
  $stmt->fetch();

  if (password_verify($password, $hash)) {
    $_SESSION['kasir_id']   = $id;
    $_SESSION['kasir_nama'] = $nama;

    header("Location: index.php");
    exit;
  }
}

header("Location: login.php?err=1");
exit;
