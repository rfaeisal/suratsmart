<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("obat/edit/{$item->id}") : site_url('obat/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('obat') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit Obat' : 'Tambah Obat' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:500px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="mb-3">
        <label class="form-label fw-semibold">Nama Obat <span class="text-danger">*</span></label>
        <input type="text" name="nama_obat" class="form-control <?= form_error('nama_obat') ? 'is-invalid' : '' ?>"
               value="<?= set_value('nama_obat', $is_edit ? $item->nama_obat : '') ?>">
        <div class="invalid-feedback"><?= form_error('nama_obat') ?></div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Satuan</label>
        <input type="text" name="satuan" class="form-control"
               value="<?= set_value('satuan', $is_edit ? $item->satuan : '') ?>"
               placeholder="Contoh: tablet, botol, kapsul">
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
          <input type="number" name="stok" min="0" class="form-control <?= form_error('stok') ? 'is-invalid' : '' ?>"
                 value="<?= set_value('stok', $is_edit ? $item->stok : '0') ?>">
          <div class="invalid-feedback"><?= form_error('stok') ?></div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
          <input type="number" name="harga" min="0" step="0.01" class="form-control <?= form_error('harga') ? 'is-invalid' : '' ?>"
                 value="<?= set_value('harga', $is_edit ? $item->harga : '0') ?>">
          <div class="invalid-feedback"><?= form_error('harga') ?></div>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="<?= site_url('obat') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>
