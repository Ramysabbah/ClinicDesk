<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <b><?= APP_NAME ?></b>
      <p class="text-muted small mb-0">Clinic Management System</p>
    </div>

    <div class="card">
      <div class="card-body login-card-body">

        <?php if (!empty($_SESSION['flash'])): ?>
          <div class="alert alert-<?= e($_SESSION['flash']['type']) ?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <?= e($_SESSION['flash']['message']) ?>
          </div>
        <?php unset($_SESSION['flash']);
        endif; ?>

        <p class="login-box-msg">Sign in to start your session</p>

        <form method="POST" action="<?= BASE_URL ?>/index.php?page=auth&action=login">
          <?= CSRF::field() ?>

          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control"
              placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
          </div>

          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control"
              placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text"><span class="fas fa-lock"></span></div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt mr-1"></i> Sign In
              </button>
            </div>
          </div>
        </form>

        <p class="mt-3 mb-1 text-center text-muted small">
          Access is restricted to authorized users only.
        </p>
      </div>
    </div>
  </div>

  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="<?= BASE_URL ?>/public/assets/adminlte/dist/js/adminlte.min.js"></script>
</body>

</html>