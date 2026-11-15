<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $koneksi = new mysqli('localhost', 'root', '', 'db_penjualan');
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}

function e($teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function validTanggal(?string $tgl): bool
{
    if ($tgl === null || $tgl === '') {
        return false;
    }
    $d = DateTime::createFromFormat('Y-m-d', $tgl);
    return $d !== false && $d->format('Y-m-d') === $tgl;
}
function tglIndo(string $tgl): string
{
    $bulan = [
        '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
        '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Agu',
        '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des',
    ];
    [$th, $bl, $tg] = explode('-', $tgl);
    return $tg . ' ' . $bulan[$bl] . ' ' . $th;
}

function ambilRekap(mysqli $k, string $mulai, string $selesai): array
{
    $sql = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
            FROM transaksi
            WHERE waktu_transaksi >= ?
              AND waktu_transaksi < DATE_ADD(?, INTERVAL 1 DAY)
            GROUP BY DATE(waktu_transaksi)
            ORDER BY tgl ASC";
    $st = $k->prepare($sql);
    $st->bind_param('ss', $mulai, $selesai);
    $st->execute();
    $hasil = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
    return $hasil;
}

function ambilPelangganUnik(mysqli $k, string $mulai, string $selesai): int
{
    $sql = "SELECT COUNT(DISTINCT pelanggan_id) AS jml
            FROM transaksi
            WHERE waktu_transaksi >= ?
              AND waktu_transaksi < DATE_ADD(?, INTERVAL 1 DAY)";
    $st = $k->prepare($sql);
    $st->bind_param('ss', $mulai, $selesai);
    $st->execute();
    $baris = $st->get_result()->fetch_assoc();
    $st->close();
    return (int) ($baris['jml'] ?? 0);
}
