<?php
session_start();
require_once '../include/config.php';

// Fungsi helper untuk format Rupiah, agar kode lebih rapi.

// PERINGATAN: Simulasi login ini harus dihapus di production dan diganti sistem login asli.
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin';
}

// --- DATA FETCHING ---

// 1. Ambil data total dengan 1 query yang efisien.
$sql_totals = "SELECT 
    SUM(CASE WHEN jenis = 'masuk' THEN jumlah ELSE 0 END) as total_pemasukan,
    SUM(CASE WHEN jenis = 'keluar' THEN jumlah ELSE 0 END) as total_pengeluaran
    FROM keuangan";
$totals = $conn->query($sql_totals)->fetch_assoc();

$total_pemasukan = $totals['total_pemasukan'] ?? 0;
$total_pengeluaran = $totals['total_pengeluaran'] ?? 0;
$saldo_akhir = $total_pemasukan - $total_pengeluaran;

// 2. Query utama sesuai ERD: JOIN ke tabel `warga` lalu `users` untuk mendapatkan NIK.
$sql = "SELECT k.transaksi_id, k.tanggal, k.keterangan, k.jenis, k.jumlah, k.warga_id, u.nik 
        FROM keuangan k
        LEFT JOIN warga w ON k.warga_id = w.warga_id
        LEFT JOIN users u ON w.user_id = u.user_id
        ORDER BY k.tanggal DESC, k.transaksi_id DESC";
$result = $conn->query($sql);


// Masukkan header standar.
include 'partials/header.php';
?>

<title>Manajemen Keuangan - Admin Panel</title>

<?php if(isset($_SESSION['pesan'])): ?>
<div class="alert alert-<?php echo $_SESSION['pesan']['jenis']; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_SESSION['pesan']['teks']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['pesan']); ?>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="m-0">Manajemen Keuangan</h1>
    <a href="tambah_transaksi.php" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-2"></i>Tambah Transaksi
    </a>
</div>

<div class="row">

    <div class="col-lg-5">
        <div class="d-flex flex-column h-100">
            <div class="card text-bg-success mb-4 flex-fill">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box-arrow-in-down"></i> Total Pemasukan</h5>
                    <p class="card-text fs-4 fw-bold"><?php echo format_rupiah($total_pemasukan); ?></p>
                </div>
            </div>
            <div class="card text-bg-danger mb-4 flex-fill">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-box-arrow-up"></i> Total Pengeluaran</h5>
                    <p class="card-text fs-4 fw-bold"><?php echo format_rupiah($total_pengeluaran); ?></p>
                </div>
            </div>
            <div class="card text-bg-primary mb-4 flex-fill">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-wallet2"></i> Saldo Akhir</h5>
                    <p class="card-text fs-4 fw-bold"><?php echo format_rupiah($saldo_akhir); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Daftar Transaksi</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($trx = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date("d M Y, H:i", strtotime($trx['tanggal'])); ?></td>
                                    
                                    <td>
                                        <?php echo htmlspecialchars($trx['keterangan']); ?>
                                        <?php if(!empty($trx['nik'])): ?>
                                            <small class="d-block text-muted fst-italic">
                                               <i class="bi bi-person-fill"></i> Terkait (NIK): <?php echo htmlspecialchars($trx['nik']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if($trx['jenis'] == 'masuk'): ?>
                                            <span class="badge text-bg-success">Masuk</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">Keluar</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="text-end fw-bold"><?php echo format_rupiah($trx['jumlah']); ?></td>

                                    <td class="text-center">
                                        <a href="edit_transaksi.php?id=<?php echo $trx['transaksi_id']; ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="proses_keuangan.php?aksi=hapus&id=<?php echo $trx['transaksi_id']; ?>" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center p-4">Belum ada data transaksi.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
// Masukkan footer standar.
include 'partials/footer.php';
?>