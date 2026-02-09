<?php
session_start();
include '../config/database.php';

$merchant = (int)$_POST['merchant_id'];
$nominal  = (int)$_POST['nominal'];
$admin    = $_SESSION['admin_id'];

$conn->begin_transaction();

try {
  // Lock saldo merchant
  $q = $conn->prepare(
    "SELECT saldo FROM merchant WHERE id=? FOR UPDATE"
  );
  $q->bind_param("i",$merchant);
  $q->execute();
  $r = $q->get_result();

  if ($r->num_rows==0) throw new Exception("Merchant tidak ditemukan");

  $m = $r->fetch_assoc();
  if ($m['saldo'] < $nominal)
    throw new Exception("Saldo merchant tidak cukup");

  // Kurangi saldo merchant
  $u = $conn->prepare(
    "UPDATE merchant SET saldo = saldo - ? WHERE id=?"
  );
  $u->bind_param("ii",$nominal,$merchant);
  $u->execute();

  // Catat penarikan
  $i = $conn->prepare(
    "INSERT INTO merchant_tarik (merchant_id, nominal, admin)
     VALUES (?,?,?)"
  );
  $i->bind_param("iis",$merchant,$nominal,$admin);
  $i->execute();

  $conn->commit();
  header("Location: keuangan_merchant.php?ok=1");

} catch (Exception $e) {
  $conn->rollback();
  header("Location: keuangan_merchant.php?err=".$e->getMessage());
}
