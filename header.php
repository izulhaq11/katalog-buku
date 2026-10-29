<?php
/**
 * header.php - bagian atas layout yang dipakai semua halaman.
 * Variabel yang harus diisi halaman pemanggil sebelum include:
 *   $pageTitle (string) -> judul halaman yang SUDAH diterjemahkan
 */
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? '') ?> | <?= e(t('app_name')) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="topbar">
    <div class="container bar">
        <a class="brand" href="index.php"><?= e(t('app_name')) ?></a>

        <nav class="nav">
            <!-- Tombol ganti bahasa: statusnya disimpan di $_SESSION['lang'] -->
            <span class="lang">
                <?= e(t('lang_label')) ?>:
                <a href="<?= e(lang_url('id')) ?>" class="<?= current_lang() === 'id' ? 'active' : '' ?>">ID</a>
                |
                <a href="<?= e(lang_url('en')) ?>" class="<?= current_lang() === 'en' ? 'active' : '' ?>">EN</a>
            </span>

            <?php if (is_logged_in()): ?>
                <span class="user"><?= e(t('hello', ['name' => $_SESSION['nama'] ?? ''])) ?></span>
                <a class="btn btn-small btn-outline" href="logout.php"><?= e(t('btn_logout')) ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>">
        <?= e(t($flash['key'])) ?>
    </div>
<?php endif; ?>
