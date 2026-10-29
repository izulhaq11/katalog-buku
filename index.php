<?php
/**
 * index.php - READ: menampilkan seluruh buku dalam tabel HTML.
 * Halaman ini diproteksi: harus login.
 */
require_once __DIR__ . '/config.php';
require_login();

$stmt  = db()->query('SELECT id, judul, penulis, penerbit, tahun_terbit, isbn, kategori, harga
                        FROM books
                       ORDER BY id DESC');
$books = $stmt->fetchAll();

$pageTitle = t('list_title');
require __DIR__ . '/header.php';
?>

<section class="card">
    <div class="card-head">
        <div>
            <h1><?= e(t('list_title')) ?></h1>
            <p class="muted"><?= e(t('total_books', ['count' => count($books)])) ?></p>
        </div>
        <a class="btn btn-primary" href="tambah.php"><?= e(t('add_book')) ?></a>
    </div>

    <?php if (count($books) === 0): ?>
        <p class="empty"><?= e(t('empty_data')) ?></p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th><?= e(t('col_no')) ?></th>
                        <th><?= e(t('col_title')) ?></th>
                        <th><?= e(t('col_author')) ?></th>
                        <th><?= e(t('col_publisher')) ?></th>
                        <th><?= e(t('col_year')) ?></th>
                        <th><?= e(t('col_isbn')) ?></th>
                        <th><?= e(t('col_category')) ?></th>
                        <th class="num"><?= e(t('col_price')) ?></th>
                        <th><?= e(t('col_action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($books as $i => $book): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= e($book['judul']) ?></td>
                        <td><?= e($book['penulis']) ?></td>
                        <td><?= e($book['penerbit']) ?></td>
                        <td><?= e($book['tahun_terbit']) ?></td>
                        <td><?= e($book['isbn']) ?></td>
                        <td><?= e(t('cat_' . $book['kategori'])) ?></td>
                        <td class="num"><?= e(format_harga($book['harga'])) ?></td>
                        <td class="actions">
                            <a class="btn btn-small btn-outline"
                               href="edit.php?id=<?= (int) $book['id'] ?>"><?= e(t('btn_edit')) ?></a>

                            <!-- Hapus memakai POST + token CSRF (bukan link GET) -->
                            <form method="post" action="hapus.php" class="inline"
                                  data-confirm="<?= e(t('confirm_delete')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                <button type="submit" class="btn btn-small btn-danger"><?= e(t('btn_delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

