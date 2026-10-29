<?php
/**
 * config.php
 * ---------------------------------------------------------------
 * File konfigurasi utama. Di-include (require_once) oleh SEMUA halaman.
 * Isi:
 *   1. Konfigurasi & koneksi database (PDO)
 *   2. Pengaturan session
 *   3. Pengaturan bahasa (Indonesia / Inggris) + kamus terjemahan
 *   4. Pemanggilan functions.php (fungsi-fungsi bantu)
 *
 * Kebutuhan: PHP 8.0 atau lebih baru, ekstensi PDO MySQL (bawaan XAMPP).
 */

// ---------------------------------------------------------------
// 1. KONFIGURASI DATABASE  (sesuaikan dengan komputer Anda)
// ---------------------------------------------------------------
const DB_HOST    = 'localhost';
const DB_NAME    = 'katalog_buku';
const DB_USER    = 'root';   // default XAMPP
const DB_PASS    = '';       // default XAMPP: kosong
const DB_CHARSET = 'utf8mb4';

/**
 * Mengembalikan objek koneksi PDO (dibuat satu kali saja).
 * Prepared statement dipakai di seluruh halaman -> mencegah SQL Injection.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // error -> exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statement asli
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

// Pesan error umum agar detail teknis (password DB, dll.) tidak bocor ke pengguna.
set_exception_handler(function (Throwable $e): void {
    error_log('[Katalog Buku] ' . $e->getMessage());
    http_response_code(500);
    echo 'Terjadi kesalahan pada server / A server error occurred. '
       . 'Pastikan MySQL aktif dan database.sql sudah di-import.';
});

// ---------------------------------------------------------------
// 2. SESSION
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,         // berakhir saat browser ditutup
        'path'     => '/',
        'httponly' => true,      // cookie tidak bisa dibaca JavaScript
        'samesite' => 'Lax',     // mengurangi risiko CSRF
    ]);
    session_start();
}

// ---------------------------------------------------------------
// 3. BAHASA (DUAL LANGUAGE) - status disimpan di $_SESSION['lang']
// ---------------------------------------------------------------
const LANG_DEFAULT   = 'id';
const LANG_AVAILABLE = ['id', 'en'];

// Jika ada ?lang=en / ?lang=id -> simpan pilihan ke session (whitelist!)
if (isset($_GET['lang']) && is_string($_GET['lang'])
    && in_array($_GET['lang'], LANG_AVAILABLE, true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
// Kunjungan pertama: pakai bahasa default
if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], LANG_AVAILABLE, true)) {
    $_SESSION['lang'] = LANG_DEFAULT;
}

/**
 * Kamus multi-bahasa. Struktur: $translations[kode_bahasa][kunci] = teks.
 * Teks yang memuat ":nama" akan diganti lewat parameter fungsi t().
 */
