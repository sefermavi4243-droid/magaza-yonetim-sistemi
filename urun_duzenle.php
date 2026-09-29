<?php
/**
 * SORU 6 urun_duzenle.php — Ürün Güncelleme 
 * Sadece giriş yapmış yönetici erişebilir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
adminKontrol();

$hatalar = [];
$urun    = null;
$id      = (int)($_GET['id'] ?? 0);

// Ürünü getir
try {
    $pdo  = baglan();
    $stmt = $pdo->prepare('SELECT * FROM urunler WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $urun = $stmt->fetch();
} catch (PDOException $e) {
    $hatalar[] = 'Veritabanı hatası: ' . e($e->getMessage());
}

if (!$urun && empty($hatalar)) {
    $_SESSION['mesaj']     = 'Ürün bulunamadı.';
    $_SESSION['mesaj_tur'] = 'danger';
    header('Location: index.php');
    exit;
}

// ── POST: Güncelleme İşlemi ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad      = trim($_POST['ad']       ?? '');
    $kategori= trim($_POST['kategori'] ?? '');
    $fiyat   = trim($_POST['fiyat']    ?? '');
    $stok    = trim($_POST['stok']     ?? '');
    $indirim = trim($_POST['indirim']  ?? '0');

    // Doğrulama (if-else)
    if ($ad === '')              $hatalar[] = 'Ürün adı boş olamaz.';
    if ($kategori === '')        $hatalar[] = 'Kategori seçilmedi.';
    if (!is_numeric($fiyat) || (float)$fiyat <= 0)
                                 $hatalar[] = 'Geçerli bir fiyat girin.';
    if (!is_numeric($stok) || (int)$stok < 0)
                                 $hatalar[] = 'Geçerli bir stok girin.';
    if (!is_numeric($indirim) || (int)$indirim < 0 || (int)$indirim > 100)
                                 $hatalar[] = 'İndirim 0-100 arasında olmalı.';

    if (empty($hatalar)) {
        try {
            // PDO Prepared Statement (Soru 7)
            $upd = $pdo->prepare(
                'UPDATE urunler SET ad=:ad, kategori=:kat, fiyat=:fiyat,
                 stok=:stok, indirim=:indirim WHERE id=:id'
            );
            $upd->execute([
                ':ad'      => $ad,
                ':kat'     => $kategori,
                ':fiyat'   => round((float)$fiyat, 2),
                ':stok'    => (int)$stok,
                ':indirim' => (int)$indirim,
                ':id'      => $id,
            ]);

            $_SESSION['mesaj']     = "\"$ad\" başarıyla güncellendi.";
            $_SESSION['mesaj_tur'] = 'success';
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $hatalar[] = 'Güncelleme hatası: ' . e($e->getMessage());
        }
    }

    // Hata varsa POST değerlerini form alanlarına geri yükle
    $urun['ad']       = $ad;
    $urun['kategori'] = $kategori;
    $urun['fiyat']    = $fiyat;
    $urun['stok']     = $stok;
    $urun['indirim']  = $indirim;
}

$kategoriler   = tumKategoriler();
$sayfa_basligi = 'Ürün Düzenle — Mağaza';
include __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Ürün Düzenle</h1>
<p class="page-sub"><a href="index.php">← Ürün Listesine Dön</a> &nbsp;·&nbsp; ID: <?= $id ?></p>

<?php if (!empty($hatalar)): ?>
  <div class="alert alert-danger">
    <ul style="margin:0;padding-left:18px">
      <?php foreach ($hatalar as $h): ?>
        <li><?= e($h) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="card" style="max-width:600px">
  <h2 class="card-title">Ürün Bilgilerini Güncelle</h2>

  <form method="POST" action="urun_duzenle.php?id=<?= $id ?>">
    <div class="form-group">
      <label for="ad">Ürün Adı *</label>
      <input type="text" id="ad" name="ad" class="form-control"
             value="<?= e($urun['ad']) ?>" required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="kategori">Kategori *</label>
        <select id="kategori" name="kategori" class="form-control" required>
          <option value="">Seçiniz…</option>
          <?php foreach ($kategoriler as $k): ?>
            <option value="<?= e($k) ?>" <?= $urun['kategori'] === $k ? 'selected' : '' ?>>
              <?= e($k) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="fiyat">Fiyat (₺) *</label>
        <input type="number" id="fiyat" name="fiyat" class="form-control"
               step="0.01" min="0.01" value="<?= e($urun['fiyat']) ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="stok">Stok Adedi *</label>
        <input type="number" id="stok" name="stok" class="form-control"
               min="0" value="<?= e($urun['stok']) ?>" required>
      </div>

      <div class="form-group">
        <label for="indirim">İndirim Oranı (%) </label>
        <input type="number" id="indirim" name="indirim" class="form-control"
               min="0" max="100" value="<?= e($urun['indirim']) ?>">
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:8px">
      <button type="submit" class="btn btn-primary">Güncelle</button>
      <a href="index.php" class="btn btn-secondary">İptal</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
