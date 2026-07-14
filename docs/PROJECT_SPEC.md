# PROJECT_SPEC.md

## Tujuan

Project ini untuk menguji integrasi dengan ApprovalSmart menggunakan dokumen dan alur kerja nyata (resep, surat). Bukan sistem produksi penuh — fokus pada validasi alur kirim → approve → callback → cetak PDF.

**Status:** Berjalan di production (Railway). Integrasi end-to-end sudah diverifikasi.

## Modul Aplikasi

| Modul | Fungsi |
|---|---|
| Login | Autentikasi user (session-based CI3) |
| User | Manajemen akun pengguna internal |
| Dokter | Data dokter — termasuk `approvalsmart_user_id` untuk mapping approver |
| Pasien | Data pasien |
| Obat | Master data obat untuk resep |
| Surat | Surat yang perlu di-approve |
| Resep | Resep obat yang perlu di-approve |
| Approval Log | Riwayat request/response ke/dari ApprovalSmart |
| Settings | Konfigurasi app: ApprovalSmart credentials + `public_base_url` |

## Alur Kerja

```
Draft → Generate PDF → Kirim ke ApprovalSmart → [Telegram Approver] → Callback PATCH → Terverifikasi → Download PDF
```

1. **Draft** — user buat Surat/Resep, status = `draft`.
2. **Generate PDF** — generate PDF lokal sebagai lampiran.
3. **Kirim** — sistem POST ke ApprovalSmart dengan HMAC signing. `source_ref` = `surat-{uuid}` / `resep-{uuid}`, unik per pengajuan. Status → `menunggu_approval`.
4. **ApprovalSmart** — teruskan ke approver via Telegram dengan lampiran PDF.
5. **Callback PATCH** — ApprovalSmart panggil `PATCH /approvals/{source_ref}` ke SuratSmart.
6. **Terverifikasi** — status diupdate, PDF final digenerate ulang.

## Status Dokumen

| Status | Keterangan |
|---|---|
| `draft` | Baru dibuat, belum dikirim |
| `menunggu_approval` | Sudah dikirim ke ApprovalSmart, menunggu keputusan |
| `terverifikasi` | Disetujui approver |
| `ditolak` | Ditolak approver |
| `kedaluwarsa` | Waktu approval habis |
| `gagal_kirim` | Error saat kirim ke ApprovalSmart |

## Tech Stack

| Komponen | Teknologi |
|---|---|
| Framework | CodeIgniter 3.1.13 |
| Database | MySQL/MariaDB |
| PDF | mPDF 8.x (via Composer) |
| HTTP Client | cURL via `Approvalbridge` library |
| Session (production) | CI3 database driver (`ci_sessions` table) |
| Session (lokal) | CI3 file driver |
| Deployment | Railway (Docker: php:8.2-fpm + nginx) |

## Ruang Lingkup

**Termasuk:**
- CRUD semua modul di atas
- Generate PDF (surat/resep) sebagai lampiran
- Integrasi outbound ke ApprovalSmart (HMAC-SHA256)
- Endpoint webhook inbound untuk callback PATCH
- Log lengkap semua komunikasi dengan ApprovalSmart

**Tidak termasuk:**
- Multi-tenant / multi-klinik
- Billing/pembayaran
- Notifikasi email/SMS
