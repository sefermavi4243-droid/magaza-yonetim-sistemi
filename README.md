# Mağaza Yönetim Sistemi — Vize Projesi

## Proje Klasör Yapısı

```
magaza/
│
├── config.php              # Veritabanı bağlantı bilgileri ve baglan() fonksiyonu
├── functions.php           # Kullanıcı tanımlı fonksiyonlar Soru 3
├── index.php               # Ürün listeleme + arama GET formu, sayfalama
├── login.php               # Yönetici girişi POST formu, session
├── logout.php              # Oturum kapatma
├── siparis.php             # Sipariş oluşturma POST formu
├── siparisler.php          # Sipariş listesi Yönetici
├── urun_ekle.php           # Ürün ekleme — CRUD Create yönetici
├── urun_duzenle.php        # Ürün güncelleme — CRUD Update yönetici
├── urun_sil.php            # Ürün silme — CRUD Delete yönetici
├── kurulum_admin.php       # Bir kez çalıştırılır; yönetici hesabı oluşturur
│
├── layout/
│   ├── header.php          # Ortak sayfa başlığı ve navigasyon
│   └── footer.php          # Ortak sayfa altlığı
│
├── assets/
│   └── style.css           # Proje genelinde kullanılan CSS
│
├── logs/
│   └── siparisler_log.txt  # Her siparişin tarih/müşteri/toplam kaydı SORU 4
│
├── database/
│   └── magaza_db.sql       # Örnek veritabanı (phpMyAdmin ile içe aktarılır)
│
├── basla.php               # Kişisel veri seti kurulumu
└── dogrulama.php           # Proje doğrulama kodu üretici
```

---

## HTTP GET ve POST Farkı

### GET Metodu

- Veri **URL'e eklenerek** sunucuya iletilir: `?arama=Elektronik&sayfa=2`
- **Veri değiştirmeyen** (yalnızca sorgulayan) işlemlerde kullanılır.
- Sayfayı yenilemek, yer işareti eklemek veya bağlantıyı paylaşmak **güvenlidir**.
- URL uzunluğu sınırlıdır (genellikle ~2000 karakter); büyük veri taşıyamaz.
- Tarayıcı geçmişine ve sunucu loglarına kaydedilir.

**Projede GET kullanan sayfalar:**
| Sayfa | Neden GET? |
|-------|-----------|
| `index.php` | Arama/filtreleme; paylaşılabilir URL gerekli |
| `urun_sil.php` (link) | Basit ID parametresi; **dikkat:** gerçek projede POST/CSRF token önerilir |
| `urun_duzenle.php` (ilk açılış) | Düzenlenecek ürünü ID ile getirme |

### POST Metodu

- Veri **istek gövdesinde** (body) iletilir; URL'de görünmez.
- **Veri değiştiren** (yan etkili) işlemlerde zorunludur.
- Şifre gibi hassas bilgiler URL'de ve tarayıcı geçmişinde kalmaz.
- Büyük veri (dosya yükleme dahil) taşıyabilir; boyut sınırı daha yüksektir.
- Tarayıcı "Sayfayı yenile" uyarısı gösterir (çift gönderim önlenir).

**Projede POST kullanan sayfalar:**
| Sayfa | Neden POST? |
|-------|-----------|
| `login.php` | Şifre hassas; oturum oluşturma yan etkili |
| `urun_ekle.php` | Veritabanına yeni kayıt eklenir |
| `urun_duzenle.php` | Mevcut kayıt güncellenir |
| `siparis.php` | Sipariş eklenir, stok azaltılır, log yazılır |

---

## Veritabanı Tasarımı Soru 5

### Tablolar ve İlişkiler

```
kategoriler          urunler                    siparisler
──────────           ────────────────────────   ──────────────────────────
id   INT PK          id         INT PK           id          INT PK
ad   VARCHAR(50)     ad         VARCHAR(100)     musteri_adi VARCHAR(100)
                     kategori   VARCHAR(50)      urun_id     INT FK → urunler.id
                     fiyat      DECIMAL(10,2)    adet        INT
                     stok       INT              toplam      DECIMAL(10,2)
                     indirim    INT              tarih       DATETIME
                     eklenme    DATETIME
                     guncelleme DATETIME

yonetici
────────────────────
id            INT PK
kullanici_adi VARCHAR(50)
sifre_hash    VARCHAR(255)   ← password_hash(BCrypt)
olusturma     DATETIME
```

