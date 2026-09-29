<?php
/**
 * kurulum_admin.php — Yönetici Hesabı Kurulumu
 *
 * Yalnızca sistemde hiç yönetici yokken çalışır. İlk hesap oluşturulduktan
 * sonra sayfa kilitlenir; böylece başkası yeni yönetici ekleyemez veya
 * mevcut şifreyi değiştiremez.
 *
 * Şifre password_hash(PASSWORD_BCRYPT) ile hashlenir, girişte password_verify() kullanılır.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

oturumBaslat();

$mesaj   = '';
$hata    = '';
$kilitli = false;

try {
    $pdo = baglan();

    // Yoksa yönetici tablosunu oluştur
    $pdo->exec("CREATE TABLE IF NOT EXISTS `yonetici` (
        id            INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50)  NOT NULL UNIQUE,
        sifre_hash    VARCHAR(255) NOT NULL,
        olusturma     DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $kilitli = (int)$pdo->query('SELECT COUNT(*) FROM yonetici')->fetchColumn() > 0;
} catch (PDOException $e) {
    $hata = hataKaydet($e, 'Veritabanına bağlanılamadı.');
}

if ($kilitli) {
    http_response_code(403);
}

if (!$hata && !$kilitli && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici    = trim($_POST['kullanici']    ?? '');
    $sifre        = $_POST['sifre']        ?? '';
    $sifre_tekrar = $_POST['sifre_tekrar'] ?? '';

    if (!csrfGecerli()) {
        $hata = 'Oturum süresi doldu. Lütfen tekrar deneyin.';
    } elseif ($kullanici === '' || $sifre === '') {
        $hata = 'Tüm alanları doldurun.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $kullanici)) {
        $hata = 'Kullanıcı adı 3-50 karakter olmalı (harf, rakam, _ . -).';
    } elseif (strlen($sifre) < 8) {
        $hata = 'Şifre en az 8 karakter olmalıdır.';
    } elseif ($sifre !== $sifre_tekrar) {
        $hata = 'Şifreler eşleşmiyor.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO yonetici (kullanici_adi, sifre_hash) VALUES (:k, :h)');
            $stmt->execute([':k' => $kullanici, ':h' => password_hash($sifre, PASSWORD_BCRYPT)]);

            $mesaj = 'Yönetici hesabı oluşturuldu: <strong>' . e($kullanici) . '</strong><br>'
                   . 'Bu sayfa artık kilitlidir.';
        } catch (PDOException $e) {
            $hata = hataKaydet($e, 'Hesap oluşturulamadı.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Yönetici Kurulumu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; background:#1C1A17; display:flex; justify-content:center; align-items:center; min-height:100vh; margin:0; }
  .kutu { background:#FDFAF5; border-radius:8px; padding:32px; width:400px; box-shadow:0 8px 40px rgba(0,0,0,.5); }
  h2 { color:#3D3830; font-size:22px; margin:0 0 20px; }
  label { display:block; font-size:13px; font-weight:600; color:#3D3830; margin-bottom:5px; text-transform:uppercase; letter-spacing:.4px; }
  input { width:100%; padding:10px 14px; border:2px solid #D4C9B5; border-radius:6px; font-size:15px; margin-bottom:16px; box-sizing:border-box; outline:none; }
  input:focus { border-color:#C9651A; }
  button { width:100%; padding:12px; background:#C9651A; color:#fff; border:none; border-radius:6px; font-size:15px; font-weight:600; cursor:pointer; }
  button:hover { background:#A8510F; }
  .hata   { background:#FCEAEA; color:#6B1621; padding:12px; border-radius:6px; margin-bottom:16px; font-size:14px; }
  .basari { background:#EAF5ED; color:#1A4532; padding:12px; border-radius:6px; margin-bottom:16px; font-size:14px; }
  .not { font-size:12px; color:#8B7355; margin-top:16px; }
  a { color:#C9651A; }
</style>
</head>
<body>
<div class="kutu">
  <h2>🔐 Yönetici Kurulumu</h2>

  <?php if ($mesaj): ?>
    <div class="basari"><?= $mesaj ?></div>
    <a href="login.php">→ Giriş Sayfasına Git</a>
  <?php elseif ($kilitli): ?>
    <div class="hata">🔒 Kurulum zaten tamamlanmış. Yönetici hesabı mevcut.</div>
    <a href="login.php">→ Giriş Sayfasına Git</a>
  <?php elseif ($hata): ?>
    <div class="hata">❌ <?= htmlspecialchars($hata) ?></div>
  <?php endif; ?>

  <?php if (!$mesaj && !$kilitli): ?>
  <form method="POST">
    <?= csrfAlan() ?>
    <label>Kullanıcı Adı</label>
    <input type="text" name="kullanici" value="<?= htmlspecialchars($_POST['kullanici'] ?? 'admin') ?>" required>

    <label>Şifre (en az 8 karakter)</label>
    <input type="password" name="sifre" required>

    <label>Şifre Tekrar</label>
    <input type="password" name="sifre_tekrar" required>

    <button type="submit">Hesabı Oluştur</button>
  </form>
  <?php endif; ?>

  <p class="not">Bu sayfa yalnızca ilk kurulum içindir; yönetici oluşturulduktan sonra otomatik kilitlenir.</p>
</div>
</body>
</html>
