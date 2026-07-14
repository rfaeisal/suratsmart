<?php
$flash_success = $this->session->flashdata('success');
$flash_error   = $this->session->flashdata('error');
?>
<?php if ($flash_success): ?>
<div class="alert alert-success alert-dismissible fade show py-2" role="alert">
  <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($flash_success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flash_error): ?>
<div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
  <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($flash_error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
