<?php
session_start();
require_once '../include/config.php';

// Simulasi login
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// --- DATA FETCHING UNTUK SEMUA LAPORAN ---

// 1. Data Laporan Keuangan
$total_pemasukan = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'masuk'")->fetch_assoc()['total'] ?? 0;
$total_pengeluaran = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'keluar'")->fetch_assoc()['total'] ?? 0;
$saldo_akhir = $total_pemasukan - $total_pengeluaran;
$result_keuangan = $conn->query("SELECT tanggal, keterangan, jenis, jumlah FROM keuangan ORDER BY tanggal ASC, transaksi_id ASC");

// 2. Data Laporan Distribusi
$total_paket = $conn->query("SELECT COUNT(*) as total FROM pembagian_daging")->fetch_assoc()['total'] ?? 0;
$sudah_diambil = $conn->query("SELECT COUNT(*) as total FROM pembagian_daging WHERE status_pengambilan = 'sudah_diambil'")->fetch_assoc()['total'] ?? 0;
$belum_diambil = $total_paket - $sudah_diambil;
$sql_distribusi = "SELECT u.nama, w.alamat, pd.berat_kg, pd.status_pengambilan 
                   FROM pembagian_daging pd
                   JOIN warga w ON pd.warga_id = w.warga_id
                   JOIN users u ON w.user_id = u.user_id
                   ORDER BY u.nama ASC";
$result_distribusi = $conn->query($sql_distribusi);

// 3. Data Laporan Peserta Qurban
$sql_peserta = "SELECT u.nama, hq.jenis, hq.hewan_id
                FROM warga w
                JOIN users u ON w.user_id = u.user_id
                JOIN hewan_qurban hq ON w.hewan_id = hq.hewan_id
                WHERE w.is_berqurban = 1
                ORDER BY hq.jenis, hq.hewan_id, u.nama";
$result_peserta = $conn->query($sql_peserta);


// Masukkan header
include 'partials/header.php';
?>

<!-- Setel Judul Halaman -->
<title>Laporan Akhir - Admin Panel</title>

<!-- Header Halaman -->
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h1 class="m-0">Laporan Akhir Kegiatan</h1>
    <button type="button" class="btn btn-success" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Semua Laporan
    </button>
</div>

<!-- LAPORAN KEUANGAN -->
<div class="card mb-4 printable-area">
    <div class="card-header">
        <h4 class="mb-0">Laporan Keuangan</h4>
        <p class="mb-0 text-muted">Ringkasan Pemasukan dan Pengeluaran Dana Qurban</p>
    </div>
    <div class="card-body">
        <!-- Ringkasan -->
        <div class="row text-center mb-4">
            <div class="col-4">
                <div class="p-3 bg-success-subtle rounded">
                    <small>Total Pemasukan</small>
                    <h5 class="mb-0 fw-bold"><?php echo format_rupiah($total_pemasukan); ?></h5>
                </div>
            </div>
            <div class="col-4">
                 <div class="p-3 bg-danger-subtle rounded">
                    <small>Total Pengeluaran</small>
                    <h5 class="mb-0 fw-bold"><?php echo format_rupiah($total_pengeluaran); ?></h5>
                </div>
            </div>
            <div class="col-4">
                <div class="p-3 bg-primary-subtle rounded">
                    <small>Saldo Akhir</small>
                    <h5 class="mb-0 fw-bold"><?php echo format_rupiah($saldo_akhir); ?></h5>
                </div>
            </div>
        </div>
        <!-- Detail Transaksi -->
        <table class="table table-bordered table-sm">
            <thead class="table-light"><tr><th>Tanggal</th><th>Keterangan</th><th class="text-end">Pemasukan</th><th class="text-end">Pengeluaran</th></tr></thead>
            <tbody>
                <?php while($trx = $result_keuangan->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("d-m-Y", strtotime($trx['tanggal'])); ?></td>
                    <td><?php echo htmlspecialchars($trx['keterangan']); ?></td>
                    <td class="text-end"><?php echo ($trx['jenis'] == 'masuk') ? format_rupiah($trx['jumlah']) : '-'; ?></td>
                    <td class="text-end"><?php echo ($trx['jenis'] == 'keluar') ? format_rupiah($trx['jumlah']) : '-'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot class="fw-bold table-light">
                <tr><td colspan="2" class="text-center">TOTAL</td><td class="text-end"><?php echo format_rupiah($total_pemasukan); ?></td><td class="text-end"><?php echo format_rupiah($total_pengeluaran); ?></td></tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- LAPORAN DISTRIBUSI DAGING -->
<div class="card mb-4 printable-area">
    <div class="card-header"><h4 class="mb-0">Laporan Distribusi Daging</h4></div>
    <div class="card-body">
        <div class="row text-center mb-4">
             <div class="col-4"><div class="p-3 bg-info-subtle rounded"><small>Total Paket</small><h5 class="mb-0 fw-bold"><?php echo $total_paket; ?></h5></div></div>
             <div class="col-4"><div class="p-3 bg-success-subtle rounded"><small>Sudah Diambil</small><h5 class="mb-0 fw-bold"><?php echo $sudah_diambil; ?></h5></div></div>
             <div class="col-4"><div class="p-3 bg-warning-subtle rounded"><small>Belum Diambil</small><h5 class="mb-0 fw-bold"><?php echo $belum_diambil; ?></h5></div></div>
        </div>
        <table class="table table-bordered table-sm">
            <thead class="table-light"><tr><th>No</th><th>Nama Penerima</th><th>Alamat</th><th class="text-center">Jatah (Kg)</th><th class="text-center">Status</th></tr></thead>
            <tbody>
                <?php $no = 1; while($dist = $result_distribusi->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($dist['nama']); ?></td>
                    <td><?php echo htmlspecialchars($dist['alamat']); ?></td>
                    <td class="text-center"><?php echo number_format($dist['berat_kg'], 2, ',', '.'); ?></td>
                    <td class="text-center"><?php echo ($dist['status_pengambilan'] == 'sudah_diambil') ? 'Diambil' : 'Belum'; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- LAPORAN PESERTA QURBAN -->
<div class="card mb-4 printable-area">
    <div class="card-header"><h4 class="mb-0">Laporan Peserta Qurban (Shohibul Qurban)</h4></div>
    <div class="card-body">
        <table class="table table-bordered table-sm">
            <thead class="table-light"><tr><th>No</th><th>Nama Peserta</th><th>Hewan Qurban</th></tr></thead>
            <tbody>
                <?php $no = 1; $current_hewan = ''; while($p = $result_peserta->fetch_assoc()): ?>
                <?php
                    $hewan_info = ucfirst($p['jenis']) . ' #' . $p['hewan_id'];
                    if ($hewan_info != $current_hewan) {
                        if ($current_hewan != '') echo '</tbody>'; // Tutup tbody sebelumnya
                        echo '<tbody class="table-group-divider"><tr class="table-secondary fw-bold"><td colspan="3">Kelompok ' . $hewan_info . '</td></tr>';
                        $current_hewan = $hewan_info;
                        $no_kelompok = 1;
                    }
                ?>
                <tr>
                    <td><?php echo $no_kelompok++; ?>.</td>
                    <td><?php echo htmlspecialchars($p['nama']); ?></td>
                    <td><?php echo $hewan_info; ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
        </table>
    </div>
</div>


<?php
// Masukkan footer
include 'partials/footer.php';
?>
