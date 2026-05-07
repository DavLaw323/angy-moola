<?php
session_start();
require_once 'koneksi.php';

$pesan = "";

// Cek apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash password demi keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query simpan data
    $query = "INSERT INTO users (nama_lengkap, email, password) VALUES ('$nama', '$email', '$hashed_password')";

    if ($koneksi->query($query) === TRUE) {
        $pesan = "<div class='alert alert-success text-center' style='border-radius: 0.5rem;'>
                    Registrasi berhasil! Mengalihkan ke Login...
                  </div>";
        
        // Script redirect otomatis
        $pesan .= "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                  </script>";
    } else {
        $pesan = "<div class='alert alert-danger' style='border-radius: 0.5rem;'>Terjadi kesalahan: " . $koneksi->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Memberikan warna background abu-abu sangat muda agar form lebih menonjol */
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    
    <div class="card border-0 shadow-lg p-4 p-md-5" style="width: 100%; max-width: 450px; border-radius: 1rem;">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">💰 AngyMoola</h3>
            <p class="text-muted">Buat akun baru untuk mulai mencatat keuanganmu</p>
        </div>
        
        <?= $pesan ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control px-3 py-2" placeholder="Masukkan nama lengkap" required style="border-radius: 0.5rem;">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary">Alamat Email</label>
                <input type="email" name="email" class="form-control px-3 py-2" placeholder="nama@email.com" required style="border-radius: 0.5rem;">
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary">Password</label>
                <input type="password" name="password" class="form-control px-3 py-2" placeholder="••••••••" required style="border-radius: 0.5rem;">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3" style="border-radius: 0.5rem;">
                Buat Akun Sekarang
            </button>
        </form>

        <div class="text-center mt-3 border-top pt-3">
            <p class="text-muted mb-0">Sudah punya akun? 
                <a href="login.php" class="text-primary fw-bold text-decoration-none">Login di sini</a>
            </p>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>