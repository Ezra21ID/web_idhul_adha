<?php
// proses/proses_edit_user.php
session_start();
require_once '../../include/config.php';

// Atur header untuk output JSON
header('Content-Type: application/json');

// Siapkan array untuk respons
$response = ['status' => 'error', 'message' => 'Terjadi kesalahan yang tidak diketahui.'];

// Cek hak akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Anda tidak memiliki hak akses untuk melakukan tindakan ini.';
    echo json_encode($response);
    exit();
}

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dan sanitasi
    $user_id = $_POST['user_id'] ?? null;
    $nama = trim($_POST['nama'] ?? '');
    $nik = trim($_POST['nik'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validasi dasar
    if (empty($user_id) || empty($nama) || empty($nik) || empty($alamat) || empty($role)) {
        $response['message'] = 'Semua field (kecuali password) harus diisi.';
        echo json_encode($response);
        exit();
    }

    $conn->begin_transaction();
    try {
        // --- 1. UPDATE TABEL users ---
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_users = "UPDATE users SET nama = ?, nik = ?, role = ?, password = ? WHERE user_id = ?";
            $stmt_users = $conn->prepare($sql_users);
            $stmt_users->bind_param("ssssi", $nama, $nik, $role, $hashed_password, $user_id);
        } else {
            $sql_users = "UPDATE users SET nama = ?, nik = ?, role = ? WHERE user_id = ?";
            $stmt_users = $conn->prepare($sql_users);
            $stmt_users->bind_param("sssi", $nama, $nik, $role, $user_id);
        }
        $stmt_users->execute();

        // --- 2. UPDATE TABEL warga ---
        $is_panitia = ($role === 'admin' || $role === 'panitia') ? 1 : 0;
        $is_berqurban = ($role === 'berqurban') ? 1 : 0;

        $check_warga = $conn->prepare("SELECT user_id FROM warga WHERE user_id = ?");
        $check_warga->bind_param("i", $user_id);
        $check_warga->execute();
        $result_warga = $check_warga->get_result();

        if ($result_warga->num_rows > 0) {
            $sql_warga = "UPDATE warga SET alamat = ?, is_panitia = ?, is_berqurban = ? WHERE user_id = ?";
            $stmt_warga = $conn->prepare($sql_warga);
            $stmt_warga->bind_param("siii", $alamat, $is_panitia, $is_berqurban, $user_id);
        } else {
            $sql_warga = "INSERT INTO warga (user_id, alamat, is_panitia, is_berqurban) VALUES (?, ?, ?, ?)";
            $stmt_warga = $conn->prepare($sql_warga);
            $stmt_warga->bind_param("isii", $user_id, $alamat, $is_panitia, $is_berqurban);
        }
        $stmt_warga->execute();

        $conn->commit();

         // Cek apakah user yang diedit adalah user yang sedang login.
        // Jika ya, perbarui data sesinya agar sinkron.
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) {
            // Anda bisa memperbarui lebih dari sekedar role jika perlu
            // $_SESSION['nama'] = $nama; 
            $_SESSION['role'] = $role;
        }
        
        $response['status'] = 'success';
        $response['message'] = 'Data user berhasil diperbarui.';
        // Kirim kembali data yang sudah diupdate untuk memperbarui tabel
        $response['updated_data'] = [
            'nama' => $nama,
            'nik' => $nik,
            'alamat' => $alamat,
            'role' => ucfirst($role), // Mengirim role dengan huruf kapital di awal
            'is_panitia' => $is_panitia,
            'is_berqurban' => $is_berqurban
        ];

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Gagal memperbarui data: ' . $e->getMessage();
    }

    if (isset($stmt_users)) $stmt_users->close();
    if (isset($stmt_warga)) $stmt_warga->close();
    $conn->close();

} else {
    $response['message'] = 'Metode request tidak valid.';
}

// Selalu outputkan respons JSON
echo json_encode($response);
exit();