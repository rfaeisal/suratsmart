<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("surat/edit/{$item->id}") : site_url('surat/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('surat') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit Surat' : 'Buat Surat Baru' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:700px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Dokter <span class="text-danger">*</span></label>
          <select name="dokter_id" class="form-select <?= form_error('dokter_id') ? 'is-invalid' : '' ?>">
            <option value="">— Pilih Dokter —</option>
            <?php foreach ($dokters as $d): ?>
            <option value="<?= $d->id ?>" <?= set_select('dokter_id', $d->id, $is_edit && $item->dokter_id == $d->id) ?>>
              <?= htmlspecialchars($d->nama) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback"><?= form_error('dokter_id') ?></div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Pasien <span class="text-danger">*</span></label>
          <select name="pasien_id" class="form-select <?= form_error('pasien_id') ? 'is-invalid' : '' ?>">
            <option value="">— Pilih Pasien —</option>
            <?php foreach ($pasiens as $p): ?>
            <option value="<?= $p->id ?>" <?= set_select('pasien_id', $p->id, $is_edit && $item->pasien_id == $p->id) ?>>
              <?= htmlspecialchars($p->nama) ?> (<?= htmlspecialchars($p->no_rm) ?>)
            </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback"><?= form_error('pasien_id') ?></div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Jenis Surat <span class="text-danger">*</span></label>
        <input type="text" name="jenis_surat" class="form-control <?= form_error('jenis_surat') ? 'is-invalid' : '' ?>"
               value="<?= set_value('jenis_surat', $is_edit ? $item->jenis_surat : '') ?>"
               placeholder="Contoh: Surat Keterangan Sakit, Surat Rujukan">
        <div class="invalid-feedback"><?= form_error('jenis_surat') ?></div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Isi Surat <span class="text-danger">*</span></label>
        <textarea name="isi" rows="8" class="form-control <?= form_error('isi') ? 'is-invalid' : '' ?>"
                  placeholder="Tulis isi surat di sini..."><?= set_value('isi', $is_edit ? $item->isi : '') ?></textarea>
        <div class="invalid-feedback"><?= form_error('isi') ?></div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Draft</button>
        <a href="<?= site_url('surat') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>
