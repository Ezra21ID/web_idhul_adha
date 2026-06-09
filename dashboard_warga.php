<?php
// Memulai session untuk manajemen login
session_start();

// Mengimpor file koneksi database
require_once 'include/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Mengambil Data Warga yang Login dari Database ---
$sql_warga = "
    SELECT u.nama, w.alamat, pd.berat_kg, pd.qr_code_path, pd.distribusi_id
    FROM users u
    JOIN warga w ON u.user_id = w.user_id
    LEFT JOIN pembagian_daging pd ON w.warga_id = pd.warga_id
    WHERE u.user_id = ?
";

// Menggunakan prepared statement untuk keamanan
$stmt = $conn->prepare($sql_warga);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$warga = $result->fetch_assoc();

// Jika data warga tidak ditemukan, tampilkan pesan
if(!$warga) {
    die("Data warga tidak ditemukan di sistem.");
}

// --- Mengambil Info Umum Qurban dari Database ---
$sql_info = "SELECT jenis, jumlah FROM hewan_qurban";
$result_info = $conn->query($sql_info);
$info_qurban = ['sapi' => 0, 'kambing' => 0];
while($row = $result_info->fetch_assoc()) {
    $info_qurban[$row['jenis']] += $row['jumlah'];
}
$sql_total_penerima = "SELECT COUNT(distribusi_id) as total FROM pembagian_daging";
$info_qurban['total_penerima'] = $conn->query($sql_total_penerima)->fetch_assoc()['total'];


// Data pengumuman (sementara, bisa dibuat tabel sendiri nanti)
$pengumuman = [
    'judul' => "Jadwal Pengambilan Daging Qurban",
    'isi' => "Pengambilan daging akan dilaksanakan pada hari Sabtu, 29 Juni 2025, mulai pukul 10:00 WIB di lapangan RT. Mohon membawa kartu ini (digital atau cetak) sebagai bukti.",
    'tanggal' => "13 Juni 2025"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga - Qurban Masjid Nashruddin</title>
    
    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons dari CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* CSS Tambahan untuk Tampilan yang Lebih Baik */
        body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
        
        /* CSS untuk mengatur halaman saat dicetak */
        @media print {
            body * { visibility: hidden; }
            #kartu-qurban-wrapper, #kartu-qurban-wrapper * { visibility: visible; }
            #kartu-qurban-wrapper {
                position: absolute; left: 0; top: 0; width: 100%;
                box-shadow: none; border: 1px solid #dee2e6;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top no-print">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-house-heart-fill text-success"></i> Qurban RT 001</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3">Selamat Datang, <strong><?php echo htmlspecialchars($warga['nama']); ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main class="container mt-4">
        <!-- Judul Halaman -->
        <div class="row mb-4 no-print">
            <div class="col">
                <h2 class="fw-light">Dashboard Anda</h2>
                <p class="text-muted">Informasi qurban dan kartu pengambilan daging Anda.</p>
            </div>
        </div>

        <div class="row">
            <!-- Kolom Kiri: Kartu Pengambilan Daging -->
            <div class="col-lg-7 mb-4">
                <div class="card h-100" id="kartu-qurban-wrapper">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-ticket-perforated-fill"></i> Kartu Pengambilan Daging</h5>
                    </div>
                    <div class="card-body" id="kartu-qurban">
                        <?php if (!empty($warga['berat_kg'])): ?>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="card-title"><?php echo htmlspecialchars($warga['nama']); ?></h4>
                                    <p class="card-text text-muted mb-2"><?php echo htmlspecialchars($warga['alamat']); ?></p>
                                    <hr>
                                    <p class="mb-0">Jatah Daging Anda:</p>
                                    <p class="fs-2 fw-bold text-success"><?php echo htmlspecialchars($warga['berat_kg']); ?> kg</p>
                                </div>
                                <div class="col-md-4 text-center">
                                    <!-- KODE BARU YANG BENAR -->
<img src="<?php echo htmlspecialchars($warga['qr_code_path']); ?>" alt="QR Code" class="img-fluid rounded">
                                    <small class="text-muted d-block mt-2">Tunjukkan kode ini ke panitia</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="bi bi-info-circle display-4 text-warning"></i>
                                <h5 class="mt-3">Data Pembagian Belum Tersedia</h5>
                                <p class="text-muted">Data pembagian daging untuk Anda belum dibuat oleh panitia. Silakan cek kembali nanti.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($warga['berat_kg'])): ?>
                    <div class="card-footer bg-light text-center no-print">
                        <button onclick="window.print()" class="btn btn-secondary">
                            <i class="bi bi-printer"></i> Cetak Kartu
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Kolom Kanan: Pengumuman dan Info -->
            <div class="col-lg-5 no-print">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-megaphone-fill"></i> Pengumuman Panitia</h5></div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo htmlspecialchars($pengumuman['judul']); ?></h6>
                        <p class="card-text"><?php echo htmlspecialchars($pengumuman['isi']); ?></p>
                        <small class="text-muted">Dipublikasikan pada: <?php echo htmlspecialchars($pengumuman['tanggal']); ?></small>
                    </div>
                </div>
                <div class="card">
                     <div class="card-header"><h5 class="mb-0"><i class="bi bi-info-circle-fill"></i> Informasi Umum Qurban</h5></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Hewan Sapi <span class="badge bg-primary rounded-pill fs-6"><?php echo $info_qurban['sapi']; ?> Ekor</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Hewan Kambing <span class="badge bg-primary rounded-pill fs-6"><?php echo $info_qurban['kambing']; ?> Ekor</span></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">Total Penerima Daging <span class="badge bg-primary rounded-pill fs-6"><?php echo $info_qurban['total_penerima']; ?> Orang</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <footer class="text-center py-4 mt-5 bg-white no-print"><div class="container"><p class="text-muted mb-0">&copy; 2025 Panitia Qurban RT 001 Desa AAAA.</p></div></footer>

    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
