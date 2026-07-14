<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Data Dokter</h5>
  <a href="<?= site_url('dokter/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Tambah Dokter
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>No. SIP</th>
            <th>Spesialisasi</th>
            <th>No. Telp</th>
            <th>Akun User</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($dokters)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data dokter.</td></tr>
          <?php else: ?>
          <?php foreach ($dokters as $i => $d): ?>
          <tr>
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($d->nama) ?></td>
            <td><?= htmlspecialchars($d->no_sip ?: '-') ?></td>
            <td><?= htmlspecialchars($d->spesialisasi ?: '-') ?></td>
            <td><?= htmlspecialchars($d->no_telp ?: '-') ?></td>
            <td><?= $d->username ? '<code>' . htmlspecialchars($d->username) . '</code>' : '<span class="text-muted">-</span>' ?></td>
            <td class="text-end">
              <a href="<?= site_url("dokter/edit/{$d->id}") ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <?= form_open("dokter/delete/{$d->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus dokter ini?")']) ?>
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
