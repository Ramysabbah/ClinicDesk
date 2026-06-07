<?php
$user    = Auth::currentUser();
$role    = Auth::role();
$curPage = $_GET['page']   ?? 'dashboard';
$curAct  = $_GET['action'] ?? 'index';

function isActive(string $page, string $action = ''): string {
    $p = $_GET['page']   ?? 'dashboard';
    $a = $_GET['action'] ?? 'index';
    if ($action) return ($p === $page && $a === $action) ? 'active' : '';
    return ($p === $page) ? 'active' : '';
}
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand -->
  <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="brand-link">
    <span class="brand-image img-circle elevation-3"
          style="background:#007bff;display:inline-flex;align-items:center;justify-content:center;width:33px;height:33px;">
      <i class="fas fa-clinic-medical text-white" style="font-size:16px;"></i>
    </span>
    <span class="brand-text font-weight-bold"><?= APP_NAME ?></span>
  </a>

  <div class="sidebar">
    <!-- User Panel -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="<?= e(avatarUrl($user['avatar'] ?? null)) ?>"
             class="img-circle elevation-2"
             style="width:34px;height:34px;object-fit:cover;" alt="User">
      </div>
      <div class="info">
        <a href="#" class="d-block"><?= e($user['name']) ?></a>
        <small class="text-muted text-capitalize"><?= e($role) ?></small>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column nav-compact" data-widget="treeview"
          role="menu" data-accordion="false">

        <!-- Dashboard -->
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=dashboard"
             class="nav-link <?= isActive('dashboard') ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <?php if ($role === 'admin'): ?>
        <!-- ADMIN MENU -->
        <li class="nav-header">MANAGEMENT</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=users"
             class="nav-link <?= isActive('users') ?>">
            <i class="nav-icon fas fa-users"></i>
            <p>Users</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=doctors"
             class="nav-link <?= isActive('doctors') ?>">
            <i class="nav-icon fas fa-user-md"></i>
            <p>Doctors</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=list"
             class="nav-link <?= isActive('appointments','list') ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>Appointments</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=reports"
             class="nav-link <?= isActive('reports') ?>">
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Reports</p>
          </a>
        </li>

        <?php elseif ($role === 'doctor'): ?>
        <!-- DOCTOR MENU -->
        <li class="nav-header">CLINIC</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule"
             class="nav-link <?= isActive('appointments','schedule') ?>">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>My Schedule</p>
          </a>
        </li>

        <!-- <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=my"
             class="nav-link <?= isActive('prescriptions','my') ?>">
            <i class="nav-icon fas fa-prescription"></i>
            <p>Prescriptions</p>
          </a>
        </li> -->

        <?php
        require_once __DIR__ . '/../../models/DoctorModel.php';
        $myDoc = (new DoctorModel())->findByUserId(Auth::id());
        if ($myDoc):
        ?>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=doctors&action=edit&id=<?= $myDoc['id'] ?>"
             class="nav-link <?= isActive('doctors','edit') ?>">
            <i class="nav-icon fas fa-id-card"></i>
            <p>My Profile</p>
          </a>
        </li>
        <?php endif; ?>

        <?php else: ?>
        <!-- PATIENT MENU -->
        <li class="nav-header">MY HEALTH</li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book"
             class="nav-link <?= isActive('appointments','book') ?>">
            <i class="nav-icon fas fa-calendar-plus"></i>
            <p>Book Appointment</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=my"
             class="nav-link <?= isActive('appointments','my') ?>">
            <i class="nav-icon fas fa-calendar-alt"></i>
            <p>My Appointments</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=my"
             class="nav-link <?= isActive('prescriptions','my') ?>">
            <i class="nav-icon fas fa-file-medical"></i>
            <p>My Prescriptions</p>
          </a>
        </li>
        <?php endif; ?>

        <!-- ACCOUNT -->
        <li class="nav-header">ACCOUNT</li>
        <li class="nav-item">
          <a href="<?= BASE_URL ?>/index.php?page=auth&action=change_password"
             class="nav-link <?= isActive('auth','change_password') ?>">
            <i class="nav-icon fas fa-key"></i>
            <p>Change Password</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>

<!-- Content Wrapper -->
<div class="content-wrapper">
