<?php
session_start();
if (!isset($_SESSION['kasir_id'])) {
  header("Location: login.php");
  exit;
}

include '../config/database.php';

$kasir = $_SESSION['kasir_nama'];

$q = $conn->prepare(
  "SELECT * FROM transaksi
   WHERE jenis='BELANJA' AND admin=?
   ORDER BY waktu DESC"
);
$q->bind_param("s", $kasir);
$q->execute();
$data = $q->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Transaksi | SMKBApay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="container mt-4">
<h4>🧾 Riwayat Transaksi - <?= htmlspecialchars($kasir); ?></h4>

<table class="table table-bordered table-striped mt-3">
<thead class="table-primary">
<tr>
  <th>No</th>
  <th>Waktu</th>
  <th>NIS</th>
  <th>Nominal</th>
</tr>
</thead>
<tbody>

<?php
$no=1;
$total=0;
while($r=mysqli_fetch_assoc($data)){
  $total += $r['nominal'];
?>
<tr>
  <td><?= $no++; ?></td>
  <td><?= $r['waktu']; ?></td>
  <td><?= $r['nis']; ?></td>
  <td>Rp <?= number_format($r['nominal'],0,',','.'); ?></td>
</tr>
<?php } ?>

<?php if ($no==1) { ?>
<tr>
<td colspan="4" class="text-center">Belum ada transaksi</td>
</tr>
<?php } ?>

</tbody>
<tfoot>
<tr class="table-success fw-bold">
<td colspan="3">TOTAL PENDAPATAN</td>
<td>Rp <?= number_format($total,0,',','.'); ?></td>
</tr>
</tfoot>
</table>

<a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
</div>

</body>
</html>
