<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Kasir | SMKBApay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh">

<div class="card p-4" style="width:320px">
  <h5 class="text-center mb-3">Login Kasir</h5>

  <?php if (isset($_GET['err'])) { ?>
    <div class="alert alert-danger">Login gagal</div>
  <?php } ?>

  <form method="POST" action="login_proses.php">
    <input name="username" class="form-control mb-2" placeholder="Username" required>
    <input name="password" type="password" class="form-control mb-3" placeholder="Password" required>
    <button class="btn btn-primary w-100">Login</button>
  </form>
</div>

</body>
</html>
