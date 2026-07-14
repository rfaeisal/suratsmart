<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Data Pasien</h5>
  <a href="<?= site_url('pasien/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Tambah Pasien
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>No. RM</th>
            <th>Nama</th>
            <th>Tgl. Lahir</th>
            <th>JK</th>
            <th>No. Telp</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pasiens)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pasien.</td></tr>
          <?php else: ?>
          <?php foreach ($pasiens as $i => $p): ?>
          <tr>
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($p->no_rm) ?></span></td>
            <td><?= htmlspecialchars($p->nama) ?></td>
            <td><?= $p->tanggal_lahir ? date('d/m/Y', strtotime($p->tanggal_lahir)) : '-' ?></td>
            <td><?= $p->jenis_kelamin === 'L' ? 'Laki-laki' : ($p->jenis_kelamin === 'P' ? 'Perempuan' : '-') ?></td>
            <td><?= htmlspecialchars($p->no_telp ?: '-') ?></td>
            <td class="text-end">
              <a href="<?= site_url("pasien/edit/{$p->id}") ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <?= form_open("pasien/delete/{$p->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus pasien ini?")']) ?>
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
