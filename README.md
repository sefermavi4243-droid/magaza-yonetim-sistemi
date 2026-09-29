# Mağaza Yönetim Sistemi

PHP ve MySQL ile geliştirilmiş basit bir mağaza yönetim uygulaması. Ürün listeleme, arama, sipariş oluşturma ve yönetici paneli üzerinden ürün yönetimi (CRUD) sağlar.

## Özellikler

- **Ürün listeleme** — arama, kategori filtresi ve sayfalama (sayfa başına 5 ürün)
- **Sipariş oluşturma** — stok kontrolü, otomatik stok düşme, indirimli fiyat hesaplama (transaction ile güvenli)
- **Sipariş loglama** — her sipariş `logs/siparisler_log.txt` dosyasına kaydedilir
- **Yönetici paneli** — oturum tabanlı giriş/çıkış
- **Ürün yönetimi** — ürün ekleme, düzenleme ve silme
- **Sipariş listesi** — tüm siparişlerin yönetici görünümü

## Kullanılan Teknolojiler

- PHP 8+ (PDO)
- MySQL / MariaDB
- HTML, CSS
- Docker, Docker Compose

## Kurulum (Docker — önerilen)

Gereksinim: [Docker](https://docs.docker.com/get-docker/) ve Docker Compose.

```bash
git clone https://github.com/sefermavi4243-droid/magaza-yonetim-sistemi.git
cd magaza-yonetim-sistemi
cp .env.example .env        # isteğe bağlı: port ve şifreleri değiştirin
docker compose up -d --build
```

| Servis | Adres |
|--------|-------|
| Uygulama | http://localhost:8080 |
| Yönetici hesabı oluşturma (ilk kurulumda bir kez) | http://localhost:8080/kurulum_admin.php |
| phpMyAdmin | http://localhost:8081 (kullanıcı: `magaza`, şifre: `magaza123`) |

Veritabanı ilk açılışta `database/magaza_db.sql` ile otomatik doldurulur.

```bash
docker compose logs -f      # logları izle
docker compose down         # durdur
docker compose down -v      # durdur ve veritabanını sıfırla
```

## Kurulum (XAMPP / Laragon)

1. **XAMPP** veya **Laragon** kurun, Apache ve MySQL'i başlatın.
2. Projeyi web dizinine kopyalayın:
   ```bash
   git clone https://github.com/sefermavi4243-droid/magaza-yonetim-sistemi.git
   # XAMPP:   C:\xampp\htdocs\magaza
   # Laragon: C:\laragon\www\magaza
   ```
3. phpMyAdmin'de `magaza_db` adlı bir veritabanı oluşturun ve `database/magaza_db.sql` dosyasını içe aktarın.
4. Veritabanı bilgileri `config.php` içinde; ortam değişkeni (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) yoksa XAMPP varsayılanları (`localhost`, `root`, boş şifre) kullanılır.
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
├── layout/               # Ortak header ve footer
├── assets/style.css      # Stil dosyası
├── database/             # Veritabanı dökümü
├── logs/                 # Sipariş logları
├── Dockerfile            # PHP 8.2 + Apache imajı
├── docker-compose.yml    # Uygulama + MariaDB + phpMyAdmin
└── .env.example          # Örnek ortam değişkenleri
```

## Veritabanı

| Tablo | Açıklama |
|-------|----------|
| `urunler` | Ürün adı, kategori, fiyat, stok, indirim |
| `kategoriler` | Ürün kategorileri |
| `siparisler` | Müşteri adı, ürün (`urun_id` → `urunler.id`), adet, toplam, tarih |
| `yonetici` | Yönetici kullanıcı adı ve bcrypt şifre hash'i |

## Güvenlik

| Tehdit | Önlem |
|--------|-------|
| SQL Injection | Tüm sorgularda PDO prepared statements |
| XSS | Tüm çıktılarda `htmlspecialchars()` (`e()` yardımcısı) |
| CSRF | Tüm POST formlarında oturuma bağlı token (`hash_equals` ile doğrulama) |
| Şifre güvenliği | `password_hash()` (bcrypt) ve `password_verify()` |
| Session fixation | Girişte `session_regenerate_id(true)`; çerezler `HttpOnly` + `SameSite=Lax` |
| Yetkisiz erişim | Yönetici sayfaları session kontrolüyle korunur; silme yalnızca POST |
| Eş zamanlı sipariş | Transaction + `SELECT ... FOR UPDATE` ile stok eksiye düşmez |
| Kurulum sayfası | İlk yönetici oluşturulduktan sonra `kurulum_admin.php` kilitlenir |
| Bilgi sızıntısı | Veritabanı hataları kullanıcıya gösterilmez, sunucu loguna yazılır |
| Log erişimi | `logs/` klasörü web üzerinden erişime kapalı |
