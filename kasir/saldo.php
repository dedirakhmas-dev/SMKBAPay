<?php
session_start();
if (!isset($_SESSION['kasir_id'])) {
  header("Location: login.php"); exit;
}
include '../config/database.php';

$merchantId = $_SESSION['kasir_id'];

/* SALDO MERCHANT */
$q = $conn->prepare("SELECT saldo FROM merchant WHERE id=?");
$q->bind_param("i",$merchantId);
$q->execute();
$saldo = $q->get_result()->fetch_assoc()['saldo'] ?? 0;

/* RIWAYAT TARIK */
$r = $conn->prepare(
  "SELECT * FROM merchant_tarik
   WHERE merchant_id=?
   ORDER BY tanggal DESC"
);
$r->bind_param("i",$merchantId);
$r->execute();
$data = $r->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Saldo Kasir</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="container mt-4">

<h4>💰 Saldo Merchant</h4>

<div class="alert alert-success">
  <b>Saldo Mengendap:</b><br>
  <h3>Rp <?= number_format($saldo,0,',','.'); ?></h3>
</div>

<button class="btn btn-danger mb-3"
  data-bs-toggle="modal"
  data-bs-target="#modalTarik">
  ⬇ Tarik Saldo
</button>

<h5>📜 Riwayat Penarikan</h5>

<table class="table table-bordered">
<thead class="table-light">
<tr>
  <th>Tanggal</th>
  <th>Nominal</th>
  <th>Admin</th>
</tr>
</thead>
<tbody>
<?php if ($data->num_rows==0): ?>
<tr><td colspan="3" class="text-center">Belum ada penarikan</td></tr>
<?php endif; ?>

<?php while($r=mysqli_fetch_assoc($data)): ?>
<tr>
  <td><?= $r['tanggal']; ?></td>
  <td>Rp <?= number_format($r['nominal'],0,',','.'); ?></td>
  <td><?= $r['admin']; ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>

<a href="index.php" class="btn btn-secondary">⬅ Kembali</a>

<!-- MODAL TARIK -->
<div class="modal fade" id="modalTarik">
<div class="modal-dialog">
<form method="POST" action="tarik_saldo.php" class="modal-content">
  <div class="modal-header">
    <h5>Tarik Saldo</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <label>Nominal</label>
    <input type="number" name="nominal"
           max="<?= $saldo ?>"
           class="form-control" required>
    <small class="text-muted">
      Maks: Rp <?= number_format($saldo,0,',','.'); ?>
    </small>
  </div>
  <div class="modal-footer">
    <button class="btn btn-success">Kirim Permintaan</button>
  </div>
</form>
</div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>