<?php
session_start();
require_once '../include/config.php';

// Pastikan koneksi database berhasil
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 1. Periksa izin akses (Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Akses ditolak. Anda harus login sebagai admin.'];
    header("Location: keuangan.php");
    exit();
}

// 2. Ambil aksi dari URL
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

switch ($aksi) {
    // ############ CREATE ############
    case 'tambah':
        $tanggal = $_POST['tanggal'];
        $jenis = $_POST['jenis'];
        $jumlah = $_POST['jumlah'];
        $keterangan = $_POST['keterangan'];
        // PERBAIKAN: Ambil 'warga_id' dari form. Jika kosong, set sebagai NULL.
        $warga_id = !empty($_POST['warga_id']) ? $_POST['warga_id'] : NULL;

        if (empty($tanggal) || empty($jenis) || empty($jumlah) || empty($keterangan)) {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Semua kolom wajib diisi.'];
        } else {
            // PERBAIKAN: Tambahkan kolom 'warga_id' ke dalam query INSERT
            $stmt = $conn->prepare("INSERT INTO keuangan (tanggal, jenis, jumlah, keterangan, warga_id) VALUES (?, ?, ?, ?, ?)");
            // PERBAIKAN: Sesuaikan tipe data dan variabel di bind_param (s menjadi i untuk warga_id)
            $stmt->bind_param("ssisi", $tanggal, $jenis, $jumlah, $keterangan, $warga_id);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Transaksi baru berhasil ditambahkan.'];
            } else {
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menyimpan data: ' . $stmt->error];
            }
            $stmt->close();
        }
        break;

    // ############ UPDATE ############
    case 'edit':
        $transaksi_id = $_POST['transaksi_id'];
        $tanggal = $_POST['tanggal'];
        $jenis = $_POST['jenis'];
        $jumlah = $_POST['jumlah'];
        $keterangan = $_POST['keterangan'];
        // PERBAIKAN: Ambil 'warga_id' dari form edit. Jika kosong, set sebagai NULL.
        $warga_id = !empty($_POST['warga_id']) ? $_POST['warga_id'] : NULL;

        if (empty($transaksi_id) || empty($tanggal) || empty($jenis) || empty($jumlah) || empty($keterangan)) {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Data untuk proses edit tidak lengkap.'];
        } else {
            // PERBAIKAN: Tambahkan 'warga_id' ke dalam query UPDATE
            $stmt = $conn->prepare("UPDATE keuangan SET tanggal = ?, jenis = ?, jumlah = ?, keterangan = ?, warga_id = ? WHERE transaksi_id = ?");
            // PERBAIKAN: Sesuaikan tipe data dan variabel di bind_param (tambah i untuk warga_id)
            $stmt->bind_param("ssisii", $tanggal, $jenis, $jumlah, $keterangan, $warga_id, $transaksi_id);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Data transaksi berhasil diperbarui.'];
            } else {
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal memperbarui data: ' . $stmt->error];
            }
            $stmt->close();
        }
        break;

    // ############ DELETE ############
    case 'hapus':
        // Bagian hapus sudah benar, tidak perlu diubah
        $transaksi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($transaksi_id > 0) {
            $stmt = $conn->prepare("DELETE FROM keuangan WHERE transaksi_id = ?");
            $stmt->bind_param("i", $transaksi_id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Transaksi telah berhasil dihapus.'];
            } else {
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menghapus data: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'ID transaksi tidak valid untuk dihapus.'];
        }
        break;

    default:
        $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'Aksi yang diminta tidak valid.'];
        break;
}

// 3. Arahkan kembali pengguna ke halaman utama
header("Location: keuangan.php");
exit();
?>