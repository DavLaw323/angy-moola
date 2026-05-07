<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// 1. Ambil daftar tahun untuk filter
$query_tahun = "SELECT DISTINCT YEAR(tanggal_transaksi) as thn FROM transactions WHERE id_user = '$id_user' ORDER BY thn DESC";
$result_tahun = $koneksi->query($query_tahun);

$bulan_array = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$pengeluaran_array = array_fill(0, 12, 0);
$pemasukan_array = array_fill(0, 12, 0);

// Inisialisasi Total Keseluruhan untuk Ringkasan
$grand_total_in = 0;
$grand_total_out = 0;

// 2. Ambil Data Pengeluaran untuk Grafik
$query_out = "SELECT MONTH(t.tanggal_transaksi) AS bulan, SUM(t.nominal) AS total 
              FROM transactions t 
              JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
              JOIN categories c ON sc.id_kategori = c.id_kategori 
              WHERE t.id_user = '$id_user' AND c.tipe = 'Expense' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
              GROUP BY MONTH(t.tanggal_transaksi)";
$res_out = $koneksi->query($query_out);
if ($res_out && $res_out->num_rows > 0) {
    while ($row = $res_out->fetch_assoc()) {
        $pengeluaran_array[$row['bulan'] - 1] = $row['total'];
        $grand_total_out += $row['total']; // Hitung grand total
    }
}

// 3. Ambil Data Pemasukan untuk Grafik
$query_in = "SELECT MONTH(t.tanggal_transaksi) AS bulan, SUM(t.nominal) AS total 
             FROM transactions t 
             JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
             JOIN categories c ON sc.id_kategori = c.id_kategori 
             WHERE t.id_user = '$id_user' AND c.tipe = 'Income' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
             GROUP BY MONTH(t.tanggal_transaksi)";
$res_in = $koneksi->query($query_in);
if ($res_in && $res_in->num_rows > 0) {
    while ($row = $res_in->fetch_assoc()) {
        $pemasukan_array[$row['bulan'] - 1] = $row['total'];
        $grand_total_in += $row['total']; // Hitung grand total
    }
}

// 4. AMBIL DATA BUKU KAS
$query_bukukas = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe 
                  FROM transactions t 
                  JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
                  JOIN categories c ON sc.id_kategori = c.id_kategori 
                  WHERE t.id_user = '$id_user' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
                  ORDER BY t.tanggal_transaksi ASC, t.id_transaksi ASC";
