# Mağaza Yönetim Sistemi

PHP ve MySQL ile geliştirilmiş basit bir mağaza yönetim uygulaması. Ürün listeleme, arama, sipariş oluşturma ve yönetici paneli üzerinden ürün yönetimi (CRUD) sağlar.

## Özellikler

- **Ürün listeleme** — arama, kategori filtresi ve sayfalama (sayfa başına 5 ürün)
- **Sipariş oluşturma** — stok kontrolü, otomatik stok düşme, indirimli fiyat hesaplama
- **Sipariş loglama** — her sipariş `logs/siparisler_log.txt` dosyasına kaydedilir
- **Yönetici paneli** — oturum tabanlı giriş/çıkış
- **Ürün yönetimi** — ürün ekleme, düzenleme ve silme
- **Sipariş listesi** — tüm siparişlerin yönetici görünümü

## Kullanılan Teknolojiler

- PHP 8+ (PDO)
- MySQL / MariaDB
- HTML, CSS

## Kurulum

1. **XAMPP** veya **Laragon** kurun, Apache ve MySQL'i başlatın.
2. Projeyi web dizinine kopyalayın:
   ```bash
   git clone https://github.com/sefermavi4243-droid/magaza-yonetim-sistemi.git
   # XAMPP:   C:\xampp\htdocs\magaza
   # Laragon: C:\laragon\www\magaza
   ```
3. phpMyAdmin'de `magaza_db` adlı bir veritabanı oluşturun ve `database/magaza_db.sql` dosyasını içe aktarın.
4. Gerekirse `config.php` içindeki veritabanı bilgilerini düzenleyin:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'magaza_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
5. Yönetici hesabı oluşturmak için tarayıcıda açın:
   ```
   http://localhost/magaza/kurulum_admin.php
   ```
6. Uygulamayı açın:
   ```
   http://localhost/magaza/index.php
   ```

## Proje Yapısı

```
magaza/
├── config.php            # Veritabanı bağlantı ayarları
├── functions.php         # Yardımcı fonksiyonlar
├── index.php             # Ürün listeleme, arama, sayfalama
├── login.php / logout.php
├── siparis.php           # Sipariş oluşturma
├── siparisler.php        # Sipariş listesi (yönetici)
├── urun_ekle.php         # Ürün ekleme (yönetici)
├── urun_duzenle.php      # Ürün düzenleme (yönetici)
├── urun_sil.php          # Ürün silme (yönetici)
├── kurulum_admin.php     # Yönetici hesabı oluşturma
├── basla.php             # Örnek veri seti oluşturma
├── layout/               # Ortak header ve footer
├── assets/style.css      # Stil dosyası
├── database/             # Veritabanı dökümü
└── logs/                 # Sipariş logları
```

## Veritabanı

| Tablo | Açıklama |
|-------|----------|
| `urunler` | Ürün adı, kategori, fiyat, stok, indirim |
| `kategoriler` | Ürün kategorileri |
| `siparisler` | Müşteri adı, ürün (`urun_id` → `urunler.id`), adet, toplam, tarih |
| `yonetici` | Yönetici kullanıcı adı ve bcrypt şifre hash'i |

## Güvenlik

- **SQL Injection:** tüm sorgularda PDO prepared statements
- **XSS:** tüm çıktılarda `htmlspecialchars()`
- **Şifreler:** `password_hash()` (bcrypt) ve `password_verify()`
- **Yetkilendirme:** yönetici sayfaları session kontrolü ile korunur
