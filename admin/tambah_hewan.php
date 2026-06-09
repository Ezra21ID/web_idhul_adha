<?php
session_start();
require_once '../include/config.php';

// Proteksi halaman admin, pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Masukkan header standar
include 'partials/header.php';
?>

<title>Tambah Hewan Qurban - Admin Panel</title>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0">Form Tambah Hewan Qurban</h1>
        <a href="data_qurban.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Daftar Qurban
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="proses_qurban.php?aksi=tambah" method="POST">
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis Hewan</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="sapi" selected>Sapi</option>
                                <option value="kambing">Kambing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah Ekor</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" value="1" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="harga_per_ekor" class="form-label">Harga per Ekor (Rp)</label>
                            <input type="number" class="form-control" id="harga_per_ekor" name="harga_per_ekor" placeholder="Contoh: 21000000" required>
                        </div>
                        <div class="mb-3">
                            <label for="biaya_admin" class="form-label">Biaya Administrasi (Rp)</label>
                            <input type="number" class="form-control" id="biaya_admin" name="biaya_admin" value="0" placeholder="Contoh: 100000" required>
                        </div>
                        
                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <a href="data_qurban.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-2"></i>Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Masukkan footer standar
include 'partials/footer.php';
?>