# Panduan Integrasi CodeIgniter — ApprovalSmart

Dokumen ini untuk tim CI yang ingin mengirim permintaan persetujuan ke
ApprovalSmart dan menerima keputusan kembali.

---

## Gambaran Alur

```
CI  --POST /api/legacy/approvals (HMAC)-->  ApprovalSmart  -->  Telegram
CI  <--PATCH /approvals/{source_ref}     --  ApprovalSmart  <--  tombol ditekan
```

1. CI mengirim permintaan persetujuan ke ApprovalSmart.
2. ApprovalSmart meneruskan ke approver via Telegram (teks atau dengan lampiran PDF/gambar).
3. Approver menekan tombol **Setujui** atau **Tolak**.
4. ApprovalSmart memanggil `PATCH` ke CI dengan hasil keputusan.

---

## Konfigurasi yang Diperlukan

Minta kepada tim ApprovalSmart nilai-nilai berikut:

| Variabel | Keterangan |
|---|---|
| `APPROVAL_URL` | URL ApprovalSmart, contoh: `https://approval.lmssmart.my.id` |
| `HMAC_KEY_ID` | ID kunci, contoh: `k1` |
| `HMAC_SECRET` | Secret 64 karakter hex untuk menandatangani request |
| `LEGACY_API_TOKEN` | Token yang dikirim ApprovalSmart saat callback ke CI |

Simpan di config CI, **jangan hardcode** di kode.

---

## Langkah 1 — Library ApprovalBridge di CI

Buat `application/libraries/ApprovalBridge.php` (CI3) atau `app/Libraries/ApprovalBridge.php` (CI4):

```php
<?php
class ApprovalBridge
{
    private string $baseUrl;
    private string $keyId;
    private string $secret;

    public function __construct()
    {
        $this->baseUrl = 'https://approval.lmssmart.my.id'; // ganti sesuai env
        $this->keyId   = 'k1';
        $this->secret  = 'HMAC_SECRET_DARI_TIM_APPROVALSMART';
    }

    /**
     * Kirim permintaan persetujuan.
     * @return array ['ok' => bool, 'body' => array, 'http_code' => int]
     */
    public function requestApproval(array $data): array
    {
        $body      = json_encode($data, JSON_UNESCAPED_UNICODE);
        $timestamp = (string) time();
        $signature = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $this->secret);

        $ch = curl_init($this->baseUrl . '/api/legacy/approvals');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Timestamp: '  . $timestamp,
                'X-Key-Id: '     . $this->keyId,
                'X-Signature: '  . $signature,
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'ok'        => $httpCode === 202,
            'body'      => json_decode($raw, true) ?? [],
            'http_code' => $httpCode,
        ];
    }
}
```

---

## Langkah 2 — Kirim Permintaan dari Controller CI

```php
<?php
$this->load->library('ApprovalBridge');

// Generate UUID v4
$approvalId = sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

$result = $this->approvalbride->requestApproval([
    // --- Wajib ---
    'approval_id'      => $approvalId,
    'source_ref'       => 'PO-2024-00123',        // referensi unik di CI, max 64 karakter
    'request_type'     => 'purchase_order',        // bebas: purchase_order, cuti, dll.
    'title'            => 'PO Alat Tulis — Rp 1.500.000',
    'approver_user_id' => 'USR007',               // ID user di ApprovalSmart
    'expires_in_hours' => 48,                      // 1–720 jam

    // --- Opsional ---
    'summary'    => 'Pembelian ATK untuk operasional Juli.',
    'detail_url' => 'https://erp.contoh.id/po/2024/00123',
    'attachment' => [                              // lampiran PDF atau gambar (URL publik)
        'filename' => 'PO-2024-00123.pdf',
        'url'      => 'https://erp.contoh.id/files/po/2024/00123.pdf',
    ],
    'payload'    => [                              // data ekstra, tidak tampil ke approver
        'vendor' => 'PT Berkah Jaya',
        'total'  => 1500000,
    ],
]);

if ($result['ok']) {
    // Simpan ke DB untuk dicocokkan saat callback
    $this->db->update('purchase_orders', [
        'approval_bridge_id' => $approvalId,
        'approval_status'    => 'pending',
    ], ['po_number' => 'PO-2024-00123']);
} else {
    log_message('error', 'ApprovalBridge error: ' . json_encode($result));
}
```

