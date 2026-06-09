<?php
// proses/proses_hapus_user.php
session_start();
require_once '../../include/config.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Anda tidak memiliki hak akses.';
    echo json_encode($response);
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    if ($user_id === $_SESSION['user_id']) {
        $response['message'] = 'Anda tidak dapat menghapus akun Anda sendiri.';
        echo json_encode($response);
        exit();
    }
    
    $conn->begin_transaction();
    try {
        // --- Langkah 1: Dapatkan warga_id dari user_id ---
        $warga_id = null;
        $stmt_get_warga = $conn->prepare("SELECT warga_id FROM warga WHERE user_id = ?");
        $stmt_get_warga->bind_param("i", $user_id);
        $stmt_get_warga->execute();
        $result_warga = $stmt_get_warga->get_result();
        if ($row = $result_warga->fetch_assoc()) {
            $warga_id = $row['warga_id'];
        }
        $stmt_get_warga->close();

        // --- Langkah 2: Jika warga_id ditemukan, hapus data terkait di tabel `keuangan` ---
        if ($warga_id) {
            $sql_keuangan = "DELETE FROM keuangan WHERE warga_id = ?";
            $stmt_keuangan = $conn->prepare($sql_keuangan);
            $stmt_keuangan->bind_param("i", $warga_id);
            $stmt_keuangan->execute();
            $stmt_keuangan->close();
        }

        // --- Langkah 3: Hapus data dari tabel `warga` ---
        $sql_warga = "DELETE FROM warga WHERE user_id = ?";
        $stmt_warga = $conn->prepare($sql_warga);
        $stmt_warga->bind_param("i", $user_id);
        $stmt_warga->execute();
        $stmt_warga->close();

        // --- Langkah 4: Hapus data dari tabel `users` ---
        $sql_users = "DELETE FROM users WHERE user_id = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("i", $user_id);
        $stmt_users->execute();

        if ($stmt_users->affected_rows > 0) {
            $conn->commit();
            $response['status'] = 'success';
            $response['message'] = 'User dan semua data terkait berhasil dihapus.';
        } else {
            $conn->rollback();
            $response['message'] = 'User tidak ditemukan atau sudah dihapus.';
        }
        $stmt_users->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        // Berikan pesan error yang lebih spesifik untuk debugging
        $response['message'] = 'Gagal menghapus data: ' . $e->getMessage();
    }
    $conn->close();

} else {
    $response['message'] = 'ID User tidak disediakan.';
}

echo json_encode($response);
exit();