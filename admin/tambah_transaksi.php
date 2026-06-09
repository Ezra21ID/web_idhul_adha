<?php
session_start();
require_once '../include/config.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// PERUBAHAN ERD: Query diubah untuk JOIN ke tabel users dan mengambil NIK
$warga_query = "SELECT w.warga_id, u.nik 
                FROM warga w
                JOIN users u ON w.user_id = u.user_id
                ORDER BY u.nik ASC";
$warga_result = $conn->query($warga_query);

// Masukkan header
include 'partials/header.php';
?>

<title>Tambah Transaksi Baru - Admin Panel</title>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0"><i class="bi bi-plus-circle-fill me-2"></i>Form Tambah Transaksi</h5>
                </div>
                <div class="card-body">
                    <form action="proses_keuangan.php?aksi=tambah" method="POST">
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis Transaksi</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="masuk" selected>Pemasukan</option>
                                <option value="keluar">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah (Rp)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" placeholder="Contoh: 50000" required>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Contoh: Iuran kas dari warga" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="warga_id" class="form-label">Terkait Dengan Warga (Opsional)</label>
                            <select class="form-select" id="warga_id" name="warga_id">
                                <option value="">-- Tidak terikat dengan warga --</option>
                                <?php if ($warga_result->num_rows > 0): ?>
                                    <?php while($warga = $warga_result->fetch_assoc()): ?>
                                        <option value="<?php echo $warga['warga_id']; ?>">
                                            NIK: <?php echo htmlspecialchars($warga['nik']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="keuangan.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle me-2"></i>Kembali</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-2"></i>Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Masukkan footer
include 'partials/footer.php';
?>