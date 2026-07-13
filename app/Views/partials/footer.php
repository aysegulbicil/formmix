<?php
$year = date('Y');
?>
<footer class="site-footer">
    <div class="container footer__grid">
        <div class="footer__col footer__brand">
            <img src="<?= logo_url() ?>" alt="<?= esc(site('brand')) ?> İş Elbiseleri logo" width="200" height="47">
            <p><?= esc(site('slogan')) ?></p>
            <p class="footer__muted">İşletmenize özel baskılı kurumsal iş kıyafetleri: polo yaka, tişört, sweatshirt, yelek ve iş pantolonu.</p>
            <a href="<?= esc(site('instagram')) ?>" target="_blank" rel="noopener" class="footer__ig" aria-label="Instagram'da FORMMIX"><?= icon('instagram', 20) ?></a>
        </div>

        <div class="footer__col">
            <h4>Sayfalar</h4>
            <ul>
                <li><a href="<?= site_url('/') ?>">Anasayfa</a></li>
                <li><a href="<?= site_url('hakkimizda') ?>">Hakkımızda</a></li>
                <li><a href="<?= site_url('urunler') ?>">Ürünler</a></li>
                <li><a href="<?= site_url('katalog') ?>">Katalog</a></li>
                <li><a href="<?= site_url('iletisim') ?>">İletişim</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4>Ürünler</h4>
            <ul>
                <li><a href="<?= site_url('urunler') ?>">Polo Yaka İş Kıyafeti</a></li>
                <li><a href="<?= site_url('urunler') ?>">Baskılı Tişört</a></li>
                <li><a href="<?= site_url('urunler') ?>">Sweatshirt</a></li>
                <li><a href="<?= site_url('urunler') ?>">Önlük</a></li>
                <li><a href="<?= site_url('urunler') ?>">Yelek</a></li>
                <li><a href="<?= site_url('urunler') ?>">İş Pantolonu</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h4>İletişim</h4>
            <ul class="footer__contact">
                <li><a href="<?= phone_link() ?>"><?= esc(site('phoneDisplay')) ?></a></li>
            </ul>
            <a class="btn btn--wa btn--sm footer__cta" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">Hemen Teklif Al</a>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <span>&copy; <?= $year ?> <?= esc(site('brand')) ?>. Tüm hakları saklıdır.</span>
            <span>Ekibiniz markanızı temsil eder.</span>
        </div>
    </div>
</footer>
