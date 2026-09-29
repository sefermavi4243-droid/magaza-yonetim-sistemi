-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost
-- Üretim Zamanı: 10 May 2026, 22:11:06
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `magaza_db`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategoriler`
--

CREATE TABLE `kategoriler` (
  `id` int(11) NOT NULL,
  `ad` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kategoriler`
--

INSERT INTO `kategoriler` (`id`, `ad`) VALUES
(9, 'Bahçe'),
(1, 'Elektronik'),
(3, 'Gıda'),
(2, 'Giyim'),
(6, 'Kırtasiye'),
(5, 'Kozmetik'),
(7, 'Mobilya'),
(10, 'Otomotiv'),
(8, 'Oyuncak'),
(4, 'Spor');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenci_bilgi`
--

CREATE TABLE `ogrenci_bilgi` (
  `id` int(11) NOT NULL,
  `no` varchar(20) NOT NULL,
  `kurulum` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `ogrenci_bilgi`
--


-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparisler`
--

CREATE TABLE `siparisler` (
  `id` int(11) NOT NULL,
  `musteri_adi` varchar(100) NOT NULL,
  `urun_id` int(11) NOT NULL,
  `adet` int(11) NOT NULL,
  `toplam` decimal(10,2) NOT NULL,
  `tarih` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `siparisler`
--


-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `fiyat` decimal(10,2) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `indirim` int(11) NOT NULL DEFAULT 0 COMMENT 'yüzde %',
  `eklenme` datetime DEFAULT current_timestamp(),
  `guncelleme` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `ad`, `kategori`, `fiyat`, `stok`, `indirim`, `eklenme`, `guncelleme`) VALUES
