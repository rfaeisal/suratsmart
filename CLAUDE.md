# CLAUDE.md — Panduan untuk Claude Code

Dokumen ini adalah instruksi utama yang dibaca Claude Code saat bekerja di project ini.
Baca juga `docs/PROJECT_SPEC.md`, `docs/DATABASE_SCHEMA.md`, `docs/API_INTEGRATION.md`, dan `docs/DEPLOYMENT.md` sebelum menulis kode.

## 1. Tentang Project

**Nama:** SuratSmart
**Tujuan:** Aplikasi CodeIgniter 3 untuk menguji integrasi dengan sistem eksternal **ApprovalSmart**. Fokus utama: validasi alur kirim dokumen, callback, dan logging approval.
**Status:** Semua fase selesai dan berjalan di production (Railway).

**Alur bisnis inti:**
```
Draft → Generate PDF → Kirim (HMAC) → ApprovalSmart → Telegram Approver → PATCH Callback → Terverifikasi → Download PDF
```

## 2. Integrasi ApprovalSmart (RESMI, sudah diimplementasi)

Spesifikasi resmi ada di `docs/APPROVALSMART_OFFICIAL.md`, diringkas di `docs/API_INTEGRATION.md`.

**Poin kunci yang sudah pasti:**
- Outbound: `POST /api/legacy/approvals`, auth **HMAC-SHA256** (key_id=`k1`, format signature: `sha256=hash_hmac('sha256', "{timestamp}.{body}", secret)`)
- Inbound callback: `PATCH /approvals/{source_ref}`, auth **Bearer token**, header `Idempotency-Key`
- `source_ref` format: `surat-{approval_id}` / `resep-{approval_id}` — **unik per pengajuan**, di-update di DB setiap kali dokumen berhasil dikirim
- `approver_user_id` diambil dari `dokter.approvalsmart_user_id` (kolom terpisah, diisi admin)
- Approver merespons via **Telegram**
- `attachment.url` harus URL publik yang bisa diakses internet (pakai Railway domain via `public_base_url` setting)

**Aturan wajib untuk Claude Code:**
- Jangan hardcode `HMAC_SECRET`/`LEGACY_API_TOKEN` — baca dari `settings` table (prioritas) atau env var via `application/config/approvalsmart.php`
- Endpoint outbound/inbound harus idempoten — lihat `docs/API_INTEGRATION.md` §4
- Jangan simpan header `X-Signature`/secret di `approval_logs`
- nginx wajib punya `fastcgi_param HTTP_AUTHORIZATION $http_authorization;` agar Bearer token terbaca PHP-FPM

## 3. Tech Stack

- **Framework:** CodeIgniter 3.1.13 (MVC klasik, bukan CI4)
- **Database:** MySQL/MariaDB — nama DB lokal `dbsuratsmart`, production `railway`
- **HTTP client ke ApprovalSmart:** `application/libraries/Approvalbridge.php`
- **PDF:** mPDF 8.x via Composer (`vendor/`)
- **Frontend:** CI3 views server-side + Bootstrap 5
- **Session:** CI3 database driver di production (`ci_sessions` table), file driver di lokal
- **Deployment:** Railway — Docker `php:8.2-fpm` + nginx, env vars untuk semua config sensitif

## 4. Struktur Folder Aktual

```
application/
  config/
    approvalsmart.php        # baca dari env var / settings table, tidak berisi nilai asli
    database.php             # baca dari MYSQLHOST/MYSQLUSER/... env var
    config.php               # base_url dinamis via HTTP_X_FORWARDED_HOST
  controllers/
    Auth.php
    Dashboard.php
    User.php
    Dokter.php
    Pasien.php
    Obat.php
    Surat.php                # kirim() → POST ke ApprovalSmart
    Resep.php                # kirim() → POST ke ApprovalSmart
    Approvals.php            # callback() → terima PATCH dari ApprovalSmart
    Approval_log.php         # UI halaman log
    Api.php                  # GET /api/approval/log (JSON)
    Settings.php
    Migrate.php
  models/
    User_model.php
    Dokter_model.php
    Pasien_model.php
    Obat_model.php
    Surat_model.php          # get_by_source_ref(), update_approval_result()
    Resep_model.php          # get_by_source_ref(), update_approval_result()
    Resep_detail_model.php
    Approval_log_model.php   # log_outbound(), log_inbound()
    Settings_model.php
  libraries/
    Approvalbridge.php       # send(), get_legacy_token() — HMAC signing + curl
    Pdf_generator.php        # generate_surat(), generate_resep(), download()
  migrations/
    001_create_tables.php
    002_cleanup_approver_setting.php
    003_add_approvalsmart_user_id_to_dokter.php
    004_create_ci_sessions.php
  views/
    layouts/                 # _header, _sidebar, _footer, _alert
    auth/, dashboard/, user/, dokter/, pasien/, obat/
    surat/, resep/           # index, form, detail
    approval_log/            # index, detail
    settings/
docs/
  PROJECT_SPEC.md
  DATABASE_SCHEMA.md
  API_INTEGRATION.md
  TASKS.md
  DEPLOYMENT.md
  APPROVALSMART_OFFICIAL.md
Dockerfile                   # php:8.2-fpm + nginx
docker-entrypoint.sh         # buat ci_sessions via PDO, tulis nginx config dinamis
```

## 5. Konvensi Coding

- Ikuti konvensi CI3: Controller/Model PascalCase, class name match filename.
- Query DB pakai Query Builder CI3 (`$this->db->...`), raw SQL hanya di migration.
- Endpoint API (JSON) return format konsisten:
  ```json
  { "status": "success|error", "message": "...", "data": {} }
  ```
- Semua request/response ke/dari ApprovalSmart wajib disimpan di `approval_logs` — tujuan app adalah test/validasi, log harus lengkap.
- Gunakan `form_validation` CI3 di semua controller untuk validasi input.
- Config ApprovalSmart tidak boleh hardcode — gunakan `Approvalbridge::_setting()` yang baca dari DB settings table (prioritas) lalu fallback ke env var via config file.
- `application/config/approvalsmart.php` sudah tidak di-gitignore (isinya hanya baca env var, tidak ada nilai asli).

## 6. Hal Penting saat Menambah Fitur

- **Kirim dokumen:** `source_ref` selalu di-generate baru dari `approval_id` (`surat-{uuid}` / `resep-{uuid}`). Update kolom `source_ref` di DB hanya jika kirim berhasil (HTTP 202/409).
- **Callback:** `Approvals::callback()` tidak extend `Auth_Controller`, tidak perlu session/login.
- **Migrasi baru:** buat file `NNN_nama_migrasi.php`, nama class CI3 harus match nama file (tanpa prefix angka), update `$config['migration_version']` di `application/config/migration.php`, lalu akses `/migrate`.
- **Deploy ke Railway:** push ke GitHub → Railway auto-build. Migrasi baru perlu akses `/migrate` manual setelah deploy.
- **PDF publik:** URL attachment dibangun dari setting `public_base_url` + path file lokal. Pastikan setting ini terisi Railway domain di production.
