# DATABASE_SCHEMA.md

> Sumber asli (`DATABASE.md`) hanya menyebutkan nama tabel, tanpa kolom.
> Kolom dasar (nama, alamat, dll) masih **[ASUMSI]** berdasarkan konteks modul (klinik: dokter, pasien, resep).
> Kolom terkait integrasi ApprovalSmart (approval_id, approver_user_id, dst.) sudah **RESMI**, mengikuti `docs/API_INTEGRATION.md` / `docs/APPROVALSMART_OFFICIAL.md`.
> Sesuaikan kolom [ASUMSI] dengan Claude Code begitu ada kebutuhan field tambahan dari user.

## Daftar Tabel
`users`, `dokter`, `pasien`, `obat`, `surat`, `resep`, `resep_detail`, `approval_logs`, `settings`

---

## DDL (MySQL)

```sql
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role ENUM('admin','dokter','staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE dokter (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  nama VARCHAR(100) NOT NULL,
  no_sip VARCHAR(50) NULL,          -- Surat Izin Praktik
  spesialisasi VARCHAR(100) NULL,
  no_telp VARCHAR(30) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE pasien (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  no_rm VARCHAR(30) NOT NULL UNIQUE,  -- nomor rekam medis
  nama VARCHAR(100) NOT NULL,
  tanggal_lahir DATE NULL,
  jenis_kelamin ENUM('L','P') NULL,
  alamat TEXT NULL,
  no_telp VARCHAR(30) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE obat (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_obat VARCHAR(150) NOT NULL,
  satuan VARCHAR(30) NULL,          -- tablet, botol, dll
  stok INT NOT NULL DEFAULT 0,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE surat (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_surat VARCHAR(50) NOT NULL UNIQUE,
  jenis_surat VARCHAR(100) NULL,     -- mis. "Surat Keterangan Sakit"
  dokter_id INT UNSIGNED NOT NULL,
  pasien_id INT UNSIGNED NOT NULL,
  isi TEXT NULL,
  file_pdf VARCHAR(255) NULL,        -- path lokal PDF
  file_pdf_public_url VARCHAR(500) NULL, -- URL publik (S3/GCS/tunnel) dipakai sbg attachment.url ke ApprovalSmart
  status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
  source_ref VARCHAR(64) NULL UNIQUE,     -- [RESMI] max 64 karakter sesuai spek ApprovalSmart
  approval_id CHAR(36) NULL UNIQUE,       -- [RESMI] UUID v4 yang dikirim ke ApprovalSmart
  approver_user_id VARCHAR(50) NULL,      -- [RESMI] user id approver di ApprovalSmart
  expires_in_hours SMALLINT UNSIGNED NULL, -- [RESMI] 1-720
  decided_by VARCHAR(50) NULL,            -- [RESMI] dari callback
  decided_at DATETIME NULL,               -- [RESMI] dari callback
  channel VARCHAR(20) NULL,               -- [RESMI] selalu 'telegram'
  decision_note TEXT NULL,                -- [RESMI] dari callback field `note`
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (dokter_id) REFERENCES dokter(id),
  FOREIGN KEY (pasien_id) REFERENCES pasien(id)
);

CREATE TABLE resep (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_resep VARCHAR(50) NOT NULL UNIQUE,
  dokter_id INT UNSIGNED NOT NULL,
  pasien_id INT UNSIGNED NOT NULL,
  tanggal DATE NOT NULL,
  catatan TEXT NULL,
  file_pdf VARCHAR(255) NULL,
  file_pdf_public_url VARCHAR(500) NULL, -- URL publik dipakai sbg attachment.url ke ApprovalSmart
  status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
  source_ref VARCHAR(64) NULL UNIQUE,     -- [RESMI] max 64 karakter
  approval_id CHAR(36) NULL UNIQUE,       -- [RESMI] UUID v4
  approver_user_id VARCHAR(50) NULL,      -- [RESMI]
  expires_in_hours SMALLINT UNSIGNED NULL, -- [RESMI] 1-720
  decided_by VARCHAR(50) NULL,            -- [RESMI]
  decided_at DATETIME NULL,               -- [RESMI]
  channel VARCHAR(20) NULL,               -- [RESMI] selalu 'telegram'
  decision_note TEXT NULL,                -- [RESMI]
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (dokter_id) REFERENCES dokter(id),
  FOREIGN KEY (pasien_id) REFERENCES pasien(id)
);

CREATE TABLE resep_detail (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  resep_id INT UNSIGNED NOT NULL,
  obat_id INT UNSIGNED NOT NULL,
  jumlah INT NOT NULL DEFAULT 1,
  aturan_pakai VARCHAR(255) NULL,   -- mis. "3x1 sehari sesudah makan"
  FOREIGN KEY (resep_id) REFERENCES resep(id) ON DELETE CASCADE,
  FOREIGN KEY (obat_id) REFERENCES obat(id)
);

CREATE TABLE approval_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_ref VARCHAR(64) NOT NULL,
  approval_id CHAR(36) NULL,                      -- [RESMI] dipakai jg sbg Idempotency-Key
  module_type ENUM('surat','resep') NOT NULL,
  direction ENUM('outbound','inbound') NOT NULL,  -- outbound=kirim ke ApprovalSmart, inbound=callback masuk
  endpoint VARCHAR(255) NULL,
  http_method VARCHAR(10) NULL,
  request_payload TEXT NULL,   -- JANGAN simpan header X-Signature/secret di sini
  response_payload TEXT NULL,
  http_status INT NULL,
  status ENUM('sukses','gagal') NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT NULL,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Catatan

- `source_ref` di `surat`/`resep` adalah kunci penghubung ke ApprovalSmart — dipakai untuk mencocokkan callback PATCH yang masuk.
- `approval_logs.direction` sengaja dibuat eksplisit (outbound/inbound) supaya gampang membedakan log kirim vs log callback saat debugging integrasi.
- Kalau ternyata `dokter`/`pasien` perlu relasi many-to-many atau field tambahan (mis. NIK, alamat lengkap), tambahkan lewat migration CI3 (`application/migrations/`), jangan edit tabel langsung tanpa versi.
