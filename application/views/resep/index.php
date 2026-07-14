<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Daftar Resep</h5>
  <a href="<?= site_url('resep/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Buat Resep
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>No. Resep</th>
            <th>Tanggal</th>
            <th>Dokter</th>
            <th>Pasien</th>
            <th>Status</th>
            <th>PDF</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reseps)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada resep.</td></tr>
          <?php else: ?>
          <?php foreach ($reseps as $r): ?>
          <?php
            $status_map = [
              'draft'             => ['secondary', 'Draft'],
              'menunggu_approval' => ['warning',   'Menunggu Approval'],
              'terverifikasi'     => ['success',   'Terverifikasi'],
              'ditolak'           => ['danger',    'Ditolak'],
              'kedaluwarsa'       => ['dark',      'Kedaluwarsa'],
              'gagal_kirim'       => ['danger',    'Gagal Kirim'],
            ];
            [$color, $label] = $status_map[$r->status] ?? ['secondary', $r->status];
          ?>
          <tr>
            <td><a href="<?= site_url('resep/detail/' . $r->id) ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($r->nomor_resep) ?></a></td>
            <td><?= date('d/m/Y', strtotime($r->tanggal)) ?></td>
            <td><?= htmlspecialchars($r->nama_dokter) ?></td>
            <td><?= htmlspecialchars($r->nama_pasien) ?></td>
            <td><span class="badge bg-<?= $color ?>"><?= $label ?></span></td>
            <td>
              <?php if ($r->file_pdf): ?>
                <a href="<?= site_url('resep/download_pdf/' . $r->id) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">
                  <i class="bi bi-file-pdf"></i>
                </a>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a href="<?= site_url('resep/detail/' . $r->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
              <?php if ($r->status === 'draft'): ?>
              <a href="<?= site_url('resep/edit/' . $r->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <?= form_open("resep/delete/{$r->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus resep ini?")']) ?>
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
              <?= form_close() ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
