<?php
session_start();
if (!isset($_SESSION['kasir_id'])) {
  header("Location: login.php");
  exit;
}
include '../config/database.php';
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
    Kasir: <?= $_SESSION['kasir_nama']; ?>
  </span>
  <a href="logout.php" class="btn btn-danger btn-sm me-3">Logout</a>
</nav>
<h4>Transaksi Belanja</h4>
<form id="formTransaksi">
  <input type="text" name="nis" id="nis" class="form-control mb-2"
         placeholder="Scan / Input NIS" required autofocus>

  <input type="number" name="nominal" id="nominal"
         class="form-control mb-2"
         placeholder="Total Belanja" required>

  <button class="btn btn-success w-100">Proses</button>
</form>

<div id="hasil" class="mt-3"></div>

<hr>

<button class="btn btn-secondary" onclick="startScan()">📷 Scan QR</button>
<div id="reader" style="width:100%"></div><br>
<a href="index.php"><button class="btn btn-primary w-100">Kembali</button></a>
<script src="../assets/js/html5-qrcode.min.js"></script>
<script>
function startScan(){
  const scanner = new Html5Qrcode("reader");
  scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    text => {
      document.getElementById('nis').value = text;
      scanner.stop();
    }
  );
}

document.getElementById('formTransaksi').addEventListener('submit', function(e){
  e.preventDefault();

  const form = this;
  const data = new FormData(form);

  fetch('transaksi_belanja.php', {
    method: 'POST',
    body: data
  })
  .then(res => res.json())
  .then(res => {

    if (res.status === 'ok') {
      document.getElementById('hasil').innerHTML = `
        <div class="alert alert-success">
          <h6>✅ Transaksi Berhasil</h6>
          <p>
            <b>NIS:</b> ${res.nis}<br>
            <b>Nama:</b> ${res.nama}<br>
            <b>Total Belanja:</b> Rp ${res.total.toLocaleString()}<br>
            <b>Sisa Saldo:</b> Rp ${res.saldo.toLocaleString()}
          </p>
        </div>
      `;

      form.reset();
      document.getElementById('nis').focus();

    } else {
      document.getElementById('hasil').innerHTML = `
        <div class="alert alert-danger">
          ❌ ${res.msg}
        </div>
      `;
    }

  });
});
</script>
