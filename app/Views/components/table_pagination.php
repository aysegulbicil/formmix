<?php

$pagination = $pagination ?? [];
$total = (int) ($pagination['total'] ?? 0);
$page = (int) ($pagination['page'] ?? 1);
$pageCount = (int) ($pagination['pageCount'] ?? 1);
$pageParam = (string) ($pagination['pageParam'] ?? 'page');
$perPageParam = (string) ($pagination['perPageParam'] ?? 'per_page');
$query = service('request')->getGet();
$baseUrl = current_url();
$urlFor = static function (int $targetPage) use ($query, $baseUrl, $pageParam): string {
    $params = $query;
    $params[$pageParam] = $targetPage;
    return $baseUrl . ($params === [] ? '' : '?' . http_build_query($params));
};
$start = max(1, $page - 2);
$end = min($pageCount, $page + 2);
if ($end - $start < 4) {
    $start = max(1, $end - 4);
    $end = min($pageCount, $start + 4);
}
?>
<div class="table-pagination" role="navigation" aria-label="Tablo sayfalama">
    <div class="table-pagination__meta">
        <label class="table-pagination__size">
            <span>Sayfada</span>
            <select data-table-per-page data-page-param="<?= esc($pageParam) ?>" data-per-page-param="<?= esc($perPageParam) ?>" aria-label="Sayfada gösterilecek kayıt sayısı">
                <?php foreach (($pagination['options'] ?? [10, 20, 50, 100]) as $option): ?>
                    <option value="<?= (int) $option ?>" <?= (int) $option === (int) ($pagination['perPage'] ?? 10) ? 'selected' : '' ?>><?= (int) $option ?></option>
                <?php endforeach; ?>
            </select>
            <span>kayıt</span>
        </label>

        <span class="table-pagination__summary">
            <?= number_format((int) ($pagination['first'] ?? 0), 0, ',', '.') ?>–<?= number_format((int) ($pagination['last'] ?? 0), 0, ',', '.') ?> / <?= number_format($total, 0, ',', '.') ?>
        </span>
    </div>

    <?php if ($pageCount > 1): ?>
        <div class="table-pagination__pages">
            <a class="table-pagination__button <?= $page <= 1 ? 'is-disabled' : '' ?>" href="<?= $page <= 1 ? '#' : esc($urlFor($page - 1)) ?>" aria-label="Önceki sayfa">‹</a>
            <?php if ($start > 1): ?>
                <a class="table-pagination__button" href="<?= esc($urlFor(1)) ?>">1</a>
                <?php if ($start > 2): ?><span class="table-pagination__ellipsis">…</span><?php endif; ?>
            <?php endif; ?>
            <?php for ($number = $start; $number <= $end; $number++): ?>
                <a class="table-pagination__button <?= $number === $page ? 'is-current' : '' ?>" href="<?= esc($urlFor($number)) ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a>
            <?php endfor; ?>
            <?php if ($end < $pageCount): ?>
                <?php if ($end < $pageCount - 1): ?><span class="table-pagination__ellipsis">…</span><?php endif; ?>
                <a class="table-pagination__button" href="<?= esc($urlFor($pageCount)) ?>"><?= $pageCount ?></a>
            <?php endif; ?>
            <a class="table-pagination__button <?= $page >= $pageCount ? 'is-disabled' : '' ?>" href="<?= $page >= $pageCount ? '#' : esc($urlFor($page + 1)) ?>" aria-label="Sonraki sayfa">›</a>
        </div>
    <?php endif; ?>
</div>
