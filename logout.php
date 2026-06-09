<?php
// 1. Selalu mulai session di awal
session_start();

// 2. Hapus semua variabel yang ada di dalam session
$_SESSION = array();

// 3. Hancurkan session-nya
session_destroy();

// 4. Arahkan (redirect) pengguna kembali ke halaman login
header("Location: login.php");
exit(); // Pastikan untuk keluar setelah redirect
?>