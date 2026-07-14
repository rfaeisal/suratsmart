<?php
$active = isset($active_menu) ? $active_menu : '';
$role   = $this->session->userdata('role');
?>
    <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>

    <div class="nav-section">Transaksi</div>
    <a href="<?= site_url('surat') ?>" class="nav-link <?= $active === 'surat' ? 'active' : '' ?>">
      <i class="bi bi-file-text me-2"></i>Surat
    </a>
    <a href="<?= site_url('resep') ?>" class="nav-link <?= $active === 'resep' ? 'active' : '' ?>">
      <i class="bi bi-capsule me-2"></i>Resep
    </a>
    <a href="<?= site_url('approval_log') ?>" class="nav-link <?= $active === 'approval_log' ? 'active' : '' ?>">
      <i class="bi bi-journal-text me-2"></i>Approval Log
    </a>

    <div class="nav-section">Master Data</div>
    <a href="<?= site_url('dokter') ?>" class="nav-link <?= $active === 'dokter' ? 'active' : '' ?>">
      <i class="bi bi-person-badge me-2"></i>Dokter
    </a>
    <a href="<?= site_url('pasien') ?>" class="nav-link <?= $active === 'pasien' ? 'active' : '' ?>">
      <i class="bi bi-people me-2"></i>Pasien
    </a>
    <a href="<?= site_url('obat') ?>" class="nav-link <?= $active === 'obat' ? 'active' : '' ?>">
      <i class="bi bi-box-seam me-2"></i>Obat
    </a>

    <?php if ($role === 'admin'): ?>
    <div class="nav-section">Admin</div>
    <a href="<?= site_url('user') ?>" class="nav-link <?= $active === 'user' ? 'active' : '' ?>">
      <i class="bi bi-person-gear me-2"></i>User
    </a>
    <a href="<?= site_url('settings') ?>" class="nav-link <?= $active === 'settings' ? 'active' : '' ?>">
      <i class="bi bi-gear me-2"></i>Settings
    </a>
    <?php endif; ?>
  </nav>
</div>

<div class="main-content">
  <div class="topbar d-flex align-items-center justify-content-between">
    <span class="fw-semibold text-secondary"><?= isset($title) ? htmlspecialchars($title) : '' ?></span>
    <div class="d-flex align-items-center gap-3">
      <span class="text-secondary small"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($this->session->userdata('nama')) ?></span>
      <a href="<?= site_url('auth/logout') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </a>
    </div>
  </div>
  <div class="page-content">
