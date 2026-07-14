<?php $this->load->view('layouts/_alert'); ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0 fw-semibold">Daftar User</h5>
  <a href="<?= site_url('user/create') ?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Tambah User
  </a>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Nama</th>
            <th>Role</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data user.</td></tr>
          <?php else: ?>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td><code><?= htmlspecialchars($u->username) ?></code></td>
            <td><?= htmlspecialchars($u->nama) ?></td>
            <td>
              <?php $role_color = ['admin' => 'danger', 'dokter' => 'primary', 'staff' => 'secondary']; ?>
              <span class="badge bg-<?= $role_color[$u->role] ?? 'secondary' ?>"><?= $u->role ?></span>
            </td>
            <td>
              <?php if ($u->is_active): ?>
                <span class="badge bg-success">Aktif</span>
              <?php else: ?>
                <span class="badge bg-secondary">Non-aktif</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small"><?= date('d/m/Y', strtotime($u->created_at)) ?></td>
            <td class="text-end">
              <a href="<?= site_url("user/edit/{$u->id}") ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i>
              </a>
              <?php if ($u->id != $this->session->userdata('user_id')): ?>
              <?= form_open("user/delete/{$u->id}", ['class' => 'd-inline', 'onsubmit' => 'return confirm("Hapus user ini?")']) ?>
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
