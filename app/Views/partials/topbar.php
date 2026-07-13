<!-- Üst bilgi çubuğu (masaüstü) -->
<div class="topbar">
    <div class="container topbar__inner">
        <div class="topbar__left">
            <a href="<?= phone_link() ?>" class="topbar__item"><?= icon('phone', 15) ?> <span><?= esc(site('phoneDisplay')) ?></span></a>
            <span class="topbar__item topbar__hours"><?= icon('clock', 15) ?> <span><?= esc(site('workingHours')) ?></span></span>
        </div>
        <div class="topbar__right">
            <a href="<?= esc(site('instagram')) ?>" target="_blank" rel="noopener" class="topbar__item topbar__ig" aria-label="Instagram'da FORMMIX"><?= icon('instagram', 17) ?></a>
        </div>
    </div>
</div>
