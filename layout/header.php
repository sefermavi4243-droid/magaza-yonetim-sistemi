<?php
/**
 * layout/header.php — Ortak Sayfa Başlığı ve Navigasyon
 * Her sayfanın en üstüne dahil edilir.
 */
oturumBaslat();
$admin_giris = !empty($_SESSION['admin']);
$aktif_sayfa = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($sayfa_basligi ?? 'Mağaza Yönetim Sistemi') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<nav class="nav">
  <a href="index.php" class="nav-brand">
    <span class="nav-brand-icon">🛒</span>
    <span>Mağaza</span>
  </a>

  <div class="nav-links">
    <a href="index.php"       class="nav-link <?= $aktif_sayfa === 'index'    ? 'aktif' : '' ?>">Ürünler</a>
    <a href="siparis.php"     class="nav-link <?= $aktif_sayfa === 'siparis'  ? 'aktif' : '' ?>">Sipariş Ver</a>

    <?php if ($admin_giris): ?>
      <a href="urun_ekle.php"     class="nav-link <?= $aktif_sayfa === 'urun_ekle'     ? 'aktif' : '' ?>">Ekle</a>
      <a href="siparisler.php"    class="nav-link <?= $aktif_sayfa === 'siparisler'    ? 'aktif' : '' ?>">Siparişler</a>
      <a href="logout.php"        class="nav-link nav-link-danger">Çıkış</a>
    <?php else: ?>
      <a href="login.php" class="nav-link nav-link-accent <?= $aktif_sayfa === 'login' ? 'aktif' : '' ?>">Yönetici Girişi</a>
    <?php endif; ?>
  </div>
</nav>

<main class="main">