### Field Request

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `approval_id` | UUID v4 | Ya | Unik per permintaan — simpan di DB CI |
| `source_ref` | string ≤64 | Ya | Nomor PO / referensi CI — dipakai di path callback PATCH |
| `request_type` | string | Ya | Bebas: `purchase_order`, `cuti`, dll. |
| `title` | string ≤120 | Ya | Ditampilkan di Telegram |
| `approver_user_id` | string | Ya | Harus ada di tabel users ApprovalSmart |
| `expires_in_hours` | integer 1–720 | Ya | Berapa jam sebelum kedaluwarsa |
| `summary` | string ≤2000 | Tidak | Deskripsi, ditampilkan di Telegram |
| `detail_url` | URL | Tidak | Tombol "Lihat detail" di Telegram |
| `attachment.filename` | string | Tidak* | Nama file lampiran |
| `attachment.url` | URL publik | Tidak* | URL file yang bisa diunduh Telegram |
| `payload` | object | Tidak | Data bebas, disimpan tapi tidak tampil |

*`filename` dan `url` harus ada bersama-sama jika ingin lampiran.

> **Syarat URL lampiran:** URL file harus bisa diakses dari internet oleh server Telegram.
> File di jaringan internal/intranet tidak akan bisa diunduh. Upload ke storage publik
> (S3, GCS, dll.) terlebih dahulu jika perlu.

### Kode HTTP Response

| Kode | Arti |
|---|---|
| 202 | Diterima — pesan akan dikirim ke Telegram |
| 400 | Field tidak valid — lihat `detail` di response body |
| 401 | HMAC salah — periksa secret, timestamp, atau header |
| 409 | `approval_id` sudah pernah dikirim — abaikan, tidak ada duplikat |
| 422 | `approver_user_id` tidak ditemukan atau nonaktif |

---

## Langkah 3 — Terima Callback Keputusan di CI

ApprovalSmart akan memanggil CI setelah keputusan dibuat:

```
PATCH {LEGACY_BASE_URL}/approvals/{source_ref}
Authorization: Bearer {LEGACY_API_TOKEN}
Idempotency-Key: {approval_id}
Content-Type: application/json
```

Body yang diterima:

```json
{
  "status":     "approved",
  "decided_by": "USR007",
  "decided_at": "2024-07-13T08:30:00.000Z",
  "channel":    "telegram",
  "note":       null
}
```

| Field | Nilai |
|---|---|
| `status` | `approved`, `rejected`, atau `expired` |
| `decided_by` | `approver_user_id` — null jika expired |
| `decided_at` | ISO 8601 UTC — null jika expired |
| `channel` | selalu `telegram` |

### Contoh Controller CI (CI3)

```php
<?php
// routes.php: $route['approvals/(:any)']['patch'] = 'approvals/callback/$1';

class Approvals extends CI_Controller
{
    public function callback($sourceRef)
    {
        // 1. Verifikasi token
        $auth = $this->input->get_request_header('Authorization');
        if ($auth !== 'Bearer TOKEN_DARI_TIM_APPROVALSMART') {
            return $this->output->set_status_header(401)->set_output('Unauthorized');
        }

        // 2. Parse body
        $body = json_decode(file_get_contents('php://input'), true);
        if (empty($body['status'])) {
            return $this->output->set_status_header(400)->set_output('Bad Request');
        }

        $sourceRef      = urldecode($sourceRef);
        $idempotencyKey = $this->input->get_request_header('Idempotency-Key');

        // 3. Cari data di CI
        $po = $this->db->get_where('purchase_orders', ['po_number' => $sourceRef])->row();
        if (!$po) {
            // Data tidak ada — kembalikan 200 agar tidak di-retry
            return $this->output->set_status_header(200)->set_output('OK');
        }

        // 4. Idempoten — jika sudah diproses sebelumnya
        if ($po->approval_bridge_id === $idempotencyKey && $po->approval_status !== 'pending') {
            return $this->output->set_status_header(409)->set_output('Already applied');
        }

        // 5. Terapkan keputusan
        $this->db->update('purchase_orders', [
            'approval_status' => $body['status'],
            'approved_by'     => $body['decided_by'],
            'approved_at'     => $body['decided_at'],
        ], ['po_number' => $sourceRef]);

        if ($body['status'] === 'approved') {
            // tindak lanjut bisnis: buat PO, notifikasi gudang, dll.
        }

        return $this->output->set_status_header(200)->set_output('OK');
    }
}
```

