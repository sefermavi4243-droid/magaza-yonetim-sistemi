<?php
/**
 * soru 4-6 siparisler.php — Sipariş Listesi 
 * Sadece giriş yapmış yönetici erişebilir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
adminKontrol();

$sayfa       = max(1, (int)($_GET['sayfa'] ?? 1));
$limit       = 10;
$offset      = ($sayfa - 1) * $limit;

try {
    $pdo = baglan();

    $toplam_siparis = (int)$pdo->query('SELECT COUNT(*) FROM siparisler')->fetchColumn();
    $toplam_ciro    = (float)$pdo->query('SELECT COALESCE(SUM(toplam),0) FROM siparisler')->fetchColumn();
    $sayfa_sayisi   = (int)ceil($toplam_siparis / $limit);

    $stmt = $pdo->prepare(
        'SELECT s.id, s.musteri_adi, u.ad AS urun_adi, u.kategori,
                s.adet, s.toplam, s.tarih
         FROM siparisler s
         JOIN urunler u ON s.urun_id = u.id
         ORDER BY s.tarih DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $siparisler = $stmt->fetchAll();

} catch (PDOException $e) {
    die('Hata: ' . e($e->getMessage()));
}

$sayfa_basligi = 'Sipariş Listesi — Mağaza';
include __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Sipariş Yönetimi</h1>
<p class="page-sub"><a href="index.php">← Ana Sayfa</a></p>

<div class="stats-grid">
  <div class="stat-kutu">
    <span class="stat-deger"><?= $toplam_siparis ?></span>
    <span class="stat-etiket">Toplam Sipariş</span>
  </div>
  <div class="stat-kutu">
    <span class="stat-deger">₺<?= number_format($toplam_ciro, 0, '.', ',') ?></span>
    <span class="stat-etiket">Toplam Ciro</span>
  </div>
</div>

<?php if (empty($siparisler)): ?>
  <div class="alert alert-info">Henüz sipariş bulunmuyor.</div>
<?php else: ?>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Müşteri</th>
        <th>Ürün</th>
        <th>Kategori</th>
        <th>Adet</th>
        <th>Toplam</th>
        <th>Tarih</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($siparisler as $s): ?>
      <tr>
        <td><span style="font-family:var(--font-mono);color:var(--warm)"><?= $s['id'] ?></span></td>
        <td><strong><?= e($s['musteri_adi']) ?></strong></td>
        <td><?= e($s['urun_adi']) ?></td>
        <td><span class="badge badge-category"><?= e($s['kategori']) ?></span></td>
        <td><?= $s['adet'] ?></td>
        <td><span class="fiyat fiyat-indirimli">₺<?= number_format((float)$s['toplam'], 2, '.', ',') ?></span></td>
        <td style="font-size:13px;color:var(--warm)"><?= $s['tarih'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Sayfalama -->
<?php if ($sayfa_sayisi > 1): ?>
<nav class="pagination">
  <a href="?sayfa=<?= max(1, $sayfa - 1) ?>" class="<?= $sayfa <= 1 ? 'disabled' : '' ?>">‹</a>
  <?php for ($p = 1; $p <= $sayfa_sayisi; $p++): ?>
    <a href="?sayfa=<?= $p ?>" class="<?= $p === $sayfa ? 'aktif' : '' ?>"><?= $p ?></a>
  <?php endfor; ?>
  <a href="?sayfa=<?= min($sayfa_sayisi, $sayfa + 1) ?>" class="<?= $sayfa >= $sayfa_sayisi ? 'disabled' : '' ?>">›</a>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/layout/footer.php'; ?>
