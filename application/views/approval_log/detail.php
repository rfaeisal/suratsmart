<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('approval_log') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold">Detail Log #<?= $log->id ?></h5>
  <span class="badge bg-<?= $log->status === 'sukses' ? 'success' : 'danger' ?>"><?= $log->status ?></span>
</div>

<div class="row g-3">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-white fw-semibold">Info</div>
      <div class="card-body">
        <dl class="row small mb-0">
          <dt class="col-5 text-muted">Waktu</dt>
          <dd class="col-7"><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></dd>
          <dt class="col-5 text-muted">Modul</dt>
          <dd class="col-7"><?= $log->module_type ?></dd>
          <dt class="col-5 text-muted">Arah</dt>
          <dd class="col-7"><?= $log->direction ?></dd>
          <dt class="col-5 text-muted">HTTP Method</dt>
          <dd class="col-7"><code><?= $log->http_method ?></code></dd>
          <dt class="col-5 text-muted">HTTP Status</dt>
          <dd class="col-7"><code><?= $log->http_status ?: '—' ?></code></dd>
          <dt class="col-5 text-muted">Source Ref</dt>
          <dd class="col-7"><code><?= htmlspecialchars($log->source_ref) ?></code></dd>
          <dt class="col-5 text-muted">Approval ID</dt>
          <dd class="col-7" style="font-size:.75rem"><code><?= htmlspecialchars($log->approval_id ?? '-') ?></code></dd>
          <dt class="col-5 text-muted">Endpoint</dt>
          <dd class="col-7" style="word-break:break-all;font-size:.75rem"><?= htmlspecialchars($log->endpoint ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-md-7">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">Request Payload</div>
      <div class="card-body p-0">
        <pre class="m-0 p-3 bg-light small" style="max-height:260px;overflow:auto;border-radius:0 0 .375rem .375rem"><?= htmlspecialchars($log->request_payload ?? '—') ?></pre>
      </div>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">Response Payload</div>
      <div class="card-body p-0">
        <pre class="m-0 p-3 bg-light small" style="max-height:200px;overflow:auto;border-radius:0 0 .375rem .375rem"><?= htmlspecialchars($log->response_payload ?? '—') ?></pre>
      </div>
    </div>
  </div>
</div>
