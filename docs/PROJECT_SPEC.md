# PROJECT_SPEC.md

## Tujuan

Project ini **hanya** untuk menguji integrasi dengan ApprovalSmart menggunakan dokumen dan alur kerja nyata (resep, surat). Bukan sistem produksi rumah sakit/klinik penuh — cukup untuk memvalidasi bahwa alur kirim → approve → callback → cetak berjalan benar.

## Modul Aplikasi

| Modul | Fungsi |
|---|---|
| Login | Autentikasi user (session-based CI3) |
| User | Manajemen akun pengguna internal |
| Dokter | Data dokter (pengirim resep/surat) |
| Pasien | Data pasien |
| Obat | Master data obat untuk resep |
| Surat | Surat yang perlu di-approve (mis. surat keterangan) |
| Resep | Resep obat yang perlu di-approve |
| Approval Log | Riwayat request/response ke/dari ApprovalSmart |
| Settings | Konfigurasi aplikasi (termasuk koneksi ApprovalSmart) |

## Alur Kerja (Workflow)

1. **Draft** — user membuat Surat atau Resep, status = `draft`.
2. **Kirim** — user submit dokumen; sistem generate PDF lampiran, lalu memanggil ApprovalSmart (`POST /approval/send`) dengan `source_ref` unik. Status berubah jadi `menunggu_approval`.
3. **ApprovalSmart memproses** — di luar sistem kita (pihak ketiga).
4. **PATCH Callback** — ApprovalSmart memanggil balik endpoint kita `PATCH /api/approval/{source_ref}` dengan hasil approval (approved/rejected).
5. **Terverifikasi** — status dokumen di-update sesuai callback.
6. **Cetak PDF** — jika approved, PDF final (dengan tanda approval) bisa dicetak/diunduh.

Setiap tahap dicatat di tabel `approval_logs` untuk keperluan debugging integrasi.

## Status Dokumen (Surat & Resep)

Disarankan enum status berikut (bisa disesuaikan):
- `draft`
- `menunggu_approval`
- `terverifikasi` (disetujui)
- `ditolak`
- `gagal_kirim` (jika request ke ApprovalSmart error)

## Ruang Lingkup

**Termasuk:**
- CRUD dasar semua modul di atas
- Generate PDF (surat/resep) sebagai lampiran
- Integrasi outbound ke ApprovalSmart + endpoint webhook untuk callback
- Log lengkap semua komunikasi dengan ApprovalSmart

**Tidak termasuk (di luar scope test client ini):**
- Multi-tenant / multi-klinik
- Billing/pembayaran
- Notifikasi email/SMS (kecuali diminta menyusul)
