<?php
session_start();
require_once '../../include/config.php'; // Sesuaikan path ke config.php

// Pastikan hanya bisa diakses via metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari formulir dan bersihkan
    $nama = trim($_POST['nama']);
    $nik = trim($_POST['nik']);
    $alamat = trim($_POST['alamat']);
    $role = trim($_POST['role']);
    $password = $_POST['password'];

    // Validasi sederhana, pastikan data tidak kosong
    if (empty($nama) || empty($nik) || empty($alamat) || empty($role) || empty($password)) {
        $_SESSION['error'] = "Semua field harus diisi!";
        header("location: ../manajemen_user.php");
        exit();
    }

    // 2. Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Gunakan Transaksi Database
    $conn->begin_transaction();

    try {
        // Query 1: Masukkan data ke tabel 'users'
        $sql_users = "INSERT INTO users (nama, nik, password, role) VALUES (?, ?, ?, ?)";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("ssss", $nama, $nik, $hashed_password, $role);
        $stmt_users->execute();

        // Ambil ID dari user yang baru saja dibuat
        $new_user_id = $conn->insert_id;

        // Cek jika user ID berhasil didapat
        if ($new_user_id == 0) {
            throw new Exception("Gagal mendapatkan ID user baru.");
        }

        // Tentukan status panitia berdasarkan role
        $is_panitia = ($role === 'panitia' || $role === 'admin') ? 1 : 0;
        $is_berqurban = ($role === 'berqurban') ? 1 : 0;

        // Query 2: Masukkan data ke tabel 'warga'
        $sql_warga = "INSERT INTO warga (user_id, alamat, is_panitia,is_berqurban) VALUES (?, ?, ?,?)";
        $stmt_warga = $conn->prepare($sql_warga);
        $stmt_warga->bind_param("isii", $new_user_id, $alamat, $is_panitia,$is_berqurban);
        $stmt_warga->execute();

        // Jika semua query berhasil, commit transaksi
        $conn->commit();
        $_SESSION['sukses'] = "User baru berhasil ditambahkan!";

        // =======================================================
        // SOLUSI: Tutup statement di dalam blok try setelah selesai digunakan
        // =======================================================
        $stmt_users->close();
        $stmt_warga->close();

    } catch (Exception $e) {
        // Jika ada error, batalkan semua perubahan
        $conn->rollback();
        // Simpan pesan error untuk ditampilkan
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        
        // =======================================================
        // SOLUSI: Pastikan juga untuk menutup statement jika terjadi error,
        // tapi cek dulu apakah variabelnya sudah dibuat.
        // =======================================================
        if (isset($stmt_users)) {
            $stmt_users->close();
        }
        if (isset($stmt_warga)) {
            $stmt_warga->close();
        }
    }

    // Tutup koneksi database
    $conn->close();

    // 4. Arahkan kembali ke halaman manajemen user
    header("location: ../manajemen_user.php");
    exit();

} else {
    // Jika file diakses langsung, arahkan ke halaman utama
    header("location: ../../index.php");
    exit();
}
?>