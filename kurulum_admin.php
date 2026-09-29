<?php
/**
 * kurulum_admin.php — Yönetici Hesabı Kurulumu
 *
 * Bu dosyayı YALNIZCA BİR KEZ çalıştırın.
 * Yönetici tablosunu oluşturur ve varsayılan yöneticiyi ekler.
 * Kurulumdan sonra bu dosyayı silin veya erişimi engelleyin.
 *
 * SORU 6 GÜVENLİK:
 *   - Şifre password_hash(PASSWORD_BCRYPT) ile hashlenir.
 *   - Girişte password_verify() kullanılır (login.php).
 */

require_once __DIR__ . '/config.php';

$mesaj = '';
$hata  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici   = trim($_POST['kullanici']    ?? '');
    $sifre       = trim($_POST['sifre']        ?? '');
    $sifre_tekrar= trim($_POST['sifre_tekrar'] ?? '');

    if ($kullanici === '' || $sifre === '') {
        $hata = 'Tüm alanları doldurun.';
    } elseif (strlen($sifre) < 6) {
        $hata = 'Şifre en az 6 karakter olmalıdır.';
    } elseif ($sifre !== $sifre_tekrar) {
        $hata = 'Şifreler eşleşmiyor.';
    } else {
        try {
            $pdo = baglan();

            // Yoksa yönetici tablosunu oluştur 
            $pdo->exec("CREATE TABLE IF NOT EXISTS `yonetici` (
                id            INT AUTO_INCREMENT PRIMARY KEY,
                kullanici_adi VARCHAR(50)  NOT NULL UNIQUE,
                sifre_hash    VARCHAR(255) NOT NULL,
                olusturma     DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            // SORU 7 password_hash ile şifreyi hashle 
            $hash = password_hash($sifre, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                'INSERT INTO yonetici (kullanici_adi, sifre_hash) VALUES (:k, :h)
                 ON DUPLICATE KEY UPDATE sifre_hash = :h2'
            );
            $stmt->execute([':k' => $kullanici, ':h' => $hash, ':h2' => $hash]);

            $mesaj = "Yönetici hesabı oluşturuldu: <strong>" . htmlspecialchars($kullanici) . "</strong><br>"
                   . "Artık bu dosyayı silin veya erişimi engelleyin!";
        } catch (PDOException $e) {
            $hata = 'Hata: ' . htmlspecialchars($e->getMessage());
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
  <?php elseif ($hata): ?>
    <div class="hata">❌ <?= htmlspecialchars($hata) ?></div>
  <?php endif; ?>

  <?php if (!$mesaj): ?>
  <form method="POST">
    <label>Kullanıcı Adı</label>
    <input type="text" name="kullanici" value="<?= htmlspecialchars($_POST['kullanici'] ?? 'admin') ?>" required>

    <label>Şifre (en az 6 karakter)</label>
    <input type="password" name="sifre" required>

    <label>Şifre Tekrar</label>
    <input type="password" name="sifre_tekrar" required>

    <button type="submit">Hesabı Oluştur</button>
  </form>
  <?php endif; ?>

  <p class="not">Bu sayfa yalnızca ilk kurulum içindir. Kurulumdan sonra erişimi engelleyin.</p>
</div>
</body>
</html>
