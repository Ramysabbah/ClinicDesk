<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
?>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card card-primary">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-user-plus mr-2"></i>Create New User</h3></div>
  <div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=users&action=store" enctype="multipart/form-data">
      <?= CSRF::field() ?>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="<?= e($_POST['name']??'') ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="<?= e($_POST['email']??'') ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Role <span class="text-danger">*</span></label>
            <select name="role" id="roleSelect" class="form-control" onchange="toggleDoctorFields()">
              <?php foreach (['patient','doctor','admin'] as $r): ?>
              <option value="<?= $r ?>" <?= ($_POST['role']??'patient')===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required minlength="6">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= e($_POST['phone']??'') ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Avatar <small class="text-muted">(JPG/PNG max 1MB)</small></label>
            <div class="custom-file">
              <input type="file" name="avatar" class="custom-file-input" accept="image/*" id="avatarFile">
              <label class="custom-file-label" for="avatarFile">Choose file</label>
            </div>
          </div>
        </div>
      </div>

      <!-- Doctor Fields -->
      <div id="doctorFields" style="display:none;">
        <hr><h6 class="text-primary text-uppercase mb-3" style="font-size:11px;letter-spacing:.6px;">Doctor Information</h6>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Specialization</label>
              <select name="specialization_id" class="form-control">
                <?php foreach ($specs as $s): ?>
                <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Consultation Fee (ILS)</label>
              <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text">₪</span></div>
                <input type="number" name="consultation_fee" class="form-control" min="0" step="0.01" value="0">
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label>Bio</label>
              <textarea name="bio" class="form-control" rows="3"></textarea>
            </div>
          </div>
          <div class="col-12">
            <div class="form-group">
              <label>Available Days</label><br>
              <?php $defaultDays=['Sun','Mon','Tue','Wed','Thu'];
              foreach ($days as $d): ?>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="available_days[]"
                       id="day_<?= $d ?>" value="<?= $d ?>" <?= in_array($d,$defaultDays)?'checked':'' ?>>
                <label class="form-check-label" for="day_<?= $d ?>"><?= $d ?></label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="form-group mt-3">
        <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Create User</button>
      </div>
    </form>
  </div>
</div>
</div>
</div>
<?php
$extraJs = <<<'JS'
<script>
function toggleDoctorFields() {
  var role = document.getElementById('roleSelect').value;
  document.getElementById('doctorFields').style.display = role === 'doctor' ? 'block' : 'none';
}
toggleDoctorFields();
// Custom file input label
document.getElementById('avatarFile').addEventListener('change', function(){
  var fname = this.files[0] ? this.files[0].name : 'Choose file';
  this.nextElementSibling.textContent = fname;
});
</script>
JS;
require_once __DIR__ . '/../../views/partials/footer.php';
?>
