<?php
session_start();
if (!isset($_SESSION['kasir_id'])) {
  header("Location: login.php");
  exit;
}

include '../config/database.php';

$kasir = $_SESSION['kasir_nama'];
// LIMIT DROPDOWN
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, [10,25,50])) $limit = 10;

// PAGE
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// TOTAL DATA
$countQ = $conn->prepare(
  "SELECT COUNT(*) total FROM transaksi
   WHERE jenis='BELANJA' AND admin=?"
);
$countQ->bind_param("s",$kasir);
$countQ->execute();
$totalData = $countQ->get_result()->fetch_assoc()['total'];

$totalPage = ceil($totalData / $limit);

// DATA PER PAGE
$q = $conn->prepare(
  "SELECT * FROM transaksi
   WHERE jenis='BELANJA' AND admin=?
   ORDER BY waktu DESC
   LIMIT ? OFFSET ?"
);
$q->bind_param("sii",$kasir,$limit,$offset);
$q->execute();
$data = $q->get_result();

// TOTAL PENDAPATAN (SEMUA DATA TANPA LIMIT)
$totalQ = $conn->prepare(
  "SELECT SUM(nominal) total FROM transaksi
   WHERE jenis='BELANJA' AND admin=?"
);
$totalQ->bind_param("s",$kasir);
$totalQ->execute();
$totalPendapatan = $totalQ->get_result()->fetch_assoc()['total'] ?? 0;

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
<form method="get" class="mb-2 d-flex gap-2">
  <label class="fw-bold">Tampilkan</label>
  <select name="limit" class="form-select w-auto"
          onchange="this.form.submit()">
    <option value="10" <?= $limit==10?'selected':'' ?>>10</option>
    <option value="25" <?= $limit==25?'selected':'' ?>>25</option>
    <option value="50" <?= $limit==50?'selected':'' ?>>50</option>
  </select>
  <input type="hidden" name="page" value="1">
</form>
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
$no = $offset + 1;

while($r = mysqli_fetch_assoc($data)){
?>
<tr>
  <td><?= $no++; ?></td>
  <td><?= $r['waktu']; ?></td>
  <td><?= $r['nis']; ?></td>
  <td>
    Rp <?= number_format($r['nominal'],0,',','.'); ?>
    <?php if (date('Y-m-d', strtotime($r['waktu'])) == date('Y-m-d')): ?>
      <button class="btn btn-sm btn-danger"
        onclick="undoTransaksi(<?= $r['id'] ?>)">
        ⏪ Undo
      </button>
    <?php endif; ?>
  </td>
</tr>
<?php } ?>

<?php if ($totalData==0) { ?>
<tr>
  <td colspan="4" class="text-center">Belum ada transaksi</td>
</tr>
<?php } ?>
</tbody>
<tfoot>
<tr class="table-success fw-bold">
  <td colspan="3">TOTAL PENDAPATAN</td>
  <td>
    Rp <?= number_format($totalPendapatan,0,',','.'); ?>
  </td>
</tr>
</tfoot>
</table>
<nav>
<ul class="pagination justify-content-center">

<?php if($page > 1): ?>
<li class="page-item">
  <a class="page-link"
     href="?page=<?= $page-1 ?>&limit=<?= $limit ?>">«</a>
</li>
<?php endif; ?>

<?php for($i=1; $i<=$totalPage; $i++): ?>
<li class="page-item <?= $i==$page?'active':'' ?>">
  <a class="page-link"
     href="?page=<?= $i ?>&limit=<?= $limit ?>">
     <?= $i ?>
  </a>
</li>
<?php endfor; ?>

<?php if($page < $totalPage): ?>
<li class="page-item">
  <a class="page-link"
     href="?page=<?= $page+1 ?>&limit=<?= $limit ?>">»</a>
</li>
<?php endif; ?>

</ul>
</nav>
<a href="index.php" class="btn btn-secondary">⬅ Kembali</a>
</div>
<script>
function undoTransaksi(id){
  if (!confirm('Batalkan transaksi ini?\nSaldo siswa akan dikembalikan'))
    return;

  fetch('transaksi_undo.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id
  })
  .then(res=>res.json())
  .then(res=>{
    if(res.status==='ok'){
      alert('Transaksi berhasil dibatalkan');
      location.reload();
    }else{
      alert(res.msg);
    }
  });
}
</script>
</body>
</html>
