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

// LOGIKA "PENDAPAT SISTEM"
$selisih = $total_pemasukan - $total_pengeluaran;
if ($total_pemasukan == 0 && $total_pengeluaran == 0) {
    $pesan_sistem = "Belum ada aktivitas keuangan bulan ini. Ayo mulai mencatat!";
    $alert_class = "alert-secondary";
    $border_color = "#6c757d";
    $icon_sistem = "bi-info-circle";
} elseif ($selisih > 0) {
    $pesan_sistem = "Keuangan bulan ini <strong>SEHAT</strong>! Kamu punya sisa dana (Surplus Rp " . number_format($selisih, 0, ',', '.') . "). Pertahankan!";
    $alert_class = "alert-success";
    $border_color = "#0ba360"; // Hijau elegan
    $icon_sistem = "bi-emoji-smile";
} elseif ($selisih < 0) {
    $pesan_sistem = "<strong>AWAS!</strong> Pengeluaranmu bulan ini lebih besar dari pemasukan (Defisit Rp " . number_format(abs($selisih), 0, ',', '.') . "). Hati-hati!";
    $alert_class = "alert-danger";
    $border_color = "#cb2d3e"; // Merah elegan
    $icon_sistem = "bi-exclamation-triangle";
} else {
    $pesan_sistem = "Keuanganmu bulan ini seimbang, pengeluaran sama persis dengan pemasukan. Hati-hati tidak bisa menabung.";
    $alert_class = "alert-warning";
    $border_color = "#d4a373"; // Kuning/Gold elegan
    $icon_sistem = "bi-emoji-neutral";
}

$query_transaksi = "SELECT t.*, sc.nama_sub, c.nama_kategori, c.tipe, a.nama_akun FROM transactions t JOIN sub_categories sc ON t.id_sub_kategori = sc.id_sub JOIN categories c ON sc.id_kategori = c.id_kategori JOIN accounts a ON t.id_account = a.id_account WHERE t.id_user = '$id_user' ORDER BY t.tanggal_transaksi DESC, t.id_transaksi DESC LIMIT 10";
$result_transaksi = $koneksi->query($query_transaksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa; /* Latar belakang lebih lembut */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar Senada dengan Login */
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        /* Desain Kartu Ringkasan Premium */
        .card-summary {
            border-radius: 1.2rem;
            border: none;
            overflow: hidden;
            position: relative;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card-summary:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.1);
        }

        /* PERUBAHAN WARNA: Gradient Colors yang Lebih Deep, Matte, dan Mewah */
        .bg-gradient-blue { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; } /* Navy Blue */
        .bg-gradient-green { background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%); color: white; } /* Emerald Green */
        .bg-gradient-red { background: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%); color: white; } /* Crimson Red */

        /* Ikon Latar Belakang Kartu - Dibuat lebih transparan agar tidak norak */
        .card-bg-icon {
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 6rem;
            opacity: 0.12; 
            transform: rotate(-10deg);
        }

        /* Kartu Tabel Kustom */
        .table-card {
            border-radius: 1.2rem;
            border: none;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.03);
            background: white;
        }

        .table-card .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f0f0f0;
            padding: 1.5rem;
        }

        /* Alert Kustom - Lebih bersih */
        .alert-custom {
            border-radius: 1rem;
            border: 1px solid rgba(0,0,0,0.03);
            border-left: 6px solid <?= $border_color ?>;
            box-shadow: 0 0.2rem 0.8rem rgba(0,0,0,0.03);
            background-color: white;
            color: #495057;
        }

        /* Tombol Navbar Kustom */
        .btn-nav {
            border-radius: 20px;
            padding: 6px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-nav:hover { transform: scale(1.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="dashboard.php"><i class="bi bi-wallet2 me-2"></i>AngyMoola</a>
        <div class="d-flex align-items-center">
            <span class="navbar-text me-4 text-white opacity-75">Halo, <strong class="text-white"><?= $nama_user ?></strong></span>
            <a href="laporan.php" class="btn btn-outline-light btn-sm btn-nav me-2"><i class="bi bi-bar-chart-line"></i> Laporan</a>
            <a href="transaksi.php" class="btn btn-warning btn-sm btn-nav me-2 text-dark"><i class="bi bi-plus-circle"></i> Catat</a>
            <a href="logout.php" class="btn btn-danger btn-sm btn-nav" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</nav>

<div class="container">
    
    <div class="alert alert-custom d-flex align-items-center mb-4 py-3" role="alert">
        <i class="bi <?= $icon_sistem ?> fs-2 me-3" style="color: <?= $border_color ?>;"></i>
        <div>
            <h6 class="mb-1 fw-bold text-dark">Analisis Sistem Bulan Ini:</h6>
            <p class="mb-0 text-muted"><?= $pesan_sistem ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card card-summary bg-gradient-blue h-100">
                <div class="card-body p-4">
                    <h6 class="opacity-75 mb-1 fw-semibold">Total Saldo (Seluruh)</h6>
                    <h2 class="fw-bold mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h2>
                    <i class="bi bi-wallet2 card-bg-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card card-summary bg-gradient-green h-100">
                <div class="card-body p-4">
                    <h6 class="opacity-75 mb-1 fw-semibold">Pemasukan (Bulan Ini)</h6>
                    <h2 class="fw-bold mb-0">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h2>
                    <i class="bi bi-graph-up-arrow card-bg-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary bg-gradient-red h-100">
                <div class="card-body p-4">
                    <h6 class="opacity-75 mb-1 fw-semibold">Pengeluaran (Bulan Ini)</h6>
                    <h2 class="fw-bold mb-0">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h2>
                    <i class="bi bi-graph-down-arrow card-bg-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card table-card mb-5">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-clock-history me-2"></i> 10 Transaksi Terakhir</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Tanggal</th>
                            <th>Dompet</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result_transaksi->num_rows > 0) { ?>
                            <?php while($row = $result_transaksi->fetch_assoc()) { 
                                $warna_teks = ($row['tipe'] == 'Income') ? 'text-success' : 'text-danger';
                                $simbol = ($row['tipe'] == 'Income') ? '+' : '-';
                            ?>
                            <tr>
                                <td class="ps-4 py-3 text-muted"><?= date('d M Y', strtotime($row['tanggal_transaksi'])) ?></td>
                                <td><span class="badge bg-secondary rounded-pill fw-normal"><?= $row['nama_akun'] ?></span></td>
                                <td class="fw-medium text-dark"><?= $row['nama_sub'] ?></td>
                                <td class="text-muted small"><?= $row['keterangan'] ? $row['keterangan'] : '-' ?></td>
                                <td class="text-end fw-bold <?= $warna_teks ?>"><?= $simbol ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?></td>
                                <td class="text-center pe-4">
                                    <a href="edit_transaksi.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="#" onclick="konfirmasiHapus('hapus_transaksi.php?id=<?= $row['id_transaksi'] ?>')" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm ms-1" title="Hapus"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-3 d-block mb-2"></i>Belum ada transaksi bulan ini.</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function konfirmasiHapus(url) {
    Swal.fire({
        title: 'Hapus transaksi ini?',
        text: "Data tidak bisa dikembalikan setelah dihapus.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#cb2d3e',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        borderRadius: '1.2rem'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

</body>
</html>