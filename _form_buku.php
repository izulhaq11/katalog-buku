<?php
/**
 * _form_buku.php - potongan form yang dipakai bersama oleh tambah.php dan edit.php.
 * Variabel yang harus tersedia dari halaman pemanggil:
 *   $data       array  nilai kolom (untuk mengisi kembali form)
 *   $errors     array  pesan kesalahan per kolom
 *   $formAction string URL tujuan form
 *   $submitKey  string kunci kamus untuk teks tombol submit
 */
?>
<?php if ($errors): ?>
    <div class="alert alert-error">
        <strong><?= e(t('err_title')) ?></strong>
        <ul>
            <?php foreach ($errors as $pesan): ?>
                <li><?= e($pesan) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-group <?= isset($errors['judul']) ? 'has-error' : '' ?>">
        <label for="judul"><?= e(t('col_title')) ?></label>
        <input type="text" id="judul" name="judul" maxlength="200"
               value="<?= e($data['judul']) ?>">
    </div>

    <div class="form-row">
        <div class="form-group <?= isset($errors['penulis']) ? 'has-error' : '' ?>">
            <label for="penulis"><?= e(t('col_author')) ?></label>
            <input type="text" id="penulis" name="penulis" maxlength="100"
                   value="<?= e($data['penulis']) ?>">
        </div>

        <div class="form-group <?= isset($errors['penerbit']) ? 'has-error' : '' ?>">
            <label for="penerbit"><?= e(t('col_publisher')) ?></label>
            <input type="text" id="penerbit" name="penerbit" maxlength="100"
                   value="<?= e($data['penerbit']) ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group <?= isset($errors['tahun_terbit']) ? 'has-error' : '' ?>">
            <label for="tahun_terbit"><?= e(t('col_year')) ?></label>
            <input type="text" id="tahun_terbit" name="tahun_terbit" maxlength="4"
                   inputmode="numeric" value="<?= e($data['tahun_terbit']) ?>">
            <small><?= e(t('hint_year')) ?></small>
        </div>

        <div class="form-group <?= isset($errors['isbn']) ? 'has-error' : '' ?>">
            <label for="isbn"><?= e(t('col_isbn')) ?></label>
            <input type="text" id="isbn" name="isbn" maxlength="20"
                   value="<?= e($data['isbn']) ?>">
            <small><?= e(t('hint_isbn')) ?></small>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group <?= isset($errors['kategori']) ? 'has-error' : '' ?>">
            <label for="kategori"><?= e(t('col_category')) ?></label>
            <select id="kategori" name="kategori">
                <option value=""><?= e(t('choose_category')) ?></option>
                <?php foreach (daftar_kategori() as $kode): ?>
                    <option value="<?= e($kode) ?>" <?= $data['kategori'] === $kode ? 'selected' : '' ?>>
                        <?= e(t('cat_' . $kode)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group <?= isset($errors['harga']) ? 'has-error' : '' ?>">
            <label for="harga"><?= e(t('col_price')) ?> (Rp)</label>
            <input type="text" id="harga" name="harga" maxlength="8"
                   inputmode="numeric" value="<?= e($data['harga']) ?>">
            <small><?= e(t('hint_price')) ?></small>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary"><?= e(t($submitKey)) ?></button>
        <a class="btn btn-outline" href="index.php"><?= e(t('btn_cancel')) ?></a>
    </div>
</form>
