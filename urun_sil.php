<?php
/**
 * urun_sil.php — Ürün Silme
 * Sadece giriş yapmış yönetici erişebilir; yalnızca CSRF token'lı POST kabul edilir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
adminKontrol();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Bu işlem yalnızca POST ile yapılabilir.');
}

$id = (int)($_POST['id'] ?? 0);

if (!csrfGecerli()) {
    $_SESSION['mesaj']     = 'Oturum süresi doldu. Lütfen tekrar deneyin.';
    $_SESSION['mesaj_tur'] = 'danger';
} elseif ($id > 0) {
    try {
        $pdo  = baglan();
        $stmt = $pdo->prepare('SELECT ad FROM urunler WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $urun = $stmt->fetch();

        if ($urun) {
            $del = $pdo->prepare('DELETE FROM urunler WHERE id = :id');
            $del->execute([':id' => $id]);

            $_SESSION['mesaj']     = "🗑 \"" . $urun['ad'] . "\" başarıyla silindi.";
            $_SESSION['mesaj_tur'] = 'success';
        } else {
            $_SESSION['mesaj']     = 'Silinmek istenen ürün bulunamadı.';
            $_SESSION['mesaj_tur'] = 'warning';
        }
    } catch (PDOException $e) {
        $_SESSION['mesaj']     = hataKaydet($e, 'Ürün silinemedi.');
        $_SESSION['mesaj_tur'] = 'danger';
    }
} else {
    $_SESSION['mesaj']     = 'Geçersiz ürün ID.';
    $_SESSION['mesaj_tur'] = 'danger';
}

header('Location: index.php');
exit;
