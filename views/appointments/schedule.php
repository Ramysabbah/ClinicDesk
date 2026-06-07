<?php
/**
 * @var array $data - Contains 'rows' and 'pager' for pagination
 * @var array[] $todayAppts - Today's appointments
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$rows=$data['rows']; $pager=$data['pager'];
$statusFilter=$_GET['status']??''; $dateFrom=$_GET['date_from']??''; $dateTo=$_GET['date_to']??'';
?>
<?php if(!empty($todayAppts)): ?>
<div class="card card-primary card-outline mb-4">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-calendar-day mr-2"></i>Today — <?= date('D, d M Y') ?> <span class="badge badge-primary ml-2"><?= count($todayAppts) ?></span></h3>
    <div class="card-tools"><a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="btn btn-sm btn-primary">Full Schedule</a></div>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead><tr><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach($todayAppts as $a): ?>
        <tr>
          <td><strong><?= formatTime($a['appt_time']) ?></strong></td>
          <td><?= e($a['patient_name']) ?></td>
          <td><?= e(truncate($a['reason']??'—',50)) ?></td>
          <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
          <td><a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $a['id'] ?>" class="btn btn-xs btn-primary">Manage</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="card">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Full Schedule</h3></div>
  <div class="card-body border-bottom">
    <form method="GET" class="form-inline flex-wrap gap-2">
      <input type="hidden" name="page" value="appointments">
      <input type="hidden" name="action" value="schedule">
      <select name="status" class="form-control form-control-sm mr-2 mb-2">
        <option value="">All Status</option>
        <?php foreach(['pending','confirmed','completed','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="date" name="date_from" class="form-control form-control-sm mr-2 mb-2" value="<?= e($dateFrom) ?>" placeholder="From">
      <input type="date" name="date_to" class="form-control form-control-sm mr-2 mb-2" value="<?= e($dateTo) ?>" placeholder="To">
      <button class="btn btn-primary btn-sm mr-2 mb-2"><i class="fas fa-search mr-1"></i>Filter</button>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=schedule" class="btn btn-secondary btn-sm mb-2">Clear</a>
    </form>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0">
      <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($rows)): ?><tr><td colspan="6" class="text-center text-muted py-3">No appointments found.</td></tr><?php endif; ?>
        <?php foreach($rows as $a): ?>
        <tr>
          <td><?= formatDate($a['appt_date']) ?></td>
          <td><?= formatTime($a['appt_time']) ?></td>
          <td><?= e($a['patient_name']) ?></td>
          <td><?= e(truncate($a['reason']??'—',50)) ?></td>
          <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
          <td><a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $a['id'] ?>" class="btn btn-xs btn-primary">Manage</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if($pager->totalPages()>1): ?>
  <div class="card-footer clearfix">
    <small class="float-left text-muted mt-2">Showing <?= $pager->firstItem() ?>–<?= $pager->lastItem() ?> of <?= $pager->totalItems() ?></small>
    <ul class="pagination pagination-sm mb-0 float-right">
      <li class="page-item <?= !$pager->hasPrev()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()-1]) ?>">«</a></li>
      <?php foreach($pager->pages() as $p): ?><li class="page-item <?= $p===$pager->currentPage()?'active':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$p]) ?>"><?= $p ?></a></li><?php endforeach; ?>
      <li class="page-item <?= !$pager->hasNext()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()+1]) ?>">»</a></li>
    </ul>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
