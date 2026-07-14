<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("dokter/edit/{$item->id}") : site_url('dokter/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('dokter') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit Dokter' : 'Tambah Dokter' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nama <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control <?= form_error('nama') ? 'is-invalid' : '' ?>"
               value="<?= set_value('nama', $is_edit ? $item->nama : '') ?>">
        <div class="invalid-feedback"><?= form_error('nama') ?></div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">No. SIP</label>
          <input type="text" name="no_sip" class="form-control"
                 value="<?= set_value('no_sip', $is_edit ? $item->no_sip : '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Spesialisasi</label>
          <input type="text" name="spesialisasi" class="form-control"
                 value="<?= set_value('spesialisasi', $is_edit ? $item->spesialisasi : '') ?>">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">No. Telp</label>
        <input type="text" name="no_telp" class="form-control"
               value="<?= set_value('no_telp', $is_edit ? $item->no_telp : '') ?>">
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Tautkan ke Akun User <span class="text-muted fw-normal">(opsional)</span></label>
        <select name="user_id" class="form-select">
          <option value="">— Tidak ditautkan —</option>
          <?php foreach ($users as $u): ?>
          <option value="<?= $u->id ?>" <?= set_select('user_id', $u->id, $is_edit && $item->user_id == $u->id) ?>>
            <?= htmlspecialchars($u->nama) ?> (<?= htmlspecialchars($u->username) ?>) — ID: <?= $u->id ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">ApprovalSmart User ID <span class="text-muted fw-normal">(opsional)</span></label>
        <input type="text" name="approvalsmart_user_id" class="form-control font-monospace"
               value="<?= set_value('approvalsmart_user_id', $is_edit ? ($item->approvalsmart_user_id ?? '') : '') ?>"
               placeholder="Contoh: 1 atau USR001">
        <div class="form-text">Isi setelah tim ApprovalSmart mengkonfirmasi mapping. Wajib diisi agar surat/resep bisa dikirim.</div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="<?= site_url('dokter') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>
