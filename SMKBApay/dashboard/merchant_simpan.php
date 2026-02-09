<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$stmt = $conn->prepare(
  "INSERT INTO merchant (nama_merchant,pemilik,no_hp)
   VALUES (?,?,?)"
);
$stmt->bind_param("sss",
  $_POST['nama'],
  $_POST['pemilik'],
  $_POST['hp']
);

$stmt->execute();
header("Location: merchant.php?msg=add_ok");
