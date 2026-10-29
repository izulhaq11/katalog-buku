<?php
/**
 * functions.php
 * ---------------------------------------------------------------
 * Kumpulan fungsi bantu. Dipanggil otomatis oleh config.php.
 * Kelompok fungsi:
 *   A. Bahasa            : t(), current_lang(), lang_url()
 *   B. Sanitasi & output : e(), bersihkan()
 *   C. Keamanan formulir : csrf_token(), csrf_field(), csrf_valid()
 *   D. Autentikasi       : is_logged_in(), require_login(), redirect()
 *   E. Flash message     : set_flash(), pull_flash()
 *   F. Validasi buku     : daftar_kategori(), validasi_buku(), format_harga()
 */

// =================================================================
// A. BAHASA
// =================================================================

/** Kode bahasa yang sedang aktif ('id' atau 'en'). */
function current_lang(): string
{
    return $_SESSION['lang'] ?? LANG_DEFAULT;
}

/**
 * Menerjemahkan teks berdasarkan bahasa di session.
 * Contoh: t('hello', ['name' => 'Budi'])  ->  "Halo, Budi"
 */
function t(string $key, array $replace = []): string
{
    global $translations;

    $text = $translations[current_lang()][$key]
         ?? $translations[LANG_DEFAULT][$key]
         ?? $key;                              // jika kunci tidak ada, tampilkan kuncinya

    foreach ($replace as $name => $value) {
        $text = str_replace(':' . $name, (string) $value, $text);
    }
    return $text;
}

/**
 * Membuat URL untuk berpindah bahasa di halaman yang sedang dibuka,
 * dengan tetap mempertahankan parameter lain (mis. ?id=3).
 */
function lang_url(string $lang): string
{
    $query         = $_GET;
    $query['lang'] = $lang;
    return basename($_SERVER['SCRIPT_NAME']) . '?' . http_build_query($query);
}

// =================================================================
// B. SANITASI & OUTPUT
// =================================================================

/**
 * ESCAPE OUTPUT (anti-XSS). Wajib dipakai setiap kali menampilkan
 * data pengguna / data dari database ke halaman HTML.
 * ENT_QUOTES -> tanda petik ' dan " ikut diubah menjadi entitas HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * SANITASI INPUT: membuang spasi di awal/akhir dan karakter kontrol
 * yang tidak terlihat. Selalu mengembalikan string (aman bila input
 * berupa array / null hasil manipulasi form).
 */
function bersihkan(mixed $value): string
{
    if (!is_string($value)) {
        return '';
    }
    $value = trim($value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
    return $value;
}

/** Jumlah karakter (bukan byte) pada teks UTF-8; tidak wajib ekstensi mbstring. */
function panjang_teks(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : (int) preg_match_all('/./us', $value);
}

// =================================================================
// C. KEAMANAN FORMULIR (CSRF)
// =================================================================

/** Token acak per-session untuk memastikan POST berasal dari form kita. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Input hidden yang diletakkan di dalam setiap <form method="post">. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Memeriksa token yang dikirim lewat POST. */
function csrf_valid(): bool
{
    $sesi  = $_SESSION['csrf_token'] ?? '';
    $kirim = $_POST['csrf_token'] ?? '';
    return is_string($kirim) && $sesi !== '' && hash_equals($sesi, $kirim);
}

// =================================================================
// D. AUTENTIKASI
// =================================================================

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** Proteksi halaman: pengunjung yang belum login dikirim ke login.php. */
function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'msg_need_login');
        redirect('login.php');
    }
}

// =================================================================
// E. FLASH MESSAGE (pesan sekali tampil, disimpan di session)
// =================================================================

