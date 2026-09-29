<?php
/**
 * functions.php — Yardımcı ve iş mantığı fonksiyonları
 */

require_once __DIR__ . '/config.php';

// ─────────────────────────────────────────────────────────────
//  1) urunListele() — Veritabanından ürün listesi döndürür
// ─────────────────────────────────────────────────────────────
/**
 * @param  string $arama    Ürün adı veya kategoride aranacak kelime
 * @param  string $kategori Belirli bir kategoriye göre filtre
 * @param  int    $sayfa    Sayfalama için sayfa numarası
 * @param  int    $limit    Sayfa başına kayıt sayısı
 * @return array  ['urunler' => [...], 'toplam' => int]
 */
function urunListele(string $arama = '', string $kategori = '', int $sayfa = 1, int $limit = 5): array {
    $pdo    = baglan();
    $offset = ($sayfa - 1) * $limit;

    $where  = [];
    $params = [];

    if ($arama !== '') {
        $where[]  = '(ad LIKE :arama OR kategori LIKE :arama2)';
        $params[':arama']  = '%' . $arama . '%';
        $params[':arama2'] = '%' . $arama . '%';
    }

    if ($kategori !== '') {
        $where[]           = 'kategori = :kategori';
        $params[':kategori'] = $kategori;
    }

    $kosul = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Toplam kayıt sayısı
    $countSql  = "SELECT COUNT(*) FROM urunler $kosul";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $toplam = (int)$countStmt->fetchColumn();

    // Sayfalı liste
    $sql  = "SELECT * FROM urunler $kosul ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return [
        'urunler' => $stmt->fetchAll(),
        'toplam'  => $toplam,
    ];
}

// ─────────────────────────────────────────────────────────────
//  2) stokKontrol() — Sipariş için stok yeterliliğin denetlenmesi
// ─────────────────────────────────────────────────────────────
/**
 * @param  int  $urun_id
 * @param  int  $adet
 * @return bool True → stok yeterli
 */
function stokKontrol(int $urun_id, int $adet): bool {
    $pdo  = baglan();
    $stmt = $pdo->prepare('SELECT stok FROM urunler WHERE id = :id');
    $stmt->execute([':id' => $urun_id]);
    $urun = $stmt->fetch();
    if (!$urun) return false;
    return $urun['stok'] >= $adet;
}

// ─────────────────────────────────────────────────────────────
//  3) fiyatHesapla() — Birim × adet toplamı
// ─────────────────────────────────────────────────────────────
/**
 * @param  float $fiyat
 * @param  int   $adet
 * @return float Toplam tutar (indirim uygulanmamış)
 */
function fiyatHesapla(float $fiyat, int $adet): float {
    return round($fiyat * $adet, 2);
}

// ─────────────────────────────────────────────────────────────
//  4) indirimUygula() — İndirim yüzdesini fiyata uygular
// ─────────────────────────────────────────────────────────────
/**
 * @param  float $fiyat
 * @param  int   $indirim_yuzde  0-100 arası
 * @return float İndirimli fiyat
 */
function indirimUygula(float $fiyat, int $indirim_yuzde): float {
    if ($indirim_yuzde < 0 || $indirim_yuzde > 100) return $fiyat;
    return round($fiyat * (1 - $indirim_yuzde / 100), 2);
}

// ─────────────────────────────────────────────────────────────
//  5) kategoriFiltrele() — Ürünleri verilen kategoriye göre süzer
// ─────────────────────────────────────────────────────────────
/**
 * @param  array  $urunler   urunListele() çıktısındaki 'urunler' dizisi
 * @param  string $kategori
 * @return array  Filtrelenmiş ürünler
 */
function kategoriFiltrele(array $urunler, string $kategori): array {
    return array_filter($urunler, function ($u) use ($kategori) {
        return strtolower($u['kategori']) === strtolower($kategori);
    });
}

// ─────────────────────────────────────────────────────────────
//  Yardımcı: Tüm kategorileri ilişkisel dizi olarak döndürür
// ─────────────────────────────────────────────────────────────
/**
 * @return array  ['Elektronik' => 'Elektronik', 'Giyim' => 'Giyim', ...]
 */
function tumKategoriler(): array {
    $pdo  = baglan();
    $stmt = $pdo->query('SELECT ad FROM kategoriler ORDER BY ad');
    $liste = [];
    foreach ($stmt->fetchAll() as $row) {
        $liste[$row['ad']] = $row['ad'];
    }
    return $liste;
}

// ─────────────────────────────────────────────────────────────
//  Yardımcı: Sipariş log dosyasına kayıt ekler
// ─────────────────────────────────────────────────────────────
function siparisLogYaz(string $musteri, float $toplam, string $urun_adi): void {
    $log_dir  = __DIR__ . '/logs';
    $log_file = $log_dir . '/siparisler_log.txt';

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $satir = sprintf(
        "[%s] Müşteri: %-25s | Ürün: %-30s | Toplam: %s ₺\n",
        date('d.m.Y H:i:s'),
        $musteri,
        $urun_adi,
        number_format($toplam, 2, '.', ',')
    );

    file_put_contents($log_file, $satir, FILE_APPEND | LOCK_EX);
}

// ─────────────────────────────────────────────────────────────
//  Yardımcı: XSS güvenli çıktı
// ─────────────────────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ─────────────────────────────────────────────────────────────
//  Yardımcı: Oturum kontrolü — Yönetici değilse yönlendir
// ─────────────────────────────────────────────────────────────
function adminKontrol(): void {
    oturumBaslat();
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}

// ─────────────────────────────────────────────────────────────
//  Yardımcı: usort ile ürünleri fiyata göre sıralar
// ─────────────────────────────────────────────────────────────
function fiyataGoreSirala(array &$urunler, bool $artanSira = true): void {
    usort($urunler, function ($a, $b) use ($artanSira) {
        return $artanSira
            ? $a['fiyat'] <=> $b['fiyat']
            : $b['fiyat'] <=> $a['fiyat'];
    });
}

// ─────────────────────────────────────────────────────────────
//  Güvenlik: Oturum, CSRF ve hata yönetimi
// ─────────────────────────────────────────────────────────────

/** Güvenli çerez ayarlarıyla oturumu başlatır. */
function oturumBaslat(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

/** Oturuma ait CSRF token'ını döndürür (yoksa üretir). */
function csrfToken(): string {
    oturumBaslat();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Formlara eklenecek gizli CSRF alanı. */
function csrfAlan(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** POST isteğindeki CSRF token'ını doğrular. */
function csrfGecerli(): bool {
    oturumBaslat();
    $gelen = $_POST['csrf_token'] ?? '';
    return is_string($gelen) && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $gelen);
}

/** Hatayı sunucu loguna yazar, kullanıcıya genel bir mesaj döndürür. */
function hataKaydet(Throwable $e, string $mesaj = 'Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin.'): string {
    error_log('[magaza] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    return $mesaj;
}
