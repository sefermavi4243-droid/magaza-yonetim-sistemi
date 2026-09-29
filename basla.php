<?php
/**
 * =============================================
 *  BAŞLANGIÇ DOSYASI — ÖĞRENCİLERE VERİLECEK
 *  Bu dosyayı SİLMEYİN ve DEĞİŞTİRMEYİN.
 *  Projenizi tamamladıktan sonra dogrulama.php
 *  çalıştırarak doğrulama kodunuzu alın.
 * =============================================
 */

// ──────────────────────────────────────────────
//  VERİTABANI BAĞLANTI BİLGİLERİ (config.php)
//  Öğrenci: bu kısmı kendi config.php dosyanıza taşıyın!
// ──────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'magaza_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ──────────────────────────────────────────────
//  SEED FONKSİYONU — DEĞİŞTİRMEYİN
// ──────────────────────────────────────────────
function veriSetiolustur(string $ogrenci_no): array {
    mt_srand(crc32($ogrenci_no));          // Öğrenci no → deterministik tohum

    $kategoriler = ['Elektronik','Giyim','Gıda','Spor','Kozmetik','Kırtasiye','Mobilya','Oyuncak','Bahçe','Otomotiv'];
    $urunler = [];

    for ($i = 1; $i <= 20; $i++) {
        $kat    = $kategoriler[mt_rand(0, 9)];
        $fiyat  = mt_rand(50, 9990) / 10;        // 5.0 – 999.0 ₺
        $stok   = mt_rand(5, 500);
        $indirim= mt_rand(0, 40);                // %0 – %40 indirim

        $urunler[] = [
            'sira'      => $i,
            'ad'        => $kat . ' Ürün-' . str_pad($i, 2, '0', STR_PAD_LEFT),
            'kategori'  => $kat,
            'fiyat'     => round($fiyat, 2),
            'stok'      => $stok,
            'indirim'   => $indirim,
        ];
    }
    return $urunler;
}

