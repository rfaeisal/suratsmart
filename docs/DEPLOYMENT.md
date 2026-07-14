# DEPLOYMENT.md

Panduan deployment SuratSmart ke Railway.

## Arsitektur

- Docker image: `php:8.2-fpm` + nginx (single container)
- Database: Railway MySQL plugin (service terpisah)
- Session: CI3 database driver, tabel `ci_sessions`

## Environment Variables (Railway PHP Service)

Set via Railway → Service → Variables:

| Variable | Nilai | Keterangan |
|---|---|---|
| `MYSQLHOST` | `${{MySQL.MYSQLHOST}}` | Dari plugin MySQL Railway |
| `MYSQLPORT` | `${{MySQL.MYSQLPORT}}` | |
| `MYSQLUSER` | `${{MySQL.MYSQLUSER}}` | |
| `MYSQLPASSWORD` | `${{MySQL.MYSQLPASSWORD}}` | |
| `MYSQLDATABASE` | `${{MySQL.MYSQLDATABASE}}` | |
| `APP_ENCRYPTION_KEY` | string acak 32+ karakter | Untuk CI3 encryption |
| `APPROVALSMART_BASE_URL` | `https://approval.lmssmart.my.id` | Opsional, bisa diisi via Settings UI |
| `APPROVALSMART_HMAC_KEY_ID` | `k1` | Opsional |
| `APPROVALSMART_HMAC_SECRET` | secret dari tim ApprovalSmart | Opsional |
| `APPROVALSMART_LEGACY_TOKEN` | token dari tim ApprovalSmart | Opsional |

> Konfigurasi ApprovalSmart bisa diisi via env var (prioritas rendah) atau via halaman Settings di app (prioritas tinggi). Disarankan pakai Settings UI agar bisa diubah tanpa redeploy.

## Deploy Pertama

1. Push ke GitHub — Railway auto-deploy dari branch `main`.
2. Tunggu build selesai.
3. Buka `https://<railway-domain>/migrate` — jalankan semua migrasi (buat tabel + seed admin).
4. Login dengan `admin` / `admin123` → **segera ganti password**.
5. Buka **Settings** → isi:
   - **Public Base URL**: `https://<railway-domain>.up.railway.app`
   - ApprovalSmart credentials (Base URL, HMAC Key ID, HMAC Secret, Legacy Token)
6. Buka **Dokter** → isi kolom **ApprovalSmart User ID** untuk setiap dokter (dapat dari tim ApprovalSmart).
7. Informasikan ke tim ApprovalSmart bahwa callback URL SuratSmart adalah:
   ```
   https://<railway-domain>.up.railway.app/approvals/{source_ref}
   ```

## Redeploy

Push ke GitHub → Railway rebuild otomatis. Tidak perlu jalankan migrasi ulang (entrypoint hanya buat `ci_sessions` jika belum ada, idempoten).

Jika ada migrasi baru (`application/migrations/`):
1. Update `$config['migration_version']` di `application/config/migration.php`.
2. Deploy.
3. Buka `/migrate` di browser.

## Troubleshooting

**Callback ApprovalSmart tidak masuk:**
1. Pastikan tim ApprovalSmart punya Railway URL yang benar sebagai base URL callback.
2. Cek Railway logs untuk PATCH request.
3. Cek `approval_logs` di DB untuk entri `direction=inbound`.
4. Jika perlu re-trigger: reset `synced_at` di ApprovalSmart DB lalu jalankan `/api/cron/sync`.

**Session error / ci_sessions tidak ada:**
- Entrypoint sudah handle ini otomatis via PDO saat startup.
- Jika masih error, jalankan `/migrate` untuk buat tabel lewat migrasi.

**PDF tidak bisa diakses ApprovalSmart:**
- Pastikan `public_base_url` di Settings terisi dengan Railway domain yang benar.
- File PDF tersimpan di folder `uploads/` — Railway volume tidak persistent antar deploy. Pertimbangkan cloud storage (S3/GCS) untuk production nyata.

**HMAC 401 dari ApprovalSmart:**
- Pastikan `HMAC_SECRET` dan `HMAC_KEY_ID` sesuai dengan yang diberikan tim ApprovalSmart.
- Cek clock sync server Railway (timestamp ±5 menit dari server ApprovalSmart).

## Catatan Railway

- `PORT` env var di-set otomatis oleh Railway, nginx membaca ini via entrypoint.
- File `uploads/` **tidak persistent** antar deploy (ephemeral filesystem). Untuk test client ini sudah cukup, tapi untuk production nyata gunakan cloud storage.
- MySQL Railway hanya accessible dari dalam Railway network — tidak bisa diakses langsung dari luar kecuali enable Public Networking di plugin MySQL.
