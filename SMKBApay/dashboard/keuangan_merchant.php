<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$q = mysqli_query($conn,"SELECT * FROM merchant ORDER BY nama_merchant");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keuangan Merchant | SMKBApay</title>
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
    <a href="keuangan_merchant.php" class="bg-dark">💰 Keuangan Merchant</a>
    <a href="laporan.php">📊 Laporan</a>
    <a href="../auth/logout.php">🚪 Logout</a>
  </div>
  <div class="flex-fill">
    <nav class="navbar navbar-light bg-white shadow-sm">
      <button class="btn btn-primary d-md-none" id="toggleSidebar">☰</button>
      <span class="ms-3">Merchant</span>
    </nav>
    <table class="table table-bordered">
      <thead class="table-primary">
      <tr>
        <th>Merchant</th>
        <th>Saldo</th>
        <th>Aksi</th>
      </tr>
      </thead>
      <tbody>
      <?php while($m=mysqli_fetch_assoc($q)) { ?>
      <tr>
        <td><?= $m['nama_merchant']; ?></td>
        <td>Rp <?= number_format($m['saldo'],0,',','.'); ?></td>
        <td>
          <button class="btn btn-success btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#tarik<?= $m['id']; ?>">
            Tarik Saldo
          </button>
        </td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <?php
$q2 = mysqli_query($conn,"SELECT * FROM merchant");
while($m=mysqli_fetch_assoc($q2)) {
?>
<div class="modal fade" id="tarik<?= $m['id']; ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="merchant_tarik.php" class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title">Tarik Saldo Merchant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="merchant_id" value="<?= $m['id']; ?>">

        <label>Merchant</label>
        <input class="form-control mb-2" readonly
          value="<?= $m['nama_merchant']; ?>">

        <label>Saldo Saat Ini</label>
        <input class="form-control mb-2" readonly
          value="Rp <?= number_format($m['saldo'],0,',','.'); ?>">

        <label>Nominal Tarik</label>
        <input type="number" name="nominal"
          class="form-control" required>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">
          Proses Tarik
        </button>
      </div>

    </form>
  </div>
</div>
<?php } ?>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
</body>
</html>