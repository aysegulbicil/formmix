<?php
/**
 * Bölüm başlığı bloğu (eyebrow + title + opsiyonel lead).
 * Tekrarlayan <div class="section__head"> yapısını tek kaynağa indirir.
 *
 * @var string $eyebrow  Üst etiket (section__eyebrow)
 * @var string $title    Başlık (section__title)
 * @var string $lead     Açıklama (opsiyonel; boşsa <p> render edilmez)
 */
$eyebrow = $eyebrow ?? '';
$title   = $title ?? '';
$lead    = $lead ?? '';
?>
<div class="section__head">
    <span class="section__eyebrow"><?= $eyebrow ?></span>
    <h2 class="section__title"><?= $title ?></h2>
    <?php if ($lead !== ''): ?><p class="section__lead"><?= $lead ?></p><?php endif; ?>
</div>
