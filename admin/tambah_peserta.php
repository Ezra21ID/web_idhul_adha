<?php
session_start();
require_once '../include/config.php';

// Proteksi halaman admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 1. Ambil ID hewan dari URL untuk mengetahui peserta akan masuk ke grup mana
$hewan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($hewan_id === 0) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'ID Grup Hewan tidak valid.'];
    header("Location: data_qurban.php");
    exit();
}

// 2. Ambil detail data hewan untuk ditampilkan di judul
$hewan_stmt = $conn->prepare("SELECT * FROM hewan_qurban WHERE hewan_id = ?");
$hewan_stmt->bind_param('i', $hewan_id);
$hewan_stmt->execute();
$hewan = $hewan_stmt->get_result()->fetch_assoc();
$hewan_stmt->close();
if (!$hewan) {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Grup hewan tidak ditemukan.'];
    header("Location: data_qurban.php");
    exit();
}

// 3. Ambil daftar warga yang BELUM terdaftar di grup qurban manapun (hewan_id IS NULL)
$warga_query = "SELECT w.warga_id, u.nik 
                FROM warga w
                JOIN users u ON w.user_id = u.user_id
                WHERE w.hewan_id IS NULL AND w.is_berqurban = 1
                ORDER BY u.nik ASC";
$warga_result = $conn->query($warga_query);


include 'partials/header.php';
?>

<title>Tambah Peserta Qurban - Admin Panel</title>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="m-0">Tambah Peserta ke Grup <?php echo ucfirst($hewan['jenis']) . ' #' . $hewan['hewan_id']; ?></h1>
        <a href="data_qurban.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form action="proses_peserta.php?aksi=tambah" method="POST">
                        <input type="hidden" name="hewan_id" value="<?php echo $hewan['hewan_id']; ?>">

                        <div class="mb-3">
                            <label for="warga_id" class="form-label">Pilih Warga</label>
                            <select class="form-select" id="warga_id" name="warga_id" required>
                                <option value="" disabled selected>-- Pilih dari daftar warga yang tersedia --</option>
                                <?php if ($warga_result && $warga_result->num_rows > 0): ?>
                                    <?php while($warga = $warga_result->fetch_assoc()): ?>
                                        <option value="<?php echo $warga['warga_id']; ?>">
                                            NIK: <?php echo htmlspecialchars($warga['nik']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada warga yang tersedia.</option>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">Hanya menampilkan warga yang belum terdaftar di grup manapun.</div>
                        </div>
                        
                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <a href="data_qurban.php" class="btn btn-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary" <?php if ($warga_result->num_rows === 0) echo 'disabled'; ?>>
                                <i class="bi bi-person-plus-fill me-2"></i>Tambahkan Peserta
                            </button>
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