<?php
session_start();
require_once '../include/config.php';

// Atur header agar responsnya berupa JSON
header('Content-Type: application/json');

// Pastikan panitia yang mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'panitia') {
    echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
    exit();
}

// Pastikan token dikirim
if (!isset($_POST['token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Token tidak ditemukan.']);
    exit();
}

$token = $_POST['token'];

// Cari data berdasarkan token
$sql = "SELECT pd.distribusi_id, pd.berat_kg, pd.status_pengambilan, u.nama 
        FROM pembagian_daging pd
        JOIN warga w ON pd.warga_id = w.warga_id
        JOIN users u ON w.user_id = u.user_id
        WHERE pd.qr_code_token = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    // Jika token tidak ditemukan di database
    echo json_encode(['status' => 'error', 'message' => 'QR Code tidak valid atau tidak terdaftar.']);
} elseif ($data['status_pengambilan'] == 'sudah_diambil') {
    // Jika sudah pernah diambil
    echo json_encode(['status' => 'error', 'message' => 'Daging untuk kartu ini SUDAH DIAMBIL sebelumnya.']);
} else {
    // Jika valid dan belum diambil, UPDATE statusnya
    $update_sql = "UPDATE pembagian_daging SET status_pengambilan = 'sudah_diambil' WHERE distribusi_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('i', $data['distribusi_id']);
    
    if($update_stmt->execute()) {
        // Kirim respons sukses ke Javascript
        echo json_encode([
            'status' => 'success',
            'message' => 'Verifikasi Berhasil!',
            'nama' => $data['nama'],
            'berat_kg' => $data['berat_kg']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate status pengambilan.']);
    }
    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>