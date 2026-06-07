<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card card-warning">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-key mr-2"></i>
          <?= (int)($_SESSION['user']['first_login'] ?? 0) === 1
              ? 'You must change your password before continuing'
              : 'Change Password' ?>
        </h3>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=auth&action=change_password">
          <?= CSRF::field() ?>

          <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" id="newPwd" class="form-control"
                   required minlength="8" placeholder="Min 8 chars, uppercase + number">
            <small class="form-text text-muted">At least 8 characters, one uppercase, one lowercase, one number.</small>
          </div>
          <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" id="confPwd" class="form-control" required>
            <small id="matchMsg"></small>
          </div>

          <button type="submit" class="btn btn-warning btn-block">
            <i class="fas fa-save mr-1"></i> Update Password
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php
$extraJs = <<<'JS'
<script>
var np = document.getElementById('newPwd');
var cp = document.getElementById('confPwd');
var msg = document.getElementById('matchMsg');
function checkMatch() {
  if (!cp.value) { msg.textContent = ''; return; }
  if (np.value === cp.value) {
    msg.textContent = '✔ Passwords match';
    msg.style.color = '#28a745';
  } else {
    msg.textContent = '✘ Passwords do not match';
    msg.style.color = '#dc3545';
  }
}
np.addEventListener('input', checkMatch);
cp.addEventListener('input', checkMatch);
</script>
JS;
require_once __DIR__ . '/../../views/partials/footer.php';
?>
