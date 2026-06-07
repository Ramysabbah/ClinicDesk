<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>404 Not Found</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/adminlte/plugins/fontawesome-free/css/all.min.css">
</head>

<body class="hold-transition" style="background:#ecf0f5;">
  <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="text-center">
      <h1 style="font-size:120px;font-weight:700;color:#007bff;">404</h1>
      <h4>Page Not Found</h4>
      <p class="text-muted">The page you're looking for doesn't exist.</p>
      <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>/index.php?page=dashboard" class="btn btn-primary">
        <i class="fas fa-home mr-1"></i>Back to Dashboard
      </a>
    </div>
  </div>
</body>

</html>