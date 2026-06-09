<?php
session_start();
require_once '../include/config.php';

// Fungsi helper untuk format Rupiah, agar kode lebih rapi.
if (!function_exists('format_rupiah')) {
    function format_rupiah($angka){
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// PERINGATAN: Simulasi login ini harus dihapus di production dan diganti sistem login asli.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}

// Query utama untuk mengambil semua data hewan qurban
$sql_hewan = "SELECT * FROM hewan_qurban ORDER BY jenis DESC, hewan_id ASC";
$result_hewan = $conn->query($sql_hewan);

// Masukkan header standar
include 'partials/header.php';
?>

<title>Data Hewan Qurban - Admin Panel</title>

<?php if(isset($_SESSION['pesan'])): ?>
<div class="alert alert-<?php echo $_SESSION['pesan']['jenis']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['pesan']['teks']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['pesan']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0">Manajemen Hewan Qurban</h1>
    <a href="tambah_hewan.php" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-2"></i>Tambah Data Hewan
    </a>
</div>

<div class="row row-cols-1 row-cols-lg-2 g-4">
    <?php if ($result_hewan && $result_hewan->num_rows > 0): ?>
        <?php while($hewan = $result_hewan->fetch_assoc()): ?>
            <?php
            // --- Untuk setiap hewan, lakukan query terpisah untuk mendapatkan detailnya ---
            $hewan_id = $hewan['hewan_id'];
            
            // 1. Query dana terkumpul (aman dengan prepared statement)
            $stmt_dana = $conn->prepare("SELECT SUM(jumlah_iuran) as terkumpul FROM warga WHERE hewan_id = ?");
            $stmt_dana->bind_param('i', $hewan_id);
            $stmt_dana->execute();
            $dana_terkumpul = $stmt_dana->get_result()->fetch_assoc()['terkumpul'] ?? 0;
            $stmt_dana->close();
            
            // 2. Query daftar peserta (aman dengan prepared statement dan join sesuai ERD)
            $stmt_peserta = $conn->prepare("SELECT u.nik, w.warga_id FROM warga w JOIN users u ON w.user_id = u.user_id WHERE w.hewan_id = ?");
            $stmt_peserta->bind_param('i', $hewan_id);
            $stmt_peserta->execute();
            $result_peserta = $stmt_peserta->get_result();
            
            // 3. Kalkulasi untuk tampilan
            $persentase = ($hewan['total_harga'] > 0) ? ($dana_terkumpul / $hewan['total_harga']) * 100 : 0;
            $slot_terisi = $result_peserta->num_rows;
            $total_slot = ($hewan['jenis'] == 'sapi') ? 7 : 1;
            ?>

            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi <?php echo ($hewan['jenis'] == 'sapi') ? 'bi-cow' : 'bi-back'; ?>"></i>
                            <?php echo ucfirst($hewan['jenis']); ?> #<?php echo $hewan['hewan_id']; ?>
                        </h5>
                        <div>
                            <a href="edit_hewan.php?id=<?php echo $hewan['hewan_id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit Data Hewan">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="proses_qurban.php?aksi=hapus&id=<?php echo $hewan['hewan_id']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus Data Hewan" onclick="return confirm('Anda yakin ingin menghapus data hewan ini? Semua peserta terkait akan dilepaskan.')">
                                <i class="bi bi-trash-fill"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <small class="text-muted">Status Pembayaran</small>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold fs-5 text-success"><?php echo format_rupiah($dana_terkumpul); ?></span>
                                <span>Target: <?php echo format_rupiah($hewan['total_harga']); ?></span>
                            </div>
                            <div class="progress" role="progressbar" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $persentase; ?>%"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Peserta (<?php echo "$slot_terisi / $total_slot"; ?>)</h6>
                            <a href="tambah_peserta.php?id=<?php echo $hewan['hewan_id']; ?>" class="btn btn-sm btn-success <?php if ($slot_terisi >= $total_slot) echo 'disabled'; ?>">
                                <i class="bi bi-person-plus-fill"></i> Tambah
                            </a>
                        </div>
                        <ul class="list-group list-group-flush flex-grow-1">
                            <?php if ($slot_terisi > 0): ?>
                                <?php while($peserta = $result_peserta->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                                        NIK: <?php echo htmlspecialchars($peserta['nik']); ?>
                                        <a href="proses_peserta.php?aksi=hapus&warga_id=<?php echo $peserta['warga_id']; ?>" class="text-danger" title="Hapus Peserta" onclick="return confirm('Anda yakin ingin menghapus peserta ini dari grup?')">
                                            <i class="bi bi-x-circle"></i>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="list-group-item text-muted text-center py-3 px-0">Belum ada peserta.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php 
            // Tutup statement peserta sebelum loop berikutnya
            $stmt_peserta->close();
            endwhile; 
        ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning">Tidak ada data hewan qurban. Silakan klik "Tambah Data Hewan" untuk memulai.</div>
        </div>
    <?php endif; ?>
</div>

<?php
// Masukkan footer
include 'partials/footer.php';
?>