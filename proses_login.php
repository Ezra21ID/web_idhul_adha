<?php
session_start();
require_once "include/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil input dan amankan dari SQL Injection
    // Menggunakan prepared statement adalah cara yang lebih aman
    $nik = $_POST["nik"]; 
    $password = $_POST["password"];

    // Cek apakah NIK ada di database menggunakan Prepared Statements
    $sql = "SELECT * FROM users WHERE nik = ?"; //2
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // [PERUBAHAN UTAMA]
        // Gunakan password_verify() untuk mencocokkan password plaintext dari form
        // dengan password hash dari database.
        if (password_verify($password, $user["password"])) { //1
            // Jika password cocok
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["role"] = $user["role"];
            // Tambahkan data lain yang mungkin perlu di header, seperti nama
            $_SESSION['user'] = [
                'nama' => $user['nama'], // Asumsi ada kolom 'nama' di tabel users
                'nik' => $user['nik']
            ];

            $_SESSION["login_message"] = "Login berhasil!";

            // Redirect sesuai role
            switch ($user["role"]) {
                case "admin": header("Location: admin/dashboard.php"); break;
                case "panitia": header("Location: verifikasi_panitia.php"); break;
                case "berqurban": header("Location: dashboard_warga.php"); break;
                case "warga": header("Location: dashboard_warga.php"); break;
            }
            exit;
        } else {
            // Password salah
            $_SESSION["login_error"] = "Password salah!";
            header("Location: login.php");
            exit;
        }
    } else {
        // NIK tidak ditemukan
        $_SESSION["login_error"] = "NIK salah!";
        header("Location: login.php");
        exit;
    }

}
?>