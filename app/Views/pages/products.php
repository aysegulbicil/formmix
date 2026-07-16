<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= view('components/page_hero', [
    'eyebrow' => 'Ürünler',
    'title'   => 'Kurumsal iş kıyafetleri',
    'text'    => "Logonuza özel baskılı/nakışlı kurumsal iş giyimi. Her ürün için WhatsApp'tan hızlı teklif alabilirsiniz.",
    'current' => 'Ürünler',
]) ?>

<section class="section">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Ürün Yelpazesi',
            'title'   => 'Polo yaka başta olmak üzere tüm ürünlerimiz',
            'lead'    => "İhtiyacınıza uygun ürünü seçin, WhatsApp'tan hızlıca teklif alın. Tüm ürünler logonuza özel hazırlanır.",
        ]) ?>
        <?php if ($products): ?>
            <div class="products-grid products-grid--five">
                <?php foreach ($products as $product): ?>
                    <?= view('components/product_card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><h2>Ürünlerimiz güncelleniyor</h2><p>Güncel ürün seçenekleri için WhatsApp üzerinden bize ulaşabilirsiniz.</p></div>
        <?php endif; ?>
    </div>
</section>

<?= view('components/cta_band', [
    'title'          => 'Aradığınız ürünü bulamadınız mı?',
    'text'           => 'İhtiyacınızı yazın; size en uygun kumaş, model ve baskı çözümünü önerelim.',
    'secondaryHref'  => site_url('katalog'),
    'secondaryLabel' => 'Kataloğu İncele',
    'waLabel'        => "WhatsApp'tan Yazın",
    'sectionClass'   => 'section section--gray',
]) ?>

<?= $this->endSection() ?>
