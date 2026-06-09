<?php
// === KODE UNTUK admin/partials/header.php ===

// Cek hak akses admin di setiap halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak. Anda harus login sebagai admin.");
}

$user_id_admin = $_SESSION['user_id'];
$nama_admin_result = $conn->query("SELECT nama FROM users WHERE user_id = $user_id_admin");
$nama_admin = $nama_admin_result->fetch_assoc()['nama'];

$current_page_name = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- BARIS YANG DIPERBAIKI: Menambahkan kembali library Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f0f2f5;
        }

        #sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
            flex-shrink: 0;
        }

        #sidebar .nav-link {
            color: #adb5bd;
            font-size: 1.05rem;
            padding: 0.75rem 1.5rem;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background-color: #495057;
            color: #fff;
            border-left: 4px solid #0d6efd;
            padding-left: calc(1.5rem - 4px);
        }

        #sidebar .nav-link .bi {
            margin-right: 0.75rem;
        }

        #main-content {
            flex-grow: 1;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .07);
        }

        /* CSS untuk mencetak */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                display: block;
                background-color: #fff;
            }

            .printable-area {
                border: none !important;
                box-shadow: none !important;
            }

            .card-header {
                border-bottom: 1px solid #dee2e6 !important;
            }
        }
    </style>
</head>

<body>
    <aside id="sidebar" class="d-flex flex-column p-3 no-print">
        <h4 class="text-center mb-4 border-bottom pb-3">
            <i class="bi bi-shield-lock-fill"></i> ADMIN PANEL
        </h4>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($current_page_name == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="bi bi-speedometer2"></i>Dashboard
                </a>
            </li>
            <li>
                <a href="manajemen_user.php" class="nav-link <?php echo ($current_page_name == 'manajemen_user.php') ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>Manajemen User
                </a>
            </li>
            <li>
                <a href="keuangan.php" class="nav-link <?php echo ($current_page_name == 'keuangan.php') ? 'active' : ''; ?>">
                    <i class="bi bi-wallet2"></i>Keuangan
                </a>
            </li>
            <li>
                <a href="data_qurban.php" class="nav-link <?php echo ($current_page_name == 'data_qurban.php') ? 'active' : ''; ?>">
                    <i class="bi bi-clipboard-data-fill"></i>Data Qurban
                </a>
            </li>
            <li>
                <a href="distribusi_daging.php" class="nav-link <?php echo ($current_page_name == 'distribusi_daging.php') ? 'active' : ''; ?>">
                    <i class="bi bi-qr-code-scan"></i>Distribusi Daging
                </a>
            </li>
            <li>
                <a href="laporan.php" class="nav-link <?php echo ($current_page_name == 'laporan.php') ? 'active' : ''; ?>">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>Laporan
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4 me-2"></i><strong><?php echo htmlspecialchars($nama_admin); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="#">Profil</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </aside>

    <main id="main-content">
        <div class="container-fluid p-4">