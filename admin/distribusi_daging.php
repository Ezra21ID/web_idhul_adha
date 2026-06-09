<?php
session_start();
// Ganti path ini sesuai struktur Anda
require_once '../include/config.php';
require_once 'partials/header.php';

// Ambil semua data warga dan status distribusinya
$sql = "SELECT w.warga_id, u.nama, w.is_panitia, w.is_berqurban, p.distribusi_id, p.berat_kg
        FROM warga w
        JOIN users u ON w.user_id = u.user_id
        LEFT JOIN pembagian_daging p ON w.warga_id = p.warga_id
        ORDER BY u.nama ASC";
$result = $conn->query($sql);
?>

<div class="container-fluid">
    <h3 class="mt-4">Manajemen Distribusi Daging</h3>
    <p>Halaman ini digunakan untuk membuat kartu pengambilan daging untuk semua warga.</p>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <i class="bi bi-table me-1"></i>
            Data Warga & Status Distribusi
            <form action="proses/proses_distribusi.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuat kartu untuk semua warga yang belum punya? Proses ini tidak bisa diulang.');">
                <button type="submit" name="generate_all" class="btn btn-primary">
                    <i class="bi bi-qr-code me-2"></i>Generate Kartu untuk Semua
                </button>
            </form>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Warga</th>
                        <th>Peran</th>
                        <th>Status Kartu</th>
                        <th>Jatah Daging</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1;
                        while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td>
                                    <?php
                                    $roles = [];
                                    if ($row['is_panitia']) $roles[] = 'Panitia';
                                    if ($row['is_berqurban']) $roles[] = 'Berqurban';
                                    if (empty($roles)) {
                                        echo 'Warga Biasa';
                                    } else {
                                        echo 'Warga + ' . implode(' + ', $roles);
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($row['distribusi_id']): ?>
                                        <span class="badge bg-success">Sudah Dibuat</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Belum Dibuat</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $row['berat_kg'] ? htmlspecialchars($row['berat_kg']) . ' Kg' : '-'; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data warga.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>