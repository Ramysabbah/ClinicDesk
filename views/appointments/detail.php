<?php
/**
 * @var array $appt - The appointment data
 * @var array|null $prescription - The prescription data (if exists)
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$role = Auth::role();
?>
<div class="row">
  <div class="col-lg-7">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-file-medical mr-2"></i>Appointment #<?= $appt['id'] ?></h3>
        <div class="card-tools">
          <span class="badge badge-<?= statusBadge($appt['status']) ?> badge-lg" style="font-size:13px;">
            <?= ucfirst($appt['status']) ?>
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-sm-6">
            <p class="text-muted mb-1 small">Patient</p>
            <h6><?= e($appt['patient_name']) ?></h6>
            <p class="text-muted small"><?= e($appt['patient_email']) ?><?= $appt['patient_phone']?' · '.e($appt['patient_phone']):'' ?></p>
          </div>
          <div class="col-sm-6">
            <p class="text-muted mb-1 small">Doctor</p>
            <h6>Dr. <?= e($appt['doctor_name']) ?></h6>
            <p class="text-muted small"><?= e($appt['specialization']) ?></p>
          </div>
          <div class="col-sm-4">
            <p class="text-muted mb-1 small">Date</p>
            <strong><?= formatDate($appt['appt_date']) ?></strong>
          </div>
          <div class="col-sm-4">
            <p class="text-muted mb-1 small">Time</p>
            <strong><?= formatTime($appt['appt_time']) ?></strong>
          </div>
          <div class="col-sm-4">
            <p class="text-muted mb-1 small">Fee</p>
            <strong>₪<?= number_format($appt['consultation_fee'],2) ?></strong>
          </div>
          <div class="col-12 mt-2">
            <p class="text-muted mb-1 small">Reason for Visit</p>
            <p><?= e($appt['reason']??'—') ?></p>
          </div>
          <?php if($appt['doctor_notes']): ?>
          <div class="col-12">
            <p class="text-muted mb-1 small">Doctor's Notes</p>
            <div class="callout callout-info"><?= nl2br(e($appt['doctor_notes'])) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Prescription card -->
    <?php if($prescription): ?>
    <div class="card card-success card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-prescription mr-2"></i>Prescription</h3></div>
      <div class="card-body">
        <p class="text-muted small mb-1">Diagnosis</p>
        <p><?= nl2br(e($prescription['diagnosis'])) ?></p>
        <p class="text-muted small mb-1">Medications</p>
        <p><?= nl2br(e($prescription['medications'])) ?></p>
        <?php if($prescription['notes']): ?>
        <p class="text-muted small mb-1">Notes</p>
        <p><?= nl2br(e($prescription['notes'])) ?></p>
        <?php endif; ?>
        <?php if($prescription['file_path']): ?>
        <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=download&appt_id=<?= $appt['id'] ?>"
           class="btn btn-success btn-sm"><i class="fas fa-download mr-1"></i>Download PDF</a>
        <?php endif; ?>
      </div>
    </div>
    <?php elseif($role==='doctor' && $appt['status']==='completed'): ?>
    <div class="callout callout-warning">
      No prescription added yet.
      <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=add&appt_id=<?= $appt['id'] ?>"
         class="btn btn-warning btn-sm ml-2"><i class="fas fa-plus mr-1"></i>Add Prescription</a>
    </div>
    <?php endif; ?>

    <a href="javascript:history.back()" class="btn btn-secondary btn-sm mt-2">
      <i class="fas fa-arrow-left mr-1"></i>Back
    </a>
  </div>

  <!-- Status Update -->
  <?php if($role==='doctor' || $role==='admin'): ?>
  <div class="col-lg-5">
    <div class="card card-warning card-outline">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-edit mr-2"></i>Update Status</h3></div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=appointments&action=update_status">
          <?= CSRF::field() ?>
          <input type="hidden" name="appt_id" value="<?= $appt['id'] ?>">
          <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
              <?php foreach(['pending','confirmed','completed','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $appt['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Doctor's Notes</label>
            <textarea name="notes" class="form-control" rows="4"
                      placeholder="Diagnosis, follow-up…"><?= e($appt['doctor_notes']??'') ?></textarea>
          </div>
          <button type="submit" class="btn btn-warning btn-block">
            <i class="fas fa-save mr-1"></i>Update Appointment
          </button>
        </form>
        <?php if($role==='doctor' && $appt['status']==='completed' && !$prescription): ?>
        <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=add&appt_id=<?= $appt['id'] ?>"
           class="btn btn-success btn-block mt-2">
          <i class="fas fa-plus mr-1"></i>Add Prescription
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
