<?php
$is_edit = isset($item) && $item !== NULL;
$action  = $is_edit ? site_url("resep/edit/{$item->id}") : site_url('resep/create');
?>
<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('resep') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold"><?= $is_edit ? 'Edit Resep' : 'Buat Resep Baru' ?></h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:800px">
  <div class="card-body">
    <?= form_open($action) ?>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
          <input type="date" name="tanggal" class="form-control <?= form_error('tanggal') ? 'is-invalid' : '' ?>"
                 value="<?= set_value('tanggal', $is_edit ? $item->tanggal : date('Y-m-d')) ?>">
          <div class="invalid-feedback"><?= form_error('tanggal') ?></div>
        </div>
        <div class="col-md-4">
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
        <div class="col-md-4">
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
        <label class="form-label fw-semibold">Catatan</label>
        <input type="text" name="catatan" class="form-control"
               value="<?= set_value('catatan', $is_edit ? $item->catatan : '') ?>"
               placeholder="Catatan tambahan (opsional)">
      </div>

      <!-- Tabel Obat -->
      <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <label class="form-label fw-semibold mb-0">Daftar Obat</label>
          <button type="button" class="btn btn-sm btn-outline-success" id="btn-add-obat">
            <i class="bi bi-plus-circle me-1"></i>Tambah Obat
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered align-middle mb-0" id="tbl-obat">
            <thead class="table-light">
              <tr>
                <th style="min-width:200px">Nama Obat</th>
                <th style="width:90px">Jumlah</th>
                <th>Aturan Pakai</th>
                <th style="width:50px"></th>
              </tr>
            </thead>
            <tbody id="obat-rows">
              <?php
              $existing = $is_edit ? $detail : [];
              $obat_options = '';
              foreach ($obats as $o) {
                  $obat_options .= '<option value="' . $o->id . '">' . htmlspecialchars($o->nama_obat) . ' (' . htmlspecialchars($o->satuan) . ')</option>';
              }
              if (empty($existing)): ?>
              <tr class="obat-row">
                <td>
                  <select name="obat_id[]" class="form-select form-select-sm" required>
                    <option value="">— Pilih Obat —</option>
                    <?= $obat_options ?>
                  </select>
                </td>
                <td><input type="number" name="jumlah[]" class="form-control form-control-sm" value="1" min="1" required></td>
                <td><input type="text" name="aturan_pakai[]" class="form-control form-control-sm" placeholder="Contoh: 3x1 sesudah makan"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del-obat"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php else: ?>
              <?php foreach ($existing as $d): ?>
              <tr class="obat-row">
                <td>
                  <select name="obat_id[]" class="form-select form-select-sm" required>
                    <option value="">— Pilih Obat —</option>
                    <?php foreach ($obats as $o): ?>
                    <option value="<?= $o->id ?>" <?= $d->obat_id == $o->id ? 'selected' : '' ?>>
                      <?= htmlspecialchars($o->nama_obat) ?> (<?= htmlspecialchars($o->satuan) ?>)
                    </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td><input type="number" name="jumlah[]" class="form-control form-control-sm" value="<?= $d->jumlah ?>" min="1" required></td>
                <td><input type="text" name="aturan_pakai[]" class="form-control form-control-sm" value="<?= htmlspecialchars($d->aturan_pakai) ?>" placeholder="Contoh: 3x1 sesudah makan"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del-obat"><i class="bi bi-x"></i></button></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Draft</button>
        <a href="<?= site_url('resep') ?>" class="btn btn-outline-secondary">Batal</a>
      </div>

    <?= form_close() ?>
  </div>
</div>

<script>
const obatOptions = `<?= $obat_options ?>`;

document.getElementById('btn-add-obat').addEventListener('click', function() {
  const row = document.createElement('tr');
  row.className = 'obat-row';
  row.innerHTML = `
    <td><select name="obat_id[]" class="form-select form-select-sm" required>
      <option value="">— Pilih Obat —</option>${obatOptions}
    </select></td>
    <td><input type="number" name="jumlah[]" class="form-control form-control-sm" value="1" min="1" required></td>
    <td><input type="text" name="aturan_pakai[]" class="form-control form-control-sm" placeholder="Contoh: 3x1 sesudah makan"></td>
    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-del-obat"><i class="bi bi-x"></i></button></td>`;
  document.getElementById('obat-rows').appendChild(row);
  row.querySelector('.btn-del-obat').addEventListener('click', delRow);
});

document.querySelectorAll('.btn-del-obat').forEach(btn => btn.addEventListener('click', delRow));

function delRow(e) {
  const rows = document.querySelectorAll('.obat-row');
  if (rows.length > 1) e.currentTarget.closest('tr').remove();
}
</script>
