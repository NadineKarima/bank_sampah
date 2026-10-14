<?php
/**
 * Shell halaman (dipakai semua halaman internal: admin, pengepul, guru & siswa).
 * Urutan include di tiap halaman: header.php -> sidebar_xxx.php -> <main>...</main> -> footer.php
 */
$logo = ($asset_path ?? '') . 'assets/img/bank_sampah_icon.png';
$nama_user  = $_SESSION['nama'] ?? '';
$role_user  = $_SESSION['role'] ?? '';
$inisial    = $nama_user !== '' ? strtoupper(substr($nama_user, 0, 1)) : 'U';
$theme_key  = is_logged_in() ? 'bs_theme_user_' . (int)($_SESSION['id_user'] ?? 0) : 'bs_theme_guest';
?>
<!DOCTYPE html>
<html lang="id" data-theme-key="<?= clean($theme_key) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? clean($page_title) . ' - ' : '' ?>Bank Sampah</title>
    <link rel="icon" type="image/png" href="<?= $logo ?>">
    <link href="<?= $asset_path ?? '' ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $asset_path ?? '' ?>assets/css/style.css" rel="stylesheet">
    <script>
        // Set tema sebelum halaman dirender, biar tidak "kedip" putih.
        (function () {
            var theme = localStorage.getItem('<?= $theme_key ?>') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
<header class="app-topbar">
    <button class="icon-btn sidebar-toggle" id="sidebarToggle" type="button" aria-label="Tutup atau buka menu" title="Tutup / buka sidebar">
        <?= icon('menu') ?>
    </button>

    <div class="topbar-brand">
        <img src="<?= $logo ?>" alt="Logo Bank Sampah">
        <div class="topbar-brand-text">
            <span class="brand-name">Bank Sampah</span>
            <span class="brand-sub"><?= isset($page_title) ? clean($page_title) : 'Sistem Informasi' ?></span>
        </div>
    </div>

    <div class="topbar-right">
        <button class="icon-btn theme-toggle" id="themeToggle" type="button" aria-label="Ganti tema" title="Mode gelap/terang">
            <span class="icon-sun"><?= icon('sun') ?></span>
            <span class="icon-moon"><?= icon('moon') ?></span>
        </button>
        <?php if (is_logged_in()): ?>
        <div class="topbar-user">
            <div class="avatar"><?= clean($inisial) ?></div>
            <div class="user-meta d-none d-sm-flex">
                <span class="user-name"><?= clean($nama_user) ?></span>
                <span class="user-role"><?= clean(ucfirst($role_user)) ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</header>
<div class="app-shell">
