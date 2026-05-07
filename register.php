<?php
session_start();
require_once 'koneksi.php';

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (nama_lengkap, email, password) VALUES ('$nama', '$email', '$hashed_password')";

    if ($koneksi->query($query) === TRUE) {
        $pesan = "<div class='alert alert-success shadow-sm rounded-3'><i class='bi bi-check-circle'></i> Registrasi berhasil! Mengalihkan ke Login...</div>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
              </script>";
    } else {
        $pesan = "<div class='alert alert-danger shadow-sm rounded-3'>Terjadi kesalahan: " . $koneksi->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-icon {
            font-size: 3rem;
            background: -webkit-linear-gradient(#667eea, #764ba2);
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .btn-register {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.8rem;
            border-radius: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(118, 75, 162, 0.4);
            color: white;
        }

        .form-control {
            border-radius: 0.8rem;
            padding: 1rem 0.75rem;
        }
        
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }
    </style>
</head>
<body>

<div class="register-card text-center">
    <div class="mb-4">
        <i class="bi bi-person-plus brand-icon d-block"></i>
        <h3 class="fw-bold mt-2 mb-1" style="color: #2d3748;">Daftar Akun</h3>
        <p class="text-muted small">Mulai kelola keuanganmu dengan AngyMoola hari ini.</p>
    </div>

    <?= $pesan ?>

    <form method="POST" action="">
        <div class="form-floating mb-3 text-start">
            <input type="text" class="form-control" id="floatNama" name="nama_lengkap" placeholder="Nama Lengkap" required>
            <label for="floatNama" class="text-muted"><i class="bi bi-person me-1"></i> Nama Lengkap</label>
        </div>

        <div class="form-floating mb-3 text-start">
            <input type="email" class="form-control" id="floatEmail" name="email" placeholder="nama@email.com" required>
            <label for="floatEmail" class="text-muted"><i class="bi bi-envelope me-1"></i> Alamat Email</label>
        </div>
        
        <div class="form-floating mb-4 text-start">
            <input type="password" class="form-control" id="floatPass" name="password" placeholder="Password" required>
            <label for="floatPass" class="text-muted"><i class="bi bi-lock me-1"></i> Buat Password</label>
        </div>
        
        <button type="submit" class="btn btn-register w-100 mb-3">
            DAFTAR SEKARANG
        </button>
    </form>

    <div class="mt-4 pt-3 border-top">
        <p class="text-muted small mb-0">Sudah punya akun? <br>
            <a href="login.php" class="fw-bold text-decoration-none" style="color: #764ba2;">Login di sini</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>