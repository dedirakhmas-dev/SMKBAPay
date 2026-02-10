<?php
session_start();
if (!isset($_SESSION['kasir_id'])) {
  header("Location: login.php");
  exit;
}
include '../config/database.php';
$kasir = $_SESSION['kasir_nama'];

$q = $conn->prepare(
 "SELECT 
   COUNT(*) total_trx,
   SUM(nominal) total_uang
  FROM transaksi
  WHERE jenis='BELANJA' AND admin=?"
);
$q->bind_param("s", $kasir);
$q->execute();
$r = $q->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kasir | SMKBApay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <span class="navbar-brand ms-3">
    Kasir: <?= $kasir; ?>
  </span>
  <a href="logout.php" class="btn btn-danger btn-sm me-3">Logout</a>
</nav>

<div class="container mt-4">
  <div class="row mt-3">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Total Transaksi</h6>
        <h3><?= $r['total_trx'] ?? 0; ?></h3>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6>Total Pendapatan</h6>
        <h3>
          Rp <?= number_format($r['total_uang'] ?? 0,0,',','.'); ?>
        </h3>
      </div>
    </div>
  </div>
</div>
<br>
  <a href="transaksi.php" class="btn btn-success w-100 mb-2">
    🧾 Transaksi Belanja
  </a>
  <a href="riwayat.php" class="btn btn-primary w-100 mb-2">
  📄 Riwayat Transaksi
  </a>
<a href="saldo.php" class="btn btn-warning w-100 mb-2">
  💰 Saldo Saya
</a>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
