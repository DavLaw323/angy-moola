<?php
// Konfigurasi Database
$host       = "localhost";      // Server lokal XAMPP
$username   = "root";           // Username default XAMPP
$password   = "";               // Password default XAMPP (kosong)
$database   = "angy_moola_db";  // Nama database yang kita buat di Workbench

// Membuat koneksi menggunakan ekstensi MySQLi (Object-Oriented)
$koneksi = new mysqli($host, $username, $password, $database);

// Mengecek apakah koneksi berhasil atau gagal
if ($koneksi->connect_error) {
    // Jika gagal, program akan berhenti dan menampilkan pesan error
    die("Koneksi Database Gagal: " . $koneksi->connect_error);
} else {
    // Jika berhasil, pesan ini akan muncul (Bisa dihapus nanti kalau sudah tahap produksi)
    // echo "Mantap! Koneksi ke database angy_moola_db berhasil.";
}
?>