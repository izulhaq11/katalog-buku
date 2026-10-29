<?php
/**
 * hapus.php - DELETE: menghapus satu buku.
 * Hanya menerima metode POST + token CSRF, sehingga data tidak bisa
 * terhapus hanya karena seseorang membuka/mengklik sebuah link (GET).
 */
require_once __DIR__ . '/config.php';
require_login();

// Akses langsung lewat URL (GET) tidak melakukan apa-apa
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!csrf_valid()) {
    set_flash('error', 'err_csrf');
    redirect('index.php');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    set_flash('error', 'msg_not_found');
    redirect('index.php');
}

$stmt = db()->prepare('DELETE FROM books WHERE id = :id');
$stmt->execute([':id' => $id]);

// rowCount() = jumlah baris yang benar-benar terhapus
set_flash($stmt->rowCount() > 0 ? 'success' : 'error',
          $stmt->rowCount() > 0 ? 'msg_deleted' : 'msg_not_found');
redirect('index.php');
