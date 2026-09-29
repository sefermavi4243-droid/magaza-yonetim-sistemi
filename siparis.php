<?php
/**
 * siparis.php — Sipariş Oluşturma
 *
 * Sipariş kaydı ve stok düşümü tek bir transaction içinde yapılır;
 * ürün satırı FOR UPDATE ile kilitlenerek eş zamanlı siparişlerde
 * stoğun eksiye düşmesi engellenir.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

oturumBaslat();

$hatalar = [];
$basari  = '';

// Tüm ürünleri listele
try {
    $pdo          = baglan();
    $urun_listesi = $pdo->query('SELECT id, ad, kategori, fiyat, stok, indirim FROM urunler ORDER BY ad')->fetchAll();
} catch (PDOException $e) {
    $hatalar[]    = hataKaydet($e, 'Ürünler yüklenemedi.');
    $urun_listesi = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $musteri = trim($_POST['musteri'] ?? '');
    $urun_id = (int)($_POST['urun_id'] ?? 0);
    $adet    = (int)($_POST['adet']   ?? 0);

    if (!csrfGecerli()) {
        $hatalar[] = 'Oturum süresi doldu. Lütfen sayfayı yenileyip tekrar deneyin.';
    }

    // Form doğrulama
    if ($musteri === '') {
        $hatalar[] = 'Müşteri adı boş bırakılamaz.';
    } elseif (mb_strlen($musteri) < 2) {
        $hatalar[] = 'Müşteri adı en az 2 karakter olmalıdır.';
    } elseif (mb_strlen($musteri) > 100) {
        $hatalar[] = 'Müşteri adı en fazla 100 karakter olabilir.';
    }

    if ($urun_id <= 0) {
        $hatalar[] = 'Lütfen bir ürün seçin.';
    }

    if (!ctype_digit((string)($_POST['adet'] ?? '')) || $adet <= 0) {
        $hatalar[] = 'Adet pozitif bir tam sayı olmalıdır.';
    }

    if (empty($hatalar)) {
        try {
            $pdo->beginTransaction();

            // Ürünü kilitle — transaction bitene kadar başka sipariş bu satırı değiştiremez
            $stmt = $pdo->prepare('SELECT * FROM urunler WHERE id = :id FOR UPDATE');
            $stmt->execute([':id' => $urun_id]);
            $urun = $stmt->fetch();

            if (!$urun) {
                $pdo->rollBack();
                $hatalar[] = 'Seçilen ürün bulunamadı.';
            } elseif ((int)$urun['stok'] < $adet) {
                $pdo->rollBack();
                $hatalar[] = 'Seçilen ürün için yeterli stok bulunmuyor.';
            } else {
                $birim_fiyat = indirimUygula((float)$urun['fiyat'], (int)$urun['indirim']);
                $toplam      = fiyatHesapla($birim_fiyat, $adet);

                $ins = $pdo->prepare(
                    'INSERT INTO siparisler (musteri_adi, urun_id, adet, toplam)
                     VALUES (:musteri, :urun_id, :adet, :toplam)'
                );
                $ins->execute([
                    ':musteri' => $musteri,
                    ':urun_id' => $urun_id,
                    ':adet'    => $adet,
                    ':toplam'  => $toplam,
                ]);

                $upd = $pdo->prepare('UPDATE urunler SET stok = stok - :adet WHERE id = :id');
                $upd->execute([':adet' => $adet, ':id' => $urun_id]);

                $pdo->commit();

                siparisLogYaz($musteri, $toplam, $urun['ad']);

                $basari = sprintf(
                    'Sipariş alındı! Müşteri: %s | Ürün: %s | Adet: %d | Toplam: ₺%s',
                    e($musteri),
                    e($urun['ad']),
                    $adet,
                    number_format($toplam, 2, '.', ',')
                );
                $_POST = [];

                // Ürün listesini yenile
                $urun_listesi = $pdo->query('SELECT id, ad, kategori, fiyat, stok, indirim FROM urunler ORDER BY ad')->fetchAll();
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $hatalar[] = hataKaydet($e, 'Sipariş kaydedilemedi. Lütfen tekrar deneyin.');
        }
    }
}

$sayfa_basligi = 'Sipariş Ver — Mağaza';
include __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Sipariş Oluştur</h1>
<p class="page-sub">Müşteri bilgilerini doldurun ve ürün seçin.</p>

<?php if ($basari): ?>
  <div class="alert alert-success"><?= $basari ?></div>
<?php endif; ?>

<?php if (!empty($hatalar)): ?>
  <div class="alert alert-danger">
    <ul style="margin:0;padding-left:18px">
      <?php foreach ($hatalar as $h): ?>
        <li><?= e($h) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">

  <!-- SİPARİŞ FORMU -->
  <div class="card">
    <h2 class="card-title">Sipariş Formu</h2>

    <form method="POST" action="siparis.php" id="siparis-form">
      <?= csrfAlan() ?>
      <div class="form-group">
        <label for="musteri">Müşteri Adı *</label>
        <input type="text" id="musteri" name="musteri" class="form-control"
               value="<?= e($_POST['musteri'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label for="urun_id">Ürün Seçin *</label>
        <select id="urun_id" name="urun_id" class="form-control" required
                onchange="urunBilgisiGoster(this)">
          <option value="">-- Ürün Seçiniz --</option>
          <?php foreach ($urun_listesi as $u): ?>
            <option value="<?= $u['id'] ?>"
                    data-fiyat="<?= $u['fiyat'] ?>"
                    data-indirim="<?= $u['indirim'] ?>"
                    data-stok="<?= $u['stok'] ?>"
                    <?= ((int)($_POST['urun_id'] ?? 0) === $u['id']) ? 'selected' : '' ?>
                    <?= $u['stok'] == 0 ? 'disabled' : '' ?>>
              <?= e($u['ad']) ?> — ₺<?= number_format((float)$u['fiyat'], 2) ?>
              <?= $u['stok'] == 0 ? '(Stok Yok)' : "(Stok: {$u['stok']})" ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="urun-bilgi" style="display:none;background:#f8f4ec;border-radius:6px;padding:14px;margin-bottom:16px;font-size:14px">
        <strong>Ürün:</strong> <span id="b-ad"></span><br>
        <strong>Birim Fiyat:</strong> <span id="b-fiyat" class="fiyat" style="color:var(--accent)"></span><br>
        <strong>Mevcut Stok:</strong> <span id="b-stok"></span>
      </div>

      <div class="form-group">
        <label for="adet">Adet *</label>
        <input type="number" id="adet" name="adet" class="form-control"
               min="1" value="<?= e($_POST['adet'] ?? '1') ?>"
               oninput="toplamHesapla()" required>
      </div>

      <div id="toplam-bilgi" style="display:none;font-family:var(--font-mono);font-size:20px;font-weight:700;color:var(--accent);margin-bottom:16px">
        Toplam: ₺<span id="b-toplam">0.00</span>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%">🛒 Siparişi Tamamla</button>
    </form>
  </div>

  <!-- SON SİPARİŞLER -->
  <div class="card">
    <h2 class="card-title">Son Siparişler</h2>
    <?php
    try {
        $son = $pdo->query(
            'SELECT s.id, s.musteri_adi, u.ad as urun_adi, s.adet, s.toplam, s.tarih
             FROM siparisler s
             JOIN urunler u ON s.urun_id = u.id
             ORDER BY s.tarih DESC LIMIT 8'
        )->fetchAll();
    } catch (Exception $e) { $son = []; }
    ?>
    <?php if (empty($son)): ?>
      <p style="color:var(--warm);font-size:14px">Henüz sipariş yok.</p>
    <?php else: ?>
      <?php foreach ($son as $sp): ?>
        <div style="border-bottom:1px solid var(--border);padding:10px 0;font-size:14px">
          <strong><?= e($sp['musteri_adi']) ?></strong> —
          <?= e($sp['urun_adi']) ?> × <?= $sp['adet'] ?><br>
          <span class="fiyat fiyat-indirimli">₺<?= number_format((float)$sp['toplam'], 2, '.', ',') ?></span>
          <span style="color:var(--warm);font-size:12px;float:right"><?= $sp['tarih'] ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<script>
const secilen = { fiyat: 0, indirim: 0, stok: 0 };

function urunBilgisiGoster(sel) {
  const opt = sel.options[sel.selectedIndex];
  if (!opt.value) { document.getElementById('urun-bilgi').style.display='none'; return; }

  secilen.fiyat   = parseFloat(opt.dataset.fiyat);
  secilen.indirim = parseInt(opt.dataset.indirim);
  secilen.stok    = parseInt(opt.dataset.stok);

  const indFiyat = secilen.fiyat * (1 - secilen.indirim / 100);
  document.getElementById('b-ad').textContent    = opt.text.split(' —')[0];
  document.getElementById('b-fiyat').textContent = '₺' + indFiyat.toFixed(2) + (secilen.indirim ? ' (%' + secilen.indirim + ' indirimli)' : '');
  document.getElementById('b-stok').textContent  = secilen.stok + ' adet';
  document.getElementById('urun-bilgi').style.display = 'block';
  document.getElementById('adet').max = secilen.stok;
  toplamHesapla();
}

function toplamHesapla() {
  const adet = parseInt(document.getElementById('adet').value) || 0;
  if (!secilen.fiyat || adet <= 0) { document.getElementById('toplam-bilgi').style.display='none'; return; }
  const birim  = secilen.fiyat * (1 - secilen.indirim / 100);
  const toplam = (birim * adet).toFixed(2);
  document.getElementById('b-toplam').textContent = toplam;
  document.getElementById('toplam-bilgi').style.display = 'block';
}

// Sayfa yüklendiğinde seçili ürün varsa bilgileri göster
window.addEventListener('DOMContentLoaded', () => {
  const sel = document.getElementById('urun_id');
  if (sel.value) urunBilgisiGoster(sel);
});
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>
