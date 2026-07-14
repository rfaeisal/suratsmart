# CLAUDE.md — Panduan untuk Claude Code

Dokumen ini adalah instruksi utama yang dibaca Claude Code saat bekerja di project ini.
Baca juga `docs/PROJECT_SPEC.md`, `docs/DATABASE_SCHEMA.md`, dan `docs/API_INTEGRATION.md` sebelum menulis kode.

## 1. Tentang Project

**Nama:** SuratSmart
**Tujuan:** Aplikasi CodeIgniter 3 sederhana yang dibuat **khusus untuk menguji integrasi** dengan sistem eksternal bernama **ApprovalSmart**. SuratSmart bukan aplikasi produksi — fokus utamanya adalah memvalidasi alur kirim dokumen, callback, dan logging approval.

**Alur bisnis inti:**
```
Draft -> Kirim (lampiran PDF) -> ApprovalSmart -> PATCH Callback -> Terverifikasi -> Cetak PDF Final
```

## 2. Status Dokumen Sumber (PENTING)

Spesifikasi integrasi ApprovalSmart **sudah resmi**, ada di `docs/APPROVALSMART_OFFICIAL.md` (dokumen asli dari tim ApprovalSmart) dan diringkas teknis di `docs/API_INTEGRATION.md`. Ini menggantikan asumsi awal — pakai dokumen ini sebagai rujukan utama untuk implementasi integrasi.

**Poin kunci dari spek resmi:**
- Outbound: `POST /api/legacy/approvals`, auth pakai **HMAC-SHA256** (bukan API key sederhana).
- Inbound (callback): `PATCH /approvals/{source_ref}`, auth pakai **Bearer token + Idempotency-Key**.
- Approver menerima & merespons via **Telegram**, bukan UI web ApprovalSmart.
- **Lampiran PDF wajib punya URL publik** yang bisa diunduh dari internet — ini butuh keputusan arsitektur (storage publik / tunnel) sebelum implementasi Fase 3 di `docs/TASKS.md`.

**Aturan kerja untuk Claude Code:**
- Jangan hardcode `HMAC_SECRET`/`LEGACY_API_TOKEN` — taruh di `application/config/approvalsmart.php`, jangan commit nilai asli ke git.
- Endpoint outbound/inbound HARUS idempoten (lihat aturan `approval_id`/`Idempotency-Key` di `docs/API_INTEGRATION.md` §4).
- Bagian yang masih **[ASUMSI]** hanya kolom data master (nama pasien, alamat, dll di `docs/DATABASE_SCHEMA.md`) — bukan lagi soal integrasi ApprovalSmart.

## 3. Tech Stack

- **Framework:** CodeIgniter 3.x (native PHP, MVC klasik — bukan CI4)
- **Database:** MySQL/MariaDB
- **HTTP client ke ApprovalSmart:** `curl` via CI3 library (buat `application/libraries/Approvalsmart_client.php`)
- **PDF:** gunakan library ringan yang kompatibel PHP lama, misalnya `mpdf` atau `tcpdf` (taruh di `application/third_party/`)
- **Frontend:** server-side rendered CI3 views (Bootstrap boleh dipakai untuk cepat)

## 4. Struktur Folder yang Diharapkan

```
application/
  config/
    approvalsmart.php        # base_url, api_key, timeout — semua [ASUMSI] dari sini
  controllers/
    Auth.php
    User.php
    Dokter.php
    Pasien.php
    Obat.php
    Surat.php
    Resep.php
    Approval.php             # POST send, PATCH callback receiver, GET log
    Settings.php
  models/
    User_model.php
    Dokter_model.php
    Pasien_model.php
    Obat_model.php
    Surat_model.php
    Resep_model.php
    Resep_detail_model.php
    Approval_log_model.php
    Settings_model.php
  libraries/
    Approvalsmart_client.php # wrapper HTTP client outbound ke ApprovalSmart
  views/
    ...  (per modul: index, form, detail)
docs/
  PROJECT_SPEC.md
  DATABASE_SCHEMA.md
  API_INTEGRATION.md
  TASKS.md
```

## 5. Konvensi Coding

- Ikuti konvensi CI3 standar: nama Controller/Model PascalCase file, class match filename.
- Query DB pakai Query Builder CI3 (`$this->db->...`), hindari raw SQL kecuali migration.
- Semua endpoint API (`Approval.php`) return JSON dengan format konsisten:
  ```json
  { "status": "success|error", "message": "...", "data": {} }
  ```
- Simpan setiap request/response ke/dari ApprovalSmart di tabel `approval_logs` (lihat `docs/DATABASE_SCHEMA.md`) — ini penting karena tujuan app adalah **test/validasi**, jadi log harus lengkap untuk debugging.
- Gunakan CI3 `form_validation` untuk validasi input di tiap controller.
- Jangan simpan API key ApprovalSmart di kode — taruh di `application/config/approvalsmart.php` dan **jangan commit** file ini kalau berisi key asli (tambahkan ke `.gitignore`, sediakan `approvalsmart.php.example`).

## 6. Urutan Kerja yang Disarankan

Ikuti `docs/TASKS.md` secara berurutan (schema dulu, baru CRUD modul, baru integrasi). Jangan loncat ke integrasi ApprovalSmart sebelum modul dasar (Dokter, Pasien, Obat, Resep, Surat) jalan, supaya ada data nyata untuk dites.

## 7. Yang HARUS ditanyakan ke user sebelum lanjut coding

Claude Code sebaiknya konfirmasi ke user dulu untuk hal-hal berikut sebelum implementasi final (karena masih asumsi):
1. Base URL & metode auth resmi ApprovalSmart (API key header? OAuth? Basic Auth?)
2. Format payload resmi `POST /approval/send` (multipart file atau base64 dalam JSON?)
3. Struktur payload PATCH callback yang dikirim ApprovalSmart ke kita
4. Apakah `source_ref` adalah ID surat/resep kita, atau ID yang di-generate ApprovalSmart
