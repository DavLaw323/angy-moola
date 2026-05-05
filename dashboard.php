<?php
session_start();
require_once 'koneksi.php';

// 1. Keamanan: Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];

// 2. Hitung Total Pemasukan
$query_in = "SELECT SUM(t.nominal) as total_in FROM transactions t 
             JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
             JOIN categories c ON sc.id_kategori = c.id_kategori 
             WHERE t.id_user = '$id_user' AND c.tipe = 'Income'";
$result_in = $koneksi->query($query_in);
$row_in = $result_in->fetch_assoc();
$total_pemasukan = $row_in['total_in'] ? $row_in['total_in'] : 0;

// 3. Hitung Total Pengeluaran
$query_out = "SELECT SUM(t.nominal) as total_out FROM transactions t 
              JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub 
              JOIN categories c ON sc.id_kategori = c.id_kategori 
              WHERE t.id_user = '$id_user' AND c.tipe = 'Expense'";
$result_out = $koneksi->query($query_out);
$row_out = $result_out->fetch_assoc();
$total_pengeluaran = $row_out['total_out'] ? $row_out['total_out'] : 0;

// 4. Hitung Saldo Akhir
$saldo_akhir = $total_pemasukan - $total_pengeluaran;

// 5. Ambil Data Riwayat Transaksi Terbaru (Menggabungkan 4 Tabel!)
$query_transaksi = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe, a.nama_akun 
                    FROM transactions t
                    JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub
                    JOIN categories c ON sc.id_kategori = c.id_kategori
                    JOIN accounts a ON t.id_account = a.id_account
                    WHERE t.id_user = '$id_user' 
                    ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC 
                    LIMIT 10"; // Tampilkan 10 transaksi terakhir saja
$result_transaksi = $koneksi->query($query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">💰 AngyMoola</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">Halo, <strong><?= $nama_user ?></strong></span>
            <a href="transaksi.php" class="btn btn-warning btn-sm me-2 fw-bold">+ Catat Transaksi</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Keluar</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Bagian Kartu Ringkasan (Summary) -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Saldo</h6>
                    <h3>Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Pemasukan</h6>
                    <h3>Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total Pengeluaran</h6>
                    <h3>Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Bagian Tabel Riwayat Transaksi -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary">Riwayat Transaksi Terakhir</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Dompet</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result_transaksi->num_rows > 0) { ?>
                            <?php while($row = $result_transaksi->fetch_assoc()) { 
                                // Tentukan warna teks (Hijau untuk Income, Merah untuk Expense)
                                $warna_teks = ($row['tipe'] == 'Income') ? 'text-success' : 'text-danger';
                                $simbol = ($row['tipe'] == 'Income') ? '+' : '-';
                            ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                <td><span class="badge bg-secondary"><?= $row['nama_akun'] ?></span></td>
                                <td><?= $row['nama_sub'] ?> <small class="text-muted">(<?= $row['nama_kategori'] ?>)</small></td>
                                <td><?= $row['keterangan'] ?></td>
                                <td class="text-end fw-bold <?= $warna_teks ?>">
                                    <?= $simbol ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi. Ayo catat pengeluaran pertamamu!</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

</body>
</html>