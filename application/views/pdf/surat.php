<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; margin: 0; }
  .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
  .kop h2 { margin: 0; font-size: 16pt; text-transform: uppercase; letter-spacing: 1px; }
  .kop p { margin: 2px 0; font-size: 10pt; }
  h3.judul { text-align: center; text-transform: uppercase; text-decoration: underline; font-size: 13pt; margin: 20px 0 15px; }
  .nomor { text-align: center; font-size: 10pt; margin-bottom: 20px; }
  table.info { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
  table.info td { padding: 2px 4px; font-size: 11pt; vertical-align: top; }
  table.info td:first-child { width: 130px; white-space: nowrap; }
  table.info td:nth-child(2) { width: 10px; }
  .isi { margin: 20px 0; text-align: justify; line-height: 1.8; white-space: pre-wrap; }
  .ttd { margin-top: 40px; float: right; text-align: center; width: 200px; }
  .ttd-kota { text-align: right; margin-top: 30px; margin-right: 20px; }
  .clear { clear: both; }
  .footer { margin-top: 60px; font-size: 9pt; color: #555; border-top: 1px solid #ccc; padding-top: 5px; text-align: center; }
</style>
</head>
<body>

<div class="kop">
  <h2>Klinik SuratSmart</h2>
  <p>Jl. Contoh No. 123, Kota — Telp. (021) 000-0000</p>
</div>

<h3 class="judul"><?= htmlspecialchars($surat->jenis_surat) ?></h3>
<div class="nomor">Nomor: <?= htmlspecialchars($surat->nomor_surat) ?></div>

<p>Yang bertanda tangan di bawah ini:</p>
<table class="info">
  <tr>
    <td>Nama Dokter</td><td>:</td><td><strong><?= htmlspecialchars($surat->nama_dokter) ?></strong></td>
  </tr>
  <tr>
    <td>No. SIP</td><td>:</td><td><?= htmlspecialchars($surat->no_sip ?: '-') ?></td>
  </tr>
  <tr>
    <td>Spesialisasi</td><td>:</td><td><?= htmlspecialchars($surat->spesialisasi ?: 'Dokter Umum') ?></td>
  </tr>
</table>

<p>Menerangkan bahwa:</p>
<table class="info">
  <tr>
    <td>Nama Pasien</td><td>:</td><td><strong><?= htmlspecialchars($surat->nama_pasien) ?></strong></td>
  </tr>
  <tr>
    <td>No. Rekam Medis</td><td>:</td><td><?= htmlspecialchars($surat->no_rm) ?></td>
  </tr>
  <?php if ($surat->tanggal_lahir): ?>
  <tr>
    <td>Tanggal Lahir</td><td>:</td><td><?= tgl_indonesia($surat->tanggal_lahir) ?></td>
  </tr>
  <?php endif; ?>
  <?php if ($surat->alamat_pasien): ?>
  <tr>
    <td>Alamat</td><td>:</td><td><?= htmlspecialchars($surat->alamat_pasien) ?></td>
  </tr>
  <?php endif; ?>
</table>

<div class="isi"><?= htmlspecialchars($surat->isi) ?></div>

<div class="ttd-kota">
  <?= tgl_indonesia($surat->created_at) ?>
</div>
<div class="ttd">
  <p>Dokter Pemeriksa,</p>
  <br><br><br>
  <p><strong><?= htmlspecialchars($surat->nama_dokter) ?></strong></p>
  <?php if ($surat->no_sip): ?><p style="font-size:10pt">SIP: <?= htmlspecialchars($surat->no_sip) ?></p><?php endif; ?>
</div>
<div class="clear"></div>

<div class="footer">
  Dokumen ini digenerate oleh SuratSmart pada <?= date('d/m/Y H:i') ?> — Nomor: <?= htmlspecialchars($surat->nomor_surat) ?>
</div>
</body>
</html>
