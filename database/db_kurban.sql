CREATE DATABASE IF NOT EXISTS db_kurban
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_kurban;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pembagian_daging;
DROP TABLE IF EXISTS keuangan;
DROP TABLE IF EXISTS warga;
DROP TABLE IF EXISTS hewan_qurban;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nik VARCHAR(32) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'panitia', 'berqurban', 'warga') NOT NULL DEFAULT 'warga',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hewan_qurban (
  hewan_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  jenis ENUM('sapi', 'kambing') NOT NULL,
  jumlah INT UNSIGNED NOT NULL DEFAULT 1,
  harga_per_ekor INT UNSIGNED NOT NULL DEFAULT 0,
  biaya_admin INT UNSIGNED NOT NULL DEFAULT 0,
  total_harga INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE warga (
  warga_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL UNIQUE,
  alamat VARCHAR(255) NOT NULL,
  is_panitia TINYINT(1) NOT NULL DEFAULT 0,
  is_berqurban TINYINT(1) NOT NULL DEFAULT 0,
  hewan_id INT UNSIGNED NULL,
  jumlah_iuran INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_warga_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_warga_hewan
    FOREIGN KEY (hewan_id) REFERENCES hewan_qurban(hewan_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE keuangan (
  transaksi_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE NOT NULL,
  jenis ENUM('masuk', 'keluar') NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  keterangan VARCHAR(255) NOT NULL,
  warga_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_keuangan_warga
    FOREIGN KEY (warga_id) REFERENCES warga(warga_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pembagian_daging (
  distribusi_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  warga_id INT UNSIGNED NOT NULL,
  berat_kg DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  qr_code_path VARCHAR(255) NOT NULL,
  qr_code_token VARCHAR(100) NOT NULL UNIQUE,
  status_pengambilan ENUM('belum_diambil', 'sudah_diambil') NOT NULL DEFAULT 'belum_diambil',
  waktu_pengambilan TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pembagian_warga
    FOREIGN KEY (warga_id) REFERENCES warga(warga_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (user_id, nama, nik, password, role)
VALUES
  (1, 'Administrator', 'admin', '$2y$10$8D0Haq1LKD9b2YIeJs2KluPRjlIWQTzUbbGRxZmxX.Fv3YTPeHqo2', 'admin');

INSERT INTO warga (warga_id, user_id, alamat, is_panitia, is_berqurban)
VALUES
  (1, 1, 'Admin', 1, 0);
