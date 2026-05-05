<?php
session_start();
require_once 'koneksi.php';

// 1. Keamanan: Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$pesan = "";

// 2. Logika saat form disubmit (Tombol Simpan ditekan)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = $_POST['tanggal_transaksi'];
    $id_account = $_POST['id_account'];
    $id_sub_kategori = $_POST['id_sub_kategori'];
    $nominal = $_POST['nominal'];
    $keterangan = $_POST['keterangan'];

    // Query untuk menyimpan data ke tabel transactions
    $query_insert = "INSERT INTO transactions (id_user, id_account, id_sub_kategori, nominal, tanggal_transaksi, keterangan) 
                     VALUES ('$id_user', '$id_account', '$id_sub_kategori', '$nominal', '$tanggal', '$keterangan')";

    if ($koneksi->query($query_insert) === TRUE) {
        $pesan = "<div class='alert alert-success'>Mantap! Transaksi berhasil dicatat.</div>";
    } else {
        $pesan = "<div class='alert alert-danger'>Gagal menyimpan: " . $koneksi->error . "</div>";
    }
}

// 3. Ambil data Dompet/Akun milik user yang sedang login
$query_accounts = "SELECT * FROM accounts WHERE id_user = '$id_user'";
$result_accounts = $koneksi->query($query_accounts);

// 4. Ambil data Sub Kategori beserta nama Kategori utamanya (Menggunakan JOIN)
$query_kategori = "SELECT sub_categories.id_sub, sub_categories.nama_sub, categories.nama_kategori, categories.tipe 
                   FROM sub_categories 
                   JOIN categories ON sub_categories.id_kategori = categories.id_kategori
                   ORDER BY categories.tipe, categories.nama_kategori";
$result_kategori = $koneksi->query($query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Transaksi - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar Sederhana -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">💰 AngyMoola</a>
        <div class="d-flex">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Kembali ke Dashboard</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 text-primary">Catat Transaksi Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <?= $pesan ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label text-muted">Tanggal Transaksi</label>
                            <input type="date" name="tanggal_transaksi" class="form-control" required value="<?= date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Gunakan Dompet/Rekening</label>
                            <select name="id_account" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Dompet --</option>
                                <?php while($row = $result_accounts->fetch_assoc()) { ?>
                                    <option value="<?= $row['id_account'] ?>"><?= $row['nama_akun'] ?> (<?= $row['tipe_akun'] ?>)</option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Kategori Transaksi</label>
                            <select name="id_sub_kategori" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Kategori --</option>
                                <?php while($row = $result_kategori->fetch_assoc()) { 
                                    // Beri tanda apakah ini Pemasukan atau Pengeluaran
                                    $tipe_label = ($row['tipe'] == 'Income') ? '📈 Pemasukan' : '📉 Pengeluaran';
                                ?>
                                    <option value="<?= $row['id_sub'] ?>">
                                        <?= $tipe_label ?> | <?= $row['nama_kategori'] ?> - <?= $row['nama_sub'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Nominal (Rp)</label>
                            <input type="number" name="nominal" class="form-control" required placeholder="Contoh: 50000" min="0">
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted">Keterangan Tambahan (Opsional)</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Beli makan siang ayam geprek"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">Simpan Transaksi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>