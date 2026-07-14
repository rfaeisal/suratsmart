# DATABASE_SCHEMA.md

Schema aktual yang berjalan di production. Semua perubahan dikelola via CI3 migrations di `application/migrations/`.

## Daftar Tabel
`users`, `dokter`, `pasien`, `obat`, `surat`, `resep`, `resep_detail`, `approval_logs`, `settings`, `ci_sessions`

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
  no_sip VARCHAR(50) NULL,
  spesialisasi VARCHAR(100) NULL,
  no_telp VARCHAR(30) NULL,
  approvalsmart_user_id VARCHAR(50) NULL,  -- [RESMI] user ID di ApprovalSmart (mis. "USR001"), diisi admin setelah konfirmasi tim ApprovalSmart
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE pasien (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  no_rm VARCHAR(30) NOT NULL UNIQUE,
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
  satuan VARCHAR(30) NULL,
  stok INT NOT NULL DEFAULT 0,
  harga DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE surat (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nomor_surat VARCHAR(50) NOT NULL UNIQUE,
  jenis_surat VARCHAR(100) NULL,
  dokter_id INT UNSIGNED NOT NULL,
  pasien_id INT UNSIGNED NOT NULL,
  isi TEXT NULL,
  file_pdf VARCHAR(255) NULL,
  file_pdf_public_url VARCHAR(500) NULL,
  status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
  source_ref VARCHAR(64) NULL UNIQUE,      -- [RESMI] format: 'surat-{approval_id}', diupdate setiap kirim
  approval_id CHAR(36) NULL UNIQUE,        -- [RESMI] UUID v4, unik per pengajuan
  approver_user_id VARCHAR(50) NULL,       -- [RESMI] dokter.approvalsmart_user_id saat kirim
  expires_in_hours SMALLINT UNSIGNED NULL,
  decided_by VARCHAR(50) NULL,             -- [RESMI] dari callback
  decided_at DATETIME NULL,                -- [RESMI] dari callback
  channel VARCHAR(20) NULL,                -- [RESMI] selalu 'telegram'
  decision_note TEXT NULL,                 -- [RESMI] dari callback field `note`
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
  file_pdf_public_url VARCHAR(500) NULL,
  status ENUM('draft','menunggu_approval','terverifikasi','ditolak','kedaluwarsa','gagal_kirim') NOT NULL DEFAULT 'draft',
  source_ref VARCHAR(64) NULL UNIQUE,      -- [RESMI] format: 'resep-{approval_id}', diupdate setiap kirim
  approval_id CHAR(36) NULL UNIQUE,        -- [RESMI] UUID v4, unik per pengajuan
  approver_user_id VARCHAR(50) NULL,
  expires_in_hours SMALLINT UNSIGNED NULL,
  decided_by VARCHAR(50) NULL,
  decided_at DATETIME NULL,
  channel VARCHAR(20) NULL,
  decision_note TEXT NULL,
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
  aturan_pakai VARCHAR(255) NULL,
  FOREIGN KEY (resep_id) REFERENCES resep(id) ON DELETE CASCADE,
  FOREIGN KEY (obat_id) REFERENCES obat(id)
);

CREATE TABLE approval_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  source_ref VARCHAR(64) NOT NULL,
  approval_id CHAR(36) NULL,
  module_type ENUM('surat','resep') NOT NULL,
  direction ENUM('outbound','inbound') NOT NULL,
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

-- Dipakai CI3 database session driver (production/Railway)
CREATE TABLE ci_sessions (
  id VARCHAR(128) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  timestamp INT(10) UNSIGNED DEFAULT 0 NOT NULL,
  data BLOB NOT NULL,
  PRIMARY KEY (id),
  KEY ci_sessions_timestamp (timestamp)
);
```

## Settings Keys

| Key | Keterangan |
|---|---|
| `approvalsmart_base_url` | Base URL ApprovalSmart, mis. `https://approval.lmssmart.my.id` |
| `approvalsmart_hmac_key_id` | ID kunci HMAC, mis. `k1` |
| `approvalsmart_hmac_secret` | Secret 64 karakter hex untuk signing outbound |
| `approvalsmart_legacy_token` | Bearer token yang dikirim ApprovalSmart saat callback ke kita |
| `public_base_url` | Base URL publik app ini, mis. `https://xxxx.up.railway.app` — dipakai untuk membangun URL attachment PDF |

## Catatan

- `source_ref` di `surat`/`resep` di-update setiap kali dokumen dikirim (tidak tetap sejak creation). Format: `surat-{approval_id}` atau `resep-{approval_id}` — unik per pengajuan, dipakai ApprovalSmart untuk routing callback PATCH.
- `dokter.approvalsmart_user_id` diisi admin setelah konfirmasi mapping dari tim ApprovalSmart. Field ini yang dikirim sebagai `approver_user_id` di payload outbound.
- Semua perubahan schema lewat CI3 migrations (`application/migrations/`), bukan edit tabel langsung.