$result_bukukas = $koneksi->query($query_bukukas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis & Laporan - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px 0; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .card-premium { border-radius: 1.5rem; border: none; box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.04); background: white; overflow: hidden; }
        .card-header-premium { background-color: transparent; border-bottom: 1px solid #f0f0f0; padding: 1.5rem 2rem; }
        .btn-filter { background: #1e3c72; color: white; border-radius: 10px; padding: 6px 15px; font-weight: 600; border: none; }
        .btn-export { background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%); color: white; border-radius: 10px; padding: 6px 15px; font-weight: 600; border: none; transition: all 0.3s; }
        .table thead th { background-color: #fcfcfc; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 15px; border-bottom: 2px solid #f0f0f0; }
        .table tbody td { padding: 15px; color: #495057; font-size: 0.9rem; border-bottom: 1px solid #f8f9fa; }
        .saldo-badge { background: #f1f3f9; color: #1e3c72; font-weight: 700; padding: 5px 12px; border-radius: 8px; display: inline-block; }
        .footer-total { background-color: #f1f3f9; font-weight: bold; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="dashboard.php"><i class="bi bi-wallet2 me-2"></i>AngyMoola</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
    </div>
</nav>

<div class="container mb-5">
    
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Laporan Keuangan <?= $tahun_pilih ?></h4>
        </div>
        <div class="col-md-6 mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <form method="GET" action="" class="d-flex">
                <select name="tahun" class="form-select form-select-sm me-2 border-0 shadow-sm" style="border-radius: 10px; width: 100px;">
                    <?php 
                    if($result_tahun && $result_tahun->num_rows > 0) {
                        while($thn = $result_tahun->fetch_assoc()) { 
                            $selected = ($thn['thn'] == $tahun_pilih) ? 'selected' : '';
                            echo "<option value='{$thn['thn']}' $selected>{$thn['thn']}</option>";
                        }
                    } else {
                        echo "<option value='$tahun_pilih'>$tahun_pilih</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-filter shadow-sm"><i class="bi bi-funnel"></i> Filter</button>
            </form>
            <a href="export_excel.php?tahun=<?= $tahun_pilih ?>" class="btn btn-export shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-premium p-3 border-start border-success border-4 shadow-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Pemasukan</small>
                <h4 class="mb-0 fw-bold text-success">Rp <?= number_format($grand_total_in, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium p-3 border-start border-danger border-4 shadow-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Pengeluaran</small>
                <h4 class="mb-0 fw-bold text-danger">Rp <?= number_format($grand_total_out, 0, ',', '.') ?></h4>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium p-3 border-start border-primary border-4 shadow-sm">
                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Saldo Akhir Tahun</small>
                <h4 class="mb-0 fw-bold text-primary">Rp <?= number_format($grand_total_in - $grand_total_out, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>

    <div class="card card-premium mb-4">
        <div class="card-header-premium">
            <h6 class="mb-0 fw-bold text-secondary">Grafik Perbandingan Bulanan</h6>
        </div>
        <div class="card-body p-4">
            <canvas id="grafikKomparasi" height="80"></canvas>
        </div>
    </div>

    <div class="card card-premium">
        <div class="card-header-premium">
            <h6 class="mb-0 fw-bold text-secondary"><i class="bi bi-journal-text me-2"></i>Detail Buku Kas</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-start">Keterangan</th>
                            <th>Kategori</th>
                            <th>Masuk (D)</th>
                            <th>Keluar (K)</th>
                            <th>Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $saldo_berjalan = 0;
                        if($result_bukukas && $result_bukukas->num_rows > 0) { 
                            while($row = $result_bukukas->fetch_assoc()) { 
                                if ($row['tipe'] == 'Income') {
                                    $saldo_berjalan += $row['nominal'];
                                    $masuk = $row['nominal'];
                                    $keluar = 0;
                                } else {
                                    $saldo_berjalan -= $row['nominal'];
                                    $masuk = 0;
                                    $keluar = $row['nominal'];
                                }
                        ?>
                        <tr class="text-center">
                            <td class="text-muted"><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                            <td class="text-start fw-medium"><?= $row['keterangan'] ? $row['keterangan'] : '-' ?></td>
                            <td><span class="badge bg-light text-dark fw-normal border"><?= $row['nama_sub'] ?></span></td>
                            <td class="text-end text-success fw-bold"><?= $masuk > 0 ? 'Rp ' . number_format($masuk, 0, ',', '.') : '-' ?></td>
                            <td class="text-end text-danger fw-bold"><?= $keluar > 0 ? 'Rp ' . number_format($keluar, 0, ',', '.') : '-' ?></td>
                            <td class="text-end"><span class="saldo-badge">Rp <?= number_format($saldo_berjalan, 0, ',', '.') ?></span></td>
                        </tr>
                        <?php } ?>
                        <tr class="footer-total text-center">
                            <td colspan="3" class="text-end py-3">TOTAL KESELURUHAN :</td>
                            <td class="text-end text-success">Rp <?= number_format($grand_total_in, 0, ',', '.') ?></td>
                            <td class="text-end text-danger">Rp <?= number_format($grand_total_out, 0, ',', '.') ?></td>
                            <td class="text-end"><span class="badge bg-primary px-3 py-2">Rp <?= number_format($grand_total_in - $grand_total_out, 0, ',', '.') ?></span></td>
                        </tr>
                        <?php } else { ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted">Tidak ada data transaksi.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('grafikKomparasi').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan_array) ?>,
            datasets: [
                { label: 'Pemasukan', data: <?= json_encode($pemasukan_array) ?>, backgroundColor: '#0ba360', borderRadius: 6 },
                { label: 'Pengeluaran', data: <?= json_encode($pengeluaran_array) ?>, backgroundColor: '#cb2d3e', borderRadius: 6 }
            ]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
</body>
</html>