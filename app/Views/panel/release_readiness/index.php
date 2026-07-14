<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$groups = [];
foreach ($items as $item) {
    $groups[$item['category']][] = $item;
}
$severityClasses = ['critical' => 'badge--danger', 'high' => 'badge--warning', 'medium' => 'badge--neutral', 'low' => 'badge--success'];
?>
<div class="page-actions"><div><p class="page-lead">Manuel kabul testlerini, bulunan sorunları ve yayına çıkmadan önceki zorunlu kontrolleri burada izleyin.</p></div><span class="badge <?= $ready ? 'badge--success' : 'badge--warning' ?>"><?= $ready ? 'Kontroller tamamlandı' : 'Manuel test bekliyor' ?></span></div>
<?php if ($errors): ?><div class="alert alert--error"><?= esc(reset($errors)) ?></div><?php endif; ?>

<section class="release-boundary <?= $ready ? 'release-boundary--ready' : '' ?>">
    <div><span class="eyebrow">Adım 10 sınırı</span><h2><?= $ready ? 'Hazırlık maddeleri tamamlandı' : 'Bu ekran canlıya yayın yapmaz' ?></h2><p>Gerçek veri aktarımı, alan adı değişikliği ve yayına açma işlemi ancak manuel testler, geri dönüş denemesi ve yazılı onay sonrasında ayrıca yapılacaktır.</p></div>
</section>

<div class="stats-grid release-stats">
    <article class="stat-card"><span>Başarılı</span><strong><?= $counts['passed'] ?></strong><small><?= count($items) ?> toplam kontrol</small></article>
    <article class="stat-card"><span>Bekleyen / sorunlu</span><strong><?= $counts['pending'] + $counts['failed'] ?></strong><small><?= $counts['not_applicable'] ?> kapsam dışı</small></article>
    <article class="stat-card"><span>Açık kritik/yüksek sorun</span><strong><?= $openCritical ?></strong><small>Yayına hazır olmak için sıfır olmalı</small></article>
</div>

<?php foreach ($groups as $category => $groupItems): ?>
<section class="panel-card readiness-group">
    <div class="report-section__head"><div><span class="eyebrow">Kontrol grubu</span><h2><?= esc($category) ?></h2></div><span class="muted"><?= count(array_filter($groupItems, static fn ($item) => $item['status'] === 'passed')) ?>/<?= count($groupItems) ?> başarılı</span></div>
    <div class="readiness-list">
        <?php foreach ($groupItems as $item): ?>
        <form class="readiness-item readiness-item--<?= esc($item['status']) ?>" method="post" action="<?= site_url('panel/yayina-hazirlik/kontrol/'.$item['id']) ?>">
            <?= csrf_field() ?>
            <div class="readiness-item__copy"><h3><?= esc($item['title']) ?></h3><p><?= esc($item['description']) ?></p><?php if ($item['checked_at']): ?><small>Son kontrol: <?= esc(date('d.m.Y H:i', strtotime($item['checked_at']))) ?></small><?php endif; ?></div>
            <label class="field"><span>Durum</span><select name="status"><?php foreach ($itemStatuses as $value => $label): ?><option value="<?= esc($value) ?>" <?= $item['status'] === $value ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></label>
            <label class="field readiness-item__note"><span>Kontrol notu</span><input name="notes" value="<?= esc($item['notes'] ?? '') ?>" placeholder="Cihaz, kullanıcı veya sonuç bilgisi"></label>
            <button class="button button--secondary" type="submit">Kaydet</button>
        </form>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<section class="panel-card release-issues">
    <div class="report-section__head"><div><span class="eyebrow">Kabul testi</span><h2>Bulunan sorunlar</h2><p>Kritik ve yüksek sorunlar çözülüp yeniden test edilmeden yazılı onay verilmemelidir.</p></div></div>
    <form class="issue-form" method="post" action="<?= site_url('panel/yayina-hazirlik/sorun') ?>">
        <?= csrf_field() ?>
        <label class="field"><span>Sorun başlığı</span><input name="title" value="<?= esc(old('title')) ?>" required></label>
        <label class="field"><span>Önem</span><select name="severity"><?php foreach ($severities as $value => $label): ?><option value="<?= esc($value) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <label class="field field--wide"><span>Açıklama ve tekrar adımları</span><textarea name="description" rows="3" required><?= esc(old('description')) ?></textarea></label>
        <button class="button" type="submit">Sorunu kaydet</button>
    </form>
    <div class="issue-list">
        <?php if (! $issues): ?><div class="empty-state"><h2>Kayıtlı deneme sorunu yok</h2><p>Manuel test sırasında bulunan sorunları yukarıdaki formdan kaydedin.</p></div><?php endif; ?>
        <?php foreach ($issues as $issue): ?><article class="issue-card <?= $issue['status'] === 'resolved' ? 'issue-card--resolved' : '' ?>"><div class="issue-card__head"><div><h3><?= esc($issue['title']) ?></h3><span class="badge <?= $severityClasses[$issue['severity']] ?? 'badge--neutral' ?>"><?= esc($severities[$issue['severity']] ?? $issue['severity']) ?></span></div><small><?= esc(date('d.m.Y H:i', strtotime($issue['created_at']))) ?> · <?= $issue['status'] === 'resolved' ? 'Çözüldü' : 'Açık' ?></small></div><p><?= nl2br(esc($issue['description'])) ?></p><?php if ($issue['status'] === 'resolved'): ?><div class="resolution-note"><strong>Çözüm ve tekrar test:</strong> <?= nl2br(esc($issue['resolution_note'])) ?></div><?php else: ?><form class="resolve-form" method="post" action="<?= site_url('panel/yayina-hazirlik/sorun/'.$issue['id'].'/kapat') ?>"><?= csrf_field() ?><label class="field"><span>Çözüm ve tekrar test notu</span><input name="resolution_note" required></label><button class="button button--secondary" type="submit">Çözüldü olarak kapat</button></form><?php endif; ?></article><?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>