$translations = [

    // ======================= BAHASA INDONESIA =======================
    'id' => [
        'app_name'        => 'Katalog Buku',
        'lang_label'      => 'Bahasa',

        // Login / logout
        'login_title'     => 'Masuk Admin',
        'username'        => 'Nama Pengguna',
        'password'        => 'Kata Sandi',
        'btn_login'       => 'Masuk',
        'btn_logout'      => 'Keluar',
        'login_required'  => 'Nama pengguna dan kata sandi wajib diisi.',
        'login_failed'    => 'Nama pengguna atau kata sandi salah.',
        'msg_logged_out'  => 'Anda berhasil keluar.',
        'msg_need_login'  => 'Silakan masuk terlebih dahulu.',

        // Halaman daftar
        'list_title'      => 'Daftar Buku',
        'total_books'     => 'Total: :count buku',
        'empty_data'      => 'Belum ada data buku.',
        'add_book'        => '+ Tambah Buku',
        'add_title'       => 'Tambah Buku Baru',
        'edit_title'      => 'Ubah Data Buku',

        // Kolom & label form
        'col_no'          => 'No',
        'col_title'       => 'Judul',
        'col_author'      => 'Penulis',
        'col_publisher'   => 'Penerbit',
        'col_year'        => 'Tahun Terbit',
        'col_isbn'        => 'ISBN',
        'col_category'    => 'Kategori',
        'col_price'       => 'Harga',
        'col_action'      => 'Aksi',
        'choose_category' => '-- Pilih kategori --',
        'hint_year'       => 'Contoh: 2024',
        'hint_isbn'       => '10 atau 13 digit, boleh memakai tanda hubung',
        'hint_price'      => 'Angka bulat dalam Rupiah, tanpa titik',

        // Tombol
        'btn_save'        => 'Simpan',
        'btn_update'      => 'Perbarui',
        'btn_cancel'      => 'Batal',
        'btn_edit'        => 'Ubah',
        'btn_delete'      => 'Hapus',
        'confirm_delete'  => 'Yakin ingin menghapus buku ini?',

        // Kategori
        'cat_fiksi'       => 'Fiksi',
        'cat_teknologi'   => 'Teknologi',
        'cat_pendidikan'  => 'Pendidikan',
        'cat_sains'       => 'Sains',
        'cat_bisnis'      => 'Bisnis',
        'cat_lainnya'     => 'Lainnya',

        // Pesan sukses / gagal
        'msg_added'       => 'Data buku berhasil ditambahkan.',
        'msg_updated'     => 'Data buku berhasil diperbarui.',
        'msg_deleted'     => 'Data buku berhasil dihapus.',
        'msg_not_found'   => 'Data buku tidak ditemukan.',

        // Validasi
        'err_title'       => 'Periksa kembali isian Anda:',
        'err_required'    => ':field wajib diisi.',
        'err_max_len'     => ':field maksimal :max karakter.',
        'err_year'        => 'Tahun terbit harus berupa angka antara :min dan :max.',
        'err_isbn'        => 'ISBN tidak valid (harus 10 atau 13 digit).',
        'err_isbn_dup'    => 'ISBN sudah terdaftar pada buku lain.',
        'err_category'    => 'Kategori yang dipilih tidak valid.',
        'err_price'       => 'Harga harus berupa angka bulat (0 - :max).',
        'err_csrf'        => 'Sesi formulir tidak valid atau kedaluwarsa. Silakan coba lagi.',
    ],

    // ========================= ENGLISH ==============================
    'en' => [
        'app_name'        => 'Book Catalog',
        'lang_label'      => 'Language',

        // Login / logout
        'login_title'     => 'Admin Login',
        'username'        => 'Username',
        'password'        => 'Password',
        'btn_login'       => 'Log In',
        'btn_logout'      => 'Log Out',
        'login_required'  => 'Username and password are required.',
        'login_failed'    => 'Incorrect username or password.',
        'msg_logged_out'  => 'You have been logged out.',
        'msg_need_login'  => 'Please log in first.',

        // List page
        'list_title'      => 'Book List',
        'total_books'     => 'Total: :count book(s)',
        'empty_data'      => 'There are no books yet.',
        'add_book'        => '+ Add Book',
        'add_title'       => 'Add New Book',
        'edit_title'      => 'Edit Book',

        // Columns & form labels
        'col_no'          => 'No',
        'col_title'       => 'Title',
        'col_author'      => 'Author',
        'col_publisher'   => 'Publisher',
        'col_year'        => 'Publication Year',
        'col_isbn'        => 'ISBN',
        'col_category'    => 'Category',
        'col_price'       => 'Price',
        'col_action'      => 'Actions',
        'choose_category' => '-- Select a category --',
        'hint_year'       => 'Example: 2024',
        'hint_isbn'       => '10 or 13 digits, hyphens are allowed',
        'hint_price'      => 'Whole number in Rupiah, no separators',

        // Buttons
        'btn_save'        => 'Save',
        'btn_update'      => 'Update',
        'btn_cancel'      => 'Cancel',
        'btn_edit'        => 'Edit',
        'btn_delete'      => 'Delete',
        'confirm_delete'  => 'Are you sure you want to delete this book?',

        // Categories
        'cat_fiksi'       => 'Fiction',
        'cat_teknologi'   => 'Technology',
        'cat_pendidikan'  => 'Education',
        'cat_sains'       => 'Science',
        'cat_bisnis'      => 'Business',
        'cat_lainnya'     => 'Others',

        // Success / failure messages
        'msg_added'       => 'The book was added successfully.',
        'msg_updated'     => 'The book was updated successfully.',
        'msg_deleted'     => 'The book was deleted successfully.',
        'msg_not_found'   => 'Book not found.',

        // Validation
        'err_title'       => 'Please check your input:',
        'err_required'    => ':field is required.',
        'err_max_len'     => ':field must be at most :max characters.',
        'err_year'        => 'Publication year must be a number between :min and :max.',
        'err_isbn'        => 'Invalid ISBN (must be 10 or 13 digits).',
        'err_isbn_dup'    => 'This ISBN is already registered to another book.',
        'err_category'    => 'The selected category is not valid.',
        'err_price'       => 'Price must be a whole number (0 - :max).',
        'err_csrf'        => 'The form session is invalid or has expired. Please try again.',
    ],
];

// ---------------------------------------------------------------
// 4. FUNGSI BANTU
// ---------------------------------------------------------------
require_once __DIR__ . '/functions.php';
