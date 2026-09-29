<?php
session_start();
require_once __DIR__ . '/config.php';

if (!empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$hata = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici = trim($_POST['kullanici'] ?? '');
    $sifre     = trim($_POST['sifre']     ?? '');

    if ($kullanici === '') {
        $hata = 'Kullanici adi bos birakilamaz.';
    } elseif ($sifre === '') {
        $hata = 'Sifre bos birakilamaz.';
    } else {
        try {
            $pdo  = baglan();
            $stmt = $pdo->prepare('SELECT sifre_hash FROM yonetici WHERE kullanici_adi = :k LIMIT 1');
            $stmt->execute([':k' => $kullanici]);
            $row  = $stmt->fetch();

            if ($row && password_verify($sifre, $row['sifre_hash'])) {
                $_SESSION['admin']    = $kullanici;
                $_SESSION['giris_ts'] = time();
                header('Location: index.php');
                exit;
            } else {
                $hata = 'Kullanici adi veya sifre hatali.';
            }
        } catch (PDOException $e) {
            $hata = 'Veritabani hatasi: ' . htmlspecialchars($e->getMessage());
        }
    }
}

$sayfa_basligi = 'Yonetici Girisi';
include __DIR__ . '/layout/header.php';
?>

<div class="login-wrap">
  <div class="card">
    <h2 class="card-title">Yonetici Girisi</h2>

    <?php if ($hata): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($hata) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="kullanici">Kullanici Adi</label>
        <input type="text"
               id="kullanici"
               name="kullanici"
               class="form-control"
               value="<?= htmlspecialchars($_POST['kullanici'] ?? '') ?>"
               autocomplete="username"
               required>
      </div>

      <div class="form-group">
        <label for="sifre">Sifre</label>
        <input type="password"
               id="sifre"
               name="sifre"
               class="form-control"
               autocomplete="current-password"
               required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%">Giris Yap</button>
    </form>

    <p style="font-size:13px;color:var(--warm);margin-top:16px;text-align:center">
      Henuz hesap olusturmadiysan:
      <a href="kurulum_admin.php">kurulum_admin.php</a>
    </p>
  </div>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
