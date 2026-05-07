<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Header agar browser mendeteksi ini sebagai file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Keuangan_AngyMoola_$tahun_pilih.xls");

// Query mengambil data lengkap
$query_bukukas = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe, a.nama_akun 
                  FROM transactions t 
                  JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
                  JOIN categories c ON sc.id_kategori = c.id_kategori 
                  JOIN accounts a ON t.id_account = a.id_account
                  WHERE t.id_user = '$id_user' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
                  ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";

$result = $koneksi->query($query_bukukas);

// Inisialisasi Penampung Total
$total_pemasukan = 0;
$total_pengeluaran = 0;
$saldo_akhir = 0;
?>

<style>
    body, table {
        font-family: "Times New Roman", Times, serif;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid #000;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .header-title { font-size: 16pt; font-weight: bold; }
    .total-row { background-color: #eee; font-weight: bold; }
</style>

<div class="header-title">LAPORAN KEUANGAN ANGYMOOLA</div>
<div style="font-size: 12pt;">Tahun Anggaran: <?= $tahun_pilih ?></div>
<br>

<table>
    <thead>
        <tr>
            <th width="50">No</th>
            <th width="120">Tanggal</th>
            <th width="150">Dompet/Akun</th>
            <th width="150">Kategori</th>
            <th width="250">Keterangan</th>
            <th width="120">Pemasukan (Rp)</th>
            <th width="120">Pengeluaran (Rp)</th>
            <th width="150">Saldo Berjalan (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if($result && $result->num_rows > 0) { 
            while($row = $result->fetch_assoc()) { 
                if ($row['tipe'] == 'Income') {
                    $masuk = $row['nominal'];
                    $keluar = 0;
                    $saldo_akhir += $masuk;
                    $total_pemasukan += $masuk;
                } else {
                    $masuk = 0;
                    $keluar = $row['nominal'];
                    $saldo_akhir -= $keluar;
                    $total_pengeluaran += $keluar;
                }
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_transaksi'])) ?></td>
            <td><?= $row['nama_akun'] ?></td>
            <td><?= $row['nama_sub'] ?></td>
            <td class="text-left"><?= $row['keterangan'] ? $row['keterangan'] : '-' ?></td>
            <td class="text-right"><?= $masuk != 0 ? number_format($masuk, 0, ',', '.') : '-' ?></td>
            <td class="text-right"><?= $keluar != 0 ? number_format($keluar, 0, ',', '.') : '-' ?></td>
            <td class="text-right" style="background-color: #f9f9f9;"><?= number_format($saldo_akhir, 0, ',', '.') ?></td>
        </tr>
        <?php 
            } 
        } else {
            echo "<tr><td colspan='8'>Data tidak ditemukan untuk tahun ini.</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5">TOTAL KESELURUHAN</td>
            <td class="text-right">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
            <td class="text-right">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
            <td class="text-right">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>

<br><br>

<table style="width: 300px;">
    <tr>
        <th colspan="2" style="background-color: #333; color: #fff;">RINGKASAN KEUANGAN</th>
    </tr>
    <tr>
        <td class="text-left">Total Pemasukan</td>
        <td class="text-right">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
    </tr>
    <tr>
        <td class="text-left">Total Pengeluaran</td>
        <td class="text-right">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
    </tr>
    <tr style="font-weight: bold; background-color: #ffff00;">
        <td class="text-left">SALDO AKHIR</td>
        <td class="text-right">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></td>
    </tr>
</table>

<br>
<div style="font-size: 10pt; font-style: italic;">
    Dicetak otomatis oleh Sistem AngyMoola pada: <?= date('d/m/Y H:i:s') ?>
</div>