<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$allDays=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$selDays=array_map('trim',explode(',',$doctor['available_days']??''));
?>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card card-primary">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-user-md mr-2"></i>Edit Doctor Profile — Dr. <?= e($doctor['name']) ?></h3></div>
  <div class="card-body">
    <form method="POST" action="<?= BASE_URL ?>/index.php?page=doctors&action=update" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($doctor['name']) ?>" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= e($doctor['phone']??'') ?>">
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Profile Photo <small class="text-muted">(JPG/PNG max 1MB)</small></label>
            <div class="custom-file">
              <input type="file" name="photo" class="custom-file-input" accept="image/jpeg,image/png" id="photoFile">
              <label class="custom-file-label" for="photoFile">Choose photo</label>
            </div>
            <?php if($doctor['photo']): ?>
            <div class="mt-2">
              <img src="<?= e(doctorPhotoUrl($doctor['photo'])) ?>" class="img-circle" style="width:50px;height:50px;object-fit:cover;" alt="">
              <small class="text-muted ml-1">Current photo</small>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>Specialization</label>
            <select name="specialization_id" class="form-control" <?= Auth::role()==='doctor'?'disabled':'' ?>>
              <?php foreach($specs as $s): ?>
              <option value="<?= $s['id'] ?>" <?= $s['id']==$doctor['specialization_id']?'selected':'' ?>><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if(Auth::role()==='doctor'): ?>
            <input type="hidden" name="specialization_id" value="<?= $doctor['specialization_id'] ?>">
            <?php endif; ?>
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
            <textarea name="bio" class="form-control" rows="4"><?= e($doctor['bio']??'') ?></textarea>
          </div>
        </div>
        <div class="col-12">
          <div class="form-group">
            <label>Available Days</label><br>
            <?php foreach($allDays as $day): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="available_days[]"
                     id="dd_<?= $day ?>" value="<?= $day ?>" <?= in_array($day,$selDays)?'checked':'' ?>>
              <label class="form-check-label" for="dd_<?= $day ?>"><?= $day ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <a href="<?= BASE_URL ?>/index.php?page=<?= Auth::role()==='admin'?'doctors':'dashboard' ?>" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Changes</button>
      </div>
    </form>
  </div>
</div>
</div>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
