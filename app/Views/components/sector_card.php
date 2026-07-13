<?php /** @var array $sector */ ?>
<div class="sector-card">
    <div class="sector-card__icon"><?= icon($sector['icon'], 28) ?></div>
    <h3><?= esc($sector['name']) ?></h3>
    <p><?= esc($sector['desc']) ?></p>
</div>
