<?php
session_start();

// SATPAM KEAMANAN: Cek apakah user sudah punya session login
// Jika belum, tendang balik ke halaman login!
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Ambil nama user dari session untuk ditampilkan
$nama_user = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AngyMoola</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar Sederhana -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">💰 AngyMoola</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">
                Halo, <strong><?= $nama_user ?></strong>!
            </span>
            <a href="logout.php" class="btn btn-danger btn-sm">Keluar</a>
        </div>
    </div>
</nav>

<!-- Konten Dashboard -->
<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <h2 class="text-muted">Selamat Datang di Dashboard AngyMoola</h2>
                    <p class="lead">Ini adalah pondasi utama aplikasi keuanganmu. Nanti di sini kita akan pasang grafik pengeluaran dan tabel transaksi ala AngyMoola!</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>