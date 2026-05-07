<?php
session_start();
require_once 'koneksi.php';

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $koneksi->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Cek password hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $pesan = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-exclamation-octagon'></i> Password yang kamu masukkan salah!</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger shadow-sm rounded-3'><i class='bi bi-person-x'></i> Email belum terdaftar, yuk daftar dulu!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Desain Background Gradient Modern */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Efek Glassmorphism (Kaca) pada Kartu Login */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1.5rem;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 3rem 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-icon {
            font-size: 3.5rem;
            background: -webkit-linear-gradient(#667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        /* Animasi Keren pada Tombol */
        .btn-login {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 0.8rem;
            border-radius: 0.8rem;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(118, 75, 162, 0.4);
            color: white;
        }

        /* Menghaluskan Input Box */
        .form-control {
            border-radius: 0.8rem;
            border: 1px solid #dee2e6;
            padding: 1rem 0.75rem;
        }
        
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card text-center slide-up">
        
        <div class="mb-4">
            <i class="bi bi-wallet2 brand-icon d-block"></i>
            <h3 class="fw-bold mt-2 mb-1" style="color: #2d3748;">AngyMoola</h3>
            <p class="text-muted small">Selamat datang kembali! Silakan masuk ke akunmu.</p>
        </div>

        <?= $pesan ?>

        <form method="POST" action="">
            
            <div class="form-floating mb-3 text-start">
                <input type="email" class="form-control" id="floatingEmail" name="email" placeholder="nama@email.com" required>
                <label for="floatingEmail" class="text-muted"><i class="bi bi-envelope me-1"></i> Alamat Email</label>
            </div>
            
            <div class="form-floating mb-4 text-start">
                <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                <label for="floatingPassword" class="text-muted"><i class="bi bi-lock me-1"></i> Password</label>
            </div>
            
            <button type="submit" class="btn btn-login w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i> MASUK SEKARANG
            </button>
        </form>

        <div class="mt-4 pt-3 border-top">
            <p class="text-muted small mb-0">Belum punya akun AngyMoola? <br>
                <a href="register.php" class="fw-bold text-decoration-none" style="color: #764ba2;">Daftar di sini sekarang!</a>
            </p>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>