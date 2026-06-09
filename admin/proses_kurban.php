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
    // ############ CREATE: Logika untuk menambah data hewan ############
    case 'tambah':
        $jenis = $_POST['jenis'];
        $jumlah = (int)$_POST['jumlah'];
        $harga_per_ekor = (int)$_POST['harga_per_ekor'];
        $biaya_admin = (int)$_POST['biaya_admin'];

        if (empty($jenis) || empty($jumlah) || empty($harga_per_ekor)) {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Kolom Jenis, Jumlah, dan Harga wajib diisi.'];
            header("Location: data_qurban.php");
            exit();
        }

        $total_harga = ($harga_per_ekor * $jumlah) + $biaya_admin;

        $stmt = $conn->prepare(
            "INSERT INTO hewan_qurban (jenis, jumlah, harga_per_ekor, biaya_admin, total_harga) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('siiii', $jenis, $jumlah, $harga_per_ekor, $biaya_admin, $total_harga);

        if ($stmt->execute()) {
            $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Data hewan qurban berhasil ditambahkan.'];
        } else {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menyimpan data: ' . $stmt->error];
        }
        $stmt->close();
        break;

    // ############ UPDATE: Logika untuk mengubah data hewan ############
    case 'edit':
        $hewan_id = (int)$_POST['hewan_id'];
        $jenis = $_POST['jenis'];
        $jumlah = (int)$_POST['jumlah'];
        $harga_per_ekor = (int)$_POST['harga_per_ekor'];
        $biaya_admin = (int)$_POST['biaya_admin'];

        if ($hewan_id === 0 || empty($jenis) || empty($jumlah) || empty($harga_per_ekor)) {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Data tidak lengkap untuk proses edit.'];
            header("Location: data_qurban.php");
            exit();
        }

        $total_harga = ($harga_per_ekor * $jumlah) + $biaya_admin;

        $stmt = $conn->prepare(
            "UPDATE hewan_qurban SET jenis=?, jumlah=?, harga_per_ekor=?, biaya_admin=?, total_harga=? WHERE hewan_id=?"
        );
        $stmt->bind_param('siiidi', $jenis, $jumlah, $harga_per_ekor, $biaya_admin, $total_harga, $hewan_id);

        if ($stmt->execute()) {
            $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Data hewan qurban berhasil diperbarui.'];
        } else {
            $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal memperbarui data: ' . $stmt->error];
        }
        $stmt->close();
        break;

    // ############ DELETE: Logika untuk menghapus data hewan ############
    case 'hapus':
        $hewan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($hewan_id > 0) {
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("UPDATE warga SET hewan_id = NULL WHERE hewan_id = ?");
                $stmt1->bind_param('i', $hewan_id);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $conn->prepare("DELETE FROM hewan_qurban WHERE hewan_id = ?");
                $stmt2->bind_param('i', $hewan_id);
                $stmt2->execute();
                $stmt2->close();

                $conn->commit();
                $_SESSION['pesan'] = ['jenis' => 'success', 'teks' => 'Data hewan qurban berhasil dihapus.'];
            } catch (mysqli_sql_exception $exception) {
                $conn->rollback();
                $_SESSION['pesan'] = ['jenis' => 'danger', 'teks' => 'Gagal menghapus data: ' . $exception->getMessage()];
            }
        } else {
            $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'ID hewan tidak valid.'];
        }
        break;

    default:
        $_SESSION['pesan'] = ['jenis' => 'warning', 'teks' => 'Aksi tidak valid.'];
        break;
}

// Arahkan pengguna kembali ke halaman daftar qurban
header("Location: data_qurban.php");
exit();
?>