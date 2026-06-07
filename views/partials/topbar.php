<?php // views/partials/topbar.php — AdminLTE content header ?>
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><?= e($pageTitle ?? APP_NAME) ?></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>/index.php?page=dashboard">
              <i class="fas fa-home"></i>
            </a>
          </li>
          <li class="breadcrumb-item active"><?= e($pageTitle ?? '') ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>
<div class="content">
  <div class="container-fluid">
