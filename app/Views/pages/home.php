<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- 1) HERO -->
<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content">
            <span class="hero__eyebrow"><?= icon('star', 16) ?> Kurumsal İş Giyimi</span>
            <h1 class="hero__title">Ekibiniz <span>Markanızı</span> Temsil Eder.</h1>
            <p class="hero__text">
                FORMMIX ile işletmenize özel baskılı kurumsal iş kıyafetleri hazırlıyoruz.
                Fabrika, servis, restoran ve tüm ekipler için profesyonel görünüm.
            </p>
            <div class="hero__actions">
                <a class="btn btn--wa btn--lg" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', 20) ?> WhatsApp'tan Teklif Al
                </a>
                <a class="btn btn--outline-light btn--lg" href="<?= site_url('urunler') ?>">Ürünleri İncele</a>
            </div>
            <ul class="hero__trust">
                <li><?= icon('check', 18) ?> Logonuza özel baskı & nakış</li>
                <li><?= icon('check', 18) ?> Hızlı üretim ve teslimat</li>
                <li><?= icon('check', 18) ?> Tüm ekipler için tek tip</li>
            </ul>
        </div>
        <div class="hero__media">
            <img src="<?= asset('images/hero.jpg') ?>" alt="FORMMIX lacivert kurumsal polo yaka iş kıyafeti" width="900" height="1040" fetchpriority="high">
        </div>
    </div>
</section>

<!-- 2) GÜVEN / KURUMSALLIK / PROFESYONELLİK -->
<section class="section">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Neden FORMMIX?',
            'title'   => 'Sadece kıyafet değil, kurumsal bir izlenim',
            'lead'    => 'İşletmenizin dışarıya verdiği ilk izlenimi güçlendiren, güven veren ve profesyonel bir görünüm sunuyoruz.',
        ]) ?>
        <div class="values-grid">
            <?php foreach ($values as $value): ?>
                <?= view('components/value_card', ['value' => $value]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 3) ÖNCE / SONRA -->
<section class="section section--gray">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Önce / Sonra',
            'title'   => 'Dağınık bir ekipten kurumsal bir ekibe',
            'lead'    => 'Aynı logoyu taşıyan tek tip kıyafetler, ekibinizi bir anda daha düzenli ve güvenilir gösterir.',
        ]) ?>
        <div class="ba-grid">
            <div class="ba-card ba-card--before">
                <img src="<?= asset('images/team-before.jpg') ?>" alt="Tek tip olmayan, dağınık görünümlü ekip" loading="lazy" width="440" height="330">
                <div class="ba-card__label"><span class="ba-tag">Önce</span> Dağınık, farklı kıyafetler</div>
            </div>
            <div class="ba-arrow"><?= icon('arrow', 26) ?></div>
            <div class="ba-card ba-card--after">
                <img src="<?= asset('images/team-after.jpg') ?>" alt="Logolu, tek tip kurumsal kıyafet giyen düzenli ekip" loading="lazy" width="440" height="330">
                <div class="ba-card__label"><span class="ba-tag">Sonra</span> Logolu, kurumsal görünüm</div>
            </div>
        </div>
    </div>
</section>

<?php if ($featured): ?>
<!-- 4) ÖNE ÇIKAN ÜRÜNLER -->
<section class="section">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Ürünler',
            'title'   => 'Öne çıkan ürünlerimiz',
            'lead'    => 'Polo yaka başta olmak üzere, her ekip ve sektör için baskıya hazır kurumsal kıyafetler.',
        ]) ?>
        <div class="products-grid">
            <?php foreach ($featured as $product): ?>
                <?= view('components/product_card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center" style="margin-top:36px;">
            <a class="btn btn--outline" href="<?= site_url('urunler') ?>">Tüm Ürünleri Gör</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5) SEKTÖRLERE ÖZEL ÇÖZÜMLER -->
<section class="section section--gray">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Sektörel Çözümler',
            'title'   => 'Her sektöre uygun iş kıyafeti',
            'lead'    => 'Çalışma koşullarınıza ve marka kimliğinize uygun kumaş, model ve baskı seçenekleri.',
        ]) ?>
        <div class="sectors-grid">
            <?php foreach ($sectors as $sector): ?>
                <?= view('components/sector_card', ['sector' => $sector]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 6) KAMPANYA -->
<section class="section">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Kampanya',
            'title'   => 'Kurumsal tişört kampanyaları',
            'lead'    => 'Logonuza özel baskılı kurumsal tişörtlerde avantajlı paket fiyatları.',
        ]) ?>
        <div class="pricing-grid">
            <?php foreach ($campaigns as $c): ?>
                <?= view('components/price_card', ['campaign' => $c]) ?>
            <?php endforeach; ?>
        </div>
        <p class="campaign__note">* Fiyatlar bilgilendirme amaçlıdır; model, kumaş ve baskı detayına göre değişebilir. Net teklif için WhatsApp'tan ulaşın.</p>
    </div>
</section>

<!-- 7) SİPARİŞ SÜRECİ -->
<section class="section section--gray">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Nasıl Çalışır?',
            'title'   => '5 adımda sipariş süreci',
            'lead'    => 'Logonuzu gönderin, gerisini biz halledelim. Süreç baştan sona şeffaf ve hızlı.',
        ]) ?>
        <div class="steps">
            <?php foreach ($process as $i => $step): ?>
                <?= view('components/step', ['step' => $step, 'num' => $i + 1]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 8) WHATSAPP CTA -->
<?= view('components/cta_band', [
    'title'          => 'Ekibinizi kurumsal bir görünüme kavuşturalım',
    'text'           => 'Logonuzu gönderin, size özel tasarım ve fiyat teklifini hemen hazırlayalım.',
    'secondaryHref'  => site_url('iletisim'),
    'secondaryLabel' => 'İletişim Bilgileri',
]) ?>

<?= $this->endSection() ?>
