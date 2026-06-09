<?php
session_start();
require_once '../include/config.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Ambil ID hewan dari URL
$hewan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($hewan_id === 0) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'ID Hewan tidak valid.'];
    // PERUBAHAN: Mengarahkan ke data_qurban.php jika terjadi error
    header("Location: data_qurban.php");
    exit();
}

// 2. Ambil data hewan yang akan diedit dari database
$stmt = $conn->prepare("SELECT * FROM hewan_qurban WHERE hewan_id = ?");
$stmt->bind_param("i", $hewan_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Data hewan tidak ditemukan.'];
    // PERUBAHAN: Mengarahkan ke data_qurban.php jika terjadi error
    header("Location: data_qurban.php");
    exit();
}
$hewan = $result->fetch_assoc();
$stmt->close();

include 'partials/header.php';
?>

<title>Edit Hewan Qurban - Admin Panel</title>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0">Edit Data Hewan Qurban</h1>
        <a href="data_qurban.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali ke Daftar Qurban
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="proses_qurban.php?aksi=edit" method="POST">
                        <input type="hidden" name="hewan_id" value="<?php echo $hewan['hewan_id']; ?>">

                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis Hewan</label>
                            <select class="form-select" id="jenis" name="jenis" required>
                                <option value="sapi" <?php echo ($hewan['jenis'] == 'sapi') ? 'selected' : ''; ?>>Sapi</option>
                                <option value="kambing" <?php echo ($hewan['jenis'] == 'kambing') ? 'selected' : ''; ?>>Kambing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah Ekor</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" value="<?php echo htmlspecialchars($hewan['jumlah']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="harga_per_ekor" class="form-label">Harga per Ekor (Rp)</label>
                            <input type="number" class="form-control" id="harga_per_ekor" name="harga_per_ekor" value="<?php echo htmlspecialchars($hewan['harga_per_ekor']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="biaya_admin" class="form-label">Biaya Administrasi (Rp)</label>
                            <input type="number" class="form-control" id="biaya_admin" name="biaya_admin" value="<?php echo htmlspecialchars($hewan['biaya_admin']); ?>" required>
                        </div>
                        
                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <a href="data_qurban.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill me-2"></i>Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>