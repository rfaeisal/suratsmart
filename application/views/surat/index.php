<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Daftar Surat</h5>
  <a href="<?= site_url('surat/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Buat Surat
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>No. Surat</th>
            <th>Jenis</th>
            <th>Dokter</th>
            <th>Pasien</th>
            <th>Status</th>
            <th>PDF</th>
            <th>Dibuat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($surats)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Belum ada surat.</td></tr>
          <?php else: ?>
          <?php foreach ($surats as $s): ?>
          <?php
            $status_map = [
              'draft'              => ['secondary', 'Draft'],
              'menunggu_approval'  => ['warning',   'Menunggu Approval'],
              'terverifikasi'      => ['success',   'Terverifikasi'],
              'ditolak'            => ['danger',    'Ditolak'],
              'kedaluwarsa'        => ['dark',      'Kedaluwarsa'],
              'gagal_kirim'        => ['danger',    'Gagal Kirim'],
            ];
            [$color, $label] = $status_map[$s->status] ?? ['secondary', $s->status];
          ?>
          <tr>
            <td><a href="<?= site_url('surat/detail/' . $s->id) ?>" class="fw-semibold text-decoration-none"><?= htmlspecialchars($s->nomor_surat) ?></a></td>
            <td><?= htmlspecialchars($s->jenis_surat) ?></td>
            <td><?= htmlspecialchars($s->nama_dokter) ?></td>
            <td><?= htmlspecialchars($s->nama_pasien) ?></td>
            <td><span class="badge bg-<?= $color ?>"><?= $label ?></span></td>
            <td>
              <?php if ($s->file_pdf): ?>
                <a href="<?= site_url('surat/download_pdf/' . $s->id) ?>" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
                  <i class="bi bi-file-pdf"></i>
                </a>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= date('d/m/Y', strtotime($s->created_at)) ?></td>
            <td class="text-end">
              <a href="<?= site_url('surat/detail/' . $s->id) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
              <?php if ($s->status === 'draft'): ?>
              <a href="<?= site_url('surat/edit/' . $s->id) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
              <?= form_open("surat/delete/{$s->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus surat ini?")']) ?>
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
