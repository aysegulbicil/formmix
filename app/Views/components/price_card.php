<?php
/**
 * Kampanya / fiyat kartı (price-card).
 * Anasayfadaki kampanya ızgarasında inline HTML yerine kullanılır.
 *
 * @var array $campaign  qty, title, amount, unit, featured, ribbon, features alanlarını içerir
 */
$c = $campaign;
?>
<div class="price-card <?= ! empty($c['featured']) ? 'price-card--featured' : '' ?>">
    <?php if (! empty($c['ribbon'])): ?>
        <span class="price-card__ribbon"><?= esc($c['ribbon']) ?></span>
    <?php endif; ?>
    <div class="price-card__qty"><?= esc($c['qty']) ?></div>
    <div class="price-card__amount"><?= esc($c['amount']) ?> <small><?= esc($c['unit']) ?></small></div>
    <div class="price-card__unit"><?= esc($c['title']) ?></div>
    <ul class="price-card__list">
        <?php foreach ($c['features'] as $f): ?>
            <li><?= icon('check', 18) ?> <?= esc($f) ?></li>
        <?php endforeach; ?>
    </ul>
    <a class="btn btn--wa btn--block" href="<?= whatsapp_link('Merhaba, ' . $c['qty'] . ' ' . $c['title'] . ' kampanyası için teklif almak istiyorum.') ?>" target="_blank" rel="noopener">
        <?= icon('whatsapp', 18) ?> Bu Paket İçin Teklif Al
    </a>
</div>
