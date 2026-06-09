<?php
session_start();
require_once 'include/config.php';

// --- SIMULASI LOGIN ADMIN ---
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Cek hak akses admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Arahkan ke halaman login jika bukan admin
    exit();
}

$user_id = $_SESSION['user_id'];
$nama_admin_result = $conn->query("SELECT nama FROM users WHERE user_id = $user_id");
$nama_admin = $nama_admin_result->fetch_assoc()['nama'];

// --- Query untuk Kartu Statistik ---
$statistik = [];
$statistik['total_dana'] = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'masuk'")->fetch_assoc()['total'] ?? 0;
$statistik['total_pengeluaran'] = $conn->query("SELECT SUM(jumlah) as total FROM keuangan WHERE jenis = 'keluar'")->fetch_assoc()['total'] ?? 0;
$statistik['shohibul_qurban'] = $conn->query("SELECT COUNT(*) as total FROM warga WHERE is_berqurban = 1")->fetch_assoc()['total'] ?? 0;
$statistik['total_penerima'] = $conn->query("SELECT COUNT(distribusi_id) as total FROM pembagian_daging")->fetch_assoc()['total'] ?? 0;

// --- Query untuk Grafik ---
// Mengelompokkan data per tanggal
$sql_chart = "SELECT tanggal, jenis, SUM(jumlah) as total FROM keuangan GROUP BY tanggal, jenis ORDER BY tanggal";
$result_chart = $conn->query($sql_chart);

$chart_data_raw = [];
while($row = $result_chart->fetch_assoc()) {
    $chart_data_raw[$row['tanggal']][$row['jenis']] = $row['total'];
}

$data_chart = ['labels' => [], 'pemasukan' => [], 'pengeluaran' => []];
foreach ($chart_data_raw as $tgl => $jenis) {
    $data_chart['labels'][] = date("d M", strtotime($tgl)); // Format tanggal agar lebih pendek
    $data_chart['pemasukan'][] = $jenis['masuk'] ?? 0;
    $data_chart['pengeluaran'][] = $jenis['keluar'] ?? 0;
}

// --- Query untuk Transaksi Terakhir ---
$sql_trx = "SELECT u.nama, k.keterangan, k.jenis, k.jumlah, k.tanggal 
            FROM keuangan k
            LEFT JOIN warga w ON k.warga_id = w.warga_id
            LEFT JOIN users u ON w.user_id = u.user_id
            ORDER BY k.tanggal DESC, k.transaksi_id DESC LIMIT 5";
$result_trx = $conn->query($sql_trx);

$transaksi_terakhir = [];
while($row = $result_trx->fetch_assoc()) {
    $transaksi_terakhir[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Qurban RT 001</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS sama persis seperti kode sebelumnya */
        body { display: flex; min-height: 100vh; background-color: #f0f2f5; }
        #sidebar { width: 260px; min-height: 100vh; background-color: #212529; color: #fff; flex-shrink: 0; }
        #sidebar .nav-link { color: #adb5bd; font-size: 1.05rem; padding: 0.75rem 1.5rem; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background-color: #495057; color: #fff; border-left: 4px solid #0d6efd; padding-left: calc(1.5rem - 4px); }
        #sidebar .nav-link .bi { margin-right: 0.75rem; }
        #main-content { flex-grow: 1; }
        .card { border: none; border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .stat-card { border-left: 5px solid; }
        .stat-card.border-primary { border-color: #0d6efd !important; }
        .stat-card.border-danger { border-color: #dc3545 !important; }
        .stat-card.border-success { border-color: #198754 !important; }
        .stat-card.border-info { border-color: #0dcaf0 !important; }
    </style>
</head>
<body>
    <aside id="sidebar" class="d-flex flex-column p-3">
        <!-- Konten Sidebar (Menu) -->
        <h4 class="text-center mb-4 border-bottom pb-3"><i class="bi bi-shield-lock-fill"></i> ADMIN PANEL</h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="#" class="nav-link active" aria-current="page"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
            <li><a href="admin/dashboard.php" class="nav-link"><i class="bi bi-people-fill"></i>Manajemen User</a></li>
            <li><a href="admin/keuangan.php" class="nav-link"><i class="bi bi-wallet2"></i>Keuangan</a></li>
            <li><a href="admin/data_qurban.php" class="nav-link"><i class="bi bi-clipboard-data-fill"></i>Data Qurban</a></li>
            <li><a href="admin/laporan.php" class="nav-link"><i class="bi bi-file-earmark-bar-graph-fill"></i>Laporan</a></li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4 me-2"></i><strong><?php echo htmlspecialchars($nama_admin); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">Logout</a></li>
            </ul>
        </div>
    </aside>

    <main id="main-content">
        <div class="container-fluid p-4">
            <h1 class="mb-4">Dashboard Utama</h1>
            <!-- Kartu Statistik -->
            <div class="row">
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
                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm"><div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-bar-chart-line-fill"></i> Grafik Arus Kas Keuangan</h6></div><div class="card-body"><canvas id="financialChart"></canvas></div></div>
                </div>
                <div class="col-lg-5 mb-4">
                     <div class="card shadow-sm">
                        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary"><i class="bi bi-arrow-down-up"></i> Transaksi Terakhir</h6></div>
                        <div class="card-body p-0"><div class="table-responsive"><table class="table table-striped table-hover mb-0"><tbody>
                        <?php foreach ($transaksi_terakhir as $trx): ?>
                        <tr>
                            <td class="ps-3"><i class="bi <?php echo $trx['jenis'] == 'masuk' ? 'bi-arrow-down-circle-fill text-success' : 'bi-arrow-up-circle-fill text-danger'; ?>"></i></td>
                            <td>
                                <?php echo htmlspecialchars($trx['keterangan']); ?>
                                <?php if($trx['nama']): ?>
                                    <small class="d-block text-muted">Dari: <?php echo htmlspecialchars($trx['nama']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold pe-3 <?php echo $trx['jenis'] == 'masuk' ? 'text-success' : 'text-danger'; ?>"><?php echo format_rupiah($trx['jumlah']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody></table></div></div>
                         <div class="card-footer text-center bg-light-subtle"><a href="#" class="small text-decoration-none">Lihat Semua Transaksi &rarr;</a></div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const chartData = <?php echo json_encode($data_chart); ?>;
        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
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
</body>
</html>

