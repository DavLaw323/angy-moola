<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_transaksi = isset($_GET['id']) ? $_GET['id'] : 0;
$pesan = "";

// 1. Logika Update Data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tanggal = $_POST['tanggal_transaksi'];
    $id_account = $_POST['id_account'];
    $id_sub_kategori = $_POST['id_sub_kategori'];
    $keterangan = $_POST['keterangan'];

    // Membersihkan titik ribuan sebelum masuk ke database
    $nominal_kotor = $_POST['nominal'];
    $nominal_bersih = str_replace('.', '', $nominal_kotor); 

    $query_update = "UPDATE transactions SET 
                     id_account = '$id_account', 
                     id_sub_kategori = '$id_sub_kategori', 
                     nominal = '$nominal_bersih', 
                     tanggal_transaksi = '$tanggal', 
                     keterangan = '$keterangan' 
                     WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'";

    if ($koneksi->query($query_update) === TRUE) {
        // PERUBAHAN: Menampilkan alert elegan di dalam form, sama persis seperti form Catat Transaksi
        $pesan = "<div class='alert alert-success shadow-sm border-0' style='border-left: 5px solid #0ba360 !important; color: #0ba360; background-color: #e8f7f0;'>
                    <i class='bi bi-check-circle-fill me-2'></i> Transaksi berhasil diperbarui!
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger shadow-sm border-0' style='border-left: 5px solid #cb2d3e !important; color: #cb2d3e; background-color: #fce8e9;'>
                    <i class='bi bi-exclamation-triangle-fill me-2'></i> Gagal mengupdate: " . $koneksi->error . "
                  </div>";
    }
}

// 2. Ambil data transaksi lama (atau data yang baru saja diupdate) untuk dimasukkan ke form
$query_data_lama = "SELECT * FROM transactions WHERE id_transaksi = '$id_transaksi' AND id_user = '$id_user'";
$result_data_lama = $koneksi->query($query_data_lama);

if ($result_data_lama->num_rows == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit;
}
$data = $result_data_lama->fetch_assoc();

// 3. Ambil opsi Dompet & Kategori
$query_accounts = "SELECT * FROM accounts WHERE id_user = '$id_user'";
$result_accounts = $koneksi->query($query_accounts);

$query_kategori = "SELECT sub_categories.id_sub, sub_categories.nama_sub, categories.nama_kategori, categories.tipe 
                   FROM sub_categories 
                   JOIN categories ON sub_categories.id_kategori = categories.id_kategori
                   ORDER BY categories.tipe DESC, categories.nama_kategori ASC";
$result_kategori = $koneksi->query($query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - AngyMoola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .card-form {
            border-radius: 1.5rem;
            border: none;
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .card-header-custom {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 1.5rem 2rem;
        }

        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 12px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.25rem rgba(42, 82, 152, 0.15);
        }

        .btn-update {
            background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(11, 163, 96, 0.3);
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-wallet2 me-2"></i>AngyMoola</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <div class="card card-form mb-5">
                <div class="card-header-custom text-center">
                    <h4 class="fw-bold mb-1" style="color: #1e3c72;"><i class="bi bi-pencil-square me-2"></i>Edit Transaksi</h4>
                    <p class="text-muted small mb-0">Perbaiki catatan keuanganmu yang salah</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    
                    <?= $pesan ?>

                    <form method="POST" action="">
                        <div class="form-floating mb-3">
                            <input type="date" name="tanggal_transaksi" class="form-control" id="tgl" required value="<?= $data['tanggal_transaksi'] ?>">
                            <label for="tgl" class="text-muted"><i class="bi bi-calendar-event me-1"></i> Tanggal Transaksi</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select name="id_account" class="form-select" id="acc" required>
                                <?php while($row = $result_accounts->fetch_assoc()) { 
                                    $selected = ($row['id_account'] == $data['id_account']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row['id_account'] ?>" <?= $selected ?>><?= $row['nama_akun'] ?></option>
                                <?php } ?>
                            </select>
                            <label for="acc" class="text-muted"><i class="bi bi-credit-card me-1"></i> Gunakan Dompet</label>
                        </div>

                        <div class="form-floating mb-3">
                            <select name="id_sub_kategori" class="form-select" id="kat" required>
                                <?php while($row = $result_kategori->fetch_assoc()) { 
                                    $label = ($row['tipe'] == 'Income') ? '📈 [Masuk]' : '📉 [Keluar]';
                                    $selected = ($row['id_sub'] == $data['id_sub_kategori']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $row['id_sub'] ?>" <?= $selected ?>>
                                        <?= $label ?> <?= $row['nama_kategori'] ?> - <?= $row['nama_sub'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <label for="kat" class="text-muted"><i class="bi bi-tag me-1"></i> Jenis Transaksi</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="nominal" class="form-control" id="inputNominal" required value="<?= number_format($data['nominal'], 0, ',', '.') ?>">
                            <label for="inputNominal" class="text-muted"><i class="bi bi-cash-stack me-1"></i> Nominal (Rp)</label>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea name="keterangan" class="form-control" id="ket" style="height: 100px"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            <label for="ket" class="text-muted"><i class="bi bi-pencil-square me-1"></i> Keterangan (Opsional)</label>
                        </div>

                        <button type="submit" class="btn btn-update w-100 text-white shadow-sm">
                            <i class="bi bi-save me-2"></i> PERBARUI TRANSAKSI
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var inputNominal = document.getElementById('inputNominal');

    inputNominal.addEventListener('keyup', function(e) {
        inputNominal.value = formatRupiah(this.value);
    });

    function formatRupiah(angka) {
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }
</script>

</body>
</html>