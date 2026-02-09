<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$stmt = $conn->prepare(
  "UPDATE merchant SET
   nama_merchant=?,
   pemilik=?,
   no_hp=?,
   status=?
   WHERE id=?"
);
$stmt->bind_param("ssssi",
  $_POST['nama'],
  $_POST['pemilik'],
  $_POST['hp'],
  $_POST['status'],
  $_POST['id']
);

$stmt->execute();
header("Location: merchant.php?msg=edit_ok");
