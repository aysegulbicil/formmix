<?php
/** @var array $product */
$waText = 'Merhaba, ' . $product['name'] . ' için teklif almak istiyorum.';
?>
<article class="product-card <?= ! empty($product['highlight']) ? 'product-card--featured' : '' ?>">
    <div class="product-card__media">
        <?php if (! empty($product['badge'])): ?>
            <span class="badge"><?= esc($product['badge']) ?></span>
        <?php endif; ?>
        <img src="<?= esc($product['image_url'] ?? asset($product['image'] ?? 'images/product-tshirt.svg')) ?>" alt="<?= esc($product['name']) ?>" loading="lazy" width="400" height="300">
    </div>
    <div class="product-card__body">
        <h3 class="product-card__title"><?= esc($product['name']) ?></h3>
        <p class="product-card__desc"><?= esc($product['short']) ?></p>
        <a class="btn btn--wa btn--sm product-card__cta" href="<?= whatsapp_link($waText) ?>" target="_blank" rel="noopener">
            <?= icon('whatsapp', 18) ?> Teklif Al
        </a>
    </div>
</article>
