<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Approval Log</h5>
  <span class="text-muted small"><?= count($logs) ?> entri</span>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <?= form_open('approval_log', ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
      <div class="col-auto">
        <select name="module_type" class="form-select form-select-sm">
          <option value="">Semua Modul</option>
          <option value="surat" <?= $filters['module_type'] === 'surat' ? 'selected' : '' ?>>Surat</option>
          <option value="resep" <?= $filters['module_type'] === 'resep' ? 'selected' : '' ?>>Resep</option>
        </select>
      </div>
      <div class="col-auto">
        <select name="direction" class="form-select form-select-sm">
          <option value="">Semua Arah</option>
          <option value="outbound" <?= $filters['direction'] === 'outbound' ? 'selected' : '' ?>>Outbound (Kirim)</option>
          <option value="inbound" <?= $filters['direction'] === 'inbound' ? 'selected' : '' ?>>Inbound (Callback)</option>
        </select>
      </div>
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">Semua Status</option>
          <option value="sukses" <?= $filters['status'] === 'sukses' ? 'selected' : '' ?>>Sukses</option>
          <option value="gagal" <?= $filters['status'] === 'gagal' ? 'selected' : '' ?>>Gagal</option>
        </select>
      </div>
      <div class="col-auto">
        <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $filters['date_from'] ?>" placeholder="Dari">
      </div>
      <div class="col-auto">
        <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $filters['date_to'] ?>" placeholder="Sampai">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
        <a href="<?= site_url('approval_log') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
      </div>
    <?= form_close() ?>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Waktu</th>
            <th>Modul</th>
            <th>Arah</th>
            <th>Source Ref</th>
            <th>Endpoint</th>
            <th class="text-center">HTTP</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Belum ada log.</td></tr>
          <?php else: ?>
          <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-muted"><?= $log->id ?></td>
            <td class="text-muted"><?= date('d/m H:i:s', strtotime($log->created_at)) ?></td>
            <td><span class="badge bg-<?= $log->module_type === 'surat' ? 'primary' : 'success' ?> bg-opacity-75"><?= $log->module_type ?></span></td>
            <td>
              <?php if ($log->direction === 'outbound'): ?>
                <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-right"></i> outbound</span>
              <?php else: ?>
                <span class="badge bg-info text-dark"><i class="bi bi-arrow-down-left"></i> inbound</span>
              <?php endif; ?>
            </td>
            <td><code><?= htmlspecialchars($log->source_ref) ?></code></td>
            <td class="text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
              <?= htmlspecialchars($log->http_method . ' ' . ($log->endpoint ?? '')) ?>
            </td>
            <td class="text-center">
              <?php $code = (int)$log->http_status; ?>
              <span class="badge bg-<?= $code >= 200 && $code < 300 ? 'success' : ($code >= 400 ? 'danger' : 'secondary') ?>">
                <?= $code ?: '—' ?>
              </span>
            </td>
            <td>
              <span class="badge bg-<?= $log->status === 'sukses' ? 'success' : 'danger' ?>">
                <?= $log->status ?>
              </span>
            </td>
            <td>
              <a href="<?= site_url('approval_log/detail/' . $log->id) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
