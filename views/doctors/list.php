<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$rows=$data['rows']; $pager=$data['pager'];
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-user-md mr-2"></i>Doctors</h3>
    <div class="card-tools">
      <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i>Add Doctor
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0">
      <thead><tr><th>Doctor</th><th>Specialization</th><th>Fee (ILS)</th><th>Available Days</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($rows)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No doctors found.</td></tr>
        <?php endif; ?>
        <?php foreach($rows as $d): ?>
        <tr>
          <td>
            <div class="d-flex align-items-center">
              <img src="<?= e(doctorPhotoUrl($d['photo']??null)) ?>" class="img-circle mr-2"
                   style="width:32px;height:32px;object-fit:cover;" alt="">
              <div>
                <strong>Dr. <?= e($d['name']) ?></strong>
                <div class="text-muted small"><?= e($d['email']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-info"><?= e($d['specialization_name']) ?></span></td>
          <td>₪<?= number_format($d['consultation_fee'],2) ?></td>
          <td><small class="text-muted"><?= e($d['available_days']) ?></small></td>
          <td><span class="badge badge-<?= $d['is_active']?'success':'danger' ?>"><?= $d['is_active']?'Active':'Inactive' ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>/index.php?page=doctors&action=edit&id=<?= $d['id'] ?>" class="btn btn-xs btn-info">
              <i class="fas fa-edit"></i>
            </a>
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
      <li class="page-item <?= !$pager->hasPrev()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()-1,'page'=>'doctors','action'=>'index']) ?>">«</a></li>
      <?php foreach($pager->pages() as $p): ?>
      <li class="page-item <?= $p===$pager->currentPage()?'active':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$p,'page'=>'doctors','action'=>'index']) ?>"><?= $p ?></a></li>
      <?php endforeach; ?>
      <li class="page-item <?= !$pager->hasNext()?'disabled':'' ?>"><a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()+1,'page'=>'doctors','action'=>'index']) ?>">»</a></li>
    </ul>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
