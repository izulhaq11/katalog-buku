# Katalog Buku (PHP Native + MySQL/MariaDB)

Tugas **Sistem Informasi Manajemen Data Sederhana** - Teknik Informatika, UNIKOM.
Aplikasi CRUD katalog buku dengan login admin (`$_SESSION`) dan dua bahasa (Indonesia/Inggris).

## Fitur
- Login/logout admin (password di-hash `password_hash()` / `password_verify()`)
- Seluruh halaman CRUD diproteksi session
- CRUD buku: tabel (Read), tambah (Create), ubah (Update), hapus (Delete)
- Validasi form (POST, `if-else`), `filter_var()`, whitelist kategori
- Keamanan: prepared statement (PDO), `htmlspecialchars()` pada semua output, token CSRF
- Dual bahasa ID/EN, pilihan disimpan di `$_SESSION['lang']`

## Kebutuhan
PHP 8.0+ dan MySQL/MariaDB (cukup install XAMPP).

## Cara Menjalankan (XAMPP)
1. Salin folder proyek ini ke `C:\xampp\htdocs\katalog-buku`.
2. Jalankan **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` > tab **Import** > pilih `database.sql` > **Go**.
4. Jika user/password MySQL Anda bukan `root` / kosong, ubah di `config.php`.
5. Buka `http://localhost/katalog-buku/login.php`.

**Akun default:** username `admin` | password `admin123`

## Struktur Berkas
| Berkas | Fungsi |
|---|---|
| `database.sql` | Pembuatan database, tabel `users` & `books`, data dummy |
| `config.php` | Koneksi database, session, kamus bahasa ID/EN |
| `functions.php` | Fungsi bantu: `e()`, `t()`, CSRF, validasi buku, dll. |
| `login.php` / `logout.php` | Masuk dan keluar |
| `index.php` | Read - tabel daftar buku |
| `tambah.php` | Create |
| `edit.php` | Update |
| `hapus.php` | Delete (hanya POST + CSRF) |
| `header.php` / `footer.php` / `_form_buku.php` / `style.css` | Tampilan |

## Catatan
Ganti password admin untuk penggunaan di luar tugas. Hash baru dapat dibuat dengan
`php -r "echo password_hash('PasswordBaru', PASSWORD_DEFAULT);"` lalu simpan ke tabel `users`.
