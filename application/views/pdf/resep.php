<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 11pt; color: #000; margin: 0; }
  .kop { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; overflow: hidden; }
  .kop-text { float: left; }
  .kop-text h2 { margin: 0; font-size: 15pt; }
  .kop-text p { margin: 2px 0; font-size: 9pt; color: #333; }
  .kop-label { float: right; text-align: right; }
  .kop-label .r-label { font-size: 22pt; font-weight: bold; color: #2c5282; letter-spacing: 3px; }
  .clear { clear: both; }
  .info-block { overflow: hidden; margin-bottom: 12px; }
  .info-left, .info-right { float: left; width: 48%; }
  .info-right { float: right; }
  table.info td { padding: 2px 3px; font-size: 10pt; vertical-align: top; }
  table.info td:first-child { width: 110px; color: #555; }
  table.info td:nth-child(2) { width: 8px; }
  table.obat { width: 100%; border-collapse: collapse; margin: 15px 0; }
  table.obat th { background: #2c5282; color: #fff; padding: 6px 8px; font-size: 10pt; text-align: left; }
  table.obat td { padding: 6px 8px; font-size: 10pt; border-bottom: 1px solid #e2e8f0; }
  table.obat tr:nth-child(even) td { background: #f7fafc; }
  .catatan { background: #fffbeb; border-left: 3px solid #f6ad55; padding: 8px 12px; margin: 10px 0; font-size: 10pt; }
  .ttd { margin-top: 30px; text-align: center; float: right; width: 180px; }
  .footer { clear: both; margin-top: 40px; font-size: 8pt; color: #718096; border-top: 1px solid #e2e8f0; padding-top: 5px; text-align: center; }
</style>
</head>
<body>

<div class="kop">
  <div class="kop-text">
    <h2>Klinik SuratSmart</h2>
    <p>Jl. Contoh No. 123, Kota — Telp. (021) 000-0000</p>
  </div>
  <div class="kop-label">
    <div class="r-label">RESEP</div>
    <div style="font-size:9pt;color:#555"><?= htmlspecialchars($resep->nomor_resep) ?></div>
  </div>
  <div class="clear"></div>
</div>

<div class="info-block">
  <div class="info-left">
    <table class="info">
      <tr><td>Tanggal</td><td>:</td><td><?= tgl_indonesia($resep->tanggal) ?></td></tr>
      <tr><td>Dokter</td><td>:</td><td><strong><?= htmlspecialchars($resep->nama_dokter) ?></strong></td></tr>
      <?php if ($resep->no_sip): ?>
      <tr><td>No. SIP</td><td>:</td><td><?= htmlspecialchars($resep->no_sip) ?></td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="info-right">
    <table class="info">
      <tr><td>Pasien</td><td>:</td><td><strong><?= htmlspecialchars($resep->nama_pasien) ?></strong></td></tr>
      <tr><td>No. RM</td><td>:</td><td><?= htmlspecialchars($resep->no_rm) ?></td></tr>
      <?php if ($resep->tanggal_lahir): ?>
      <tr><td>Tgl. Lahir</td><td>:</td><td><?= date('d/m/Y', strtotime($resep->tanggal_lahir)) ?></td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="clear"></div>
</div>

<p style="font-style:italic;font-size:10pt">R/</p>

<table class="obat">
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Nama Obat</th>
      <th style="width:70px;text-align:center">Jumlah</th>
      <th>Aturan Pakai</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($detail as $i => $d): ?>
    <tr>
      <td><?= $i + 1 ?></td>
      <td><?= htmlspecialchars($d->nama_obat) ?> <span style="color:#718096;font-size:9pt"><?= htmlspecialchars($d->satuan) ?></span></td>
      <td style="text-align:center;font-weight:bold"><?= $d->jumlah ?></td>
      <td><?= htmlspecialchars($d->aturan_pakai ?: '-') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($resep->catatan): ?>
<div class="catatan"><strong>Catatan:</strong> <?= htmlspecialchars($resep->catatan) ?></div>
<?php endif; ?>

<div class="ttd">
  <p style="margin-bottom:40px"><?= tgl_indonesia($resep->tanggal) ?></p>
  <p><strong><?= htmlspecialchars($resep->nama_dokter) ?></strong></p>
  <?php if ($resep->no_sip): ?><p style="font-size:9pt">SIP: <?= htmlspecialchars($resep->no_sip) ?></p><?php endif; ?>
</div>
<div class="clear"></div>

<div class="footer">
  Dokumen ini digenerate oleh SuratSmart pada <?= date('d/m/Y H:i') ?>
</div>
</body>
</html>
