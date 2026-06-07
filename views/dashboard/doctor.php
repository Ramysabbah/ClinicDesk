<?php
/**
 * @var array $doctor - Doctor information
 * @var array[] $todayAppts - Today's appointments
 * @var array $stats - Statistics data
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$greeting = date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening');
?>
<div class="callout callout-info mb-4">
  <h5>Good <?= $greeting ?>, Dr. <?= e($doctor['name']) ?>!</h5>
  <p class="mb-0"><?= e($doctor['specialization_name']) ?></p>
</div>

<div class="row">
  <div class="col-lg-3 col-6">
    <div class="small-box bg-info">
      <div class="inner"><h3><?= $stats['this_month'] ?? 0 ?></h3><p>This Month</p></div>
      <div class="icon"><i class="fas fa-calendar-alt"></i></div>
      <a href="#" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-warning">
      <div class="inner"><h3><?= $stats['pending'] ?? 0 ?></h3><p>Pending</p></div>
      <div class="icon"><i class="fas fa-hourglass-half"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule&status=pending" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-success">
      <div class="inner"><h3><?= $stats['completed'] ?? 0 ?></h3><p>Completed</p></div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule&status=completed" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-primary">
      <div class="inner"><h3><?= count($todayAppts) ?></h3><p>Today</p></div>
      <div class="icon"><i class="fas fa-calendar-day"></i></div>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-calendar-day mr-2"></i>
          Today — <?= date('D, d M Y') ?>
          <span class="badge badge-primary ml-2"><?= count($todayAppts) ?></span>
        </h3>
        <div class="card-tools">
          <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="btn btn-sm btn-primary">
            Full Schedule
          </a>
        </div>
      </div>
      <div class="card-body p-0">
        <?php if (empty($todayAppts)): ?>
        <div class="text-center text-muted py-4">
          <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>No appointments today.
        </div>
        <?php else: ?>
        <table class="table table-hover mb-0">
          <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($todayAppts as $a): ?>
            <tr>
              <td><strong><?= formatTime($a['appt_time']) ?></strong></td>
              <td><?= e($a['patient_name']) ?></td>
              <td><?= e(truncate($a['reason'] ?? '—', 40)) ?></td>
              <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
              <td>
                <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $a['id'] ?>"
                   class="btn btn-xs btn-primary">Manage</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card card-info card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Upcoming</h3>
      </div>
      <div class="card-body p-0">
        <?php if (empty($upcoming)): ?>
        <div class="text-center text-muted py-3">No upcoming appointments.</div>
        <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($upcoming as $a): ?>
          <li class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong><?= e($a['patient_name']) ?></strong>
                <div class="text-muted small"><?= formatDate($a['appt_date']) ?> · <?= formatTime($a['appt_time']) ?></div>
              </div>
              <span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
