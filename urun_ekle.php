<?php
/**
 * SORU 6 urun_ekle.php — Ürün Ekleme 
 *
 * SORU 4 Sadece giriş yapmış yönetici erişebilir .
 * POST metodu kullanılır; ürün veritabanına eklenir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
adminKontrol(); // Giriş yoksa login.php'ye yönlendir

$hatalar = [];
$basari  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad      = trim($_POST['ad']      ?? '');
    $kategori= trim($_POST['kategori']?? '');
    $fiyat   = trim($_POST['fiyat']   ?? '');
    $stok    = trim($_POST['stok']    ?? '');
    $indirim = trim($_POST['indirim'] ?? '0');

    // ── SORU 2 If-Else ve Switch ile Form Doğrulama ───
    if ($ad === '') {
        $hatalar[] = 'Ürün adı boş bırakılamaz.';
    } elseif (mb_strlen($ad) < 3) {
        $hatalar[] = 'Ürün adı en az 3 karakter olmalıdır.';
    }

    if ($kategori === '') {
        $hatalar[] = 'Kategori seçilmedi.';
    }

    // Switch ile fiyat aralığı kontrolü
    switch (true) {
        case ($fiyat === ''):
            $hatalar[] = 'Fiyat boş bırakılamaz.';
            break;
        case (!is_numeric($fiyat)):
            $hatalar[] = 'Fiyat sayısal bir değer olmalıdır.';
            break;
        case ((float)$fiyat <= 0):
            $hatalar[] = 'Fiyat 0\'dan büyük olmalıdır.';
            break;
    }

    if (!is_numeric($stok) || (int)$stok < 0) {
        $hatalar[] = 'Stok geçerli bir sayı olmalıdır (0 veya üstü).';
    }

    if (!is_numeric($indirim) || (int)$indirim < 0 || (int)$indirim > 100) {
        $hatalar[] = 'İndirim 0-100 arasında olmalıdır.';
    }

    if (empty($hatalar)) {
        try {
            $pdo  = baglan();
            // SORU 7 PDO Prepared Statement — SQL Injection önleme 
            $stmt = $pdo->prepare(
                'INSERT INTO urunler (ad, kategori, fiyat, stok, indirim)
                 VALUES (:ad, :kat, :fiyat, :stok, :indirim)'
            );
            $stmt->execute([
                ':ad'      => $ad,
                ':kat'     => $kategori,
                ':fiyat'   => round((float)$fiyat, 2),
                ':stok'    => (int)$stok,
                ':indirim' => (int)$indirim,
            ]);

            $_SESSION['mesaj']     = "\"" . $ad . "\" başarıyla eklendi.";
            $_SESSION['mesaj_tur'] = 'success';
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $hatalar[] = 'Veritabanı hatası: ' . e($e->getMessage());
        }
    }
}

$kategoriler   = tumKategoriler();
$sayfa_basligi = 'Ürün Ekle — Mağaza';
include __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Yeni Ürün Ekle</h1>
<p class="page-sub"><a href="index.php">← Ürün Listesine Dön</a></p>

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
  <h2 class="card-title">Ürün Bilgileri</h2>

  <!--
    POST kullanım gerekçesi:
    Veritabanına veri eklemek bir "yan etki" oluşturur.
    Veri değiştiren işlemlerde HTTP standardına göre POST kullanılır.
    Ayrıca büyük form verileri GET'te URL uzunluğu sınırını aşabilir.
  -->
  <form method="POST" action="urun_ekle.php">
    <div class="form-group">
      <label for="ad">Ürün Adı *</label>
      <input type="text" id="ad" name="ad" class="form-control"
             value="<?= e($_POST['ad'] ?? '') ?>" required>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="kategori">Kategori *</label>
        <select id="kategori" name="kategori" class="form-control" required>
          <option value="">Seçiniz…</option>
          <?php foreach ($kategoriler as $k): ?>
            <option value="<?= e($k) ?>" <?= ($_POST['kategori'] ?? '') === $k ? 'selected' : '' ?>>
              <?= e($k) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="fiyat">Fiyat (₺) *</label>
        <input type="number" id="fiyat" name="fiyat" class="form-control"
               step="0.01" min="0.01"
               value="<?= e($_POST['fiyat'] ?? '') ?>" required>
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="stok">Stok Adedi *</label>
        <input type="number" id="stok" name="stok" class="form-control"
               min="0" value="<?= e($_POST['stok'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="indirim">İndirim Oranı (%) </label>
        <input type="number" id="indirim" name="indirim" class="form-control"
               min="0" max="100" value="<?= e($_POST['indirim'] ?? '0') ?>">
      </div>
    </div>

    <div style="display:flex;gap:10px;margin-top:8px">
      <button type="submit" class="btn btn-primary">Kaydet</button>
      <a href="index.php" class="btn btn-secondary">İptal</a>
    </div>
  </form>
</div>

<?php include __DIR__ . '/layout/footer.php'; ?>
