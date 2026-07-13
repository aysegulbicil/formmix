<?php
/**
 * Alt sayfa başlığı (page-hero): eyebrow + title + text + breadcrumb.
 * urunler / katalog / hakkimizda / iletisim sayfalarındaki tekrarı birleştirir.
 *
 * @var string $eyebrow  Üst etiket
 * @var string $title    Sayfa başlığı (h1)
 * @var string $text     Açıklama paragrafı
 * @var string $current  Breadcrumb'ta aktif sayfa adı
 */
$eyebrow = $eyebrow ?? '';
$title   = $title ?? '';
$text    = $text ?? '';
$current = $current ?? '';
?>
<section class="page-hero">
    <div class="container">
        <span class="page-hero__eyebrow"><?= $eyebrow ?></span>
        <h1 class="page-hero__title"><?= $title ?></h1>
        <p class="page-hero__text"><?= $text ?></p>
        <nav class="breadcrumb" aria-label="Sayfa yolu">
            <a href="<?= site_url('/') ?>">Anasayfa</a> / <span><?= $current ?></span>
        </nav>
    </div>
</section>
