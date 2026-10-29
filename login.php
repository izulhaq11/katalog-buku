<?php
/**
 * login.php - halaman masuk admin.
 * Alur: form (POST) -> cek CSRF -> validasi kelengkapan (if-else)
 *       -> cari user (prepared statement) -> password_verify()
 *       -> simpan identitas ke $_SESSION -> redirect ke index.php
 */
require_once __DIR__ . '/config.php';

// Sudah login? Tidak perlu melihat halaman login lagi.
if (is_logged_in()) {
    redirect('index.php');
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_valid()) {
        $errors[] = t('err_csrf');
    } else {
        $username = bersihkan($_POST['username'] ?? '');
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

        if ($username === '' || $password === '') {
            // Validasi kelengkapan data
            $errors[] = t('login_required');
        } else {
            // Prepared statement: input TIDAK digabung langsung ke string SQL
            $stmt = db()->prepare(
                'SELECT id, username, password, nama_lengkap
                   FROM users
                  WHERE username = :username
                  LIMIT 1'
            );
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil
                session_regenerate_id(true);   // cegah session fixation
                $_SESSION['user_id']  = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama']     = $user['nama_lengkap'];
                redirect('index.php');
            } else {
                // Pesan sengaja dibuat umum (tidak menyebut mana yang salah)
                $errors[] = t('login_failed');
            }
        }
    }
}

$pageTitle = t('login_title');
require __DIR__ . '/header.php';
?>

<section class="card card-narrow">
    <h1><?= e(t('login_title')) ?></h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $pesan): ?>
                <div><?= e($pesan) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" autocomplete="off" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username"><?= e(t('username')) ?></label>
            <input type="text" id="username" name="username" maxlength="50"
                   value="<?= e($username) ?>" autofocus>
        </div>

        <div class="form-group">
            <label for="password"><?= e(t('password')) ?></label>
            <input type="password" id="password" name="password" maxlength="100">
        </div>

        <button type="submit" class="btn btn-primary btn-block"><?= e(t('btn_login')) ?></button>
    </form>
</section>

<?php require __DIR__ . '/footer.php'; ?>