(1, 'Spor Ürün-01', 'Spor', 451.40, 100, 11, '2026-05-10 19:55:07', '2026-05-10 22:54:27'),
(2, 'Oyuncak Ürün-02', 'Oyuncak', 596.70, 160, 14, '2026-05-10 19:55:07', '2026-05-10 22:56:35'),
(3, 'Otomotiv Ürün-03', 'Otomotiv', 493.20, 434, 22, '2026-05-10 19:55:07', NULL),
(4, 'Otomotiv Ürün-04', 'Otomotiv', 13.10, 470, 5, '2026-05-10 19:55:07', NULL),
(5, 'Kırtasiye Ürün-05', 'Kırtasiye', 678.50, 329, 4, '2026-05-10 19:55:07', '2026-05-10 22:56:03'),
(6, 'Bahçe Ürün-06', 'Bahçe', 463.80, 32, 38, '2026-05-10 19:55:07', '2026-05-10 22:56:45'),
(7, 'Bahçe Ürün-07', 'Bahçe', 547.60, 24, 12, '2026-05-10 19:55:07', '2026-05-10 22:53:48'),
(8, 'Spor Ürün-08', 'Spor', 153.40, 104, 27, '2026-05-10 19:55:07', '2026-05-10 22:56:14'),
(9, 'Elektronik Ürün-09', 'Elektronik', 638.50, 77, 7, '2026-05-10 19:55:07', '2026-05-10 22:52:48'),
(10, 'Spor Ürün-10', 'Spor', 150.00, 427, 39, '2026-05-10 19:55:07', '2026-05-10 22:57:00'),
(11, 'Mobilya Ürün-11', 'Mobilya', 47.30, 4, 4, '2026-05-10 19:55:07', '2026-05-10 22:58:42'),
(12, 'Kozmetik Ürün-12', 'Kozmetik', 263.60, 294, 23, '2026-05-10 19:55:07', NULL),
(13, 'Elektronik Ürün-13', 'Elektronik', 845.50, 143, 39, '2026-05-10 19:55:07', '2026-05-10 22:57:09'),
(14, 'Otomotiv Ürün-14', 'Otomotiv', 756.70, 320, 5, '2026-05-10 19:55:07', '2026-05-10 22:55:45'),
(15, 'Elektronik Ürün-15', 'Elektronik', 482.00, 21, 31, '2026-05-10 19:55:07', '2026-05-10 22:55:27'),
(16, 'Spor Ürün-16', 'Spor', 210.40, 77, 36, '2026-05-10 19:55:07', '2026-05-10 22:54:02'),
(17, 'Otomotiv Ürün-17', 'Otomotiv', 841.40, 53, 29, '2026-05-10 19:55:07', '2026-05-10 22:53:19'),
(18, 'Bahçe Ürün-18', 'Bahçe', 658.70, 160, 8, '2026-05-10 19:55:07', NULL),
(19, 'Gıda Ürün-19', 'Gıda', 748.00, 430, 24, '2026-05-10 19:55:07', '2026-05-10 22:58:13'),
(20, 'Bahçe Ürün-20', 'Bahçe', 822.60, 0, 34, '2026-05-10 19:55:07', '2026-05-10 22:53:37'),
(21, 'Raspberry Pi 5 - 8GB', 'Elektronik', 10000.00, 80, 0, '2026-05-10 22:59:46', NULL),
(22, 'Arduino UNO R3', 'Elektronik', 1500.00, 500, 0, '2026-05-10 23:00:40', NULL),
(23, 'Raspberry Pi Pico 2', 'Elektronik', 350.00, 1000, 0, '2026-05-10 23:00:55', NULL),
(24, 'Elegoo OrangeStorm Giga 3D Yazıcı', 'Elektronik', 181000.00, 8, 5, '2026-05-10 23:01:10', '2026-05-10 23:09:27'),
(25, 'Acoms Technisport V - 2 Kanal Kumanda Sistemi', 'Elektronik', 2216.00, 53, 20, '2026-05-10 23:01:36', NULL),
(26, 'Kurtel Tüp Lehim Sn60/40', 'Elektronik', 220.00, 200, 0, '2026-05-10 23:03:05', NULL),
(27, '1 Kanal 5V Röle Kartı -', 'Elektronik', 85.00, 450, 30, '2026-05-10 23:03:21', '2026-05-10 23:09:08'),
(28, 'tinylab 3D 1.75 mm Bordo PLA Filament', 'Elektronik', 641.00, 209, 15, '2026-05-10 23:03:42', NULL),
(29, 'Raspberry Pi Zero W Header', 'Elektronik', 928.00, 150, 0, '2026-05-10 23:04:08', NULL),
(30, 'ESP8266 3dBi WiFi Anten Seti', 'Elektronik', 420.00, 500, 40, '2026-05-10 23:04:25', '2026-05-10 23:09:15'),
(31, 'Antenna Home AHCG.212 SMA Erkek', 'Elektronik', 40.80, 1000, 0, '2026-05-10 23:04:45', NULL),
(32, 'XBLW L293D DIP-16 Çift H-Köprü DC Motor Sürücü Entegresi', 'Elektronik', 36.00, 900, 0, '2026-05-10 23:05:01', NULL),
(33, 'Raspberry Pi Zero W Header', 'Elektronik', 928.00, 50, 18, '2026-05-10 23:05:22', NULL),
(34, 'Anycubic Photon Mono M7 Pro', 'Elektronik', 25474.00, 10, 5, '2026-05-10 23:05:55', NULL),
(35, 'Raspberry Pi Pico 2', 'Elektronik', 361.72, 50, 0, '2026-05-10 23:06:06', NULL),
(36, '350W 12V 29A Güç Kaynağı', 'Elektronik', 2022.00, 65, 30, '2026-05-10 23:06:48', NULL),
(37, 'Minix NGC-7 UP Mini Bilgisayar', 'Elektronik', 35354.00, 25, 7, '2026-05-10 23:07:05', NULL),
(38, 'PicoBricks Base Kit', 'Elektronik', 3804.00, 34, 10, '2026-05-10 23:07:25', NULL),
(39, 'Suya Dayanıklı Ultrasonik Mesafe Ölçüm Sensörü 300Khz', 'Elektronik', 356.00, 150, 0, '2026-05-10 23:07:47', NULL),
(40, 'RP100B 3D Baskı Kalemi', 'Elektronik', 551.41, 50, 13, '2026-05-10 23:08:04', NULL),
(41, '4.5 mm x 8 mm Mini Kablolu Titreşim Motoru - Silikon Kılıflı', 'Elektronik', 47.97, 500, 3, '2026-05-10 23:08:33', NULL),
(42, '3-6 V DC Motor Hobi ve Oyuncak Motoru', 'Elektronik', 10.00, 800, 0, '2026-05-10 23:08:53', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yonetici`
--

CREATE TABLE `yonetici` (
  `id` int(11) NOT NULL,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre_hash` varchar(255) NOT NULL,
  `olusturma` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `yonetici`
--


--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `kategoriler`
--
ALTER TABLE `kategoriler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ad` (`ad`);

--
-- Tablo için indeksler `ogrenci_bilgi`
--
ALTER TABLE `ogrenci_bilgi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no` (`no`);

--
-- Tablo için indeksler `siparisler`
--
ALTER TABLE `siparisler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `urun_id` (`urun_id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `yonetici`
--
ALTER TABLE `yonetici`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `kategoriler`
--
ALTER TABLE `kategoriler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Tablo için AUTO_INCREMENT değeri `ogrenci_bilgi`
--
ALTER TABLE `ogrenci_bilgi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `siparisler`
--
ALTER TABLE `siparisler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Tablo için AUTO_INCREMENT değeri `yonetici`
--
ALTER TABLE `yonetici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `siparisler`
--
ALTER TABLE `siparisler`
  ADD CONSTRAINT `siparisler_ibfk_1` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