> **Wajib:** Kembalikan **200–299** jika berhasil, atau **409** jika sudah pernah diproses.
> Kode 4xx (selain 409) menandai permintaan sebagai gagal permanen — tidak di-retry.
> Kode 5xx atau timeout di-retry otomatis hingga 5 kali dengan backoff eksponensial.

---

## Langkah 4 — Tes via Terminal

Jalankan dari terminal macOS atau Linux.

### 4a. Tes kirim approval tanpa lampiran

```bash
SECRET="HMAC_SECRET_DARI_TIM_APPROVALSMART"
TS=$(date +%s)
UUID=$(uuidgen | tr '[:upper:]' '[:lower:]')   # atau python3 -c "import uuid; print(uuid.uuid4())"
B="{\"approval_id\":\"$UUID\",\"source_ref\":\"PO-TEST-001\",\"request_type\":\"purchase_order\",\"title\":\"Test PO Tanpa Lampiran\",\"summary\":\"Tes integrasi dari terminal.\",\"approver_user_id\":\"USR001\",\"expires_in_hours\":24}"
SIG="sha256=$(printf '%s' "${TS}.${B}" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $NF}')"

curl -s -X POST https://approval.lmssmart.my.id/api/legacy/approvals \
  -H "Content-Type: application/json" \
  -H "X-Timestamp: $TS" \
  -H "X-Key-Id: k1" \
  -H "X-Signature: $SIG" \
  -d "$B"

# Response yang diharapkan:
# {"approval_id":"...","status":"pending"}
```

### 4b. Tes kirim approval dengan lampiran PDF

```bash
SECRET="HMAC_SECRET_DARI_TIM_APPROVALSMART"
TS=$(date +%s)
UUID=$(uuidgen | tr '[:upper:]' '[:lower:]')
B="{\"approval_id\":\"$UUID\",\"source_ref\":\"PO-TEST-002\",\"request_type\":\"purchase_order\",\"title\":\"Test PO Dengan Lampiran\",\"summary\":\"Tes dengan file PDF.\",\"approver_user_id\":\"USR001\",\"expires_in_hours\":24,\"attachment\":{\"filename\":\"po-test.pdf\",\"url\":\"https://URL_FILE_PDF_PUBLIK\"}}"
SIG="sha256=$(printf '%s' "${TS}.${B}" | openssl dgst -sha256 -hmac "$SECRET" | awk '{print $NF}')"

curl -s -X POST https://approval.lmssmart.my.id/api/legacy/approvals \
  -H "Content-Type: application/json" \
  -H "X-Timestamp: $TS" \
  -H "X-Key-Id: k1" \
  -H "X-Signature: $SIG" \
  -d "$B"
```

### 4c. Simulasi callback dari ApprovalSmart ke CI

Gunakan ini untuk tes endpoint PATCH di CI sebelum ApprovalSmart dipasang:

```bash
SOURCE_REF="PO-TEST-001"
APPROVAL_ID="uuid-yang-dikirim-sebelumnya"
TOKEN="TOKEN_DARI_TIM_APPROVALSMART"

curl -s -X PATCH https://erp.contoh.id/api/approvals/$SOURCE_REF \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Idempotency-Key: $APPROVAL_ID" \
  -d '{"status":"approved","decided_by":"USR001","decided_at":"2024-07-13T08:00:00.000Z","channel":"telegram","note":null}'

# Response yang diharapkan: HTTP 200
```

---

## Catatan Penting

- **`source_ref` harus unik per permintaan aktif.** Kirim `source_ref` yang sama dua kali menghasilkan 409 (idempoten) — tidak ada duplikat.
- **`approval_id` harus UUID v4 valid** (format `xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`).
- **Timestamp ±5 menit** — pastikan clock server CI sinkron NTP agar HMAC tidak ditolak.
- **URL lampiran harus publik** — Telegram mengunduh file dari URL, tidak bisa lewat intranet.
- **Status tidak pernah kembali ke `pending`** — jika perlu approval ulang, kirim `approval_id` dan `source_ref` baru.
