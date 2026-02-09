<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | SMKBApay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="d-flex">
  
  <!-- SIDEBAR -->
  <div class="sidebar" id="sidebar">
    <h5 class="text-white text-center py-3">SMKBApay</h5>

    <a href="index.php">🏠 Dashboard</a>
    <a href="siswa.php">👨‍🎓 Data Siswa</a>
    <a href="topup.php">💳 Top Up Saldo</a>
    <a href="riwayat_topup.php">📜 Riwayat Top Up</a>
    <a href="merchant.php">🏪 Merchant</a>
    <a href="keuangan_merchant.php">💰 Keuangan Merchant</a>
    <a href="laporan.php">📊 Laporan</a>
    <a href="../auth/logout.php">🚪 Logout</a>
  </div>

  <!-- CONTENT -->
  <div class="flex-fill">
    <nav class="navbar navbar-light bg-white shadow-sm">
      <button class="btn btn-primary d-md-none" id="toggleSidebar">
        ☰
      </button>
      <span class="ms-3">
        Halo, <?= $_SESSION['admin_nama']; ?>
      </span>
    </nav>

    <div class="content">
      <h4>Dashboard</h4>
      <p>Selamat datang di sistem pembayaran digital SMKBApay.</p>

      <div class="row">
        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6>Total Siswa</h6>
              <h3>0</h3>
            </div>
          </div>
        </div>

        <div class="col-md-3 mb-3">
          <div class="card shadow-sm">
            <div class="card-body">
              <h6>Total Saldo</h6>
              <h3>Rp 0</h3>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>
