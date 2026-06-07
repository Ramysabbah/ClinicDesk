<?php if (!empty($_SESSION['flash'])):
  $type    = htmlspecialchars($_SESSION['flash']['type'],    ENT_QUOTES, 'UTF-8');
  $message = htmlspecialchars($_SESSION['flash']['message'], ENT_QUOTES, 'UTF-8');
  $icon    = match($type) {
      'success' => 'fas fa-check-circle',
      'danger'  => 'fas fa-times-circle',
      'warning' => 'fas fa-exclamation-triangle',
      default   => 'fas fa-info-circle',
  };
  unset($_SESSION['flash']);
?>
<div class="alert alert-<?= $type ?> alert-dismissible fade show">
  <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  <i class="<?= $icon ?> mr-2"></i><?= $message ?>
</div>
<?php endif; ?>
