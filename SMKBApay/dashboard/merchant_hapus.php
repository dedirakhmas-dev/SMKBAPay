<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$id = $_GET['id'];
$conn->query("DELETE FROM merchant WHERE id=$id");

header("Location: merchant.php?msg=delete_ok");
