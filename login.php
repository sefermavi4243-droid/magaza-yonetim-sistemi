<?php
/**
 * login.php — Yönetici Girişi
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

oturumBaslat();

if (!empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici = trim($_POST['kullanici'] ?? '');
    $sifre     = trim($_POST['sifre']     ?? '');

    if (!csrfGecerli()) {
        $hata = 'Oturum süresi doldu. Lütfen tekrar deneyin.';
    } elseif ($kullanici === '') {
        $hata = 'Kullanıcı adı boş bırakılamaz.';
    } elseif ($sifre === '') {
        $hata = 'Şifre boş bırakılamaz.';
    } else {
        try {
            $pdo  = baglan();
            $stmt = $pdo->prepare('SELECT sifre_hash FROM yonetici WHERE kullanici_adi = :k LIMIT 1');
            $stmt->execute([':k' => $kullanici]);
            $row  = $stmt->fetch();

            if ($row && password_verify($sifre, $row['sifre_hash'])) {
                // Oturum sabitleme (session fixation) saldırısına karşı yeni ID
                session_regenerate_id(true);
                unset($_SESSION['csrf_token']);
                $_SESSION['admin']    = $kullanici;
                $_SESSION['giris_ts'] = time();
                header('Location: index.php');
                exit;
            } else {
                $hata = 'Kullanıcı adı veya şifre hatalı.';
            }
        } catch (PDOException $e) {
            $hata = hataKaydet($e, 'Giriş şu anda yapılamıyor. Lütfen daha sonra tekrar deneyin.');
        }
    }
}

$sayfa_basligi = 'Yönetici Girişi';
include __DIR__ . '/layout/header.php';
?>

<div class="login-wrap">
  <div class="card">
    <h2 class="card-title">Yönetici Girişi</h2>

    <?php if ($hata): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <?= csrfAlan() ?>
      <div class="form-group">
        <label for="kullanici">Kullanıcı Adı</label>
        <input type="text"
               id="kullanici"
               name="kullanici"
               class="form-control"
               value="<?= htmlspecialchars($_POST['kullanici'] ?? '') ?>"
               autocomplete="username"
               required>
      </div>

      <div class="form-group">
        <label for="sifre">Şifre</label>
        <input type="password"
               id="sifre"
               name="sifre"
               class="form-control"
               autocomplete="current-password"
               required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%">Giriş Yap</button>
    </form>

    <p style="font-size:13px;color:var(--warm);margin-top:16px;text-align:center">
      Henüz hesap oluşturmadıysan:
      <a href="kurulum_admin.php">kurulum_admin.php</a>
    </p>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
