<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — SuratSmart</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    body { background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { width: 100%; max-width: 400px; }
    .login-brand { background: #1e293b; color: #fff; border-radius: .5rem .5rem 0 0; padding: 1.5rem; text-align: center; }
  </style>
</head>
<body>
<div class="login-card">
  <div class="login-brand">
    <i class="bi bi-file-earmark-check-fill fs-2"></i>
    <div class="fw-bold fs-5 mt-1">SuratSmart</div>
    <div class="text-white-50 small">Sistem Integrasi ApprovalSmart</div>
  </div>
  <div class="card border-0 shadow-sm rounded-top-0">
    <div class="card-body p-4">
      <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
          <i class="bi bi-exclamation-triangle me-1"></i>
          <?= $this->session->flashdata('error') ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?= form_open('auth/login') ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control <?= form_error('username') ? 'is-invalid' : '' ?>"
                   value="<?= set_value('username') ?>" placeholder="Masukkan username" autofocus>
            <?php if (form_error('username')): ?>
              <div class="invalid-feedback"><?= form_error('username') ?></div>
            <?php endif; ?>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
                   placeholder="Masukkan password">
            <?php if (form_error('password')): ?>
              <div class="invalid-feedback"><?= form_error('password') ?></div>
            <?php endif; ?>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
        </button>
      <?= form_close() ?>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
