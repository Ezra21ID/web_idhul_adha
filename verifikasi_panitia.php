<?php
// 1. Memulai session untuk manajemen login
session_start();

// 2. Mengimpor file koneksi database
require_once 'include/config.php';

// 3. Cek apakah user sudah login dan ROLENYA PANITIA
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'panitia') {
    header("Location: login.php"); // Arahkan ke halaman login jika bukan panitia
    exit();
}

$user_id = $_SESSION['user_id'];

// 4. Kueri untuk mengambil nama panitia
$sql_panitia = "SELECT nama FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql_panitia);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$panitia = $result->fetch_assoc();

if(!$panitia) {
    die("Data panitia tidak ditemukan.");
}

// Data pengumuman dan info umum bisa dibiarkan atau dihapus, tidak akan digunakan di halaman ini
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pengambilan - Panel Panitia</title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .card { border: none; border-radius: 0.75rem; box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#"><i class="bi bi-shield-check text-primary"></i> Panel Panitia</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3">Selamat Bertugas, <strong><?php echo htmlspecialchars($panitia['nama']); ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <main class="container mt-4">
        <!-- Judul Halaman -->
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-light">Verifikasi Pengambilan Daging</h2>
                <p class="text-muted">Gunakan kamera untuk memindai QR Code pada kartu warga.</p>
            </div>
        </div>

        <div class="row justify-content-center">
            <!-- Kolom Scanner -->
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-qr-code-scan"></i> Pemindai QR Code</h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <!-- 1. Tempat untuk menampilkan video kamera -->
                        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: auto;"></div>
                        
                        <!-- 2. Tempat untuk menampilkan hasil scan -->
                        <div id="scan-result" class="mt-4">
                            <p class="text-muted">Arahkan kamera ke QR Code...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer class="text-center py-4 mt-5 bg-white"><div class="container"><p class="text-muted mb-0">© 2025 Panitia Qurban RT 001 Desa AAAA.</p></div></footer>

    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Library dan Logika Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Code matched = ${decodedText}`, decodedResult);
            let scanner = html5QrcodeScanner;
            scanner.clear().catch(error => console.error("Gagal membersihkan scanner.", error));
            
            document.getElementById('scan-result').innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Memproses token...</p>
            `;
            
            fetch('proses/proses_verifikasi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'token=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json())
            .then(data => {
                let resultHtml = '';
                if (data.status === 'success') {
                    resultHtml = `
                        <div class="alert alert-success">
                            <h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> Verifikasi Berhasil!</h4>
                            <p><strong>Nama:</strong> ${data.nama}</p>
                            <p><strong>Jatah:</strong> ${data.berat_kg} kg</p>
                            <hr>
                            <p class="mb-0">Status telah diperbarui menjadi SUDAH DIAMBIL.</p>
                        </div>
                        <button onclick="window.location.reload()" class="btn btn-primary mt-3">Scan Berikutnya</button>
                    `;
                } else {
                    resultHtml = `
                        <div class="alert alert-danger">
                            <h4 class="alert-heading"><i class="bi bi-x-circle-fill"></i> Gagal!</h4>
                            <p>${data.message}</p>
                        </div>
                        <button onclick="window.location.reload()" class="btn btn-danger mt-3">Coba Lagi</button>
                    `;
                }
                document.getElementById('scan-result').innerHTML = resultHtml;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('scan-result').innerHTML = `
                    <div class="alert alert-danger">Terjadi kesalahan saat menghubungi server.</div>
                    <button onclick="window.location.reload()" class="btn btn-secondary mt-3">Ulangi</button>
                `;
            });
        }

        function onScanFailure(error) { /* Biarkan kosong */ }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false
        );
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>

</body>
</html>