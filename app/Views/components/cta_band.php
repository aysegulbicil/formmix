<?php
/**
 * WhatsApp CTA bandı (cta-band). Birincil buton WhatsApp, ikincil buton serbest.
 * home / urunler / hakkimizda sayfalarındaki tekrarı birleştirir.
 *
 * @var string $title          Başlık
 * @var string $text           Açıklama
 * @var string $secondaryHref  İkincil butonun bağlantısı (site_url(...) ile hazır verilir)
 * @var string $secondaryLabel İkincil buton metni
 * @var string $waLabel        Birincil (WhatsApp) buton metni (opsiyonel)
 * @var string $sectionClass   Dış <section> sınıfı (opsiyonel; örn. "section section--gray")
 */
$title          = $title ?? '';
$text           = $text ?? '';
$secondaryHref  = $secondaryHref ?? '';
$secondaryLabel = $secondaryLabel ?? '';
$waLabel        = $waLabel ?? "WhatsApp'tan Teklif Al";
$sectionClass   = $sectionClass ?? 'section';
?>
<section class="<?= $sectionClass ?>">
    <div class="container">
        <div class="cta-band">
            <h2 class="cta-band__title"><?= $title ?></h2>
            <p class="cta-band__text"><?= $text ?></p>
            <div class="cta-band__actions">
                <a class="btn btn--wa btn--lg" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">
                    <?= icon('whatsapp', 20) ?> <?= $waLabel ?>
                </a>
                <a class="btn btn--outline-light btn--lg" href="<?= $secondaryHref ?>"><?= $secondaryLabel ?></a>
            </div>
        </div>
    </div>
</section>