// ──────────────────────────────────────────────
//  KURULUM FORMU VE KURULUM İŞLEMİ
// ──────────────────────────────────────────────
$mesaj = '';
$hata  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ogrenci_no = trim($_POST['ogrenci_no'] ?? '');

    if (!preg_match('/^\d{8,10}$/', $ogrenci_no)) {
        $hata = 'Lütfen geçerli bir öğrenci numarası girin (8-10 hane, yalnızca rakam).';
    } else {
        try {
            $dsn = 'mysql:host='.DB_HOST.';charset='.DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Veritabanını oluştur
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci");
            $pdo->exec("USE `".DB_NAME."`");

            // Tabloları oluştur
            $pdo->exec("CREATE TABLE IF NOT EXISTS `ogrenci_bilgi` (
                id        INT AUTO_INCREMENT PRIMARY KEY,
                no        VARCHAR(20) NOT NULL UNIQUE,
                kurulum   DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `urunler` (
                id        INT AUTO_INCREMENT PRIMARY KEY,
                ad        VARCHAR(100) NOT NULL,
                kategori  VARCHAR(50)  NOT NULL,
                fiyat     DECIMAL(10,2) NOT NULL,
                stok      INT          NOT NULL DEFAULT 0,
                indirim   INT          NOT NULL DEFAULT 0 COMMENT 'yüzde %',
                eklenme   DATETIME DEFAULT CURRENT_TIMESTAMP,
                guncelleme DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `kategoriler` (
                id    INT AUTO_INCREMENT PRIMARY KEY,
                ad    VARCHAR(50) NOT NULL UNIQUE
            ) ENGINE=InnoDB");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `siparisler` (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                musteri_adi VARCHAR(100) NOT NULL,
                urun_id     INT NOT NULL,
                adet        INT NOT NULL,
                toplam      DECIMAL(10,2) NOT NULL,
                tarih       DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
            ) ENGINE=InnoDB");

            // Daha önce kurulum yapılmış mı?
            $stmt = $pdo->prepare("SELECT id FROM ogrenci_bilgi WHERE no = ?");
            $stmt->execute([$ogrenci_no]);
            if ($stmt->fetch()) {
                $hata = 'Bu öğrenci numarası ile kurulum zaten yapılmış! Eğer veritabanını sıfırlamak istiyorsanız DROP DATABASE yapıp tekrar çalıştırın.';
            } else {
                // Kategorileri ekle
                $kats = ['Elektronik','Giyim','Gıda','Spor','Kozmetik','Kırtasiye','Mobilya','Oyuncak','Bahçe','Otomotiv'];
                $ins  = $pdo->prepare("INSERT IGNORE INTO kategoriler (ad) VALUES (?)");
                foreach ($kats as $k) $ins->execute([$k]);

                // Kişisel ürün veri setini ekle
                $veri = veriSetiolustur($ogrenci_no);
                $ins2 = $pdo->prepare("INSERT INTO urunler (ad, kategori, fiyat, stok, indirim) VALUES (?,?,?,?,?)");
                foreach ($veri as $u) {
                    $ins2->execute([$u['ad'], $u['kategori'], $u['fiyat'], $u['stok'], $u['indirim']]);
                }

                // Öğrenci kaydı
                $pdo->prepare("INSERT INTO ogrenci_bilgi (no) VALUES (?)")->execute([$ogrenci_no]);

                $mesaj = "✅ Kurulum tamamlandı! Öğrenci No: <strong>{$ogrenci_no}</strong><br>
                          Veritabanına <strong>20 benzersiz ürün</strong> eklendi.<br>
                          Şimdi projeyi geliştirmeye başlayabilirsiniz.<br><br>
                          🔒 <strong>Önemli:</strong> Bu dosyayı bir daha çalıştırmayın. 
                          Projeyi teslim ederken <code>dogrulama.php</code> sayfasındaki kodu ekran görüntüsü ile birlikte gönderin.";
            }
        } catch (PDOException $e) {
            $hata = 'Veritabanı hatası: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Proje Kurulum — Başlangıç Dosyası</title>
<style>
  body { font-family: Arial, sans-serif; max-width: 600px; margin: 60px auto; padding: 20px; background:#f4f6f9; }
  .kutu { background:#fff; border-radius:10px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,.1); }
  h2 { color:#2c3e50; }
  input[type=text] { width:100%; padding:10px; font-size:18px; border:2px solid #bdc3c7; border-radius:6px; box-sizing:border-box; }
  button { margin-top:15px; width:100%; padding:12px; background:#2980b9; color:#fff; border:none; border-radius:6px; font-size:16px; cursor:pointer; }
  button:hover { background:#1a6fa8; }
  .hata  { background:#fde8e8; color:#c0392b; padding:15px; border-radius:6px; margin-top:15px; }
  .basari{ background:#e8f8e8; color:#27ae60; padding:15px; border-radius:6px; margin-top:15px; }
  .uyari { background:#fff3cd; padding:12px; border-radius:6px; margin-top:15px; font-size:13px; color:#856404; }
</style>
</head>
<body>
<div class="kutu">
  <h2>🚀 Mağaza Projesi — Kişisel Kurulum</h2>
  <p>Öğrenci numaranızı girerek <strong>size özel veri setini</strong> oluşturun. Bu işlemi <u>yalnızca bir kez</u> yapın.</p>

  <?php if ($mesaj): ?>
    <div class="basari"><?= $mesaj ?></div>
  <?php endif; ?>
  <?php if ($hata): ?>
    <div class="hata">❌ <?= htmlspecialchars($hata) ?></div>
  <?php endif; ?>

  <?php if (!$mesaj): ?>
  <form method="POST" action="">
    <label><strong>Öğrenci Numarası:</strong></label><br><br>
    <input type="text" name="ogrenci_no" placeholder="Örn: 21140007"
           maxlength="10" pattern="\d{8,10}" required
           value="<?= htmlspecialchars($_POST['ogrenci_no'] ?? '') ?>">
    <button type="submit">Kişisel Veri Setini Oluştur ve Kurulumu Başlat</button>
  </form>
  <?php endif; ?>

  <div class="uyari">
    ⚠️ <strong>Dikkat:</strong> Başka bir öğrencinin numarasını girmek yasaktır.
    Her öğrenci kendi numarasını kullanmak zorundadır.
    Teslimatta <code>dogrulama.php</code> çıktısı ve ekran görüntüleri istenmektedir.
  </div>
</div>
</body>
</html>
