<?php
/**
 * logout.php - mengakhiri sesi login.
 * Yang dihapus hanya data login & token CSRF; pilihan bahasa
 * ($_SESSION['lang']) sengaja dipertahankan agar tampilan tidak
 * tiba-tiba kembali ke bahasa default.
 */
require_once __DIR__ . '/config.php';

unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['nama'], $_SESSION['csrf_token']);
session_regenerate_id(true);   // ID session baru, ID lama tidak berlaku lagi

set_flash('success', 'msg_logged_out');
redirect('login.php');
