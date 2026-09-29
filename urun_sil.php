<?php
/**
 * SORU 6 urun_sil.php — Ürün Silme
 * Sadece giriş yapmış yönetici erişebilir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
adminKontrol();

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo  = baglan();
        // Önce ürün adını al 
        $stmt = $pdo->prepare('SELECT ad FROM urunler WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $urun = $stmt->fetch();

        if ($urun) {
            // PDO Prepared Statement — SQL Injection önleme (Soru 7)
            $del = $pdo->prepare('DELETE FROM urunler WHERE id = :id');
            $del->execute([':id' => $id]);

            $_SESSION['mesaj']     = "🗑 \"" . $urun['ad'] . "\" başarıyla silindi.";
            $_SESSION['mesaj_tur'] = 'success';
        } else {
            $_SESSION['mesaj']     = 'Silinmek istenen ürün bulunamadı.';
            $_SESSION['mesaj_tur'] = 'warning';
        }
    } catch (PDOException $e) {
        $_SESSION['mesaj']     = 'Silme hatası: ' . $e->getMessage();
        $_SESSION['mesaj_tur'] = 'danger';
    }
} else {
    $_SESSION['mesaj']     = 'Geçersiz ürün ID.';
    $_SESSION['mesaj_tur'] = 'danger';
}

header('Location: index.php');
exit;
