<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Top Up | SMKBApay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>

<div class="d-flex">

<!-- SIDEBAR -->
<div class="sidebar">
  <h5 class="text-white text-center py-3">SMKBApay</h5>
  <a href="index.php">🏠 Dashboard</a>
  <a href="siswa.php">👨‍🎓 Data Siswa</a>
  <a href="topup.php">💳 Top Up Saldo</a>
  <a href="riwayat_topup.php" class="bg-dark">📜 Riwayat Top Up</a>
  <a href="merchant.php">🏪 Merchant</a>
  <a href="laporan.php">📊 Laporan</a>
  <a href="../auth/logout.php">🚪 Logout</a>
</div>

<!-- CONTENT -->
<div class="flex-fill">
<nav class="navbar navbar-light bg-white shadow-sm">
  <span class="ms-3">Riwayat Top Up Saldo</span>
</nav>

<div class="content">

<!-- FILTER -->
<form class="row g-2 mb-3" method="GET">
  <div class="col-md-3">
    <input type="date" name="tgl" class="form-control"
      value="<?= $_GET['tgl'] ?? '' ?>">
  </div>
  <div class="col-md-3">
    <input type="text" name="nis" class="form-control"
      placeholder="Cari NIS"
      value="<?= $_GET['nis'] ?? '' ?>">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100">🔍 Filter</button>
  </div>
</form>

<!-- TABEL -->
<div class="table-responsive">
<table class="table table-bordered table-striped">
<thead class="table-dark">
<tr>
  <th>No</th>
  <th>Waktu</th>
  <th>NIS</th>
  <th>Nama</th>
  <th>Nominal</th>
  <th>Admin</th>
  <th>Status</th>
</tr>
</thead>
<tbody>
<?php
$where = "WHERE t.jenis='TOPUP'";
if (!empty($_GET['tgl'])) {
  $tgl = $_GET['tgl'];
  $where .= " AND DATE(t.waktu)='$tgl'";
}
if (!empty($_GET['nis'])) {
  $nis = $_GET['nis'];
  $where .= " AND t.nis LIKE '%$nis%'";
}

$sql = "
  SELECT 
    t.id,
    t.nis,
    s.nama_siswa,
    t.nominal,
    t.admin,
    t.waktu,
    IFNULL(t.keterangan,'AKTIF') AS status
  FROM transaksi t
  JOIN siswa s ON t.nis = s.nis
  WHERE t.jenis != 'BELANJA'
";

if (!empty($_GET['tgl'])) {
  $tgl = mysqli_real_escape_string($conn, $_GET['tgl']);
  $sql .= " AND DATE(t.waktu) = '$tgl'";
}

if (!empty($_GET['nis'])) {
  $nis = mysqli_real_escape_string($conn, $_GET['nis']);
  $sql .= " AND t.nis LIKE '%$nis%'";
}

$sql .= " ORDER BY t.waktu DESC";

$q = mysqli_query($conn, $sql);

// DEBUG WAJIB (hapus setelah fix)
if (!$q) {
  die("Query Error: " . mysqli_error($conn));
}

$no = 1;
while ($row = mysqli_fetch_assoc($q)) {
?>
<tr>
  <td><?= $no++ ?></td>
  <td><?= $row['waktu'] ?></td>
  <td><?= $row['nis'] ?></td>
  <td><?= $row['nama_siswa'] ?></td>
  <td>Rp <?= number_format($row['nominal'],0,',','.') ?></td>
  <td><?= $row['admin'] ?></td>
  <td>
    <?php if ($row['status']=='DIBATALKAN'): ?>
      <span class="badge bg-secondary">Dibatalkan</span>
    <?php else: ?>
      <span class="badge bg-success">Aktif</span>
    <?php endif ?>
    <button class="btn btn-danger btn-sm"
      onclick="undoTopup(<?= $row['id'] ?>)">
      ⛔ Undo
    </button>

  </td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
</div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
function undoTopup(id){
  if (!confirm('Batalkan top up ini? Saldo siswa akan dikurangi')) return;

  fetch('topup_undo.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'id=' + encodeURIComponent(id)
  })
  .then(res => res.text()) // 🔍 lihat respon mentah
  .then(txt => {
    console.log(txt);
    const res = JSON.parse(txt);

    if (res.status === 'ok') {
      alert('Top up berhasil dibatalkan');
      location.reload();
    } else {
      alert(res.msg);
    }
  })
  .catch(err => {
    alert('Gagal koneksi / response tidak valid');
    console.error(err);
  });
}
</script>

</body>
</html>