/** Yang disimpan adalah KUNCI kamus, bukan teksnya -> ikut berganti bahasa. */
function set_flash(string $type, string $messageKey): void
{
    $_SESSION['flash'] = ['type' => $type, 'key' => $messageKey];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// =================================================================
// F. VALIDASI DATA BUKU
// =================================================================

/** Daftar kode kategori yang diizinkan (whitelist). */
function daftar_kategori(): array
{
    return ['fiksi', 'teknologi', 'pendidikan', 'sains', 'bisnis', 'lainnya'];
}

const HARGA_MAKS = 99999999;

/** Format harga sesuai bahasa: id -> Rp 95.000 | en -> Rp 95,000 */
function format_harga(mixed $harga): string
{
    $desimal  = current_lang() === 'id' ? ',' : '.';
    $ribuan   = current_lang() === 'id' ? '.' : ',';
    return 'Rp ' . number_format((float) $harga, 0, $desimal, $ribuan);
}

/**
 * Memvalidasi & menyanitasi input form buku (dipakai tambah.php & edit.php).
 *
 * @return array [$data, $errors]
 *   $data   = nilai yang sudah dibersihkan (untuk diisi kembali ke form)
 *   $errors = daftar pesan kesalahan per kolom (kosong bila semua valid)
 */
function validasi_buku(array $post): array
{
    // 1) SANITASI: bersihkan semua input mentah
    $data = [
        'judul'        => bersihkan($post['judul']        ?? ''),
        'penulis'      => bersihkan($post['penulis']      ?? ''),
        'penerbit'     => bersihkan($post['penerbit']     ?? ''),
        'tahun_terbit' => bersihkan($post['tahun_terbit'] ?? ''),
        'isbn'         => bersihkan($post['isbn']         ?? ''),
        'kategori'     => bersihkan($post['kategori']     ?? ''),
        'harga'        => bersihkan($post['harga']        ?? ''),
    ];
    $errors = [];

    // 2) VALIDASI KELENGKAPAN: semua kolom wajib diisi
    $wajib = [
        'judul'        => 'col_title',
        'penulis'      => 'col_author',
        'penerbit'     => 'col_publisher',
        'tahun_terbit' => 'col_year',
        'isbn'         => 'col_isbn',
        'kategori'     => 'col_category',
        'harga'        => 'col_price',
    ];
    foreach ($wajib as $kolom => $labelKey) {
        if ($data[$kolom] === '') {
            $errors[$kolom] = t('err_required', ['field' => t($labelKey)]);
        }
    }

    // 3) VALIDASI PANJANG TEKS
    $maks = ['judul' => 200, 'penulis' => 100, 'penerbit' => 100];
    $label = ['judul' => 'col_title', 'penulis' => 'col_author', 'penerbit' => 'col_publisher'];
    foreach ($maks as $kolom => $batas) {
        if (!isset($errors[$kolom]) && panjang_teks($data[$kolom]) > $batas) {
            $errors[$kolom] = t('err_max_len', ['field' => t($label[$kolom]), 'max' => $batas]);
        }
    }

    // 4) VALIDASI TAHUN: filter_var + rentang
    if (!isset($errors['tahun_terbit'])) {
        $min   = 1900;
        $max   = (int) date('Y') + 1;
        $tahun = filter_var($data['tahun_terbit'], FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($tahun === false) {
            $errors['tahun_terbit'] = t('err_year', ['min' => $min, 'max' => $max]);
        } else {
            $data['tahun_terbit'] = $tahun;
        }
    }

    // 5) VALIDASI ISBN: hanya angka & tanda hubung, total 10 atau 13 digit
    if (!isset($errors['isbn'])) {
        $jumlahDigit = strlen(preg_replace('/\D/', '', $data['isbn']));
        $polaValid   = preg_match('/^[0-9][0-9\-]*$/', $data['isbn']) === 1;
        if (!$polaValid || !in_array($jumlahDigit, [10, 13], true)) {
            $errors['isbn'] = t('err_isbn');
        }
    }

    // 6) VALIDASI KATEGORI: harus salah satu dari whitelist
    if (!isset($errors['kategori']) && !in_array($data['kategori'], daftar_kategori(), true)) {
        $errors['kategori'] = t('err_category');
    }

    // 7) VALIDASI HARGA: bilangan bulat 0 .. HARGA_MAKS
    if (!isset($errors['harga'])) {
        $harga = filter_var($data['harga'], FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 0, 'max_range' => HARGA_MAKS]]);
        if ($harga === false) {
            $errors['harga'] = t('err_price', ['max' => number_format(HARGA_MAKS, 0, ',', '.')]);
        } else {
            $data['harga'] = $harga;
        }
    }

    return [$data, $errors];
}
