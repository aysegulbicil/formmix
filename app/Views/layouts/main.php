<?php
$brand     = site('brand');
$metaTitle = $title ?? ($brand . ' | Kurumsal Baskılı İş Kıyafetleri');
$metaDesc  = $description ?? site('defaultDescription');
$bodyClass = $bodyClass ?? '';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= esc($metaTitle) ?></title>
    <meta name="description" content="<?= esc($metaDesc) ?>">
    <link rel="canonical" href="<?= current_url() ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= esc($brand) ?>">
    <meta property="og:title" content="<?= esc($metaTitle) ?>">
    <meta property="og:description" content="<?= esc($metaDesc) ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:image" content="<?= asset('images/og-cover.svg') ?>">
    <meta name="theme-color" content="#0E2A47">

    <!-- Favicon -->
    <link rel="icon" href="<?= asset('images/favicon.ico') ?>" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Stil -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=1.9">
    <link rel="stylesheet" href="<?= asset('css/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/notifications.css') ?>">

    <script src="<?= asset('js/main.js') ?>?v=1.9" defer></script>
    <script src="<?= asset('js/sweetalert2.all.min.js') ?>" defer></script>
    <script src="<?= asset('js/notifications.js') ?>" defer></script>
</head>
<body class="<?= esc($bodyClass) ?>">

    <?= $this->include('partials/header') ?>

    <main id="main">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('partials/footer') ?>
    <?= $this->include('partials/whatsapp_float') ?>

</body>
</html>
