# TASKS.md — Checklist Implementasi

Kerjakan berurutan. Jangan mulai Fase 3 (integrasi ApprovalSmart) sebelum Fase 1 & 2 selesai dan bisa diuji dengan data nyata.

## Fase 0 — Setup Project
- [ ] Install CodeIgniter 3 base project
- [ ] Setup koneksi database (`application/config/database.php`)
- [ ] Buat database & jalankan DDL dari `docs/DATABASE_SCHEMA.md`
- [ ] Setup `.env`/config terpisah untuk kredensial (jangan commit ke git)
- [ ] Setup base layout view (header/footer/sidebar sederhana)
- [ ] Setup autentikasi session dasar (Login/Logout)

## Fase 1 — Modul Master Data
- [ ] Modul User (CRUD + role)
- [ ] Modul Dokter (CRUD)
- [ ] Modul Pasien (CRUD)
- [ ] Modul Obat (CRUD)
- [ ] Modul Settings (form untuk isi `approvalsmart_base_url`, `approvalsmart_api_key`, dll dari tabel `settings`)

## Fase 2 — Modul Transaksi (belum ada integrasi)
- [ ] Modul Surat: create draft, edit, generate PDF lampiran (belum kirim ke ApprovalSmart)
- [ ] Modul Resep + Resep Detail: create draft, tambah item obat, generate PDF lampiran
- [ ] Pastikan status default `draft` berjalan benar
- [ ] Uji generate PDF berhasil dibuka & rapi

## Fase 3 — Integrasi ApprovalSmart (Outbound)
- [ ] Minta ke tim ApprovalSmart: `APPROVAL_URL`, `HMAC_KEY_ID`, `HMAC_SECRET`, `LEGACY_API_TOKEN`, dan daftar `approver_user_id` yang valid untuk testing
- [ ] **Putuskan solusi hosting lampiran publik** (S3/GCS, atau ngrok/cloudflared untuk dev lokal) — lihat `docs/API_INTEGRATION.md` §3, wajib sebelum lanjut kalau mau uji lampiran PDF
- [ ] Buat `application/libraries/Approvalbridge.php` (nama file/class sesuai konvensi CI3, huruf besar hanya di awal — perhatikan case-sensitivity di server Linux), isi sesuai contoh di `docs/APPROVALSMART_OFFICIAL.md` Langkah 1
- [ ] Implementasi generate UUID v4 untuk `approval_id`
- [ ] Implementasi tombol "Kirim" di Surat & Resep → panggil `POST /api/legacy/approvals` dengan HMAC signing
- [ ] Simpan `approval_id`, `source_ref`, `approver_user_id`, `expires_in_hours` ke tabel surat/resep saat kirim
- [ ] Simpan request & response mentah ke `approval_logs` (direction=outbound) — sukses maupun gagal (jangan simpan `HMAC_SECRET`)
- [ ] Update status dokumen sesuai kode response: `202`→`menunggu_approval`, `400`/`401`/`422`→`gagal_kirim`, `409`→anggap sudah terkirim
- [ ] Tes pakai curl manual dulu (lihat `docs/API_INTEGRATION.md` §6) sebelum coba dari UI

## Fase 4 — Integrasi ApprovalSmart (Inbound / Webhook)
- [ ] Buat route `$route['approvals/(:any)']['patch'] = 'approvals/callback/$1';`
- [ ] Buat Controller `Approvals.php::callback($sourceRef)` untuk terima PATCH
- [ ] Verifikasi header `Authorization: Bearer {LEGACY_API_TOKEN}` → `401` jika salah
- [ ] Baca `Idempotency-Key` header, cocokkan dengan `approval_id` tersimpan untuk cek duplikat → `409` jika sudah diproses
- [ ] Cari dokumen berdasar `source_ref` dari URL → jika tidak ketemu, **tetap return 200** (bukan 404, supaya tidak di-retry)
- [ ] Update status dokumen (`terverifikasi`/`ditolak`/`kedaluwarsa`) + simpan `decided_by`, `decided_at`, `channel`, `decision_note`
- [ ] Generate PDF final saat status `terverifikasi`
- [ ] Pastikan response selalu 200–299 untuk kasus sukses (ApprovalSmart retry 5x dengan backoff kalau dapat 5xx/timeout)
- [ ] Simpan ke `approval_logs` (direction=inbound)
- [ ] Test callback pakai curl manual (lihat `docs/API_INTEGRATION.md` §6) sebelum sandbox nyata terhubung

## Fase 5 — Approval Log & GET Endpoint
- [ ] Endpoint `GET /api/approval/log` (JSON, untuk kebutuhan API/testing eksternal)
- [ ] Halaman dashboard "Approval Log" (list + filter status/module/tanggal) untuk dilihat manusia

## Fase 6 — Hardening & Testing
- [ ] Validasi input semua form (form_validation CI3)
- [ ] Error handling untuk timeout/koneksi gagal ke ApprovalSmart
- [ ] Retry manual (tombol "Kirim Ulang") untuk dokumen berstatus `gagal_kirim`
- [ ] Review log — pastikan tidak menyimpan API key mentah di `approval_logs`
- [ ] Uji end-to-end: Draft → Kirim → (simulasi callback) → Terverifikasi → Cetak PDF

## Backlog / Perlu Konfirmasi User dulu
- [ ] Dokumen resmi ApprovalSmart (auth, format payload pasti)
- [ ] Apakah butuh role-based permission lebih detail (dokter hanya lihat resepnya sendiri, dst)
- [ ] Apakah butuh notifikasi (email/WA) saat status berubah
