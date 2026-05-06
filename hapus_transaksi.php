<?php
session_start();
require_once 'koneksi.php';

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Mengecek apakah ada parameter 'id' yang dikirim dari URL
if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];
    $id_user = $_SESSION['id_user']; // Sebagai lapisan keamanan tambahan

    // Query untuk menghapus data. 
    // AND id_user = '$id_user' memastikan user hanya bisa menghapus datanya sendiri
    $query = "DELETE FROM transactions WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'";

    if ($koneksi->query($query) === TRUE) {
        // Jika berhasil, munculkan alert javascript dan kembalikan ke dashboard
        echo "<script>
                alert('Data transaksi berhasil dihapus!');
                window.location.href='dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data: " . $koneksi->error . "');
                window.location.href='dashboard.php';
              </script>";
    }
} else {
    // Jika tidak ada ID di URL, tendang balik ke dashboard
    header("Location: dashboard.php");
}
?>