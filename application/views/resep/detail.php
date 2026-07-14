<?php $this->load->view('layouts/_alert'); ?>

<?php
$status_map = [
  'draft'             => ['secondary', 'Draft'],
  'menunggu_approval' => ['warning',   'Menunggu Approval'],
  'terverifikasi'     => ['success',   'Terverifikasi'],
  'ditolak'           => ['danger',    'Ditolak'],
  'kedaluwarsa'       => ['dark',      'Kedaluwarsa'],
  'gagal_kirim'       => ['danger',    'Gagal Kirim'],
];
[$color, $label] = $status_map[$item->status] ?? ['secondary', $item->status];
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= site_url('resep') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h5 class="mb-0 fw-semibold">Detail Resep</h5>
  <span class="badge bg-<?= $color ?> ms-1"><?= $label ?></span>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Informasi Resep</span>
        <span class="text-muted small font-monospace"><?= htmlspecialchars($item->nomor_resep) ?></span>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-4 text-muted">Tanggal</dt>
          <dd class="col-8"><?= tgl_indonesia($item->tanggal) ?></dd>
          <dt class="col-4 text-muted">Dokter</dt>
          <dd class="col-8"><?= htmlspecialchars($item->nama_dokter) ?> <?= $item->no_sip ? '<small class="text-muted">(' . htmlspecialchars($item->no_sip) . ')</small>' : '' ?></dd>
          <dt class="col-4 text-muted">Pasien</dt>
          <dd class="col-8"><?= htmlspecialchars($item->nama_pasien) ?> <small class="text-muted">[<?= htmlspecialchars($item->no_rm) ?>]</small></dd>
          <?php if ($item->catatan): ?>
          <dt class="col-4 text-muted">Catatan</dt>
          <dd class="col-8"><?= htmlspecialchars($item->catatan) ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">Daftar Obat</div>
      <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nama Obat</th>
              <th class="text-center">Jumlah</th>
              <th>Aturan Pakai</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($detail)): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada item obat.</td></tr>
            <?php else: ?>
            <?php foreach ($detail as $i => $d): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($d->nama_obat) ?> <small class="text-muted"><?= htmlspecialchars($d->satuan) ?></small></td>
              <td class="text-center fw-semibold"><?= $d->jumlah ?></td>
              <td><?= htmlspecialchars($d->aturan_pakai ?: '-') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">Aksi</div>
      <div class="card-body d-grid gap-2">
        <?php if ($item->status === 'draft'): ?>
        <a href="<?= site_url('resep/edit/' . $item->id) ?>" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-pencil me-1"></i>Edit Resep
        </a>
        <?php endif; ?>

        <?= form_open("resep/generate_pdf/{$item->id}") ?>
          <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
            <i class="bi bi-file-earmark-pdf me-1"></i><?= $item->file_pdf ? 'Generate Ulang PDF' : 'Generate PDF' ?>
          </button>
        <?= form_close() ?>

        <?php if ($item->file_pdf): ?>
        <a href="<?= site_url('resep/download_pdf/' . $item->id) ?>" class="btn btn-success btn-sm">
          <i class="bi bi-download me-1"></i>Download PDF
        </a>
        <?php endif; ?>

        <?php if ($item->status === 'draft'): ?>
        <?= form_open("resep/delete/{$item->id}", ['onsubmit' => 'return confirm("Hapus resep ini?")']) ?>
          <button type="submit" class="btn btn-outline-danger btn-sm w-100">
            <i class="bi bi-trash me-1"></i>Hapus
          </button>
        <?= form_close() ?>
        <?php endif; ?>
      </div>
    </div>

    <?php if (in_array($item->status, ['draft', 'gagal_kirim'])): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-send me-1 text-warning"></i>Kirim ke ApprovalSmart
      </div>
      <div class="card-body">
        <?php if (!$item->file_pdf): ?>
          <div class="alert alert-warning py-2 small mb-0">Generate PDF terlebih dahulu.</div>
        <?php else: ?>
        <?php $pdf_url = $pdf_public_url ?? NULL; ?>
        <?= form_open("resep/kirim/{$item->id}") ?>
          <?php if ($pdf_url): ?>
          <div class="alert alert-info py-2 small mb-3">
            <i class="bi bi-paperclip me-1"></i>Lampiran PDF akan disertakan:<br>
            <code class="text-break" style="font-size:.7rem"><?= htmlspecialchars($pdf_url) ?></code>
          </div>
          <?php else: ?>
          <div class="alert alert-warning py-2 small mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>Public Base URL belum diisi di <a href="<?= site_url('settings') ?>" class="alert-link">Settings</a>. PDF tidak akan disertakan sebagai lampiran.
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold mb-1">Berlaku (jam)</label>
            <input type="number" name="expires_in_hours" class="form-control form-control-sm" value="48" min="1" max="720">
          </div>
          <button type="submit" class="btn btn-warning btn-sm w-100 fw-semibold" onclick="return confirm('Kirim resep ini ke ApprovalSmart?')">
            <i class="bi bi-send me-1"></i>Kirim Sekarang
          </button>
        <?= form_close() ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($item->status === 'gagal_kirim'): ?>
    <div class="alert alert-danger py-2 small">
      <i class="bi bi-exclamation-triangle me-1"></i>Pengiriman sebelumnya gagal.
      <a href="<?= site_url('approval_log?module_type=resep&direction=outbound&status=gagal') ?>" class="alert-link">Lihat Log</a>
    </div>
    <?php endif; ?>

    <?php if (in_array($item->status, ['menunggu_approval', 'ditolak', 'kedaluwarsa'])): ?>
    <div class="card border-0 shadow-sm mb-3 border-secondary border-opacity-25">
      <div class="card-header bg-white fw-semibold">
        <i class="bi bi-arrow-clockwise me-1 text-secondary"></i>Kirim Ulang ke ApprovalSmart
      </div>
      <div class="card-body">
        <?php if (!$item->file_pdf): ?>
          <div class="alert alert-warning py-2 small mb-0">Generate PDF terlebih dahulu.</div>
        <?php else: ?>
        <div class="alert alert-secondary py-2 small mb-3">
          <i class="bi bi-info-circle me-1"></i>Akan membuat <strong>approval_id baru</strong> dan menggantikan pengiriman sebelumnya. Status saat ini: <strong><?= $label ?></strong>.
        </div>
        <?php $pdf_url = $pdf_public_url ?? NULL; ?>
        <?= form_open("resep/kirim/{$item->id}") ?>
          <?php if (!$pdf_url): ?>
          <div class="alert alert-warning py-2 small mb-3">
            <i class="bi bi-exclamation-triangle me-1"></i>Public Base URL belum diisi di <a href="<?= site_url('settings') ?>" class="alert-link">Settings</a>. PDF tidak akan disertakan.
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label small fw-semibold mb-1">Berlaku (jam)</label>
            <input type="number" name="expires_in_hours" class="form-control form-control-sm" value="48" min="1" max="720">
          </div>
          <button type="submit" class="btn btn-secondary btn-sm w-100 fw-semibold" onclick="return confirm('Kirim ulang resep ini ke ApprovalSmart? Approval sebelumnya akan digantikan.')">
            <i class="bi bi-arrow-clockwise me-1"></i>Kirim Ulang
          </button>
        <?= form_close() ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($item->approval_id || $item->decided_at): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold">Info Approval</div>
      <div class="card-body small">
        <dl class="row mb-0">
          <dt class="col-5 text-muted">Approval ID</dt>
          <dd class="col-7 font-monospace" style="font-size:.75rem"><?= htmlspecialchars($item->approval_id ?? '-') ?></dd>
          <dt class="col-5 text-muted">Diputuskan</dt>
          <dd class="col-7"><?= $item->decided_at ? date('d/m/Y H:i', strtotime($item->decided_at)) : '-' ?></dd>
          <dt class="col-5 text-muted">Oleh</dt>
          <dd class="col-7"><?= htmlspecialchars($item->decided_by ?? '-') ?></dd>
          <dt class="col-5 text-muted">Catatan</dt>
          <dd class="col-7"><?= htmlspecialchars($item->decision_note ?? '-') ?></dd>
        </dl>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
