<?php
/**
 * edit.php - UPDATE: mengubah data buku yang sudah ada.
 * Alur: ambil ?id= (filter_var) -> SELECT data lama -> isi form
 *       -> POST: CSRF -> sanitasi+validasi -> UPDATE -> redirect
 */
require_once __DIR__ . '/config.php';
require_login();

// 1) Validasi ID dari URL: harus bilangan bulat >= 1
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    set_flash('error', 'msg_not_found');
    redirect('index.php');
}

// 2) Ambil data lama dari database
$stmt = db()->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute([':id' => $id]);
$book = $stmt->fetch();

if (!$book) {
    set_flash('error', 'msg_not_found');
    redirect('index.php');
}

$errors = [];
$data   = $book;     // nilai awal form = data yang tersimpan

// 3) Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid()) {
        $errors['csrf'] = t('err_csrf');
    } else {
        [$data, $errors] = validasi_buku($_POST);

        if (count($errors) === 0) {
            try {
                $stmt = db()->prepare(
                    'UPDATE books
                        SET judul = :judul, penulis = :penulis, penerbit = :penerbit,
                            tahun_terbit = :tahun, isbn = :isbn, kategori = :kategori,
                            harga = :harga
                      WHERE id = :id'
                );
                $stmt->execute([
                    ':judul'    => $data['judul'],
                    ':penulis'  => $data['penulis'],
                    ':penerbit' => $data['penerbit'],
                    ':tahun'    => $data['tahun_terbit'],
                    ':isbn'     => $data['isbn'],
                    ':kategori' => $data['kategori'],
                    ':harga'    => $data['harga'],
                    ':id'       => $id,
                ]);

                set_flash('success', 'msg_updated');
                redirect('index.php');

            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    $errors['isbn'] = t('err_isbn_dup');
                } else {
                    throw $ex;
                }
            }
        }
    }
}

$pageTitle  = t('edit_title');
$formAction = 'edit.php?id=' . $id;
$submitKey  = 'btn_update';
require __DIR__ . '/header.php';
?>

<section class="card">
    <h1><?= e(t('edit_title')) ?></h1>
    <?php require __DIR__ . '/_form_buku.php'; ?>
</section>

<?php require __DIR__ . '/footer.php'; ?>
