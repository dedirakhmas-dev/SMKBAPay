<?php
session_start();
include '../config/database.php';

$merchantId = $_SESSION['kasir_id'];
$admin = 'SYSTEM';
$nominal = (int)$_POST['nominal'];

$conn->begin_transaction();

try {
  $q = $conn->prepare(
    "SELECT saldo FROM merchant WHERE id=? FOR UPDATE"
  );
  $q->bind_param("i",$merchantId);
  $q->execute();
  $saldo = $q->get_result()->fetch_assoc()['saldo'];

  if ($nominal > $saldo) {
    throw new Exception("Saldo tidak mencukupi");
  }

  $u = $conn->prepare(
    "UPDATE merchant SET saldo=saldo-? WHERE id=?"
  );
  $u->bind_param("ii",$nominal,$merchantId);
  $u->execute();

  $i = $conn->prepare(
    "INSERT INTO merchant_tarik
     (merchant_id, nominal, admin)
     VALUES (?, ?, ?)"
  );
  $i->bind_param("iis",$merchantId,$nominal,$admin);
  $i->execute();

  $conn->commit();
  header("Location: saldo.php");

} catch(Exception $e){
  $conn->rollback();
  die($e->getMessage());
}