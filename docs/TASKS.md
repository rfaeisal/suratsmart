# TASKS.md — Checklist Implementasi

Semua fase sudah selesai dan berjalan di production (Railway).

---

## Fase 0 — Setup Project ✅
- [x] Install CodeIgniter 3 base project
- [x] Setup koneksi database (`application/config/database.php`)
- [x] Buat database & jalankan DDL dari `docs/DATABASE_SCHEMA.md`
- [x] Setup config terpisah untuk kredensial (`application/config/approvalsmart.php`, gitignored jika berisi nilai asli)
- [x] Setup base layout view (header/footer/sidebar, Bootstrap)
- [x] Setup autentikasi session dasar (Login/Logout)

## Fase 1 — Modul Master Data ✅
- [x] Modul User (CRUD + role)
- [x] Modul Dokter (CRUD) — termasuk kolom `approvalsmart_user_id` untuk mapping ke user ApprovalSmart
- [x] Modul Pasien (CRUD)
- [x] Modul Obat (CRUD)
- [x] Modul Settings (form untuk konfigurasi ApprovalSmart + `public_base_url` untuk URL PDF publik)

## Fase 2 — Modul Transaksi ✅
- [x] Modul Surat: create draft, edit, generate PDF
- [x] Modul Resep + Resep Detail: create draft, tambah item obat, generate PDF
- [x] Status default `draft` berjalan benar
- [x] Generate PDF (mPDF 8.x) berhasil

## Fase 3 — Integrasi ApprovalSmart (Outbound) ✅
- [x] `application/libraries/Approvalbridge.php` — wrapper HTTP client dengan HMAC-SHA256 signing
- [x] Generate UUID v4 untuk `approval_id`
- [x] Tombol "Kirim" di Surat & Resep → `POST /api/legacy/approvals`
- [x] `source_ref` unik per pengajuan: format `surat-{approval_id}` / `resep-{approval_id}` (bukan per dokumen)
- [x] Simpan `approval_id`, `source_ref`, `approver_user_id`, `expires_in_hours` ke tabel surat/resep
- [x] Simpan request & response ke `approval_logs` (direction=outbound)
- [x] PDF publik via `public_base_url` setting → disertakan sebagai `attachment.url`
- [x] Update status: `202`→`menunggu_approval`, lainnya→`gagal_kirim`

## Fase 4 — Integrasi ApprovalSmart (Inbound / Callback) ✅
- [x] Route `$route['approvals/(:any)'] = 'approvals/callback/$1';`
- [x] Controller `Approvals.php::callback($source_ref)` untuk terima PATCH
- [x] Verifikasi `Authorization: Bearer {LEGACY_API_TOKEN}` → 401 jika salah
- [x] Baca `Idempotency-Key`, cek duplikat → 409 jika sudah diproses
- [x] Cari dokumen via `source_ref` → return 200 jika tidak ketemu (supaya tidak di-retry)
- [x] Update status (`terverifikasi`/`ditolak`/`kedaluwarsa`) + `decided_by`, `decided_at`, `decision_note`
- [x] Generate PDF final saat `approved`
- [x] Simpan ke `approval_logs` (direction=inbound)
- [x] nginx dikonfigurasi teruskan header `Authorization` ke PHP-FPM (`fastcgi_param HTTP_AUTHORIZATION`)

## Fase 5 — Approval Log & API ✅
- [x] Endpoint `GET /api/approval/log` (JSON)
- [x] Halaman "Approval Log" di UI (list + filter module_type/direction/status/tanggal)
- [x] Halaman detail log (request & response payload)

## Fase 6 — Hardening & Testing ✅
- [x] Validasi input semua form (CI3 form_validation)
- [x] Error handling timeout/gagal koneksi ke ApprovalSmart
- [x] Log lengkap tanpa menyimpan HMAC secret
- [x] Uji end-to-end: Draft → Kirim → Callback → Terverifikasi → Download PDF ✅

## Fase 7 — Deployment Railway ✅
- [x] Docker: `php:8.2-fpm` + nginx (bukan Apache)
- [x] Entrypoint dinamis: port dari env `PORT`, nginx config ditulis saat startup
- [x] Entrypoint buat `ci_sessions` via PDO sebelum server start (bootstrap DB)
- [x] CI3 session driver: `database` di production (Railway), `files` di lokal
- [x] Semua config via env var: `MYSQLHOST`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`, `MYSQLPORT`
- [x] Config ApprovalSmart via env var atau Settings UI (bukan hardcode)
- [x] Migrasi dijalankan via `/migrate` setelah deploy pertama

---

## Backlog / Opsional
- [ ] Role-based permission lebih detail (dokter hanya lihat dokumennya sendiri)
- [ ] Notifikasi (email/WA) saat status berubah
- [ ] Upload PDF ke cloud storage (S3/GCS) agar `attachment.url` tidak bergantung Railway URL
