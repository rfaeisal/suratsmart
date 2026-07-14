<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Data Obat</h5>
  <a href="<?= site_url('obat/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Tambah Obat
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nama Obat</th>
            <th>Satuan</th>
            <th class="text-end">Stok</th>
            <th class="text-end">Harga</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($obats)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data obat.</td></tr>
          <?php else: ?>
          <?php foreach ($obats as $i => $o): ?>
          <tr>
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($o->nama_obat) ?></td>
            <td><?= htmlspecialchars($o->satuan ?: '-') ?></td>
            <td class="text-end">
              <span class="<?= $o->stok <= 0 ? 'text-danger fw-semibold' : '' ?>"><?= number_format($o->stok) ?></span>
            </td>
            <td class="text-end">Rp <?= number_format($o->harga, 0, ',', '.') ?></td>
            <td class="text-end">
              <a href="<?= site_url("obat/edit/{$o->id}") ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <?= form_open("obat/delete/{$o->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus obat ini?")']) ?>
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              <?= form_close() ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
