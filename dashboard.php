<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];

$bulan_ini = date('m');
$tahun_ini = date('Y');

// Hitung Pemasukan & Pengeluaran Bulan Ini
$query_in = "SELECT SUM(t.nominal) as total_in FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori WHERE t.id_user = '$id_user' AND c.tipe = 'Income' AND MONTH(t.tanggal_transaksi) = '$bulan_ini' AND YEAR(t.tanggal_transaksi) = '$tahun_ini'";
$row_in = $koneksi->query($query_in)->fetch_assoc();
$total_pemasukan = $row_in['total_in'] ? $row_in['total_in'] : 0;

$query_out = "SELECT SUM(t.nominal) as total_out FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori WHERE t.id_user = '$id_user' AND c.tipe = 'Expense' AND MONTH(t.tanggal_transaksi) = '$bulan_ini' AND YEAR(t.tanggal_transaksi) = '$tahun_ini'";
$row_out = $koneksi->query($query_out)->fetch_assoc();
$total_pengeluaran = $row_out['total_out'] ? $row_out['total_out'] : 0;

// Hitung Saldo Keseluruhan
$query_saldo = "SELECT (SELECT SUM(nominal) FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori WHERE id_user = '$id_user' AND c.tipe = 'Income') - (SELECT SUM(nominal) FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori WHERE id_user = '$id_user' AND c.tipe = 'Expense') AS saldo_akhir";
$row_saldo = $koneksi->query($query_saldo)->fetch_assoc();
$saldo_akhir = $row_saldo['saldo_akhir'] ? $row_saldo['saldo_akhir'] : 0;

// LOGIKA "PENDAPAT SISTEM" (AI Sederhana)
$selisih = $total_pemasukan - $total_pengeluaran;
if ($total_pemasukan == 0 && $total_pengeluaran == 0) {
    $pesan_sistem = "Belum ada aktivitas keuangan bulan ini. Ayo mulai mencatat!";
    $alert_class = "alert-secondary";
    $icon_sistem = "bi-info-circle";
} elseif ($selisih > 0) {
    $pesan_sistem = "Keuangan bulan ini <strong>SEHAT</strong>! Kamu punya sisa dana lebih besar dari pengeluaran (Surplus Rp " . number_format($selisih, 0, ',', '.') . "). Pertahankan!";
    $alert_class = "alert-success";
    $icon_sistem = "bi-emoji-smile";
} elseif ($selisih < 0) {
    $pesan_sistem = "<strong>AWAS!</strong> Pengeluaranmu bulan ini lebih besar dari pemasukan (Defisit Rp " . number_format(abs($selisih), 0, ',', '.') . "). Kurangi jajan dan nongkrong ya!";
    $alert_class = "alert-danger";
    $icon_sistem = "bi-exclamation-triangle";
} else {
    $pesan_sistem = "Keuanganmu bulan ini seimbang, pengeluaran sama persis dengan pemasukan. Hati-hati, kamu tidak punya sisa untuk ditabung.";
    $alert_class = "alert-warning";
    $icon_sistem = "bi-emoji-neutral";
}

$query_transaksi = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe, a.nama_akun FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori JOIN accounts a ON t.id_account = a.id_account WHERE t.id_user = '$id_user' ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC LIMIT 10";
$result_transaksi = $koneksi->query($query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-wallet2"></i> AngyMoola</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-3 text-white">Halo, <strong><?= $nama_user ?></strong></span>
            <a href="laporan.php" class="btn btn-info btn-sm me-2 fw-bold text-white"><i class="bi bi-bar-chart-line"></i> Analisis & Grafik</a>
            <a href="transaksi.php" class="btn btn-warning btn-sm me-2 fw-bold"><i class="bi bi-plus-circle"></i> Catat</a>
            <a href="logout.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <!-- Pendapat Sistem -->
    <div class="alert <?= $alert_class ?> shadow-sm d-flex align-items-center" role="alert">
        <i class="bi <?= $icon_sistem ?> fs-4 me-3"></i>
        <div>
            <h6 class="alert-heading mb-1">Analisis Sistem Bulan Ini:</h6>
            <p class="mb-0"><?= $pesan_sistem ?></p>
        </div>
    </div>

    <!-- Kartu Ringkasan -->
    <div class="row mb-4">
        <!-- ... (Kode kartu Saldo, Pemasukan, Pengeluaran tetap sama persis seperti sebelumnya, biarkan utuh jika kamu copy manual, atau gunakan file aslimu tanpa kanvas grafik) ... -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-white-50 mb-1">Total Saldo (Seluruh)</h6><h3 class="mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h3></div>
                    <i class="bi bi-wallet2" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-white-50 mb-1">Pemasukan (Bulan Ini)</h6><h3 class="mb-0">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3></div>
                    <i class="bi bi-graph-up-arrow" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div><h6 class="text-white-50 mb-1">Pengeluaran (Bulan Ini)</h6><h3 class="mb-0">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3></div>
                    <i class="bi bi-graph-down-arrow" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-secondary"><i class="bi bi-clock-history"></i> 10 Transaksi Terakhir</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Tanggal</th><th>Dompet</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Nominal</th><th class="text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php if($result_transaksi->num_rows > 0) { ?>
                            <?php while($row = $result_transaksi->fetch_assoc()) { 
                                $warna_teks = ($row['tipe'] == 'Income') ? 'text-success' : 'text-danger';
                                $simbol = ($row['tipe'] == 'Income') ? '+' : '-';
                            ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                <td><span class="badge bg-secondary"><?= $row['nama_akun'] ?></span></td>
                                <td><?= $row['nama_sub'] ?></td>
                                <td><?= $row['keterangan'] ?></td>
                                <td class="text-end fw-bold <?= $warna_teks ?>"><?= $simbol ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                <td class="text-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                                    <a href="hapus_transaksi.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')"><i class="bi bi-trash3"></i></a>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="6" class="text-center py-4">Belum ada transaksi.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>