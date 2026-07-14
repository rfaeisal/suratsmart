<?php $this->load->view('layouts/_alert'); ?>

<div class="mb-3">
  <h5 class="mb-1 fw-semibold">Settings ApprovalSmart</h5>
  <p class="text-muted small mb-0">Konfigurasi koneksi ke server ApprovalSmart. Nilai disimpan di database — jangan bagikan ke orang lain.</p>
</div>

<div class="card border-0 shadow-sm" style="max-width:600px">
  <div class="card-body">
    <?= form_open('settings') ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Public Base URL <span class="text-muted fw-normal">(URL publik aplikasi ini)</span></label>
        <input type="url" name="public_base_url" class="form-control font-monospace"
               value="<?= htmlspecialchars($settings['public_base_url'] ?? '') ?>"
               placeholder="https://xxxx.trycloudflare.com">
        <div class="form-text">URL tunnel/domain publik aplikasi ini. Dipakai untuk generate link PDF lampiran yang bisa diakses ApprovalSmart.</div>
      </div>

      <hr class="my-3">

      <div class="mb-3">
        <label class="form-label fw-semibold">ApprovalSmart Base URL</label>
        <input type="url" name="approvalsmart_base_url" class="form-control"
               value="<?= htmlspecialchars($settings['approvalsmart_base_url'] ?? '') ?>"
               placeholder="https://approval.lmssmart.my.id">
        <div class="form-text">Endpoint server ApprovalSmart — tempat kita kirim request.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">HMAC Key ID</label>
        <input type="text" name="approvalsmart_hmac_key_id" class="form-control font-monospace"
               value="<?= htmlspecialchars($settings['approvalsmart_hmac_key_id'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">HMAC Secret</label>
        <div class="input-group">
          <input type="password" name="approvalsmart_hmac_secret" id="hmac_secret" class="form-control font-monospace"
                 value="<?= htmlspecialchars($settings['approvalsmart_hmac_secret'] ?? '') ?>">
          <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('hmac_secret', this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <div class="form-text">Dipakai untuk HMAC-SHA256 signing outbound request.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Legacy API Token</label>
        <div class="input-group">
          <input type="password" name="approvalsmart_legacy_token" id="legacy_token" class="form-control font-monospace"
                 value="<?= htmlspecialchars($settings['approvalsmart_legacy_token'] ?? '') ?>">
          <button type="button" class="btn btn-outline-secondary" onclick="toggleVisibility('legacy_token', this)">
            <i class="bi bi-eye"></i>
          </button>
        </div>
        <div class="form-text">Dipakai untuk verifikasi header <code>Authorization: Bearer ...</code> dari callback inbound.</div>
      </div>

      <button type="submit" class="btn btn-primary">
        <i class="bi bi-save me-1"></i>Simpan Settings
      </button>

    <?= form_close() ?>
  </div>
</div>

<script>
function toggleVisibility(id, btn) {
  var inp = document.getElementById(id);
  var icon = btn.querySelector('i');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'bi bi-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'bi bi-eye';
  }
}
</script>
