<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../auth/login.php");
  exit;
}
include "../config/database.php";

/* AMBIL DATA KELAS */
$kelasList = mysqli_query($conn, "SELECT * FROM kelas ORDER BY kelas ASC");

/* FILTER KELAS */
$kelasDipilih = $_GET['kelas'] ?? '';

$dataSiswa = null;
if ($kelasDipilih != '') {
  $stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM siswa WHERE kelas = ? ORDER BY nis ASC"
  );
  mysqli_stmt_bind_param($stmt, "s", $kelasDipilih);
  mysqli_stmt_execute($stmt);
  $dataSiswa = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Siswa | SMKBApay</title>
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
    <a href="siswa.php" class="bg-dark">👨‍🎓 Data Siswa</a>
    <a href="topup.php">💳 Top Up Saldo</a>
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
      <span class="ms-3">Data Siswa</span>
    </nav>
    <div class="content">

	<?php
	if (isset($_GET['msg'])) {
	  switch ($_GET['msg']) {
	    case 'update_success':
	      echo '<div class="alert alert-success">✅ Data siswa berhasil diperbarui</div>';
	      break;
	    case 'update_error':
	      echo '<div class="alert alert-danger">❌ Gagal memperbarui data siswa</div>';
	      break;
	    case 'insert_success':
	      echo '<div class="alert alert-success">✅ Data siswa berhasil ditambahkan</div>';
	      break;
	    case 'nis_exist':
	      echo '<div class="alert alert-warning">⚠️ NIS sudah digunakan oleh siswa lain</div>';
	      break;
	    case 'import_ok':
	      echo '<div class="alert alert-info">
	              📥 Import CSV selesai<br>
	              ✅ Berhasil: '.($_GET['ok'] ?? 0).' data<br>
	              ⚠️ Duplikat: '.($_GET['dup'] ?? 0).' data
	            </div>';
	      break;
	    case 'import_error':
	      echo '<div class="alert alert-danger">❌ File CSV tidak bisa diproses</div>';
	      break;
	  }
	}
	?>
      <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
  ➕ Tambah Siswa
		</button>

		<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalImport">
		  📥 Import CSV
		</button>

    <?php if (!empty($kelasDipilih)) { ?>
      <a href="kartu_siswa.php?kelas=<?= urlencode($kelasDipilih); ?>"
         class="btn btn-warning mb-3"
         target="_blank">
        🖨 Cetak QR
      </a>
    <?php } ?>

    </div>
    <form method="GET" class="mb-3">
	  <div class="row g-2">
	    <div class="col-md-4">
	      <select name="kelas" class="form-select" required onchange="this.form.submit()">
	        <option value="">-- Pilih Kelas --</option>
	        <?php while($k = mysqli_fetch_assoc($kelasList)) { ?>
	          <option value="<?= $k['kelas']; ?>"
	            <?= ($kelasDipilih == $k['kelas']) ? 'selected' : ''; ?>>
	            <?= $k['kelas']; ?>
	          </option>
	        <?php } ?>
	      </select>
	    </div>
	  </div>
	</form>
	<?php if ($kelasDipilih != '' && $dataSiswa) { ?>

<div class="table-responsive">
  <table class="table table-bordered table-striped">
    <thead class="table-primary">
      <tr>
        <th>NIS</th>
        <th>Nama</th>
        <th>Kelas</th>
        <th>Saldo</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>

    <?php if (mysqli_num_rows($dataSiswa) == 0) { ?>
      <tr>
        <td colspan="5" class="text-center">Data siswa kosong</td>
      </tr>
    <?php } else { ?>

      <?php while ($row = mysqli_fetch_assoc($dataSiswa)) { ?>
      <tr>
        <td><?= $row['nis']; ?></td>
        <td><?= $row['nama_siswa']; ?></td>
        <td><?= $row['kelas']; ?></td>
        <td>Rp <?= number_format($row['saldo'],0,',','.'); ?></td>
        <td>
          <button class="btn btn-warning btn-sm btnEdit"
            data-id="<?= $row['id']; ?>"
            data-nis="<?= $row['nis']; ?>"
            data-nama="<?= $row['nama_siswa']; ?>"
            data-kelas="<?= $row['kelas']; ?>"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit">
            Edit
          </button>

          <a href="siswa_hapus.php?id=<?= $row['id']; ?>"
             onclick="return confirm('Hapus siswa ini?')"
             class="btn btn-danger btn-sm">
             Hapus
          </a>
        </td>
      </tr>
      <?php } ?>  <!-- END WHILE -->

    <?php } ?>  <!-- END IF ROWS -->

    </tbody>
  </table>
</div>

<?php } ?> <!-- END IF KELAS -->

  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah">
  <div class="modal-dialog">
    <form method="POST" action="siswa_simpan.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label>NIS</label>
          <input type="text" name="nis" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Nama Siswa</label>
          <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Kelas</label>
          <input type="text" name="kelas" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL IMPORT EXCEL -->
<div class="modal fade" id="modalImport">
  <div class="modal-dialog">
    <form method="POST" action="siswa_import_csv.php" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Data Siswa (Excel)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <input type="file" name="file_csv" class="form-control" accept=".csv" required>
        </div>
        <small class="text-muted">
          Format: nis | nama_siswa | kelas
        </small>
      </div>

      <div class="modal-footer">
        <button class="btn btn-success">Import</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT (SATU SAJA) -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="siswa_update.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Data Siswa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">

        <div class="mb-2">
          <label>NIS</label>
          <input type="text" name="nis" id="edit_nis" class="form-control" required>
        </div>

        <div class="mb-2">
          <label>Nama Siswa</label>
          <input type="text" name="nama" id="edit_nama" class="form-control" required>
        </div>

        <div class="mb-2">
          <label>Kelas</label>
          <input type="text" name="kelas" id="edit_kelas" class="form-control" required>
        </div>
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
document.querySelectorAll('.btnEdit').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('edit_id').value    = this.dataset.id;
    document.getElementById('edit_nis').value   = this.dataset.nis;
    document.getElementById('edit_nama').value  = this.dataset.nama;
    document.getElementById('edit_kelas').value = this.dataset.kelas;
  });
});
</script>

</body>
</html>