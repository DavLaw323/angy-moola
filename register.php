<?php
// 1. Panggil koneksi database
require_once 'koneksi.php';

$pesan = "";

// 2. Cek apakah form sudah disubmit (tombol daftar ditekan)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tangkap data dari form
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 3. Keamanan: Hash password agar tidak tersimpan dalam bentuk teks biasa
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. Query untuk menyimpan data ke tabel users
    $query = "INSERT INTO users (nama_lengkap, email, password) VALUES ('$nama', '$email', '$hashed_password')";

    if ($koneksi->query($query) === TRUE) {
        $pesan = "<div class='alert alert-success'>Registrasi berhasil! Silakan lanjut ke halaman Login.</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Terjadi kesalahan: " . $koneksi->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - AngyMoola</title>
    <!-- Memanggil CSS Bootstrap dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0">Daftar Akun AngyMoola</h4>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Menampilkan pesan sukses atau error -->
                    <?= $pesan ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label text-muted">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label text-muted">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="nama@email.com">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label text-muted">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Buat password yang kuat">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">Buat Akun</button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>