<?php
/**
 * =============================================
 *  DOĞRULAMA SAYFASI — ÖĞRENCİLERE VERİLECEK
 *  Projeyi tamamladıktan sonra bu sayfayı
 *  çalıştırın. Çıkan kodu ve ekran görüntüsünü
 *  Moodle'a yükleyin.
 * =============================================
 */
session_start();

// Yönetici oturumu kontrolü — öğrenci login sistemini tamamlamış olmalı
// Bu satırı kendi session değişkeninize göre uyarlayın:
// if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }

define('DB_HOST', 'localhost');
define('DB_NAME', 'magaza_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// ──────────────────────────────────────────────
//  DOĞRULAMA KODU HESAPLAMA ALGORİTMASI
//  Bu fonksiyonu DEĞİŞTİRMEYİN.
// ──────────────────────────────────────────────
function dogrulamaKoduHesapla(string $ogrenci_no, float $db_toplam, int $urun_sayisi, int $siparis_sayisi): string {
    $ara1 = (int)round($db_toplam);
    $ara2 = $urun_sayisi * (int)$ogrenci_no[-1];          // son hane
    $ara3 = $siparis_sayisi + (int)substr($ogrenci_no, 0, 3); // ilk 3 hane
    $ham  = ($ara1 + $ara2 + $ara3) % 100000;
    return str_pad($ham, 5, '0', STR_PAD_LEFT);
}

$hata = '';
$sonuc = null;

try {
    $pdo = new PDO(
        'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Öğrenci no'yu al
    $stmt = $pdo->query("SELECT no, kurulum FROM ogrenci_bilgi LIMIT 1");
    $ogrenci = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ogrenci) {
        $hata = 'Öğrenci kaydı bulunamadı. Önce basla.php dosyasını çalıştırın.';
    } else {
        $ogrenci_no = $ogrenci['no'];

        // DB sorguları — CRUD tamamlanmış olmalı
        $row = $pdo->query("SELECT 
            COUNT(*)                         AS urun_sayisi,
            COALESCE(SUM(fiyat * stok), 0)  AS stok_degeri,
            COALESCE(SUM(fiyat * stok * (1 - indirim/100)), 0) AS indirimli_deger,
            COALESCE(AVG(fiyat), 0)         AS ort_fiyat,
            COALESCE(MAX(fiyat), 0)         AS max_fiyat,
            COALESCE(MIN(fiyat), 0)         AS min_fiyat
        FROM urunler")->fetch(PDO::FETCH_ASSOC);

        $siparis_row = $pdo->query("SELECT COUNT(*) AS adet, COALESCE(SUM(toplam),0) AS ciro FROM siparisler")->fetch(PDO::FETCH_ASSOC);

        $urun_sayisi   = (int)$row['urun_sayisi'];
        $stok_degeri   = (float)$row['stok_degeri'];
        $siparis_sayisi= (int)$siparis_row['adet'];

        // Doğrulama kodu
        $kod = dogrulamaKoduHesapla($ogrenci_no, $stok_degeri, $urun_sayisi, $siparis_sayisi);

        $sonuc = [
            'ogrenci_no'    => $ogrenci_no,
            'kurulum_tarihi'=> $ogrenci['kurulum'],
            'urun_sayisi'   => $urun_sayisi,
            'stok_degeri'   => number_format($stok_degeri, 2, '.', ','),
            'indirimli'     => number_format((float)$row['indirimli_deger'], 2, '.', ','),
            'ort_fiyat'     => number_format((float)$row['ort_fiyat'], 2, '.', ','),
            'siparis_sayisi'=> $siparis_sayisi,
            'ciro'          => number_format((float)$siparis_row['ciro'], 2, '.', ','),
            'kod'           => $kod,
            'tarih'         => date('d.m.Y H:i:s'),
        ];
    }

} catch (PDOException $e) {
    $hata = 'Veritabanı bağlantı hatası: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Proje Doğrulama Kodu</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: 'Segoe UI', Arial, sans-serif; background:#1a1a2e; margin:0; padding:30px; }
  .kart { background:#fff; max-width:700px; margin:0 auto; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.4); }
  .baslik { background:#2c3e50; color:#fff; padding:25px 30px; }
  .baslik h1 { margin:0; font-size:22px; }
  .baslik p  { margin:5px 0 0; opacity:.7; font-size:14px; }
  .icerik { padding:30px; }
  .bilgi-grid { display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:25px; }
  .bilgi-kutu { background:#f8f9fa; border-radius:8px; padding:15px; }
  .bilgi-kutu .etiket { font-size:12px; color:#6c757d; text-transform:uppercase; letter-spacing:.5px; }
  .bilgi-kutu .deger  { font-size:18px; font-weight:bold; color:#2c3e50; margin-top:4px; }
  .kod-alani { background:#2c3e50; border-radius:10px; padding:30px; text-align:center; margin:20px 0; }
  .kod-alani .etiket { color:#95a5a6; font-size:13px; letter-spacing:2px; text-transform:uppercase; }
  .kod-alani .kod { font-size:56px; font-weight:900; color:#f1c40f; letter-spacing:10px; font-family:'Courier New',monospace; margin:10px 0; }
  .kod-alani .no { color:#ecf0f1; font-size:14px; }
  .uyari { background:#fff3cd; border-left:4px solid #ffc107; padding:15px; border-radius:4px; font-size:13px; color:#856404; }
  .hata  { background:#fde8e8; color:#c0392b; padding:20px; border-radius:8px; }
  .tarih { text-align:right; font-size:12px; color:#aaa; margin-top:15px; }
  @media print { body{background:#fff;padding:0} .kart{box-shadow:none} }
</style>
</head>
<body>
<div class="kart">
  <div class="baslik">
    <h1>📊 PHP Projesi — Doğrulama Raporu</h1>
    <p>Web Programlama Dersi · Vize Proje Değerlendirmesi</p>
  </div>
  <div class="icerik">

  <?php if ($hata): ?>
    <div class="hata">❌ <?= $hata ?></div>

  <?php elseif ($sonuc): ?>

    <div class="bilgi-grid">
      <div class="bilgi-kutu">
        <div class="etiket">Öğrenci No</div>
        <div class="deger"><?= htmlspecialchars($sonuc['ogrenci_no']) ?></div>
      </div>
      <div class="bilgi-kutu">
        <div class="etiket">Kurulum Tarihi</div>
        <div class="deger" style="font-size:14px"><?= htmlspecialchars($sonuc['kurulum_tarihi']) ?></div>
      </div>
      <div class="bilgi-kutu">
        <div class="etiket">Ürün Sayısı (DB)</div>
        <div class="deger"><?= $sonuc['urun_sayisi'] ?> adet</div>
      </div>
      <div class="bilgi-kutu">
        <div class="etiket">Toplam Stok Değeri</div>
        <div class="deger">₺<?= $sonuc['stok_degeri'] ?></div>
      </div>
      <div class="bilgi-kutu">
        <div class="etiket">İndirimli Stok Değeri</div>
        <div class="deger">₺<?= $sonuc['indirimli'] ?></div>
      </div>
      <div class="bilgi-kutu">
        <div class="etiket">Sipariş Sayısı (DB)</div>
        <div class="deger"><?= $sonuc['siparis_sayisi'] ?> sipariş</div>
      </div>
    </div>

    <div class="kod-alani">
      <div class="etiket">🔐 Doğrulama Kodu</div>
      <div class="kod"><?= $sonuc['kod'] ?></div>
      <div class="no">Öğrenci: <?= htmlspecialchars($sonuc['ogrenci_no']) ?></div>
    </div>

    <div class="uyari">
      📸 <strong>Bu sayfanın ekran görüntüsünü alın</strong> ve projenizle birlikte Moodle'a yükleyin.
      Doğrulama kodu, veritabanınızdaki verilere göre hesaplanmaktadır.
      Başka bir öğrencinin verileri kullanılarak elde edilen kod geçersiz sayılacaktır.
    </div>

    <div class="tarih">Rapor oluşturma tarihi: <?= $sonuc['tarih'] ?></div>

  <?php endif; ?>
  </div>
</div>
</body>
</html>
