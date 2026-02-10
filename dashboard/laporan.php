<?php
session_start();
if (!isset($_SESSION['admin_id'])) exit;
include "../config/database.php";

$bulan = $_GET['bulan'] ?? date('Y-m');
?>
<h4>📊 Laporan Keuangan</h4>

<form method="GET" class="mb-3">
  <input type="month" name="bulan" value="<?= $bulan ?>">
  <button class="btn btn-primary btn-sm">Filter</button>
</form>

<?php
$q = $conn->prepare(
  "SELECT
    SUM(CASE WHEN jenis='TOPUP' THEN nominal ELSE 0 END) AS topup,
    SUM(CASE WHEN jenis='BELANJA' THEN nominal ELSE 0 END) AS belanja
   FROM transaksi
   WHERE DATE_FORMAT(waktu,'%Y-%m')=?"
);
$q->bind_param("s",$bulan);
$q->execute();
$d = $q->get_result()->fetch_assoc();
?>

<table class="table table-bordered w-50">
<tr>
  <th>Total Top Up</th>
  <td>Rp <?= number_format($d['topup'],0,',','.'); ?></td>
</tr>
<tr>
  <th>Total Belanja</th>
  <td>Rp <?= number_format($d['belanja'],0,',','.'); ?></td>
</tr>
<tr class="table-success fw-bold">
  <th>Saldo Beredar</th>
  <td>Rp <?= number_format($d['topup']-$d['belanja'],0,',','.'); ?></td>
</tr>
</table>
