<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("user/edit/{$item->id}") : site_url('user/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('user') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit User' : 'Tambah User' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
        <input type="text" name="username" class="form-control <?= form_error('username') ? 'is-invalid' : '' ?>"
               value="<?= set_value('username', $is_edit ? $item->username : '') ?>">
        <div class="invalid-feedback"><?= form_error('username') ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control <?= form_error('nama') ? 'is-invalid' : '' ?>"
               value="<?= set_value('nama', $is_edit ? $item->nama : '') ?>">
        <div class="invalid-feedback"><?= form_error('nama') ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Password <?= $is_edit ? '<span class="text-muted fw-normal">(kosongkan jika tidak diubah)</span>' : '<span class="text-danger">*</span>' ?></label>
        <input type="password" name="password" class="form-control <?= form_error('password') ? 'is-invalid' : '' ?>"
               placeholder="<?= $is_edit ? 'Biarkan kosong jika tidak diubah' : 'Min. 6 karakter' ?>">
        <div class="invalid-feedback"><?= form_error('password') ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
        <select name="role" class="form-select <?= form_error('role') ? 'is-invalid' : '' ?>">
          <?php foreach (['admin' => 'Admin', 'dokter' => 'Dokter', 'staff' => 'Staff'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= set_select('role', $val, $is_edit && $item->role === $val) ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= form_error('role') ?></div>
      </div>

      <div class="mb-4">
        <div class="form-check">
          <input type="checkbox" name="is_active" value="1" id="is_active" class="form-check-input"
                 <?= set_checkbox('is_active', '1', $is_edit ? (bool)$item->is_active : TRUE) ?>>
          <label class="form-check-label" for="is_active">Akun aktif</label>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="<?= site_url('user') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>
