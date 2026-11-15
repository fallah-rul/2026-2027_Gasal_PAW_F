<?php
require 'koneksi.php';

$mulai   = $_GET['mulai']   ?? '';
$selesai = $_GET['selesai'] ?? '';

$pesan_error      = '';
$tampilkan        = false; 
$rekap            = [];
$labels           = [];
$data_total       = [];
$total_pendapatan = 0;
$total_pelanggan  = 0;

if ($mulai !== '' || $selesai !== '') {
    if (!validTanggal($mulai) || !validTanggal($selesai)) {
        $pesan_error = 'Tanggal mulai dan tanggal selesai harus diisi dengan format yang benar.';
    } elseif ($mulai > $selesai) {
        $pesan_error = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
    } else {
        $tampilkan       = true;
        $rekap           = ambilRekap($koneksi, $mulai, $selesai);
        $total_pelanggan = ambilPelangganUnik($koneksi, $mulai, $selesai);

        foreach ($rekap as $r) {
            $labels[]          = $r['tgl'];
            $data_total[]      = (float) $r['total_harian'];
            $total_pendapatan += (float) $r['total_harian'];
        }
    }
}

$ada_data = $tampilkan && !empty($rekap);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan dan Grafik</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 24px auto; max-width: 900px; padding: 0 16px; color: #222; }
        h1 { font-size: 26px; margin: 0 0 16px; }
        h1 small { font-size: 16px; color: #666; font-weight: normal; }
        h2 { font-size: 18px; margin: 24px 0 8px; }
        form { margin-bottom: 12px; }
        input[type=date] { padding: 6px; }
        button, .btn { padding: 7px 14px; border: 0; border-radius: 4px; background: #e8590c; color: #fff; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        button.biru { background: #1c5fa8; }
        .aksi { margin: 12px 0; }
        .error { background: #fde8e8; color: #9b1c1c; padding: 8px 12px; border-radius: 4px; margin-bottom: 12px; }
        .info { background: #f1f5f9; padding: 8px 12px; border-radius: 4px; }
        .kotak-grafik { position: relative; width: 100%; height: 320px; }
        table { border-collapse: collapse; width: 100%; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #d9eaf7; }
        .ringkasan { display: flex; gap: 0; margin-top: 16px; }
        .kartu { flex: 1; background: #d9eaf7; padding: 12px 16px; border: 1px solid #b8d4ea; }
        .kartu span { display: block; color: #345; font-size: 14px; }
        .kartu strong { display: block; text-align: right; font-size: 20px; margin-top: 6px; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; max-width: none; padding: 0; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table, .ringkasan, .kotak-grafik { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<h1>
    Rekap Laporan Penjualan
    <?php if ($tampilkan): ?>
        <small><?= e($mulai) ?> sampai <?= e($selesai) ?></small>
    <?php endif; ?>
</h1>

<!-- Form filter dan tombol aksi: tidak ikut tercetak -->
<div class="no-print">
    <form method="GET" action="laporan.php">
        Dari: <input type="date" name="mulai" value="<?= e($mulai) ?>" required>
        Sampai: <input type="date" name="selesai" value="<?= e($selesai) ?>" required>
        <button type="submit" class="biru">Tampilkan</button>
    </form>

    <?php if ($pesan_error !== ''): ?>
        <div class="error"><?= e($pesan_error) ?></div>
    <?php endif; ?>

    <?php if ($ada_data): ?>
        <div class="aksi">
            <button type="button" onclick="window.print()">Cetak</button>
            <a class="btn" href="export_excel.php?mulai=<?= urlencode($mulai) ?>&amp;selesai=<?= urlencode($selesai) ?>">Excel</a>
        </div>
    <?php endif; ?>
</div>

<?php if (!$tampilkan && $pesan_error === ''): ?>
    <p class="info no-print">Pilih rentang tanggal lalu tekan <b>Tampilkan</b> untuk melihat laporan.</p>
<?php endif; ?>

<?php if ($tampilkan && !$ada_data): ?>
    <p class="info">Tidak ada transaksi pada rentang tanggal tersebut.</p>
<?php endif; ?>

<?php if ($ada_data): ?>
    <h2>Grafik Penjualan</h2>
    <div class="kotak-grafik"><canvas id="grafikPenjualan"></canvas></div>

    <h2>Rekapitulasi</h2>
    <table>
        <tr><th>No</th><th>Total Pendapatan</th><th>Tanggal Transaksi</th></tr>
        <?php $no = 1; foreach ($rekap as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>Rp. <?= number_format((float) $row['total_harian'], 0, ',', '.') ?></td>
                <td><?= e(tglIndo($row['tgl'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="ringkasan">
        <div class="kartu">
            <span>Jumlah Pelanggan</span>
            <strong><?= (int) $total_pelanggan ?> Orang</strong>
        </div>
        <div class="kartu">
            <span>Jumlah Pendapatan</span>
            <strong>Rp. <?= number_format($total_pendapatan, 0, ',', '.') ?></strong>
        </div>
    </div>

    <script>
        const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');

        new Chart(document.getElementById('grafikPenjualan'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Total',
                    data: <?= json_encode($data_total) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,   // supaya grafik langsung utuh saat dicetak
                scales: {
                    x: { title: { display: true, text: 'Tanggal Transaksi' } },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Pendapatan (Rp)' },
                        ticks: { callback: (v) => rupiah(v) }
                    }
                },
                plugins: {
                    tooltip: { callbacks: { label: (ctx) => 'Total: ' + rupiah(ctx.parsed.y) } }
                }
            }
        });
    </script>
<?php endif; ?>

</body>
</html>
