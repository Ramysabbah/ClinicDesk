<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>
<?php if(empty($prescriptions)): ?>
<div class="card"><div class="card-body text-center py-5">
  <i class="fas fa-file-medical fa-3x text-muted mb-3 d-block"></i>
  <h5 class="text-muted">No prescriptions yet.</h5>
  <p class="text-muted">Prescriptions will appear here after completed appointments.</p>
  <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book" class="btn btn-primary">
    <i class="fas fa-calendar-plus mr-1"></i>Book an Appointment
  </a>
</div></div>
<?php else: ?>
<div class="row">
  <?php foreach($prescriptions as $rx): ?>
  <div class="col-md-6 col-xl-4 mb-4">
    <div class="card card-success card-outline h-100">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-md mr-1"></i>Dr. <?= e($rx['doctor_name']) ?></h3>
        <div class="card-tools"><span class="text-muted small"><?= formatDate($rx['appt_date']) ?></span></div>
      </div>
      <div class="card-body">
        <span class="badge badge-info mb-2"><?= e($rx['specialization']) ?></span>
        <p class="text-muted small mb-1">Diagnosis</p>
        <p class="small"><?= nl2br(e(truncate($rx['diagnosis'],120))) ?></p>
        <p class="text-muted small mb-1">Medications</p>
        <p class="small"><?= nl2br(e(truncate($rx['medications'],150))) ?></p>
        <?php if($rx['notes']): ?>
        <p class="text-muted small mb-1">Notes</p>
        <p class="small"><?= e(truncate($rx['notes'],100)) ?></p>
        <?php endif; ?>
      </div>
      <?php if($rx['file_path']): ?>
      <div class="card-footer">
        <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=download&appt_id=<?= $rx['appointment_id'] ?>"
           class="btn btn-success btn-sm btn-block">
          <i class="fas fa-download mr-1"></i>Download Prescription PDF
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
