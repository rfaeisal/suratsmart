# API_INTEGRATION.md

> **STATUS: RESMI & TERIMPLEMENTASI.** Berdasarkan dokumen resmi tim ApprovalSmart (`docs/APPROVALSMART_OFFICIAL.md`). Integrasi sudah berjalan di production (Railway).

## 1. Gambaran Alur

```
SuratSmart  --POST /api/legacy/approvals (HMAC)-->  ApprovalSmart  -->  Telegram
SuratSmart  <--PATCH /approvals/{source_ref}     --  ApprovalSmart  <--  tombol ditekan approver
```

1. SuratSmart kirim permintaan approval ke ApprovalSmart (outbound, HMAC-signed).
2. ApprovalSmart teruskan ke approver via Telegram (dengan lampiran PDF jika ada).
3. Approver tekan tombol **Setujui**/**Tolak** di Telegram.
4. ApprovalSmart panggil balik `PATCH` ke endpoint SuratSmart dengan hasil keputusan.

## 2. Konfigurasi

Semua nilai dikonfigurasi via halaman **Settings** di app (tersimpan di tabel `settings`) atau via env var sebagai fallback.

| Variabel | Keterangan |
|---|---|
| `approvalsmart_base_url` | Base URL ApprovalSmart, mis. `https://approval.lmssmart.my.id` |
| `approvalsmart_hmac_key_id` | ID kunci HMAC, mis. `k1` |
| `approvalsmart_hmac_secret` | Secret 64 karakter hex untuk signing outbound |
| `approvalsmart_legacy_token` | Bearer token yang dikirim ApprovalSmart saat callback ke kita |
| `public_base_url` | URL publik SuratSmart (mis. Railway domain) — dipakai untuk `attachment.url` PDF |

File config: `application/config/approvalsmart.php` — jangan hardcode nilai asli, gunakan env var.

## 3. Outbound — POST /api/legacy/approvals

**Signing (HMAC-SHA256):**
```
timestamp  = time() saat request dibuat
body       = json_encode(payload, JSON_UNESCAPED_UNICODE)
signature  = 'sha256=' + hash_hmac('sha256', "{timestamp}.{body}", HMAC_SECRET)
```

**Headers:**
```
Content-Type: application/json
X-Timestamp: {timestamp}
X-Key-Id: {HMAC_KEY_ID}
X-Signature: {signature}
```

**Payload:**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `approval_id` | UUID v4 | Ya | Generate di SuratSmart, unik per pengajuan, simpan di DB |
| `source_ref` | string ≤64 | Ya | Format: `surat-{approval_id}` atau `resep-{approval_id}` |
| `request_type` | string | Ya | `surat` atau `resep` |
| `title` | string ≤120 | Ya | Ditampilkan di Telegram |
| `approver_user_id` | string | Ya | Dari `dokter.approvalsmart_user_id`, harus terdaftar di ApprovalSmart |
| `expires_in_hours` | int 1–720 | Ya | Masa berlaku (default 48 jam) |
| `summary` | string ≤2000 | Tidak | Ringkasan tampil di Telegram |
| `detail_url` | URL | Tidak | Tombol "Lihat detail" di Telegram |
| `attachment.filename` | string | Tidak* | Nama file PDF |
| `attachment.url` | URL publik | Tidak* | Harus bisa diakses internet (Railway URL + path file) |

\* `filename` & `url` harus sepasang jika ingin ada lampiran.

**source_ref penting:** Format `surat-{approval_id}` memastikan setiap pengajuan punya identifier unik. Saat kirim berhasil (HTTP 202), `source_ref` di DB diupdate ke nilai baru ini sehingga callback ApprovalSmart bisa dicocokkan.

**Response codes:**

| Kode | Arti | Aksi di SuratSmart |
|---|---|---|
| 202 | Diterima, akan dikirim ke Telegram | Status → `menunggu_approval`, DB diupdate |
| 400 | Field tidak valid | Status → `gagal_kirim` |
| 401 | HMAC salah | Status → `gagal_kirim`, cek clock sync & secret |
| 409 | `approval_id` sudah pernah dikirim | Anggap sudah terkirim (`menunggu_approval`) |
| 422 | `approver_user_id` tidak ditemukan | Status → `gagal_kirim` |

Selalu catat request & response ke `approval_logs` (direction=`outbound`), termasuk saat gagal. Jangan simpan header `X-Signature`/secret di log.

## 4. Inbound — PATCH /approvals/{source_ref} (Callback)

Endpoint ini ada di SuratSmart: `application/controllers/Approvals.php::callback()`.

```
PATCH {public_base_url}/approvals/{source_ref}
Authorization: Bearer {LEGACY_API_TOKEN}
Idempotency-Key: {approval_id}
Content-Type: application/json
```

**Body dari ApprovalSmart:**
```json
{
  "status":     "approved",
  "decided_by": "USR007",
  "decided_at": "2024-07-13T08:30:00.000Z",
  "channel":    "telegram",
  "note":       null
}
```

- `status`: `approved` | `rejected` | `expired`
- `decided_by`: null jika `expired`
- `channel`: selalu `telegram`

**Behavior endpoint kita:**
1. Verifikasi `Authorization: Bearer` cocok dengan `LEGACY_API_TOKEN` → 401 jika salah.
2. Cek method PATCH → 405 jika bukan.
3. Parse body, validasi `status` → 400 jika tidak valid.
4. Cari dokumen via `source_ref` → **return 200** jika tidak ketemu (bukan 404, supaya tidak di-retry ApprovalSmart).
5. Idempoten: jika status dokumen sudah terminal (`terverifikasi`/`ditolak`/`kedaluwarsa`) dan `Idempotency-Key` sama dengan `approval_id` tersimpan → return 409.
6. Update status dokumen + simpan `decided_by`, `decided_at`, `decision_note`.
7. Jika `approved` → generate PDF final.
8. Catat ke `approval_logs` (direction=`inbound`).
9. Return 200.

**Catatan deployment:** nginx harus meneruskan header `Authorization` ke PHP-FPM. Sudah dikonfigurasi di `docker-entrypoint.sh`:
```nginx
fastcgi_param HTTP_AUTHORIZATION $http_authorization;
```

**Callback URL yang harus didaftarkan ke tim ApprovalSmart:**
```
https://{public_base_url}/approvals/{source_ref}
```
Pastikan `public_base_url` di ApprovalSmart selalu sesuai dengan domain aktif SuratSmart (Railway URL).

## 5. GET /api/approval/log (Internal)

Endpoint internal untuk monitoring. Filter via query string: `module_type`, `direction`, `status`, `date_from`, `date_to`.

## 6. Tes Manual

**Kirim approval (simulasi dari terminal):**
```bash
SECRET="HMAC_SECRET_DARI_TIM_APPROVALSMART"
TS=$(date +%s)
UUID=$(uuidgen | tr '[:upper:]' '[:lower:]')
SOURCE_REF="surat-${UUID}"
B="{\"approval_id\":\"$UUID\",\"source_ref\":\"$SOURCE_REF\",\"request_type\":\"surat\",\"title\":\"Test Surat\",\"summary\":\"Tes integrasi.\",\"approver_user_id\":\"USR001\",\"expires_in_hours\":24}"
SIG="sha256=$(printf '%s' "${TS}.${B}" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $NF}')"

curl -s -X POST https://approval.lmssmart.my.id/api/legacy/approvals \
  -H "Content-Type: application/json" \
  -H "X-Timestamp: $TS" -H "X-Key-Id: k1" -H "X-Signature: $SIG" \
  -d "$B"
```

**Simulasi callback masuk ke SuratSmart:**
```bash
DOMAIN="https://xxxx.up.railway.app"
SOURCE_REF="surat-{uuid-yang-dikirim}"
APPROVAL_ID="{uuid-yang-dikirim}"
TOKEN="LEGACY_API_TOKEN"

curl -s -X PATCH $DOMAIN/approvals/$SOURCE_REF \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $APPROVAL_ID" \
  -d '{"status":"approved","decided_by":"USR001","decided_at":"2024-07-14T08:00:00.000Z","channel":"telegram","note":null}'
```

**Re-trigger sync dari ApprovalSmart (jika callback belum masuk):**
```bash
# Reset synced_at dulu
docker exec db psql -U approvalsmart -d approvalsmart \
  -c "UPDATE approval_requests SET synced_at = NULL, sync_attempts = 0 WHERE id = '{approval_id}';"

# Trigger cron sync
curl -s -X POST https://approval.lmssmart.my.id/api/cron/sync \
  -H "Authorization: Bearer {CRON_TOKEN}"
```

## 7. Catatan Penting

- **`source_ref` unik per pengajuan** — formatnya `surat-{approval_id}` / `resep-{approval_id}`. Surat yang sama dikirim ulang akan mendapat `source_ref` baru (bukan reuse).
- **`approval_id` wajib UUID v4 valid**.
- **Timestamp ±5 menit** — server SuratSmart harus sinkron NTP, kalau tidak HMAC ditolak (401).
- **URL lampiran wajib publik** — `attachment.url` harus bisa diakses dari internet. Di Railway, gunakan Railway URL sebagai `public_base_url` di Settings.
- **Callback URL di ApprovalSmart** harus selalu diupdate kalau domain SuratSmart berubah (mis. deploy ulang dengan URL baru).
- **`approvalsmart_user_id` dokter** — kolom di tabel `dokter`, diisi admin setelah konfirmasi mapping dari tim ApprovalSmart. Wajib terisi sebelum surat/resep bisa dikirim.
