<?php session_start(); ?>
<!doctype html>
<html lang="en">

<head>
    <title>Login - E Adha Masjid Nashruddin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/login-form-18/css/style.css">

    <style>
        /* Pengaturan Tata Letak Dasar agar Sidebar dan Konten berdampingan */
        body {
            display: flex;
            flex-wrap: wrap;
            margin: 0;
            min-height: 100vh;
        }

        .sidebar {
            flex: 0 0 100px;
            /* Lebar sidebar di desktop */
            background: #000;
            display: flex;
            justify-content: center;
            padding-top: 20px;
        }

        .logo-sapi {
            width: 60px;
            height: auto;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            /* Warna background lembut */
        }

        /* --- PERBAIKAN RESPONSIF UNTUK HP --- */
        @media (max-width: 992px) {
            body {
                flex-direction: column;
                /* Sidebar jadi di atas, konten di bawah */
            }

            .sidebar {
                flex: 0 0 auto;
                width: 100%;
                padding: 15px 0;
            }

            .logo-sapi {
                width: 50px;
            }

            .main-content {
                padding: 10px;
            }

            /* Memastikan gambar ucapan tidak terlalu besar di HP */
            .login-img-left {
                max-width: 100%;
                height: auto;
                margin-bottom: 20px;
            }

            /* Menyesuaikan judul agar tidak memenuhi layar */
            h1.judul-utama {
                font-size: 1.4rem;
                margin-top: 20px;
            }

            .login-wrap {
                margin-bottom: 30px;
            }
        }

        /* Perbaikan untuk layar sangat kecil */
        @media (max-width: 576px) {
            .login-wrap {
                padding: 1.5rem !important;
            }

            .btn-primary {
                width: 100%;
                /* Tombol login full width di HP agar mudah diklik */
            }
        }
        /* --- TAMBAHAN FITUR SCROLLING --- */
        html, body {
            /* Memastikan halaman bisa digulir secara vertikal */
            height: auto !important;
            overflow-y: auto !important;
            overflow-x: hidden; /* Mencegah geser ke samping */
        }

        .main-content {
            /* Memberi ruang agar konten tidak mentok di layar HP */
            min-height: 100vh;
            display: block;
            padding-bottom: 50px; 
        }

        @media (max-width: 992px) {
            .login-img-left {
                /* Memastikan gambar tetap di tengah dan tidak terlalu besar */
                display: block;
                margin-left: auto;
                margin-right: auto;
                max-width: 80% !important;
            }
        }
        /* --- PERBAIKAN AGAR SIMETRIS DI TENGAH (HP) --- */
        @media (max-width: 992px) {
            /* Membuat container utama menjadi pusat */
            .main-content .container-fluid, 
            .main-content .row {
                display: flex;
                flex-direction: column;
                align-items: center; /* Mengetengahkan semua item secara horizontal */
                justify-content: center;
                text-align: center;
                width: 100%;
            }

            /* Mengetengahkan judul */
            h1.judul-utama {
                width: 100%;
                margin: 20px 0 !important;
                display: block;
            }

            /* Mengetengahkan gambar Idul Adha agar tidak melenceng */
            .login-img-left {
                max-width: 85% !important; /* Ukuran pas untuk layar HP */
                height: auto;
                margin: 0 auto 25px auto !important; /* Margin auto kiri-kanan untuk center */
                display: block;
            }

            /* Mengetengahkan kotak login */
            .login-wrap {
                width: 100%;
                max-width: 400px; /* Batas lebar agar tidak terlalu melar di HP lebar */
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 30px 20px !important;
            }

            /* Memastikan baris (row) tidak memiliki margin negatif yang bikin miring */
            .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
        }
        /* --- FIX GAMBAR IDUL ADHA TENGAH (HP) --- */
        @media (max-width: 992px) {
            /* Menghilangkan float/flex default bootstrap pada kolom gambar */
            .col-lg-7.text-center {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                width: 100% !important;
                padding: 0 !important;
            }

            /* Memastikan gambar memiliki margin otomatis kiri & kanan */
            .login-img-left {
                display: block !important;
                margin-left: auto !important;
                margin-right: auto !important;
                max-width: 80% !important; /* Ukuran proporsional di HP */
            }
            
            /* Mengatur agar teks judul dan form juga mengikuti pusat yang sama */
            .row.align-items-center {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }
        /* --- FIX AKHIR: SIMETRIS TOTAL DI HP --- */
        @media (max-width: 992px) {
            /* 1. Memaksa baris (row) untuk menumpuk ke bawah dan rata tengah */
            .row.align-items-center {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                justify-content: center !important;
                margin: 0 !important;
                width: 100% !important;
            }

            /* 2. Memaksa kolom pembungkus gambar agar lebarnya penuh dan kontennya di tengah */
            .col-lg-7 {
                width: 100% !important;
                max-width: 100% !important;
                display: flex !important;
                justify-content: center !important;
                padding: 0 !important;
                margin-bottom: 20px !important;
            }

            /* 3. Mengatur gambar agar ukurannya pas dan margin kiri-kanan otomatis */
            .login-img-left {
                width: 85% !important; /* Sesuaikan angka ini jika ingin lebih besar/kecil */
                max-width: 400px !important; /* Supaya tidak terlalu raksasa di tablet */
                height: auto !important;
                margin: 0 auto !important;
                display: block !important;
            }

            /* 4. Memastikan kolom form login juga rata tengah */
            .col-lg-5 {
                width: 100% !important;
                max-width: 100% !important;
                display: flex !important;
                justify-content: center !important;
                padding: 0 15px !important;
            }

            /* 5. Membuat kotak login tidak melar penuh tapi tetap di tengah */
            .login-wrap {
                width: 100% !important;
                max-width: 350px !important;
                margin: 0 auto !important;
            }
        }
        /* --- PENYEMPURNAAN AKHIR: CENTER PRESISI --- */
        @media (max-width: 992px) {
            /* Memaksa kontainer utama untuk memposisikan semua anak di tengah */
            .main-content .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important; /* Menarik ke tengah secara horizontal */
            }

            /* Mengoreksi kolom gambar agar tidak berat ke kiri */
            .col-lg-7 {
                width: 100% !important;
                display: flex !important;
                justify-content: center !important; /* Mengetengahkan isi kolom */
                padding: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* Menyesuaikan gambar Idul Adha agar seimbang */
            .login-img-left {
                width: 90% !important;
                max-width: 380px !important;
                margin: 0 auto !important; /* Margin otomatis kiri-kanan */
                display: block !important;
            }

            /* Memastikan kolom form login juga simetris */
            .col-lg-5 {
                width: 100% !important;
                display: flex !important;
                justify-content: center !important;
                padding: 0 !important;
            }

            /* Membuat form login pas di tengah */
            .login-wrap {
                width: 85% !important;
                max-width: 350px !important;
                margin-left: auto !important;
                margin-right: auto !important;
            }
        }
    </style>
</head>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<body>

    <!-- BAGIAN 1: SIDEBAR HITAM DENGAN LOGO SAPI -->
    <div class="sidebar">
        <!-- GANTI 'src' DENGAN PATH LOGO SAPI ANDA -->
        <img src="assets/login-form-18/images/logo sapi 2.png" alt="Logo Sapi" class="logo-sapi">
    </div>

    <!-- BAGIAN 2: KONTEN UTAMA DI KANAN -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Judul Utama -->
            <div class="row justify-content-center mb-5">
                <div class="col-12 text-center">
                    <h1 class="judul-utama">E-ADHA MASJID NASHRUDDIN</h1>
                </div>
            </div>

            <!-- Konten Gambar dan Form -->
            <div class="row align-items-center justify-content-center">

                <!-- Gambar Ucapan Idul Adha -->
                <div class="col-lg-7 text-center mb-4">
                    <img src="assets/login-form-18/images/ucapan_adha.png" alt="Idul Adha" class="img-fluid login-img-left">
                </div>

                <!-- Form Login -->
                <div class="col-lg-5">
                    <div class="login-wrap p-4 p-md-5">
                        <div class="icon d-flex align-items-center justify-content-center">
                            <span class="fa fa-user-o"></span>
                        </div>
                        <h3 class="text-center mb-4">Silakan login terlebih dahulu</h3>

                        <!-- Pesan Error -->
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_SESSION['login_error']); ?>
                            </div>
                            <?php unset($_SESSION['login_error']); ?>
                        <?php endif; ?>

                        <form action="proses_login.php" method="post" class="login-form">
                            <div class="form-group mb-3">
                                <input type="text" name="nik" class="form-control" placeholder="NIK" required>
                            </div>
                            <div class="form-group d-flex mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary rounded submit p-3 px-5">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>