<?php
/** @var array $step */
/** @var int $num */
?>
<div class="step">
    <div class="step__num"><?= (int) $num ?></div>
    <h3><?= esc($step['title']) ?></h3>
    <p><?= esc($step['text']) ?></p>
</div>
