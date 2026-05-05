<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    exit("Akses ditolak.");
}

$id_user = $_SESSION['id_user'];
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Header agar browser mendownload sebagai file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Buku_Kas_AngyMoola_$tahun_pilih.xls");
header("Pragma: no-cache");
header("Expires: 0");

$query_bukukas = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe 
                  FROM transactions t 
                  JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
                  JOIN categories c ON sc.id_kategori = c.id_kategori 
                  WHERE t.id_user = '$id_user' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
                  ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";
$result_bukukas = $koneksi->query($query_bukukas);
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="7" style="font-size: 16px; font-weight: bold; text-align: center; background-color: #D9EAD3; height: 30px;">
                LAPORAN BUKU KAS ANGYMOOLA - TAHUN <?= $tahun_pilih ?>
            </th>
        </tr>
        <tr style="background-color: #f2f2f2; font-weight: bold; text-align: center;">
            <th width="50">No</th>
            <th width="120">Tanggal</th>
            <th width="250">Keterangan</th>
            <th width="150">Kategori</th>
            <th width="150">Masuk (Debit)</th>
            <th width="150">Keluar (Kredit)</th>
            <th width="150">Saldo Berjalan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $saldo_berjalan = 0;
        $total_masuk = 0;
        $total_keluar = 0;

        if($result_bukukas->num_rows > 0) { 
            while($row = $result_bukukas->fetch_assoc()) { 
                if ($row['tipe'] == 'Income') {
                    $saldo_berjalan += $row['nominal'];
                    $total_masuk += $row['nominal'];
                    $masuk = $row['nominal'];
                    $keluar = 0;
                } else {
                    $saldo_berjalan -= $row['nominal'];
                    $total_keluar += $row['nominal'];
                    $masuk = 0;
                    $keluar = $row['nominal'];
                }
        ?>
        <tr>
            <td style="text-align: center;"><?= $no++ ?></td>
            <td style="text-align: center;"><?= date('d-m-Y', strtotime($row['tanggal_transaksi'])) ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
            <td><?= htmlspecialchars($row['nama_sub']) ?></td>
            
            <!-- Perhatikan style mso-number-format:"\@" di bawah ini -->
            <td style='mso-number-format:"\@"; text-align: right;'>
                <?= $masuk > 0 ? number_format($masuk, 0, ',', '.') : '-' ?>
            </td>
            <td style='mso-number-format:"\@"; text-align: right;'>
                <?= $keluar > 0 ? number_format($keluar, 0, ',', '.') : '-' ?>
            </td>
            <td style='mso-number-format:"\@"; text-align: right; font-weight: bold;'>
                <?= number_format($saldo_berjalan, 0, ',', '.') ?>
            </td>
        </tr>
        <?php 
            } 
        } 
        ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #f2f2f2; font-weight: bold;">
            <td colspan="4" style="text-align: right; height: 25px;">TOTAL KESELURUHAN : </td>
            <td style='mso-number-format:"\@"; text-align: right; color: green;'><?= number_format($total_masuk, 0, ',', '.') ?></td>
            <td style='mso-number-format:"\@"; text-align: right; color: red;'><?= number_format($total_keluar, 0, ',', '.') ?></td>
            <td style='mso-number-format:"\@"; text-align: right; background-color: #fff2cc;'><?= number_format($saldo_berjalan, 0, ',', '.') ?></td>
        </tr>
    </tfoot>
</table>