<?php
/**
 * @var int $rxCount - Count of prescriptions
 * @var array|null $nextAppt - Next appointment details
 * @var array $stats - Statistics data
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$user = Auth::currentUser();
?>
<div class="callout callout-success mb-4">
  <h5>Welcome back, <?= e($user['name']) ?>!</h5>
  <p class="mb-0">Here's your health summary.</p>
</div>

<div class="row">
  <div class="col-lg-4 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= $stats['active'] ?? 0 ?></h3><p>Active Appointments</p></div>
      <div class="icon"><i class="fas fa-calendar-alt"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=my" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3><?= $stats['completed'] ?? 0 ?></h3><p>Completed Visits</p></div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=my&status=completed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-4 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= $rxCount ?></h3><p>Prescriptions</p></div>
      <div class="icon"><i class="fas fa-file-medical"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=my" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book" class="small-box bg-primary d-block p-3 text-white text-decoration-none" style="border-radius:4px;">
      <div class="d-flex align-items-center gap-3">
        <i class="fas fa-calendar-plus fa-2x mr-3"></i>
        <div><strong>Book New Appointment</strong><div class="small">Find a doctor and schedule your visit</div></div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="<?= BASE_URL ?>/index.php?page=prescriptions&action=my" class="small-box bg-warning d-block p-3 text-white text-decoration-none" style="border-radius:4px;">
      <div class="d-flex align-items-center">
        <i class="fas fa-file-prescription fa-2x mr-3"></i>
        <div><strong>View My Prescriptions</strong><div class="small">Access your medical prescriptions</div></div>
      </div>
    </a>
  </div>
</div>

<?php if ($nextAppt): ?>
<div class="callout callout-info">
  <h5><i class="fas fa-calendar-check mr-2"></i>Next Appointment</h5>
  Dr. <?= e($nextAppt['doctor_name']) ?> —
  <?= formatDate($nextAppt['appt_date']) ?> at <?= formatTime($nextAppt['appt_time']) ?>
  <span class="badge badge-<?= statusBadge($nextAppt['status']) ?> ml-2"><?= ucfirst($nextAppt['status']) ?></span>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list mr-2"></i>My Active Appointments</h3>
    <div class="card-tools">
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=my" class="btn btn-sm btn-primary">View All</a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0">
      <thead><tr><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($activeAppts)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No active appointments.</td></tr>
        <?php else: ?>
        <?php foreach ($activeAppts as $a): ?>
        <tr>
          <td>Dr. <?= e($a['doctor_name']) ?></td>
          <td><?= formatDate($a['appt_date']) ?></td>
          <td><?= formatTime($a['appt_time']) ?></td>
          <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
          <td><a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $a['id'] ?>" class="btn btn-xs btn-default"><i class="fas fa-eye"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
