<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102a43">
    <title><?= esc($title) ?></title>
    <link rel="icon" href="<?= base_url('assets/images/favicon.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= base_url('assets/css/panel.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/panel.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/panel-forms.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/panel-forms.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/panel-ui.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/panel-ui.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/product-admin.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/product-admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/customer-admin.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/customer-admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/panel-controls.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/panel-controls.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/notifications.css') ?>">
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
            <?php if ($currentUser?->can('customers.view-all') || $currentUser?->can('customers.view-own')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'customers' ? 'is-active' : '' ?>" href="<?= site_url('panel/musteriler') ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg>
                    Müşteriler
                </a>
            <?php endif; ?>
            <?php if ($currentUser?->can('products.view')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'products' ? 'is-active' : '' ?>" href="<?= site_url('panel/urunler') ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8.5 12 3 3 8.5V19h18V8.5ZM12 5.35l5.83 3.56L12 12.47 6.17 8.91 12 5.35ZM5 10.69l6 3.67v5.14H5v-8.81Zm8 8.81v-5.14l6-3.67v8.81h-6Z"/></svg>
                    Ürünler
                </a>
            <?php endif; ?>
            <?php if ($currentUser?->can('orders.create') || $currentUser?->can('orders.view-all') || $currentUser?->can('orders.fulfill')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'orders' ? 'is-active' : '' ?>" href="<?= site_url('panel/siparisler') ?>">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2ZM1 2v2h2l3.6 7.59-1.35 2.45A2 2 0 0 0 7 17h12v-2H7.42a.25.25 0 0 1-.22-.37L8.1 13h7.45a2 2 0 0 0 1.75-1.03L20.88 5H5.21l-.94-2H1Zm16 16c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2Z"/></svg>
                    Teklif ve siparişler
                </a>
            <?php endif; ?>
            <?php if ($currentUser?->can('stock.manage')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'inventory' ? 'is-active' : '' ?>" href="<?= site_url('panel/stok') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 8h-3V4H3a2 2 0 0 0-2 2v11h2.18a3 3 0 0 0 5.64 0h6.36a3 3 0 0 0 5.64 0H23v-5l-3-4ZM6 18.5A1.5 1.5 0 1 1 6 15a1.5 1.5 0 0 1 0 3.5ZM15 15H8.82A3 3 0 0 0 3 15V6h12v9Zm3 3.5a1.5 1.5 0 1 1 0-3.5 1.5 1.5 0 0 1 0 3.5ZM17 12V9.5h2.5l1.5 2V12h-4Z"/></svg>Stok ve depo</a>
            <?php endif; ?>
            <?php if ($currentUser?->can('purchases.manage')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'purchases' ? 'is-active' : '' ?>" href="<?= site_url('panel/alislar') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-1 9h-5v5h-2v-5H6v-2h5V5h2v5h5v2Z"/></svg>Alışlar</a>
            <?php endif; ?>
            <?php if ($currentUser?->can('commissions.view-own') || $currentUser?->can('commissions.view-all') || $currentUser?->can('commissions.manage')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'commissions' ? 'is-active' : '' ?>" href="<?= site_url('panel/primler') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 9 8l-7 .6 5.3 4.6L5.8 20 12 16.5 18.2 20l-1.5-6.8L22 8.6 15 8l-3-7Z"/></svg>Primler</a>
            <?php endif; ?>
            <?php if ($currentUser?->can('reports.view')): ?>
                <a class="nav-item <?= ($activeNav ?? '') === 'reports' ? 'is-active' : '' ?>" href="<?= site_url('panel/raporlar') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18v-2H5V3H3Zm4 13h3V9H7v7Zm5 0h3V5h-3v11Zm5 0h3v-4h-3v4Z"/></svg>Raporlar</a>
            <?php endif; ?>
            <a class="nav-item <?= ($activeNav ?? '') === 'user-guide' ? 'is-active' : '' ?>" href="<?= site_url('panel/kullanim-rehberi') ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 4H3a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 15h-9V6h9v13ZM10 19H3V6h7v13Z"/></svg>Kullanım rehberi</a>
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
<script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>" defer></script>
<script src="<?= base_url('assets/js/notifications.js') ?>" defer></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
