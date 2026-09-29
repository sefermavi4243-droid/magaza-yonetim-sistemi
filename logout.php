<?php
/**
 * logout.php — Oturum Kapatma
 * Session'ı tamamen temizler ve giriş sayfasına yönlendirir.
 */
require_once __DIR__ . '/functions.php';

oturumBaslat();
$_SESSION = [];
session_unset();
session_destroy();

// Oturum çerezini geçersiz kıl
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

header('Location: login.php');
exit;
