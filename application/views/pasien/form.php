<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("pasien/edit/{$item->id}") : site_url('pasien/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('pasien') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit Pasien' : 'Tambah Pasien' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:560px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">No. Rekam Medis <span class="text-danger">*</span></label>
        <input type="text" name="no_rm" class="form-control <?= form_error('no_rm') ? 'is-invalid' : '' ?>"
               value="<?= set_value('no_rm', $is_edit ? $item->no_rm : '') ?>" placeholder="Contoh: RM-001">
        <div class="invalid-feedback"><?= form_error('no_rm') ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" name="nama" class="form-control <?= form_error('nama') ? 'is-invalid' : '' ?>"
               value="<?= set_value('nama', $is_edit ? $item->nama : '') ?>">
        <div class="invalid-feedback"><?= form_error('nama') ?></div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" class="form-control"
                 value="<?= set_value('tanggal_lahir', $is_edit ? $item->tanggal_lahir : '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select">
            <option value="">— Pilih —</option>
            <option value="L" <?= set_select('jenis_kelamin', 'L', $is_edit && $item->jenis_kelamin === 'L') ?>>Laki-laki</option>
            <option value="P" <?= set_select('jenis_kelamin', 'P', $is_edit && $item->jenis_kelamin === 'P') ?>>Perempuan</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Alamat</label>
        <textarea name="alamat" class="form-control" rows="2"><?= set_value('alamat', $is_edit ? $item->alamat : '') ?></textarea>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">No. Telp</label>
        <input type="text" name="no_telp" class="form-control"
               value="<?= set_value('no_telp', $is_edit ? $item->no_telp : '') ?>">
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="<?= site_url('pasien') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>
