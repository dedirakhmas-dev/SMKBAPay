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

<div class="container mt-3">
<h4>Transaksi Belanja</h4>

<form id="formTransaksi">
  <label>NIS</label>
  <input type="text" id="nis" name="nis"
         class="form-control mb-2"
         placeholder="Scan / Input NIS" required>

  <div class="d-flex gap-2 mb-2">
    <button type="button" class="btn btn-secondary" onclick="startScan()">
      📷 Scan QR
    </button>
    <button type="button" class="btn btn-info" onclick="cekSiswa()">
      🔍 Cek Siswa
    </button>
  </div>

  <div id="previewSiswa" class="alert alert-info d-none"></div>

  <label>Total Belanja</label>
  <input type="number" id="nominal" name="nominal"
         class="form-control mb-2" disabled required>

  <button id="btnProses" class="btn btn-success w-100" disabled>
    💳 Proses Transaksi
  </button>
</form>

<div id="hasil" class="mt-3"></div>

<div id="reader" style="max-width:350px;display:none"></div>

<a href="index.php" class="btn btn-primary w-100 mt-2">Kembali</a>
</div>

<!-- MODAL KONFIRMASI -->
<div class="modal fade" id="modalKonfirmasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Konfirmasi Transaksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <ul>
          <li><b>NIS:</b> <span id="k_nis"></span></li>
          <li><b>Nama:</b> <span id="k_nama"></span></li>
          <li><b>Total:</b> Rp <span id="k_total"></span></li>
        </ul>
        <div class="alert alert-danger">
          ⚠ Saldo siswa akan dikurangi
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" id="btnKonfirmasi">✔ Ya, Proses</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPT -->
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/html5-qrcode.min.js"></script>

<script>
let siswaValid = false;
let namaSiswa  = '';
let modalKonfirmasi;
let qrScanner;

document.addEventListener('DOMContentLoaded', () => {
  modalKonfirmasi =
    new bootstrap.Modal(document.getElementById('modalKonfirmasi'));
});

// === SCAN QR ===
function startScan(){
  resetHasilTransaksi(); // ⬅️ TAMBAH INI
  const reader = document.getElementById('reader');
  reader.style.display = 'block';
  reader.innerHTML = '';

  qrScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 },
    false
  );
  qrScanner.render(text => {
    document.getElementById('nis').value = text;
    qrScanner.clear();
    reader.style.display = 'none';
    cekSiswa();
  });
}

function onScanSuccess(text){
  document.getElementById('nis').value = text;

  qrScanner.clear().then(()=>{
    document.getElementById('reader').style.display = 'none';
  });

  cekSiswa();
}
function onScanError(error) {
  // dibiarkan kosong agar tidak spam
}

// === CEK SISWA ===
function cekSiswa(){
  resetHasilTransaksi(); // ⬅️ TAMBAH INI
  const nis = document.getElementById('nis').value;
  if (!nis) return alert('Masukkan NIS');

  fetch('get_siswa.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'nis='+nis
  })
  .then(r=>r.json())
  .then(r=>{
    const box = document.getElementById('previewSiswa');
    box.classList.remove('d-none','alert-danger');
    box.classList.add('alert-info');

    if(r.status==='ok'){
      siswaValid = true;
      namaSiswa = r.nama;

      document.getElementById('nominal').disabled=false;
      document.getElementById('btnProses').disabled=false;

      box.innerHTML = `
        <b>NIS:</b> ${r.nis}<br>
        <b>Nama:</b> ${r.nama}<br>
        <b>Saldo:</b> Rp ${r.saldo.toLocaleString()}
      `;
    } else {
      siswaValid=false;
      box.classList.replace('alert-info','alert-danger');
      box.innerHTML = r.msg;
    }
  });
}

// === SUBMIT FORM ===
document.getElementById('formTransaksi').addEventListener('submit', e=>{
  e.preventDefault();
  if(!siswaValid) return alert('Cek siswa dulu');

  document.getElementById('k_nis').innerText =
    document.getElementById('nis').value;
  document.getElementById('k_nama').innerText = namaSiswa;
  document.getElementById('k_total').innerText =
    Number(document.getElementById('nominal').value).toLocaleString();

  modalKonfirmasi.show();
});

// === KONFIRMASI ===
document.getElementById('btnKonfirmasi').addEventListener('click', ()=>{
  modalKonfirmasi.hide();

  const data = new FormData(document.getElementById('formTransaksi'));

  fetch('transaksi_belanja.php',{method:'POST',body:data})
  .then(r=>r.json())
  .then(r=>{
    if(r.status==='ok'){
      document.getElementById('hasil').innerHTML = `
        <div class="alert alert-success">
          ✅ Transaksi berhasil<br>
          NIS: ${r.nis}<br>
          Nama: ${r.nama}<br>
          Total: Rp ${r.total.toLocaleString()}<br>
          Sisa Saldo: Rp ${r.saldo.toLocaleString()}
        </div>
      `;
      document.getElementById('formTransaksi').reset();
      document.getElementById('previewSiswa').classList.add('d-none');
      document.getElementById('nominal').disabled=true;
      document.getElementById('btnProses').disabled=true;
      siswaValid=false;
    } else alert(r.msg);
  });
});

document.getElementById('nis').addEventListener('input', () => {
  resetHasilTransaksi();
});

function resetHasilTransaksi() {
  document.getElementById('hasil').innerHTML = '';
}
</script>

</body>
</html>
