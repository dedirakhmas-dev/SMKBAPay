<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

$q = mysqli_query($conn, "SELECT * FROM merchant ORDER BY status, nama_merchant");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Merchant | SMKBApay</title>
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
    <a href="merchant.php" class="bg-dark">🏪 Merchant</a>
    <a href="keuangan_merchant.php">💰 Keuangan Merchant</a>
    <a href="laporan.php">📊 Laporan</a>
    <a href="../auth/logout.php">🚪 Logout</a>
  </div>

  <div class="flex-fill">
    <nav class="navbar navbar-light bg-white shadow-sm">
      <button class="btn btn-primary d-md-none" id="toggleSidebar">☰</button>
      <span class="ms-3">Merchant</span>
    </nav>

    <div class="content">

      <?php if (isset($_GET['msg'])) { ?>
        <?php if ($_GET['msg']=='add_ok') { ?>
          <div class="alert alert-success">✅ Merchant berhasil ditambahkan</div>
        <?php } elseif ($_GET['msg']=='edit_ok') { ?>
          <div class="alert alert-success">✏️ Merchant berhasil diperbarui</div>
        <?php } elseif ($_GET['msg']=='delete_ok') { ?>
          <div class="alert alert-warning">🗑️ Merchant dihapus</div>
        <?php } ?>
      <?php } ?>

      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
        ➕ Tambah Merchant
      </button>

      <div class="table-responsive">
        <table class="table table-bordered table-striped">
          <thead class="table-primary">
            <tr>
              <th>No</th>
              <th>Nama Merchant</th>
              <th>Pemilik</th>
              <th>No HP</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no=1; while($m=mysqli_fetch_assoc($q)) { ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= $m['nama_merchant']; ?></td>
              <td><?= $m['pemilik']; ?></td>
              <td><?= $m['no_hp']; ?></td>
              <td>
                <span class="badge <?= $m['status']=='AKTIF'?'bg-success':'bg-secondary'; ?>">
                  <?= $m['status']; ?>
                </span>
              </td>
              <td>
                <button
                  class="btn btn-warning btn-sm btn-edit"
                  data-id="<?= $m['id']; ?>"
                  data-nama="<?= $m['nama_merchant']; ?>"
                  data-pemilik="<?= $m['pemilik']; ?>"
                  data-hp="<?= $m['no_hp']; ?>"
                  data-status="<?= $m['status']; ?>"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEdit">
                  Edit
                </button>

                <a href="merchant_hapus.php?id=<?= $m['id']; ?>"
                   onclick="return confirm('Hapus merchant ini?')"
                   class="btn btn-danger btn-sm">
                  Hapus
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="merchant_simpan.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Merchant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input name="nama" class="form-control mb-2" placeholder="Nama Merchant" required>
        <input name="pemilik" class="form-control mb-2" placeholder="Pemilik" required>
        <input name="hp" class="form-control mb-2" placeholder="No HP">
      </div>

      <div class="modal-footer">
        <button class="btn btn-success">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL EDIT (SATU SAJA) ================= -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="merchant_update.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Merchant</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">

        <label>Nama Merchant</label>
        <input type="text" name="nama" id="edit_nama" class="form-control mb-2" required>

        <label>Pemilik</label>
        <input type="text" name="pemilik" id="edit_pemilik" class="form-control mb-2" required>

        <label>No HP</label>
        <input type="text" name="hp" id="edit_hp" class="form-control mb-2">

        <label>Status</label>
        <select name="status" id="edit_status" class="form-select">
          <option value="AKTIF">AKTIF</option>
          <option value="NONAKTIF">NONAKTIF</option>
        </select>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/dashboard.js"></script>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('edit_id').value = this.dataset.id;
    document.getElementById('edit_nama').value = this.dataset.nama;
    document.getElementById('edit_pemilik').value = this.dataset.pemilik;
    document.getElementById('edit_hp').value = this.dataset.hp;
    document.getElementById('edit_status').value = this.dataset.status;
  });
});
</script>

</body>
</html>
