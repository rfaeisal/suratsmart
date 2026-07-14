<?php
$stat_cards = [
    ['label' => 'Surat Draft',         'value' => $stats['surat_draft'],         'icon' => 'bi-file-earmark',       'color' => 'secondary'],
    ['label' => 'Surat Menunggu',       'value' => $stats['surat_menunggu'],       'icon' => 'bi-hourglass-split',    'color' => 'warning'],
    ['label' => 'Surat Terverifikasi',  'value' => $stats['surat_terverifikasi'],  'icon' => 'bi-file-earmark-check', 'color' => 'success'],
    ['label' => 'Resep Draft',          'value' => $stats['resep_draft'],          'icon' => 'bi-capsule',            'color' => 'secondary'],
    ['label' => 'Resep Menunggu',       'value' => $stats['resep_menunggu'],       'icon' => 'bi-hourglass-split',    'color' => 'warning'],
    ['label' => 'Resep Terverifikasi',  'value' => $stats['resep_terverifikasi'],  'icon' => 'bi-capsule-pill',       'color' => 'success'],
];
?>
<div class="row g-3 mb-4">
  <?php foreach ($stat_cards as $card): ?>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body text-center py-3">
        <i class="bi <?= $card['icon'] ?> fs-3 text-<?= $card['color'] ?>"></i>
        <div class="fw-bold fs-4 mt-1"><?= $card['value'] ?></div>
        <div class="text-muted small"><?= $card['label'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-file-text me-1"></i>Aksi Cepat
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="<?= site_url('surat/create') ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Buat Surat Baru
          </a>
          <a href="<?= site_url('resep/create') ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-plus-circle me-1"></i>Buat Resep Baru
          </a>
          <a href="<?= site_url('approval_log') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-journal-text me-1"></i>Approval Log
          </a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-info-circle me-1"></i>Info Sistem
      </div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted">Framework</dt>
          <dd class="col-7">CodeIgniter <?= CI_VERSION ?></dd>
          <dt class="col-5 text-muted">PHP</dt>
          <dd class="col-7"><?= PHP_VERSION ?></dd>
          <dt class="col-5 text-muted">User</dt>
          <dd class="col-7"><?= htmlspecialchars($this->session->userdata('nama')) ?><br><span class="badge bg-primary"><?= $this->session->userdata('role') ?></span></dd>
        </dl>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold d-flex justify-content-between">
        <span><i class="bi bi-send me-1"></i>Endpoint Callback</span>
      </div>
      <div class="card-body small">
        <p class="text-muted mb-1">ApprovalSmart kirim callback ke:</p>
        <code class="d-block text-break bg-light p-2 rounded" style="font-size:.75rem"><?= base_url('approvals/{source_ref}') ?></code>
        <p class="text-muted mt-2 mb-1">Log API internal:</p>
        <code class="d-block text-break bg-light p-2 rounded" style="font-size:.75rem"><?= base_url('api/approval/log') ?></code>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history me-1"></i>Log Terbaru</span>
    <a href="<?= site_url('approval_log') ?>" class="btn btn-sm btn-outline-secondary py-0">Lihat Semua</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>Waktu</th>
            <th>Modul</th>
            <th>Arah</th>
            <th>Source Ref</th>
            <th class="text-center">HTTP</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recent_logs)): ?>
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada log approval.</td></tr>
          <?php else: ?>
          <?php foreach ($recent_logs as $log): ?>
          <tr>
            <td class="text-muted"><?= date('d/m H:i', strtotime($log->created_at)) ?></td>
            <td><span class="badge bg-<?= $log->module_type === 'surat' ? 'primary' : 'success' ?> bg-opacity-75"><?= $log->module_type ?></span></td>
            <td>
              <?php if ($log->direction === 'outbound'): ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-right"></i> out</span>
              <?php else: ?>
                <span class="badge bg-info text-dark"><i class="bi bi-arrow-down-left"></i> in</span>
              <?php endif; ?>
            </td>
            <td><code><?= htmlspecialchars($log->source_ref) ?></code></td>
            <td class="text-center">
              <?php $code = (int)$log->http_status; ?>
              <span class="badge bg-<?= $code >= 200 && $code < 300 ? 'success' : ($code >= 400 ? 'danger' : 'secondary') ?>">
                <?= $code ?: '—' ?>
              </span>
            </td>
            <td>
              <span class="badge bg-<?= $log->status === 'sukses' ? 'success' : 'danger' ?>"><?= $log->status ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
