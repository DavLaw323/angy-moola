<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$pesan = "";

// 1. PROSES TAMBAH DOMPET/REKENING BARU (DARI MODAL POP-UP)
if (isset($_POST['tambah_akun'])) {
    $nama_akun_baru = $_POST['nama_akun_baru'];
    
    $query_akun = "INSERT INTO accounts (id_user, nama_akun) VALUES ('$id_user', '$nama_akun_baru')";
    if ($koneksi->query($query_akun) === TRUE) {
        $pesan = "<div class='alert alert-success shadow-sm border-0' style='border-left: 5px solid #0ba360 !important;'>
                    <i class='bi bi-check-circle-fill me-2'></i> Rekening <strong>$nama_akun_baru</strong> berhasil ditambahkan!
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger shadow-sm border-0' style='border-left: 5px solid #cb2d3e !important;'>
                    <i class='bi bi-exclamation-triangle-fill me-2'></i> Gagal menambah rekening: " . $koneksi->error . "
                  </div>";
    }
}

// 2. PROSES SIMPAN TRANSAKSI
if (isset($_POST['simpan_transaksi'])) {
    $tanggal = $_POST['tanggal_transaksi'];
    $id_account = $_POST['id_account'];
    $id_sub_kategori = $_POST['id_sub_kategori'];
    $keterangan = $_POST['keterangan'];
    
    // Membersihkan titik dari inputan nominal
    $nominal_kotor = $_POST['nominal'];
    $nominal_bersih = str_replace('.', '', $nominal_kotor); 

    $query = "INSERT INTO transactions (id_user, id_account, id_sub_kategori, nominal, tanggal_transaksi, keterangan) 
              VALUES ('$id_user', '$id_account', '$id_sub_kategori', '$nominal_bersih', '$tanggal', '$keterangan')";

    if ($koneksi->query($query) === TRUE) {
        $pesan = "<div class='alert alert-success shadow-sm border-0' style='border-left: 5px solid #0ba360 !important;'>
                    <i class='bi bi-check-circle-fill me-2'></i> Transaksi berhasil dicatat!
                  </div>";
    } else {
        $pesan = "<div class='alert alert-danger shadow-sm border-0' style='border-left: 5px solid #cb2d3e !important;'>
                    <i class='bi bi-exclamation-triangle-fill me-2'></i> Gagal mencatat: " . $koneksi->error . "
                  </div>";
    }
}

// Ambil data Akun/Dompet yang terbaru
$query_accounts = "SELECT * FROM accounts WHERE id_user = '$id_user' ORDER BY id_account DESC";
$result_accounts = $koneksi->query($query_accounts);

// Ambil data Kategori
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
    <title>Catat Transaksi - AngyMoola</title>
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

        .btn-save {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3);
            color: white;
        }

        /* Styling Tombol Plus Baru */
        .btn-add-wallet {
            border-radius: 12px;
            border: 1px dashed #2a5298;
            color: #2a5298;
            background: rgba(42, 82, 152, 0.05);
            transition: all 0.2s;
        }
        
        .btn-add-wallet:hover {
            background: #2a5298;
            color: white;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php"><i class="bi bi-wallet2 me-2"></i>AngyMoola</a>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <div class="card card-form mb-5">
                <div class="card-header-custom text-center">
                    <h4 class="fw-bold mb-1" style="color: #1e3c72;">Catat Transaksi Baru</h4>
                    <p class="text-muted small mb-0">Masukkan detail pengeluaran atau pemasukanmu</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?= $pesan ?>

                    <form method="POST" action="">
                        <div class="form-floating mb-3">
                            <input type="date" name="tanggal_transaksi" class="form-control" id="tgl" required value="<?= date('Y-m-d') ?>">
                            <label for="tgl" class="text-muted"><i class="bi bi-calendar-event me-1"></i> Tanggal Transaksi</label>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <div class="form-floating flex-grow-1">
                                <select name="id_account" class="form-select" id="acc" required>
                                    <option value="" selected disabled>Pilih Dompet...</option>
                                    <?php 
                                    // Tampilkan dompet jika ada, jika tidak, biarkan kosong
                                    if($result_accounts->num_rows > 0) {
                                        while($row = $result_accounts->fetch_assoc()) { 
                                    ?>
                                        <option value="<?= $row['id_account'] ?>"><?= $row['nama_akun'] ?></option>
                                    <?php 
                                        } 
                                    }
                                    ?>
                                </select>
                                <label for="acc" class="text-muted"><i class="bi bi-credit-card me-1"></i> Gunakan Dompet</label>
                            </div>
                            
                            <button type="button" class="btn btn-add-wallet px-3" data-bs-toggle="modal" data-bs-target="#modalTambahDompet" title="Tambah Dompet Baru">
                                <i class="bi bi-plus-lg fs-5"></i>
                            </button>
                        </div>

                        <div class="form-floating mb-3">
                            <select name="id_sub_kategori" class="form-select" id="kat" required>
                                <option value="" selected disabled>Pilih Kategori...</option>
                                <?php while($row = $result_kategori->fetch_assoc()) { 
                                    $label = ($row['tipe'] == 'Income') ? '📈 [Masuk]' : '📉 [Keluar]';
                                ?>
                                    <option value="<?= $row['id_sub'] ?>">
                                        <?= $label ?> <?= $row['nama_kategori'] ?> - <?= $row['nama_sub'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <label for="kat" class="text-muted"><i class="bi bi-tag me-1"></i> Jenis Transaksi</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="nominal" class="form-control" id="inputNominal" placeholder="0" required>
                            <label for="inputNominal" class="text-muted"><i class="bi bi-cash-stack me-1"></i> Nominal (Rp)</label>
                        </div>

                        <div class="form-floating mb-4">
                            <textarea name="keterangan" class="form-control" placeholder="Catatan" id="ket" style="height: 100px"></textarea>
                            <label for="ket" class="text-muted"><i class="bi bi-pencil-square me-1"></i> Keterangan (Opsional)</label>
                        </div>

                        <button type="submit" name="simpan_transaksi" class="btn btn-save w-100 text-white shadow-sm">
                            <i class="bi bi-check2-circle me-2"></i> SIMPAN TRANSAKSI
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahDompet" tabindex="-1" aria-labelledby="modalTambahDompetLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1.2rem; border: none;">
      
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-primary" id="modalTambahDompetLabel"><i class="bi bi-wallet2 me-2"></i>Tambah Dompet Baru</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <form method="POST" action="">
          <div class="modal-body">
              <p class="text-muted small mb-3">Tambahkan rekening bank, *e-wallet*, atau jenis penyimpanan uang lainnya (Misal: BCA, DANA, Uang Tunai).</p>
              
              <div class="form-floating mb-2">
                  <input type="text" name="nama_akun_baru" class="form-control" id="namaAkun" placeholder="Nama Dompet" required style="border-radius: 10px;">
                  <label for="namaAkun" class="text-muted">Nama Rekening / Dompet</label>
              </div>
          </div>
          
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah_akun" class="btn btn-primary rounded-pill px-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border: none;">Simpan Dompet</button>
          </div>
      </form>
      
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