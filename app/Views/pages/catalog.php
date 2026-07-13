<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- HERO: KATALOG TANITIM -->
<section class="catalog-hero">
    <div class="container catalog-hero__grid">
        <div class="catalog-hero__content">
            <nav class="breadcrumb breadcrumb--light" aria-label="Sayfa yolu">
                <a href="<?= site_url('/') ?>">Anasayfa</a> / <span>Katalog</span>
            </nav>
            <span class="catalog-hero__eyebrow"><?= icon('file', 16) ?> Dijital Katalog</span>
            <h1 class="catalog-hero__title">FORMMIX Dijital Katalog</h1>
            <p class="catalog-hero__lead">Kurumsal iş giyimi modellerimizi, renk seçeneklerimizi ve kampanyalarımızı tek katalogda inceleyin.</p>

            <ul class="catalog-hero__features">
                <li><?= icon('check', 20) ?> <span>Polo yaka iş kıyafetleri</span></li>
                <li><?= icon('check', 20) ?> <span>Baskılı tişört modelleri</span></li>
                <li><?= icon('check', 20) ?> <span>Kurumsal kampanyalar</span></li>
                <li><?= icon('check', 20) ?> <span>Firma logolu baskı seçenekleri</span></li>
            </ul>

            <div class="catalog-hero__actions">
                <?php if (! empty($pdfUrl)): ?>
                    <a class="btn btn--light btn--lg" href="<?= esc($pdfUrl) ?>" target="_blank" rel="noopener"><?= icon('file', 20) ?> Kataloğu Aç</a>
                    <a class="btn btn--outline-light btn--lg" href="<?= esc($pdfUrl) ?>" download><?= icon('download', 20) ?> PDF İndir</a>
                <?php endif; ?>
                <a class="btn btn--wa btn--lg" href="<?= whatsapp_link('Merhaba, FORMMIX ürün kataloğunu paylaşır mısınız?') ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 20) ?> WhatsApp'tan İste</a>
            </div>
        </div>

        <div class="catalog-hero__media">
            <div class="cover3d">
                <div class="cover3d__inner">
                    <img src="<?= asset('images/catalog-cover.jpg') ?>" alt="FORMMIX Ürün Kataloğu kapağı" width="340" height="481">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- KATALOGDA NELER VAR -->
<section class="section section--gray">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'İçindekiler',
            'title'   => 'Katalogda Neler Var?',
            'lead'    => 'Kataloğumuzda öne çıkan başlıklara göz atın.',
        ]) ?>
        <div class="values-grid">
            <a class="value-card" href="<?= site_url('urunler') ?>">
                <div class="value-card__icon"><?= icon('star', 26) ?></div>
                <h3>Polo Yaka</h3>
                <p>En çok tercih edilen modeller ve geniş renk seçenekleri.</p>
            </a>
            <a class="value-card" href="<?= site_url('urunler') ?>">
                <div class="value-card__icon"><?= icon('check', 26) ?></div>
                <h3>Tişört</h3>
                <p>Baskılı kurumsal tişört modelleri ve kumaş seçenekleri.</p>
            </a>
            <a class="value-card" href="<?= site_url('/') ?>">
                <div class="value-card__icon"><?= icon('file', 26) ?></div>
                <h3>Kampanyalar</h3>
                <p>10, 20 ve 50 adet için avantajlı paket fiyatları.</p>
            </a>
            <a class="value-card" href="<?= site_url('/') ?>">
                <div class="value-card__icon"><?= icon('clock', 26) ?></div>
                <h3>Sipariş Süreci</h3>
                <p>Logo gönder, onayla, teslim al — 5 adımda kolay süreç.</p>
            </a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
