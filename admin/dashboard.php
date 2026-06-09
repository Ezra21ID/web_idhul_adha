<?php
session_start();
// Gunakan `../` karena koneksi.php ada di luar folder admin
require_once '../include/config.php';

// Simulasi login untuk development
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// --- DATA FETCHING (SAMA SEPERTI SEBELUMNYA) ---
$statistik = [];
$statistik['total_dana'] = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'masuk'")->fetch_assoc()['total'] ?? 0;
$statistik['total_pengeluaran'] = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'keluar'")->fetch_assoc()['total'] ?? 0;
$statistik['shohibul_qurban'] = $conn->query("SELECT COUNT(*) as total FROM warga WHERE is_berqurban = 1")->fetch_assoc()['total'] ?? 0;
$statistik['total_penerima'] = $conn->query("SELECT COUNT(distribusi_id) as total FROM pembagian_daging")->fetch_assoc()['total'] ?? 0;

$sql_chart = "SELECT tanggal, jenis, SUM(jumlah) as total FROM keuangan GROUP BY tanggal, jenis ORDER BY tanggal";
$result_chart = $conn->query($sql_chart);
$chart_data_raw = [];
while($row = $result_chart->fetch_assoc()) {
    $chart_data_raw[$row['tanggal']][$row['jenis']] = $row['total'];
}
$data_chart = ['labels' => [], 'pemasukan' => [], 'pengeluaran' => []];
foreach ($chart_data_raw as $tgl => $jenis) {
    $data_chart['labels'][] = date("d M", strtotime($tgl));
    $data_chart['pemasukan'][] = $jenis['masuk'] ?? 0;
    $data_chart['pengeluaran'][] = $jenis['keluar'] ?? 0;
}

$sql_trx = "SELECT u.nama, k.keterangan, k.jenis, k.jumlah FROM keuangan k LEFT JOIN warga w ON k.warga_id = w.warga_id LEFT JOIN users u ON w.user_id = u.user_id ORDER BY k.tanggal DESC, k.transaksi_id DESC LIMIT 5";
$result_trx = $conn->query($sql_trx);
// --- AKHIR DATA FETCHING ---

// Masukkan header, sudah termasuk sidebar dan session check
include 'partials/header.php';
?>

<!-- Setel Judul Halaman -->
<title>Admin Dashboard - Qurban Masjid Nashruddin</title>

<!-- Konten Utama Dashboard -->
<h1 class="mb-4">Dashboard Utama</h1>
<!-- Kartu Statistik -->
<div class="row">
    <!-- (Kode HTML untuk kartu statistik sama persis seperti sebelumnya) -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-primary shadow-sm h-100"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
            <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Dana</div>
            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo format_rupiah($statistik['total_dana']); ?></div>
        </div><div class="col-auto"><i class="bi bi-cash-stack fs-1 text-black-50"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-danger shadow-sm h-100"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
            <div class="text-xs fw-bold text-danger text-uppercase mb-1">Total Pengeluaran</div>
            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo format_rupiah($statistik['total_pengeluaran']); ?></div>
        </div><div class="col-auto"><i class="bi bi-cart-x-fill fs-1 text-black-50"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-success shadow-sm h-100"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
            <div class="text-xs fw-bold text-success text-uppercase mb-1">Peserta Qurban</div>
            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $statistik['shohibul_qurban']; ?> Orang</div>
        </div><div class="col-auto"><i class="bi bi-person-check-fill fs-1 text-black-50"></i></div></div></div></div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card border-info shadow-sm h-100"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
            <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Penerima</div>
            <div class="h5 mb-0 fw-bold text-gray-800"><?php echo $statistik['total_penerima']; ?> Keluarga</div>
        </div><div class="col-auto"><i class="bi bi-people-fill fs-1 text-black-50"></i></div></div></div></div>
    </div>
</div>

<!-- Grafik dan Transaksi -->
<div class="row">
    <!-- (Kode HTML untuk grafik dan tabel transaksi sama persis seperti sebelumnya) -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-line-fill"></i> Grafik Arus Kas Keuangan</h6></div><div class="card-body"><canvas id="financialChart"></canvas></div></div>
    </div>
    <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-arrow-down-up"></i> Transaksi Terakhir</h6></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-striped table-hover mb-0"><tbody>
            <?php while($trx = $result_trx->fetch_assoc()): ?>
            <tr>
                <td class="ps-3"><i class="bi <?php echo $trx['jenis'] == 'masuk' ? 'bi-arrow-down-circle-fill text-success' : 'bi-arrow-up-circle-fill text-danger'; ?>"></i></td>
                <td>
                    <?php echo htmlspecialchars($trx['keterangan']); ?>
                    <?php if($trx['nama']): ?><small class="d-block text-muted">Dari: <?php echo htmlspecialchars($trx['nama']); ?></small><?php endif; ?>
                </td>
                <td class="text-end fw-bold pe-3 <?php echo $trx['jenis'] == 'masuk' ? 'text-success' : 'text-danger'; ?>"><?php echo format_rupiah($trx['jumlah']); ?></td>
            </tr>
            <?php endwhile; ?>
            </tbody></table></div></div>
                <div class="card-footer text-center bg-light-subtle"><a href="keuangan.php" class="small text-decoration-none">Lihat Semua Transaksi &rarr;</a></div>
        </div>
    </div>
</div>

<!-- Script khusus untuk grafik di halaman ini -->
<script>
    const chartData = <?php echo json_encode($data_chart); ?>;
    const ctx = document.getElementById('financialChart').getContext('2d');
    if (window.financialChart instanceof Chart) { window.financialChart.destroy(); } // Hancurkan grafik lama jika ada
    window.financialChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                { label: 'Pemasukan', data: chartData.pemasukan, backgroundColor: 'rgba(25, 135, 84, 0.7)' }, 
                { label: 'Pengeluaran', data: chartData.pengeluaran, backgroundColor: 'rgba(220, 53, 69, 0.7)' }
            ]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { callback: (value) => 'Rp ' + value.toLocaleString('id-ID') } } },
            plugins: { tooltip: { callbacks: { label: (context) => context.dataset.label + ': ' + new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y) } } }
        }
    });
</script>

<?php
// Masukkan footer
include 'partials/footer.php';
?>
