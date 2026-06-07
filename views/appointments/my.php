<?php
/**
 * @var array $data - Contains 'rows' and 'pager' for pagination
 */
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$rows=$data['rows']; $pager=$data['pager'];
$statusFilter=$_GET['status']??'';
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>My Appointments</h3>
    <div class="card-tools">
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=book" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i>Book New
      </a>
    </div>
  </div>
  <div class="card-body pb-0">
    <div class="btn-group mb-3" role="group">
      <?php foreach([''  =>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $val=>$label): ?>
      <a href="<?= BASE_URL ?>/index.php?page=appointments&action=my&status=<?= $val ?>"
         class="btn btn-sm btn-<?= $statusFilter===$val?'primary':'outline-secondary' ?>">
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0">
      <thead><tr><th>Doctor</th><th>Specialization</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($rows)): ?><tr><td colspan="7" class="text-center text-muted py-3">No appointments found.</td></tr><?php endif; ?>
        <?php foreach($rows as $a): ?>
        <tr>
          <td>Dr. <?= e($a['doctor_name']) ?></td>
          <td><?= e($a['specialization']) ?></td>
          <td><?= formatDate($a['appt_date']) ?></td>
          <td><?= formatTime($a['appt_time']) ?></td>
          <td><?= e(truncate($a['reason']??'—',40)) ?></td>
          <td><span class="badge badge-<?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>/index.php?page=appointments&action=detail&id=<?= $a['id'] ?>" class="btn btn-xs btn-info mr-1"><i class="fas fa-eye"></i></a>
            <?php if($a['status']==='pending'): ?>
            <form method="POST" class="d-inline" action="<?= BASE_URL ?>/index.php?page=appointments&action=cancel" onsubmit="return confirm('Cancel this appointment?')">
              <?= CSRF::field() ?><input type="hidden" name="appt_id" value="<?= $a['id'] ?>">
              <button class="btn btn-xs btn-danger"><i class="fas fa-times"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if($pager->totalPages()>1): ?>
  <div class="card-footer clearfix">
    <small class="float-left text-muted mt-2">Showing <?= $pager->firstItem() ?>–<?= $pager->lastItem() ?> of <?= $pager->totalItems() ?></small>
    <ul class="pagination pagination-sm mb-0 float-right">
      <li class="page-item <?= !$pager->hasPrev()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()-1,'page'=>'appointments','action'=>'my','status'=>$statusFilter]) ?>">«</a></li>
      <?php foreach($pager->pages() as $p): ?><li class="page-item <?= $p===$pager->currentPage()?'active':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$p,'page'=>'appointments','action'=>'my','status'=>$statusFilter]) ?>"><?= $p ?></a></li><?php endforeach; ?>
      <li class="page-item <?= !$pager->hasNext()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()+1,'page'=>'appointments','action'=>'my','status'=>$statusFilter]) ?>">»</a></li>
    </ul>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