### Normalizasyon 2NF

- Her tablo tek bir konuyu temsil eder (ürünler, siparişler, kategoriler).
- `siparisler` tablosunda ürün adı **tekrarlanmaz**; `urun_id` FK ile bağlanır.
- Kısmi bağımlılık yoktur: tüm alan değerleri yalnızca kendi tablolarının PK'sine bağlıdır.
- Yabancı anahtar: `siparisler.urun_id → urunler.id` (`ON DELETE CASCADE`)

---

## Güvenlik Önlemleri Soru 7

| Tehdit | Önlem |
|--------|-------|
| SQL Injection | Tüm sorgularda **PDO Prepared Statements** |
| XSS | Tüm çıktılarda `htmlspecialchars()` (`e()` yardımcı fonksiyonu) |
| Şifre güvenliği | `password_hash(PASSWORD_BCRYPT)` ile hashleme, `password_verify()` ile doğrulama |
| Yetkisiz erişim | `adminKontrol()` session denetimi; korumalı sayfalar login'e yönlendirir |

---

## Çalışma Ortamı Kurulumu Soru 1

### XAMPP ile:

```bash
# 1. XAMPP'ı başlatın → Apache ve MySQL'i çalıştırın
# 2. Proje klasörünü kopyalayın:
cp -r magaza/ /Applications/XAMPP/htdocs/    # macOS
# veya
cp -r magaza/ C:\xampp\htdocs\               # Windows

# 3. Tarayıcıda açın:
http://localhost/magaza/basla.php            # Önce kişisel veri setini oluşturun
http://localhost/magaza/kurulum_admin.php    # Yönetici hesabı oluşturun
http://localhost/magaza/index.php            # Ana sayfa
```

### Laragon ile:

```bash
# Proje klasörünü buraya kopyalayın:
C:\laragon\www\magaza\

# Laragon otomatik sanal host oluşturur:
http://magaza.test/basla.php
```

---

## Kullanıcı Tanımlı Fonksiyonlar Özeti Soru 3

| Fonksiyon | Açıklama |
|-----------|---------|
| `urunListele($arama, $kategori, $sayfa, $limit)` | Veritabanından sayfalı ürün listesi döndürür |
| `stokKontrol($urun_id, $adet)` | Sipariş için stok yeterliliğini denetler |
| `fiyatHesapla($fiyat, $adet)` | Birim fiyat × adet toplamı |
| `indirimUygula($fiyat, $indirim_yuzde)` | İndirimli fiyatı hesaplar |
| `kategoriFiltrele($urunler, $kategori)` | `array_filter()` ile kategori süzgeci |
| `tumKategoriler()` | İlişkisel dizi olarak kategoriler |
| `siparisLogYaz($musteri, $toplam, $urun)` | `siparisler_log.txt`'ye kayıt ekler |
| `fiyataGoreSirala(&$urunler, $artanSira)` | `usort()` ile fiyat sıralaması |
| `adminKontrol()` | Session yoksa `login.php`'ye yönlendirir |
| `e($str)` | `htmlspecialchars()` kısayolu (XSS önleme) |

---

## Teslim Kontrol Listesi

- [ ] `basla.php` çalıştırıldı, 20 ürün eklendi
- [ ] `kurulum_admin.php` çalıştırıldı, yönetici hesabı oluşturuldu
- [ ] Ürün listeleme + arama çalışıyor
- [ ] Sayfalama 5 kayıt/sayfa çalışıyor
- [ ] Yönetici girişi/çıkışı çalışıyor
- [ ] Ürün Ekle / Düzenle / Sil çalışıyor
- [ ] Sipariş oluşturma çalışıyor, stok azalıyor
- [ ] `logs/siparisler_log.txt` yazılıyor
- [ ] `dogrulama.php` çalıştırıldı, ekran görüntüsü alındı
- [ ] `magaza_db.sql` phpMyAdmin'den dışa aktarıldı
- [ ] `ogrenci_no.zip` hazırlandı

