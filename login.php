<?php
// 1. Mulai Session (Harus diletakkan di baris paling atas)
session_start();

// 2. Panggil koneksi database
require_once 'koneksi.php';

// Jika user sudah login, arahkan langsung ke halaman dashboard
if (isset($_SESSION['id_user'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan = "";

// 3. Logika saat tombol login ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password_input = $_POST['password'];

    // Cari data user di database berdasarkan email yang diinput
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = $koneksi->query($query);

    // Cek apakah email ditemukan
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Ambil data user
        
        // 4. Verifikasi Password: Cek apakah password input cocok dengan password acak di database
        if (password_verify($password_input, $user['password'])) {
            
            // 5. Buat Session (Kartu Identitas)
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            
            // 6. Arahkan ke halaman utama (Dashboard)
            header("Location: dashboard.php");
            exit;
        } else {
            // Jika password salah
            $pesan = "<div class='alert alert-danger'>Password yang Anda masukkan salah!</div>";
        }
    } else {
        // Jika email tidak ada di database
        $pesan = "<div class='alert alert-warning'>Email belum terdaftar. Silakan daftar dulu!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AngyMoola</title>
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0">Masuk AngyMoola</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Menampilkan pesan error jika ada -->
                    <?= $pesan ?>

                    <!-- Ingat autocomplete="off" agar tidak otomatis diisi browser -->
                    <form method="POST" action="" autocomplete="off">
                        <div class="mb-3">
                            <label for="email" class="form-label text-muted">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="nama@email.com">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label text-muted">Password</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required placeholder="Masukkan password Anda">
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2">Masuk</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <small class="text-muted">Belum punya akun? <a href="register.php" class="text-success">Daftar di sini</a></small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>