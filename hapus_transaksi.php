<?php
session_start();
require_once 'koneksi.php';

// Cek login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];
    $id_user = $_SESSION['id_user'];

    // Eksekusi penghapusan data
    $query = "DELETE FROM transactions WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'";

    if ($koneksi->query($query) === TRUE) {
        // Jika berhasil, LANGSUNG lempar balik ke dashboard tanpa animasi
        header("Location: dashboard.php");
        exit;
    } else {
        // Jika gagal database, baru munculkan alert bawaan
        echo "<script>alert('Gagal menghapus data!'); window.location.href='dashboard.php';</script>";
    }
} else {
    header("Location: dashboard.php");
}
?>