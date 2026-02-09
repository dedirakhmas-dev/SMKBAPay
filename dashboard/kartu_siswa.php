<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$kelas = $_GET['kelas'] ?? '';
$where = $kelas ? "WHERE kelas='$kelas'" : '';

$q = mysqli_query($conn, "SELECT * FROM siswa $where ORDER BY kelas, nama_siswa");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Cetak Kartu QR Siswa</title>
  <link rel="stylesheet" href="../assets/css/kartu_cetak.css">
</head>
<body onload="window.print()">

<div class="sheet">
<?php while ($s = mysqli_fetch_assoc($q)) { ?>
  <div class="card">
    <div class="header">
      <img src="../assets/img/logo_smk.png" class="logo">
      <div class="school">
        <b>SMK NEGERI 1 BAURENO</b><br>
        Kartu Pembayaran Digital
      </div>
    </div>

    <div class="qr">
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= $s['nis']; ?>">
    </div>

    <div class="info">
      <b><?= $s['nama_siswa']; ?></b><br>
      NIS: <?= $s['nis']; ?><br>
      Kelas: <?= $s['kelas']; ?>
    </div>
  </div>
<?php } ?>
</div>

</body>
</html>
