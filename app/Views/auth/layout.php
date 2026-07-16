<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102a43">
    <title><?= $this->renderSection('title') ?> | FORMMIX</title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/notifications.css') ?>">
</head>
<body>
    <main class="auth-page">
        <?= $this->renderSection('main') ?>
    </main>
    <script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>" defer></script>
    <script src="<?= base_url('assets/js/notifications.js') ?>" defer></script>
</body>
</html>
