<?php
require 'koneksi.php';

$mulai   = $_GET['mulai']   ?? '';
$selesai = $_GET['selesai'] ?? '';

if (!validTanggal($mulai) || !validTanggal($selesai) || $mulai > $selesai) {
    http_response_code(400);
    die('Rentang tanggal tidak valid.');
}

$rekap           = ambilRekap($koneksi, $mulai, $selesai);
$total_pelanggan = ambilPelangganUnik($koneksi, $mulai, $selesai);

$total_pendapatan = 0;
foreach ($rekap as $r) {
    $total_pendapatan += (float) $r['total_harian'];
}

$nama_file = 'rekap_penjualan_' . $mulai . '_sd_' . $selesai . '.xls';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<html>
<head><meta charset="UTF-8"></head>
<body>
<table border="1">
    <tr>
        <th colspan="3" style="font-size:14pt;">Rekap Laporan Penjualan</th>
    </tr>
    <tr>
        <td colspan="3">Periode: <?= e(tglIndo($mulai)) ?> sampai <?= e(tglIndo($selesai)) ?></td>
    </tr>
    <tr style="background:#d9eaf7; font-weight:bold;">
        <th>No</th>
        <th>Total Pendapatan</th>
        <th>Tanggal Transaksi</th>
    </tr>
    <?php $no = 1; foreach ($rekap as $row): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td style="mso-number-format:'\#\,\#\#0';"><?= (float) $row['total_harian'] ?></td>
        <td style="mso-number-format:'\@';"><?= e(tglIndo($row['tgl'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="font-weight:bold;">
        <td colspan="2">Jumlah Pelanggan Unik</td>
        <td><?= (int) $total_pelanggan ?> Orang</td>
    </tr>
    <tr style="font-weight:bold;">
        <td>Jumlah Pendapatan</td>
        <td style="mso-number-format:'\#\,\#\#0';"><?= $total_pendapatan ?></td>
        <td></td>
    </tr>
</table>
</body>
</html>
