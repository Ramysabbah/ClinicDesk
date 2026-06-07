<?php $user = Auth::currentUser(); ?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <!-- Left navbar links -->
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button">
        <i class="fas fa-bars"></i>
      </a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="nav-link">
        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
      </a>
    </li>
  </ul>

  <!-- Right navbar links -->
  <ul class="navbar-nav ml-auto">
    <li class="nav-item">
      <span class="nav-link text-muted">
        <i class="far fa-calendar-alt mr-1"></i><?= date('D, d M Y') ?>
      </span>
    </li>
    <!-- User dropdown -->
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#">
        <img src="<?= e(avatarUrl($user['avatar'] ?? null)) ?>"
             class="img-circle elevation-2"
             style="width:30px;height:30px;object-fit:cover;"
             alt="avatar">
        <span class="ml-1"><?= e($user['name']) ?></span>
        <i class="fas fa-caret-down ml-1"></i>
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <span class="dropdown-item-text text-muted small text-uppercase">
          <?= e($user['role']) ?>
        </span>
        <div class="dropdown-divider"></div>
        <a href="<?= BASE_URL ?>/index.php?page=auth&action=change_password" class="dropdown-item">
          <i class="fas fa-key mr-2"></i> Change Password
        </a>
        <div class="dropdown-divider"></div>
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=auth&action=logout">
          <?= CSRF::field() ?>
          <button type="submit" class="dropdown-item text-danger">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </button>
        </form>
      </div>
    </li>
  </ul>
</nav>
