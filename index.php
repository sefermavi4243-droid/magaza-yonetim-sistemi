<?php
/**
 * index.php — Ürün Listeleme Ana Sayfası
 *
 * HTTP GET kullanımı:
 *   - Sayfa yenilendiğinde veya link ile gelindiğinde GET metodu kullanılır.
 *   - Arama ve filtreleme de GET ile yapılır çünkü URL paylaşılabilir olmalıdır.
 *   Örnek: index.php?arama=elektronik&kategori=Elektronik&sayfa=2
 *
 * HTTP POST kullanımı:
 *   - Bu sayfada POST kullanılmaz; veri değiştirmeyen işlemler GET uygundur.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$admin_giris = !empty($_SESSION['admin']);

// ── GET Parametrelerini Oku ────────────────────────────────
$arama    = trim($_GET['arama']    ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$sayfa    = max(1, (int)($_GET['sayfa'] ?? 1));
$limit    = 5; // sayfa başına kayıt (Soru 6)

// ── Veri Çek ──────────────────────────────────────────────
$sonuc      = urunListele($arama, $kategori, $sayfa, $limit);
$urunler    = $sonuc['urunler'];
$toplam     = $sonuc['toplam'];
$sayfa_sayisi = (int)ceil($toplam / $limit);

$kategoriler = tumKategoriler();

// ── Sayfa Başlığı ──────────────────────────────────────────
$sayfa_basligi = 'Ürünler — Mağaza';
include __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Ürün Kataloğu</h1>
<p class="page-sub">Toplam <strong><?= $toplam ?></strong> ürün &nbsp;·&nbsp; Sayfa <?= $sayfa ?> / <?= $sayfa_sayisi ?: 1 ?></p>

<?php if (!empty($_SESSION['mesaj'])): ?>
  <div class="alert alert-<?= $_SESSION['mesaj_tur'] ?? 'info' ?>">
    <?= e($_SESSION['mesaj']) ?>
  </div>
  <?php unset($_SESSION['mesaj'], $_SESSION['mesaj_tur']); ?>
<?php endif; ?>

<!-- ── Arama Formu (GET — Soru 2) ─────────────────────── -->
<form method="GET" action="index.php" class="search-bar">
  <!--
    GET kullanım gerekçesi:
    Arama kriterleri URL'de görünür kalmalıdır. Böylece kullanıcı sayfayı
    yenileyebilir, yer işareti ekleyebilir veya linki paylaşabilir.
    Veri değiştirmeyen işlemlerde GET tercih edilir.
  -->
  <input type="text" name="arama" class="form-control"
         placeholder="Ürün adı veya kategori ara…"
         value="<?= e($arama) ?>">

  <select name="kategori" class="form-control" style="max-width:200px">
    <option value="">Tüm Kategoriler</option>
    <?php foreach ($kategoriler as $k): ?>
      <option value="<?= e($k) ?>" <?= $kategori === $k ? 'selected' : '' ?>>
        <?= e($k) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <button type="submit" class="btn btn-primary">🔍 Ara</button>

  <?php if ($arama || $kategori): ?>
    <a href="index.php" class="btn btn-secondary">✕ Temizle</a>
  <?php endif; ?>
</form>

<?php if ($admin_giris): ?>
  <div style="text-align:right; margin-bottom:16px;">
    <a href="urun_ekle.php" class="btn btn-primary">+ Yeni Ürün Ekle</a>
  </div>
<?php endif; ?>

<!-- ── Ürün Tablosu ───────────────────────────────────── -->
<?php if (empty($urunler)): ?>
  <div class="alert alert-warning">Arama kriterlerinize uyan ürün bulunamadı.</div>
<?php else: ?>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Ürün Adı</th>
        <th>Kategori</th>
        <th>Fiyat</th>
        <th>İndirimli</th>
        <th>Stok</th>
        <?php if ($admin_giris): ?><th>İşlemler</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($urunler as $u):
        $ind_fiyat = indirimUygula((float)$u['fiyat'], (int)$u['indirim']);
        $stok_sinif = $u['stok'] < 20 ? 'badge-stok-az' : 'badge-stok-ok';
      ?>
      <tr>
        <td><span style="font-family:var(--font-mono);color:var(--warm)"><?= $u['id'] ?></span></td>
        <td><strong><?= e($u['ad']) ?></strong></td>
        <td><span class="badge badge-category"><?= e($u['kategori']) ?></span></td>
        <td>
          <?php if ($u['indirim'] > 0): ?>
            <span class="fiyat fiyat-eski">₺<?= number_format((float)$u['fiyat'], 2, '.', ',') ?></span>
          <?php else: ?>
            <span class="fiyat">₺<?= number_format((float)$u['fiyat'], 2, '.', ',') ?></span>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($u['indirim'] > 0): ?>
            <span class="fiyat fiyat-indirimli">₺<?= number_format($ind_fiyat, 2, '.', ',') ?></span>
            <span class="badge badge-indirim">%<?= $u['indirim'] ?></span>
          <?php else: ?>
            <span class="fiyat">₺<?= number_format((float)$u['fiyat'], 2, '.', ',') ?></span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?= $stok_sinif ?>">
            <?= $u['stok'] ?> adet
          </span>
        </td>
        <?php if ($admin_giris): ?>
        <td>
          <a href="urun_duzenle.php?id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">✏️ Düzenle</a>
          <a href="urun_sil.php?id=<?= $u['id'] ?>"
             class="btn btn-danger btn-sm"
             onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">🗑 Sil</a>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ── Sayfalama — SORU 6 ──────────────── -->
<?php if ($sayfa_sayisi > 1): ?>
<nav class="pagination">
  <a href="?arama=<?= urlencode($arama) ?>&kategori=<?= urlencode($kategori) ?>&sayfa=<?= max(1, $sayfa - 1) ?>"
     class="<?= $sayfa <= 1 ? 'disabled' : '' ?>">‹</a>

  <?php for ($p = 1; $p <= $sayfa_sayisi; $p++): ?>
    <a href="?arama=<?= urlencode($arama) ?>&kategori=<?= urlencode($kategori) ?>&sayfa=<?= $p ?>"
       class="<?= $p === $sayfa ? 'aktif' : '' ?>"><?= $p ?></a>
  <?php endfor; ?>

  <a href="?arama=<?= urlencode($arama) ?>&kategori=<?= urlencode($kategori) ?>&sayfa=<?= min($sayfa_sayisi, $sayfa + 1) ?>"
     class="<?= $sayfa >= $sayfa_sayisi ? 'disabled' : '' ?>">›</a>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
