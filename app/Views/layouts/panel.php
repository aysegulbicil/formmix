<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102a43">
    <title><?= esc($title) ?></title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/css/panel.css') ?>">
</head>
<body>
<?php $currentUser = auth()->user(); ?>
<div class="app-shell">
    <aside class="sidebar" id="sidebar" aria-label="Panel menüsü">
        <div class="sidebar__brand">
            <a href="<?= site_url('panel') ?>" aria-label="FORMMIX panel ana sayfa">
                <img src="<?= base_url('assets/images/logo-white.svg') ?>" alt="FORMMIX">
            </a>
            <span>İş Yönetimi</span>
        </div>

        <nav class="sidebar__nav">
            <p class="nav-label">Çalışma alanı</p>
            <a class="nav-item <?= ($activeNav ?? '') === 'dashboard' ? 'is-active' : '' ?>" href="<?= site_url('panel') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z"/></svg>
                Genel bakış
            </a>
            <?php if ($currentUser?->can('employees.view')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'employees' ? 'is-active' : '' ?>" href="<?= site_url('panel/personel') ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3Zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg>
                    Personel
                </a>
            <?php endif; ?>
            <div class="nav-item is-disabled" aria-disabled="true">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg>
                Müşteriler <small>Yakında</small>
            </div>
        </nav>

        <div class="sidebar__user">
            <span class="avatar"><?= esc(mb_strtoupper(mb_substr($currentUser->email ?? 'F', 0, 1))) ?></span>
            <span class="sidebar__user-copy"><strong><?= esc($currentUser->email ?? 'Kullanıcı') ?></strong><small>Güvenli oturum</small></span>
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="icon-button icon-button--dark" type="submit" title="Güvenli çıkış" aria-label="Güvenli çıkış">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17v-2h4V9h-4V7l-5 5 5 5Zm9-12h-7v2h7v10h-7v2h7c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2Z"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <button class="sidebar-overlay" type="button" data-sidebar-close aria-label="Menüyü kapat"></button>

    <div class="app-main">
        <header class="top-header">
            <button class="icon-button menu-toggle" type="button" data-sidebar-open aria-controls="sidebar" aria-label="Menüyü aç">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 18h18v-2H3v2Zm0-5h18v-2H3v2Zm0-7v2h18V6H3Z"/></svg>
            </button>
            <div>
                <p class="top-header__eyebrow">FORMMIX</p>
                <h1><?= esc($pageTitle ?? 'Yönetim paneli') ?></h1>
            </div>
            <div class="top-header__date"><?= esc(date('d.m.Y')) ?></div>
        </header>

        <main class="content">
            <?php if (session('message')): ?>
                <div class="alert alert--success" role="status">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 16.2-4.2-4.2-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z"/></svg>
                    <?= esc(session('message')) ?>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
<script src="<?= base_url('assets/js/panel.js') ?>" defer></script>
</body>
</html>
