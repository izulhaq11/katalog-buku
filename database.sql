-- =====================================================================
-- database.sql  |  Katalog Buku (Tugas SIM Data Sederhana - UNIKOM)
-- Cara pakai: import lewat phpMyAdmin (tab "Import") atau jalankan:
--   mysql -u root -p < database.sql
-- PERHATIAN: skrip ini menghapus tabel lama (DROP TABLE) agar bisa
-- di-import ulang dengan bersih.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS katalog_buku
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE katalog_buku;

DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------
-- Tabel users : menyimpan akun admin (password disimpan dalam bentuk HASH)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username     VARCHAR(50)   NOT NULL,
    password     VARCHAR(255)  NOT NULL,
    nama_lengkap VARCHAR(100)  NOT NULL,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tabel books : data katalog buku
-- kategori diisi KODE (fiksi, teknologi, pendidikan, sains, bisnis,
-- lainnya) supaya tampilannya bisa diterjemahkan ke ID / EN.
-- ---------------------------------------------------------------------
CREATE TABLE books (
    id           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    judul        VARCHAR(200)      NOT NULL,
    penulis      VARCHAR(100)      NOT NULL,
    penerbit     VARCHAR(100)      NOT NULL,
    tahun_terbit SMALLINT UNSIGNED NOT NULL,
    isbn         VARCHAR(20)       NOT NULL,
    kategori     VARCHAR(30)       NOT NULL,
    harga        INT UNSIGNED      NOT NULL DEFAULT 0,
    created_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_books_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Data dummy
-- Akun default  -> username: admin | password: admin123
-- (kolom password berisi hasil password_hash() bcrypt, BUKAN teks asli)
-- Nomor ISBN di bawah adalah data fiktif untuk keperluan tugas.
-- ---------------------------------------------------------------------
INSERT INTO users (username, password, nama_lengkap) VALUES
('admin', '$2y$10$38V/4J9gUQWeKQtzTiZCqOK80hfUFTMDcI/jg7eLoWMYGWrBwNpzW', 'Administrator');

INSERT INTO books (judul, penulis, penerbit, tahun_terbit, isbn, kategori, harga) VALUES
('Laskar Pelangi',                 'Andrea Hirata',        'Bentang Pustaka',   2005, '978-602-0000-01-1', 'fiksi',      95000),
('Bumi Manusia',                   'Pramoedya Ananta Toer','Lentera Dipantara', 2011, '978-602-0000-02-8', 'fiksi',     120000),
('Clean Code',                     'Robert C. Martin',     'Prentice Hall',     2008, '978-602-0000-03-5', 'teknologi', 450000),
('Pemrograman Web dengan PHP',     'Budi Santoso',         'Informatika',       2021, '978-602-0000-04-2', 'teknologi', 135000),
('Atomic Habits',                  'James Clear',          'Gramedia',          2019, '978-602-0000-05-9', 'bisnis',     108000),
('Dasar-Dasar Basis Data',         'Siti Rahmawati',       'Andi Offset',       2020, '978-602-0000-06-6', 'pendidikan', 98000);
