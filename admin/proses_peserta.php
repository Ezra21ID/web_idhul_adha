<?php
session_start();
require_once '../include/config.php';

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Akses ditolak.'];
    header("Location: data_qurban.php");
    exit();
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : '';

switch ($aksi) {
    // ############ Logika untuk MENAMBAH PESERTA ke grup ############
    case 'tambah':
        $hewan_id = isset($_POST['hewan_id']) ? intval($_POST['hewan_id']) : 0;
        $warga_id = isset($_POST['warga_id']) ? intval($_POST['warga_id']) : 0;

        if ($hewan_id > 0 && $warga_id > 0) {
            // Update kolom hewan_id di tabel warga
            $stmt = $conn->prepare("UPDATE warga SET hewan_id = ? WHERE warga_id = ?");
            $stmt->bind_param('ii', $hewan_id, $warga_id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Peserta berhasil ditambahkan ke grup.'];
            } else {
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menambahkan peserta.'];
            }
            $stmt->close();
        } else {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'Data tidak valid.'];
        }
        break;

    // ############ Logika untuk MENGHAPUS PESERTA dari grup ############
    case 'hapus':
        $warga_id = isset($_GET['warga_id']) ? intval($_GET['warga_id']) : 0;

        if ($warga_id > 0) {
            // "Menghapus" berarti mengembalikan hewan_id menjadi NULL
            $stmt = $conn->prepare("UPDATE warga SET hewan_id = NULL WHERE warga_id = ?");
            $stmt->bind_param('i', $warga_id);
            if ($stmt->execute()) {
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Peserta berhasil dihapus dari grup.'];
            } else {
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menghapus peserta.'];
            }
            $stmt->close();
        } else {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'ID Warga tidak valid.'];
        }
        break;

    default:
        $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'Aksi tidak dikenal.'];
        break;
}

// Arahkan pengguna kembali ke halaman daftar qurban
header("Location: data_qurban.php");
exit();
?>