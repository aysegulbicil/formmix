<?php
$menu = [
    '/'          => 'Anasayfa',
    'hakkimizda' => 'Hakkımızda',
    'urunler'    => 'Ürünler',
    'katalog'    => 'Katalog',
    'iletisim'   => 'İletişim',
];
?>
<?= $this->include('partials/topbar') ?>

<!-- Ana menü -->
<header class="site-header" id="siteHeader">
    <div class="container navbar">
        <a href="<?= site_url('/') ?>" class="brand" aria-label="<?= esc(site('brand')) ?> ana sayfa">
            <img src="<?= logo_url() ?>" alt="<?= esc(site('brand')) ?> İş Elbiseleri logo" width="200" height="47">
        </a>

        <nav class="nav-menu" aria-label="Ana menü">
            <ul>
                <?php foreach ($menu as $path => $label): ?>
                    <li><a class="<?= nav_active($path) ?>" href="<?= site_url($path) ?>"><?= esc($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="nav-actions">
            <a class="btn btn--wa btn--sm" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">
                <?= icon('whatsapp', 18) ?> <span>WhatsApp'tan Teklif Al</span>
            </a>
            <a class="nav-call" href="<?= phone_link() ?>" aria-label="Telefonla ara"><?= icon('phone', 20) ?></a>
            <button class="hamburger" id="hamburger" aria-label="Menüyü aç/kapat" aria-expanded="false" aria-controls="mobileMenu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Mobil menü -->
    <div class="mobile-menu" id="mobileMenu" hidden>
        <nav aria-label="Mobil menü">
            <ul>
                <?php foreach ($menu as $path => $label): ?>
                    <li><a class="<?= nav_active($path) ?>" href="<?= site_url($path) ?>"><?= esc($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <a class="btn btn--wa btn--block" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">WhatsApp'tan Teklif Al</a>
        <a class="btn btn--ghost btn--block" href="<?= phone_link() ?>"><?= esc(site('phoneDisplay')) ?></a>
    </div>
</header>
