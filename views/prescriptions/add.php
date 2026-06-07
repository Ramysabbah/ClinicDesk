<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="card card-success">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-prescription mr-2"></i>Add Prescription — <?= e($appt['patient_name']) ?>
      <span class="badge badge-secondary ml-2"><?= formatDate($appt['appt_date']) ?></span>
    </h3>
  </div>
  <div class="card-body">
    <div class="callout callout-info mb-4">
      <b>Patient:</b> <?= e($appt['patient_name']) ?> |
      <b>Date:</b> <?= formatDate($appt['appt_date']) ?> at <?= formatTime($appt['appt_time']) ?>
      <?php if($appt['reason']): ?> | <b>Reason:</b> <?= e($appt['reason']) ?><?php endif; ?>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/index.php?page=prescriptions&action=store" enctype="multipart/form-data">
      <?= CSRF::field() ?>
      <input type="hidden" name="appt_id" value="<?= $appt['id'] ?>">

      <div class="form-group">
        <label>Diagnosis <span class="text-danger">*</span></label>
        <textarea name="diagnosis" class="form-control" rows="3" required placeholder="Patient diagnosis…"><?= e($_POST['diagnosis']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label>Medications <span class="text-danger">*</span></label>
        <textarea name="medications" class="form-control" rows="5" required
                  placeholder="Drug name — Dose — Frequency — Duration&#10;e.g. Amoxicillin 500mg — 1 tab — 3x/day — 7 days"><?= e($_POST['medications']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label>Additional Notes</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Follow-up, dietary advice…"><?= e($_POST['notes']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label>Attach Prescription PDF <small class="text-muted">(optional, max 3MB)</small></label>
        <div class="custom-file">
          <input type="file" name="prescription_file" class="custom-file-input" accept="application/pdf" id="pdfFile">
          <label class="custom-file-label" for="pdfFile">Choose PDF file</label>
        </div>
      </div>
      <div class="form-group">
        <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $appt['id'] ?>" class="btn btn-secondary mr-2">Cancel</a>
        <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Save Prescription</button>
      </div>
    </form>
  </div>
</div>
</div>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
