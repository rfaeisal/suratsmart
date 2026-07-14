# API_INTEGRATION.md

> **STATUS: RESMI.** Berdasarkan dokumen resmi tim ApprovalSmart: `docs/APPROVALSMART_OFFICIAL.md` (integrasi via HMAC + callback Telegram). File ini adalah ringkasan teknis untuk implementasi di CI3 — dokumen asli tetap jadi rujukan utama kalau ada detail yang kurang jelas di sini.

## 1. Gambaran Alur

```
CI  --POST /api/legacy/approvals (HMAC)-->  ApprovalSmart  -->  Telegram
CI  <--PATCH /approvals/{source_ref}     --  ApprovalSmart  <--  tombol ditekan approver
```

1. CI kirim permintaan approval ke ApprovalSmart (outbound, HMAC-signed).
2. ApprovalSmart teruskan ke approver via Telegram (dengan lampiran jika ada).
3. Approver tekan tombol **Setujui**/**Tolak** di Telegram.
4. ApprovalSmart panggil balik `PATCH` ke endpoint kita dengan hasil keputusan.

> **Catatan rekonsiliasi:** `API.md` awal (dari user) menyebut `POST /api/approval/send` dan `PATCH /api/approval/{source_ref}`. Dokumen resmi memakai path berbeda: **`POST /api/legacy/approvals`** dan **`PATCH /approvals/{source_ref}`**. Gunakan path dari dokumen resmi ini sebagai yang benar. `GET /api/approval/log` tetap endpoint internal kita sendiri (tidak disebut di dokumen resmi ApprovalSmart, itu murni untuk dashboard kita).

## 2. Konfigurasi Wajib (minta dari tim ApprovalSmart)

| Variabel | Keterangan |
|---|---|
| `APPROVAL_URL` | Base URL ApprovalSmart, mis. `https://approval.lmssmart.my.id` |
| `HMAC_KEY_ID` | ID kunci, mis. `k1` |
| `HMAC_SECRET` | Secret 64 karakter hex untuk tanda tangan request outbound |
| `LEGACY_API_TOKEN` | Token Bearer yang dikirim ApprovalSmart saat callback ke CI kita |

Simpan di `application/config/approvalsmart.php` (jangan hardcode, jangan commit nilai asli ke git):

```php
$config['approvalsmart_base_url']    = 'https://approval.lmssmart.my.id';
$config['approvalsmart_key_id']      = 'k1';
$config['approvalsmart_hmac_secret'] = 'GANTI_DENGAN_SECRET_ASLI';
$config['approvalsmart_legacy_token']= 'GANTI_DENGAN_TOKEN_CALLBACK_ASLI';
$config['approvalsmart_timeout']     = 15;
```

## 3. Outbound — POST /api/legacy/approvals

**Signing (HMAC-SHA256):**
```
timestamp  = time() saat request dibuat
body       = JSON encode payload (JSON_UNESCAPED_UNICODE)
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
| `approval_id` | UUID v4 | Ya | Unik per permintaan — generate di CI, simpan di DB |
| `source_ref` | string ≤64 | Ya | Referensi unik CI (nomor Surat/Resep) — dipakai path callback |
| `request_type` | string | Ya | Bebas, mis. `surat` atau `resep` |
| `title` | string ≤120 | Ya | Ditampilkan di Telegram |
| `approver_user_id` | string | Ya | Harus terdaftar & aktif di ApprovalSmart |
| `expires_in_hours` | int 1–720 | Ya | Masa berlaku permintaan |
| `summary` | string ≤2000 | Tidak | Deskripsi tampil di Telegram |
| `detail_url` | URL | Tidak | Tombol "Lihat detail" di Telegram |
| `attachment.filename` | string | Tidak* | Nama file lampiran |
| `attachment.url` | URL **publik** | Tidak* | **Harus bisa diakses internet** — Telegram unduh dari sini |
| `payload` | object | Tidak | Data ekstra, tidak tampil ke approver |

\* `filename` & `url` harus sepasang jika ingin ada lampiran.

**⚠️ Implikasi arsitektur penting:** Karena app ini test client yang mungkin jalan di localhost/intranet saat development, PDF Surat/Resep **tidak bisa langsung dipakai sebagai `attachment.url`** kecuali di-hosting di tempat yang bisa diakses publik (mis. upload ke S3/GCS, atau pakai tunnel seperti ngrok saat testing). Ini perlu diputuskan sebelum Fase 3 (lihat `docs/TASKS.md`).

**Response codes:**

| Kode | Arti | Aksi di app kita |
|---|---|---|
| 202 | Diterima, akan dikirim ke Telegram | Status → `menunggu_approval` |
| 400 | Field tidak valid | Status → `gagal_kirim`, log detail error |
| 401 | HMAC salah (secret/timestamp/header) | Status → `gagal_kirim`, cek clock sync |
| 409 | `approval_id` sudah pernah dikirim | Anggap sudah terkirim, jangan kirim ulang otomatis |
| 422 | `approver_user_id` tidak ditemukan/nonaktif | Status → `gagal_kirim`, tampilkan pesan ke user |

Catat **selalu** request & response mentah ke `approval_logs` (direction=`outbound`), termasuk saat gagal.

## 4. Inbound — PATCH /approvals/{source_ref} (Callback)

Endpoint ini **ada di app kita** (Controller `Approvals.php`).

```
PATCH {our_domain}/approvals/{source_ref}
Authorization: Bearer {LEGACY_API_TOKEN}
Idempotency-Key: {approval_id}
Content-Type: application/json
```

**Body:**
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

**Perilaku wajib app kita:**
1. **Verifikasi token** `Authorization: Bearer` sama dengan `LEGACY_API_TOKEN` → jika salah, response `401`.
2. Parse body, kalau `status` kosong → `400`.
3. Cari dokumen (`surat`/`resep`) berdasarkan `source_ref` dari URL path.
4. Kalau tidak ketemu → **tetap response 200** (bukan 404!) supaya ApprovalSmart tidak retry terus.
5. **Idempoten**: kalau `Idempotency-Key` (=`approval_id`) sudah pernah diproses dan status dokumen bukan lagi `pending`/`menunggu_approval` → response **409** ("Already applied"), jangan proses ulang.
6. Kalau valid & baru: update status dokumen (`terverifikasi`/`ditolak`/`kedaluwarsa`), simpan `decided_by`, `decided_at`, `note`, `channel`.
7. Jika `approved` → lanjut proses bisnis (generate PDF final).
8. **Selalu return 200–299 jika sukses diproses.** Kode 4xx (selain 409) dianggap gagal permanen (tidak di-retry ApprovalSmart). Kode 5xx/timeout akan **di-retry otomatis hingga 5x dengan backoff eksponensial** oleh ApprovalSmart — jadi controller ini harus idempoten dan cepat merespons.
9. Catat payload masuk ke `approval_logs` (direction=`inbound`).

## 5. GET /api/approval/log (Internal, bukan bagian spek ApprovalSmart)

Tetap seperti rencana awal — endpoint/dashboard internal untuk lihat histori dari tabel `approval_logs` kita sendiri. Filter opsional: `module_type`, `status`, `date_from`, `date_to`.

## 6. Tes Manual (dari dokumen resmi, adaptasi untuk dev CI3)

**Kirim approval tanpa lampiran:**
```bash
SECRET="HMAC_SECRET_DARI_TIM_APPROVALSMART"
TS=$(date +%s)
UUID=$(uuidgen | tr '[:upper:]' '[:lower:]')
B="{\"approval_id\":\"$UUID\",\"source_ref\":\"RESEP-TEST-001\",\"request_type\":\"resep\",\"title\":\"Test Resep Tanpa Lampiran\",\"summary\":\"Tes integrasi dari terminal.\",\"approver_user_id\":\"USR001\",\"expires_in_hours\":24}"
SIG="sha256=$(printf '%s' "${TS}.${B}" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $NF}')"

curl -s -X POST https://approval.lmssmart.my.id/api/legacy/approvals \
  -H "Content-Type: application/json" \
  -H "X-Timestamp: $TS" -H "X-Key-Id: k1" -H "X-Signature: $SIG" \
  -d "$B"
```

**Simulasi callback masuk ke CI (sebelum ApprovalSmart benar-benar terpasang):**
```bash
SOURCE_REF="RESEP-TEST-001"
APPROVAL_ID="uuid-yang-dikirim-sebelumnya"
TOKEN="TOKEN_DARI_TIM_APPROVALSMART"

curl -s -X PATCH https://<domain-ci-kita>/approvals/$SOURCE_REF \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $APPROVAL_ID" \
  -d '{"status":"approved","decided_by":"USR001","decided_at":"2024-07-13T08:00:00.000Z","channel":"telegram","note":null}'
```

## 7. Catatan Penting (dari dokumen resmi)

- `source_ref` harus unik per permintaan **aktif** — kirim ulang `source_ref` sama → `409` (idempoten, bukan error).
- `approval_id` **wajib UUID v4 valid**.
- **Timestamp ±5 menit** — pastikan server CI sinkron NTP, kalau tidak HMAC ditolak (`401`).
- **URL lampiran wajib publik** — lihat catatan arsitektur di §3.
- Status **tidak pernah kembali ke `pending`** — kalau perlu approval ulang, generate `approval_id` + `source_ref` baru (bukan reuse).
