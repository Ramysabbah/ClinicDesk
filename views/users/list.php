<?php
require_once __DIR__ . '/../../views/partials/header.php';
require_once __DIR__ . '/../../views/partials/navbar.php';
require_once __DIR__ . '/../../views/partials/sidebar.php';
require_once __DIR__ . '/../../views/partials/topbar.php';
require_once __DIR__ . '/../../views/partials/alerts.php';
$rows  = $data['rows'];
$pager = $data['pager'];
$role  = $_GET['role']   ?? '';
$search = $_GET['search'] ?? '';
?>
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-users mr-2"></i>User Management</h3>
    <div class="card-tools">
      <a href="<?= BASE_URL ?>/index.php?page=users&action=create" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i>Add User
      </a>
    </div>
  </div>

  <!-- Filters -->
  <div class="card-body border-bottom">
    <form method="GET" action="<?= BASE_URL ?>/index.php" class="form-inline flex-wrap gap-2">
      <input type="hidden" name="page" value="users">
      <input type="hidden" name="action" value="index">
      <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2"
             placeholder="Name or email…" value="<?= e($search) ?>">
      <select name="role" class="form-control form-control-sm mr-2 mb-2">
        <option value="">All Roles</option>
        <?php foreach (['admin','doctor','patient'] as $r): ?>
        <option value="<?= $r ?>" <?= $role===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm mr-2 mb-2"><i class="fas fa-search mr-1"></i>Filter</button>
      <a href="<?= BASE_URL ?>/index.php?page=users" class="btn btn-secondary btn-sm mb-2">Clear</a>
    </form>
  </div>

  <div class="card-body p-0">
    <table class="table table-striped table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Specialization</th><th>Status</th><th>Created</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center text-muted py-3">No users found.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $u): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td>
            <div class="d-flex align-items-center">
              <img src="<?= e(avatarUrl($u['avatar'])) ?>" class="img-circle mr-2"
                   style="width:30px;height:30px;object-fit:cover;" alt="">
              <?= e($u['name']) ?>
            </div>
          </td>
          <td><?= e($u['email']) ?></td>
          <td>
            <span class="badge badge-<?= $u['role']==='admin'?'danger':($u['role']==='doctor'?'info':'secondary') ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </td>
          <td><?= e($u['specialization'] ?? '—') ?></td>
          <td>
            <span class="badge badge-<?= $u['is_active']?'success':'danger' ?>">
              <?= $u['is_active']?'Active':'Inactive' ?>
            </span>
          </td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="<?= BASE_URL ?>/index.php?page=users&action=edit&id=<?= $u['id'] ?>"
               class="btn btn-xs btn-info mr-1"><i class="fas fa-edit"></i></a>
            <?php if ($u['id'] !== Auth::id()): ?>
            <form method="POST" class="d-inline"
                  action="<?= BASE_URL ?>/index.php?page=users&action=toggle_active"
                  onsubmit="return confirm('Toggle account status?')">
              <?= CSRF::field() ?>
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button class="btn btn-xs btn-<?= $u['is_active']?'warning':'success' ?>">
                <i class="fas fa-<?= $u['is_active']?'ban':'check' ?>"></i>
              </button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pager->totalPages() > 1): ?>
  <div class="card-footer clearfix">
    <small class="float-left text-muted mt-2">
      Showing <?= $pager->firstItem() ?>–<?= $pager->lastItem() ?> of <?= $pager->totalItems() ?>
    </small>
    <ul class="pagination pagination-sm mb-0 float-right">
      <li class="page-item <?= !$pager->hasPrev()?'disabled':'' ?>">
        <a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()-1,'page'=>'users','action'=>'index','role'=>$role,'search'=>$search]) ?>">«</a>
      </li>
      <?php foreach ($pager->pages() as $p): ?>
      <li class="page-item <?= $p===$pager->currentPage()?'active':'' ?>">
        <a class="page-link" href="?<?= buildQuery(['page_num'=>$p,'page'=>'users','action'=>'index','role'=>$role,'search'=>$search]) ?>"><?= $p ?></a>
      </li>
      <?php endforeach; ?>
      <li class="page-item <?= !$pager->hasNext()?'disabled':'' ?>">
        <a class="page-link" href="?<?= buildQuery(['page_num'=>$pager->currentPage()+1,'page'=>'users','action'=>'index','role'=>$role,'search'=>$search]) ?>">»</a>
      </li>
    </ul>
  </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>
