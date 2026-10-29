<?php
/**
 * tambah.php - CREATE: menambah data buku baru.
 * Alur: tampil form (GET) -> kirim (POST) -> CSRF -> sanitasi+validasi
 *       -> if tidak ada error: INSERT (prepared statement) -> redirect
 *       -> else: tampilkan form lagi lengkap dengan pesan kesalahan
 */
require_once __DIR__ . '/config.php';
require_login();

$errors = [];
$data   = [
    'judul' => '', 'penulis' => '', 'penerbit' => '',
    'tahun_terbit' => '', 'isbn' => '', 'kategori' => '', 'harga' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid()) {
        $errors['csrf'] = t('err_csrf');
    } else {
        [$data, $errors] = validasi_buku($_POST);

        if (count($errors) === 0) {          // semua data valid -> simpan
            try {
                $stmt = db()->prepare(
                    'INSERT INTO books (judul, penulis, penerbit, tahun_terbit, isbn, kategori, harga)
                     VALUES (:judul, :penulis, :penerbit, :tahun, :isbn, :kategori, :harga)'
                );
                $stmt->execute([
                    ':judul'    => $data['judul'],
                    ':penulis'  => $data['penulis'],
                    ':penerbit' => $data['penerbit'],
                    ':tahun'    => $data['tahun_terbit'],
                    ':isbn'     => $data['isbn'],
                    ':kategori' => $data['kategori'],
                    ':harga'    => $data['harga'],
                ]);

                set_flash('success', 'msg_added');
                redirect('index.php');       // pola Post-Redirect-Get (cegah submit ganda)

            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {       // pelanggaran UNIQUE (ISBN kembar)
                    $errors['isbn'] = t('err_isbn_dup');
                } else {
                    throw $ex;
                }
            }
        }
    }
}

$pageTitle  = t('add_title');
$formAction = 'tambah.php';
$submitKey  = 'btn_save';
require __DIR__ . '/header.php';
?>

<section class="card">
    <h1><?= e(t('add_title')) ?></h1>
    <?php require __DIR__ . '/_form_buku.php'; ?>
</section>

<?php require __DIR__ . '/footer.php'; ?>
