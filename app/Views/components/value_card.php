<?php /** @var array $value */ ?>
<div class="value-card">
    <div class="value-card__icon"><?= icon($value['icon'], 26) ?></div>
    <h3><?= esc($value['title']) ?></h3>
    <p><?= esc($value['text']) ?></p>
</div>
