<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
?>
<div class="card card-primary card-outline">
  <div class="card-header"><h3 class="card-title"><i class="fas fa-filter mr-2"></i>Filter Report</h3></div>
  <div class="card-body">
    <form method="GET" class="form-inline flex-wrap gap-2">
      <input type="hidden" name="page" value="reports">
      <input type="hidden" name="action" value="index">
      <div class="form-group mr-2 mb-2">
        <label class="mr-1">From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from']??'') ?>" required>
      </div>
      <div class="form-group mr-2 mb-2">
        <label class="mr-1">To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to']??'') ?>" required>
      </div>
      <div class="form-group mr-2 mb-2">
        <select name="doctor_id" class="form-control form-control-sm">
          <option value="">All Doctors</option>
          <?php foreach($doctors as $d): ?><option value="<?= $d['id'] ?>" <?= ($filters['doctor_id']??'')==$d['id']?'selected':'' ?>>Dr. <?= e($d['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group mr-2 mb-2">
        <select name="status" class="form-control form-control-sm">
          <option value="">All Status</option>
          <?php foreach(['pending','confirmed','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary btn-sm mr-2 mb-2"><i class="fas fa-search mr-1"></i>Generate</button>
      <?php if(!empty($rows)): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['export'=>'csv'])) ?>" class="btn btn-success btn-sm mb-2">
        <i class="fas fa-file-csv mr-1"></i>Export CSV
      </a>
      <?php endif; ?>
    </form>
    <?php if(!empty($error)): ?><div class="alert alert-danger mt-3 mb-0"><?= e($error) ?></div><?php endif; ?>
  </div>
</div>

<?php if(!empty($rows)):
$summary=['pending'=>0,'confirmed'=>0,'completed'=>0,'cancelled'=>0];
foreach($rows as $r) $summary[$r['status']] = ($summary[$r['status']]??0)+1;
?>
<div class="row">
  <?php foreach([['Total','info',count($rows)],['Completed','success',$summary['completed']],['Pending','warning',$summary['pending']],['Cancelled','danger',$summary['cancelled']]] as [$label,$color,$count]): ?>
  <div class="col-lg-3 col-6">
    <div class="small-box bg-<?= $color ?>">
      <div class="inner"><h3><?= $count ?></h3><p><?= $label ?></p></div>
      <div class="icon"><i class="fas fa-calendar-check"></i></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-table mr-2"></i>Results (<?= count($rows) ?> appointments)</h3>
    <div class="card-tools">
      <a href="?<?= http_build_query(array_merge($_GET,['export'=>'csv'])) ?>" class="btn btn-success btn-sm">
        <i class="fas fa-download mr-1"></i>Export CSV
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0" id="reportTable">
      <thead><tr><th>Patient</th><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Status</th><th>Reason</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><?= e($r['patient_name']) ?></td>
          <td>Dr. <?= e($r['doctor_name']) ?></td>
          <td><?= e($r['specialization']) ?></td>
          <td><?= formatDate($r['appt_date']) ?></td>
          <td><?= formatTime($r['appt_time']) ?></td>
          <td><span class="badge badge-<?= statusBadge($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= e(truncate($r['reason']??'—',50)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php elseif(!empty($filters['date_from'])&&empty($error)): ?>
<div class="card"><div class="card-body text-center text-muted py-4">
  <i class="fas fa-inbox fa-2x mb-2 d-block"></i>No appointments found for the selected filters.
</div></div>
<?php endif; ?>

<?php
$extraJs = empty($rows) ? '' : <<<'JS'
<script>
$(document).ready(function(){
  $('#reportTable').DataTable({ paging:false, info:false, searching:true, order:[[3,'asc']] });
});
</script>
JS;
require_once __DIR__ . '/../../views/partials/footer.php';
?>
