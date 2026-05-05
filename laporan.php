<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$tahun_pilih = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Ambil daftar tahun untuk filter
$query_tahun = "SELECT DISTINCT YEAR(tanggal_transaksi) as thn FROM transactions WHERE id_user = '$id_user' ORDER BY thn DESC";
$result_tahun = $koneksi->query($query_tahun);

$bulan_array = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
$pengeluaran_array = array_fill(0, 12, 0);
$pemasukan_array = array_fill(0, 12, 0);

// PERBAIKAN BUG: Relasi tabel disempurnakan (Melewati sub_categories dulu)
// Ambil Data Pengeluaran
$query_out = "SELECT MONTH(t.tanggal_transaksi) AS bulan, SUM(t.nominal) AS total 
              FROM transactions t 
              JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
              JOIN categories c ON sc.id_kategori = c.id_kategori 
              WHERE t.id_user = '$id_user' AND c.tipe = 'Expense' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
              GROUP BY bulan";
$res_out = $koneksi->query($query_out);
if ($res_out) {
    while ($row = $res_out->fetch_assoc()) {
        $pengeluaran_array[$row['bulan'] - 1] = $row['total'];
    }
}

// Ambil Data Pemasukan
$query_in = "SELECT MONTH(t.tanggal_transaksi) AS bulan, SUM(t.nominal) AS total 
             FROM transactions t 
             JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
             JOIN categories c ON sc.id_kategori = c.id_kategori 
             WHERE t.id_user = '$id_user' AND c.tipe = 'Income' AND YEAR(t.tanggal_transaksi) = '$tahun_pilih' 
             GROUP BY bulan";
$res_in = $koneksi->query($query_in);
if ($res_in) {
    while ($row = $res_in->fetch_assoc()) {
        $pemasukan_array[$row['bulan'] - 1] = $row['total'];
    }
}

// AMBIL DATA BUKU KAS (Diurutkan dari tanggal terlama ke terbaru untuk hitung saldo berjalan)
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
    <title>Analisis & Laporan - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-wallet2"></i> AngyMoola</a>
        <div class="d-flex">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="bi bi-bar-chart-line"></i> Laporan Keuangan <?= $tahun_pilih ?></h4>
        
        <div class="d-flex gap-2">
            <!-- Form Filter Tahun -->
            <form method="GET" action="" class="d-flex">
                <select name="tahun" class="form-select form-select-sm me-2">
                    <?php 
                    if($result_tahun->num_rows > 0) {
                        while($thn = $result_tahun->fetch_assoc()) { 
                            $selected = ($thn['thn'] == $tahun_pilih) ? 'selected' : '';
                            echo "<option value='{$thn['thn']}' $selected>{$thn['thn']}</option>";
                        }
                    } else {
                        echo "<option value='$tahun_pilih'>$tahun_pilih</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>
            
            <!-- Tombol Export Excel -->
            <a href="export_excel.php?tahun=<?= $tahun_pilih ?>" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Kanvas Grafik Ganda -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <canvas id="grafikKomparasi" height="80"></canvas>
        </div>
    </div>

    <!-- Tabel Buku Kas -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 text-secondary"><i class="bi bi-journal-text"></i> Buku Kas Detail Tahun <?= $tahun_pilih ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th>Masuk (Debit)</th>
                            <th>Keluar (Kredit)</th>
                            <th>Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $saldo_berjalan = 0;
                        if($result_bukukas->num_rows > 0) { 
                            while($row = $result_bukukas->fetch_assoc()) { 
                                // Hitung Saldo
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
                        <tr>
                            <td class="text-center"><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                            <td><?= $row['keterangan'] ? $row['keterangan'] : '-' ?></td>
                            <td><?= $row['nama_sub'] ?></td>
                            <td class="text-end text-success"><?= $masuk > 0 ? 'Rp ' . number_format($masuk, 0, ',', '.') : '-' ?></td>
                            <td class="text-end text-danger"><?= $keluar > 0 ? 'Rp ' . number_format($keluar, 0, ',', '.') : '-' ?></td>
                            <td class="text-end fw-bold">Rp <?= number_format($saldo_berjalan, 0, ',', '.') ?></td>
                        </tr>
                        <?php } } else { ?>
                            <tr><td colspan="6" class="text-center py-4">Belum ada data untuk tahun ini.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('grafikKomparasi').getContext('2d');
    const grafikKomparasi = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan_array) ?>,
            datasets: [
                {
                    label: 'Pemasukan (Rp)',
                    data: <?= json_encode($pemasukan_array) ?>,
                    backgroundColor: 'rgba(25, 135, 84, 0.8)', 
                    borderRadius: 3
                },
                {
                    label: 'Pengeluaran (Rp)',
                    data: <?= json_encode($pengeluaran_array) ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.8)', 
                    borderRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

</body>
</html>