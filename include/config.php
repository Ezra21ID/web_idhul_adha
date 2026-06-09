<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db = "db_kurban";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set karakter set ke utf8mb4 untuk mendukung berbagai karakter
$conn->set_charset('utf8mb4');

/**
 * Fungsi helper untuk memformat angka menjadi format Rupiah.
 * @param int $angka Angka yang akan diformat.
 * @return string String dalam format Rupiah.
 */
function format_rupiah($angka)
{
    return "Rp " . number_format($angka ?? 0, 0, ',', '.');
}
