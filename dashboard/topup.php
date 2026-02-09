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
  <title>Top Up | SMKBApay</title>
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
    <a href="topup.php" class="bg-dark">💳 Top Up Saldo</a>
    <a href="riwayat_topup.php">📜 Riwayat Top Up</a>
    <a href="merchant.php">🏪 Merchant</a>
    <a href="keuangan_merchant.php">💰 Keuangan Merchant</a>
    <a href="laporan.php">📊 Laporan</a>
    <a href="../auth/logout.php">🚪 Logout</a>
  </div>

  <!-- CONTENT -->
  <div class="flex-fill">
    <nav class="navbar navbar-light bg-white shadow-sm">
      <button class="btn btn-primary d-md-none" id="toggleSidebar">☰</button>
      <span class="ms-3">Top Up Saldo Siswa</span>
    </nav>
    <div class="content">
    <?php
	if (isset($_GET['msg'])) {
	  switch ($_GET['msg']) {
	    case 'ok':
	      echo '<div class="alert alert-success">✅ Topup Sukses</div>';
	      break;
	    case 'fail':
	      echo '<div class="alert alert-danger">❌ Topup Gagal</div>';
	      break;
	  }
	}
	?>

	<div class="row">
	  <div class="col-md-6">
	    <form id="formTopup">
  <label>NIS</label>
<input type="text" name="nis" id="nis" class="form-control mb-2" required>

<div class="d-flex gap-2 mb-2">
  <button type="button" class="btn btn-secondary" onclick="startScan()">
    📷 Scan QR
  </button>

  <button type="button" class="btn btn-info" onclick="cekSiswa()">
    🔍 Cek Data Siswa
  </button>
</div>
<div id="infoSiswa" class="alert alert-info d-none"></div>
<label>Nominal Top Up</label>
<input type="number" name="nominal" id="nominal"
       class="form-control mb-3" required>
<button id="btnTopup" class="btn btn-success w-100" disabled>
  💰 Proses Top Up
</button>
</form>
<div id="loading" class="text-center my-3 d-none">
  <div class="spinner-border text-primary" role="status"></div>
  <div>Memproses...</div>
</div>
<div id="hasilTopup" class="mt-3"></div>
<div id="reader" style="width:100%;display:none"></div>
	  </div>
	</div>
<script src="../assets/js/html5-qrcode.min.js"></script>
<script>
let scanner;

function startScan(){
  document.getElementById('reader').style.display = 'block';

  scanner = new Html5Qrcode("reader");
  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    qrCodeMessage => {
		  document.getElementById('nis').value = qrCodeMessage;
		  scanner.stop();
		  document.getElementById('reader').style.display = 'none';
		  cekSiswa(); // AUTO tampil data siswa
		}
  );
}

document.getElementById('formTopup').addEventListener('submit', function(e){
  e.preventDefault();

  if (!siswaValid) {
    alert('Cek data siswa terlebih dahulu');
    return;
  }

  showLoading(true);
  document.getElementById('btnTopup').disabled = true;

  const data = new FormData(this);

  fetch('topup_proses.php', {
    method: 'POST',
    body: data
  })
  .then(res => res.json())
  .then(res => {
    showLoading(false);

    if (res.status === 'ok') {
      document.getElementById('hasilTopup').innerHTML = `
        <div class="alert alert-success">
          <h6>✅ Top Up Berhasil</h6>
          <b>NIS:</b> ${res.nis}<br>
          <b>Nama:</b> ${res.nama}<br>
          <b>Saldo Awal:</b> Rp ${res.saldo_awal.toLocaleString()}<br>
          <b>Top Up:</b> Rp ${res.topup.toLocaleString()}<br>
          <b>Saldo Sekarang:</b>
          <span class="fw-bold text-success">
            Rp ${res.saldo_akhir.toLocaleString()}
          </span>
        </div>
      `;

      siswaValid = false;
      document.getElementById('formTopup').reset();
      document.getElementById('infoSiswa').classList.add('d-none');

    } else {
      document.getElementById('hasilTopup').innerHTML =
        `<div class="alert alert-danger">❌ ${res.msg}</div>`;
    }
  })
  .catch(err => {
    showLoading(false);
    alert('Top up gagal');
  });
});

function cekSiswa(){
  const nis = document.getElementById('nis').value;

  if (!nis) {
    alert('Masukkan NIS terlebih dahulu');
    return;
  }

  siswaValid = false;
  document.getElementById('btnTopup').disabled = true;

  showLoading(true);

  const data = new FormData();
  data.append('nis', nis);

  fetch('topup_get_siswa.php', {
    method: 'POST',
    body: data
  })
  .then(res => res.json())
  .then(res => {
    showLoading(false);

    const info = document.getElementById('infoSiswa');
    info.classList.remove('d-none','alert-danger');
    info.classList.add('alert-info');

    if (res.status === 'ok') {
      siswaValid = true;
      document.getElementById('btnTopup').disabled = false;

      info.innerHTML = `
        <b>NIS:</b> ${res.nis}<br>
        <b>Nama:</b> ${res.nama}<br>
        <b>Saldo Saat Ini:</b>
        <span class="fw-bold text-success">
          Rp ${res.saldo.toLocaleString()}
        </span>
      `;
    } else {
      siswaValid = false;
      info.classList.remove('alert-info');
      info.classList.add('alert-danger');
      info.innerHTML = `❌ ${res.msg}`;
    }
  })
  .catch(err => {
    showLoading(false);
    alert('Gagal mengambil data siswa');
  });
}

let siswaValid = false;

function showLoading(show=true){
  document.getElementById('loading')
    .classList.toggle('d-none', !show);
}

</script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dashboard.js"></script>
</body>
</html>