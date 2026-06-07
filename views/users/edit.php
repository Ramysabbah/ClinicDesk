<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

$selDays = $doctor ? array_map('trim', explode(',', $doctor['available_days'])) : [];
?>
<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-user-edit mr-2"></i>Edit User — <?= e($editUser ['name']) ?>
      <span class="badge badge-<?= $editUser ['role']==='admin'?'danger':($editUser ['role']==='doctor'?'info':'secondary') ?> ml-2">
        <?= ucfirst($editUser ['role']) ?>
      </span>
    </h3>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=update" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      <input type="hidden" name="user_id" value="<?= $editUser ['id'] ?>">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= e($editUser ['name']) ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= e($editUser ['email']) ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= e($editUser ['phone']??'') ?>">
          </div>
        </div>
        <!-- <div class="col-md-6">
          <div class="form-group">
            <label>Avatar <small class="text-muted">(leave blank to keep current)</small></label>
            <div class="custom-file">
              <input type="file" name="avatar" class="custom-file-input" accept="image/*" id="avatarFile">
              <label class="custom-file-label" for="avatarFile">Choose file</label>
            </div>
            <?php if ($editUser ['avatar']): ?>
            <div class="mt-2">
              <img src="<?= e(avatarUrl($editUser ['avatar'])) ?>" class="img-circle" style="width:40px;height:40px;object-fit:cover;" alt="">
              <small class="text-muted ml-1">Current avatar</small>
            </div>
            <?php endif; ?>
          </div>
        </div> -->
      </div>

      <?php if ($doctor): ?>
      <hr>
      <h6 class="text-primary text-uppercase mb-3" style="font-size:11px;letter-spacing:.6px;">Doctor Information</h6>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Specialization</label>
            <select name="specialization_id" class="form-control">
              <?php foreach ($specs as $s): ?>
              <option value="<?= $s['id'] ?>" <?= $s['id']==$doctor['specialization_id']?'selected':'' ?>><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Consultation Fee (ILS)</label>
            <div class="input-group">
              <div class="input-group-prepend"><span class="input-group-text">₪</span></div>
              <input type="number" name="consultation_fee" class="form-control" min="0" step="0.01" value="<?= e($doctor['consultation_fee']) ?>">
            </div>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?= e($doctor['bio']??'') ?></textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Available Days</label><br>
            <?php foreach ($days as $d): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="available_days[]"
                     id="d_<?= $d ?>" value="<?= $d ?>" <?= in_array($d,$selDays)?'checked':'' ?>>
              <label class="form-check-label" for="d_<?= $d ?>"><?= $d ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="form-group">
        <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password -->
<div class="card card-warning">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-key mr-2"></i>Reset Password</h3></div>
  <div class="card-body">
    <p class="text-muted">The user will be required to change their password on next login.</p>
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=reset_password">
      <?= CSRF::field() ?>
      <input type="hidden" name="user_id" value="<?= $editUser ['id'] ?>">
      <div class="row align-items-end">
        <div class="col-md-8">
          <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('Reset this user\'s password?')">
              <i class="fas fa-key mr-1"></i>Reset
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

</div>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
