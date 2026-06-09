<?php
// Sesuaikan path ini dengan struktur Anda
require_once '../../include/config.php';
// Menggunakan __DIR__ untuk path yang lebih pasti
require_once __DIR__ . '/../../include/phpqrcode/phpqrcode.php';

if (isset($_POST['generate_all'])) {
    // Ambil semua warga yang BELUM punya kartu distribusi
    $sql = "SELECT w.warga_id, w.is_panitia, w.is_berqurban 
            FROM warga w
            LEFT JOIN pembagian_daging p ON w.warga_id = p.warga_id
            WHERE p.distribusi_id IS NULL";
    
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Cek dan buat folder jika belum ada
        $qrcodes_dir = '../../qrcodes/'; 
        if (!is_dir($qrcodes_dir)) {
            mkdir($qrcodes_dir, 0777, true);
        }

        while ($warga = $result->fetch_assoc()) {
            $warga_id = $warga['warga_id'];
            $is_panitia = $warga['is_panitia'];
            $is_berqurban = $warga['is_berqurban'];

            // ATURAN PERHITUNGAN BERAT DAGING
            $berat_daging = 2; // Default jatah warga
            if ($is_panitia) { $berat_daging += 0.5; } // Tambahan panitia
            if ($is_berqurban) { $berat_daging += 2; } // Tambahan berqurban
            
            // Aturan multi-role sudah otomatis tertangani
            // Warga + Panitia = 2 + 0.5 = 2.5
            // Warga + Berqurban = 2 + 2 = 4
            // Warga + Panitia + Berqurban = 2 + 0.5 + 2 = 4.5
            
            // GENERATE QR CODE
            $token = 'QURBAN-RT01-' . strtoupper(uniqid());
            $qr_code_file = $qrcodes_dir . $token . '.png';
            
            QRcode::png($token, $qr_code_file, QR_ECLEVEL_L, 5, 2);

            $db_qr_path = 'qrcodes/' . $token . '.png';

            // SIMPAN KE DATABASE
            $stmt = $conn->prepare("INSERT INTO pembagian_daging (warga_id, berat_kg, qr_code_path, qr_code_token, status_pengambilan) VALUES (?, ?, ?, ?, 'belum_diambil')");
            $stmt->bind_param("idss", $warga_id, $berat_daging, $db_qr_path, $token);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Arahkan kembali ke halaman distribusi, mungkin dengan pesan sukses
    header("Location: ../distribusi_daging.php");
    exit();

} else {
    // Jika diakses langsung, kembalikan ke dashboard
    header("Location: ../dashboard.php");
    exit();
}
?>