<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$money = static fn ($value) => number_format((float) $value, 2, ',', '.').' ₺';
$qty = static fn ($value) => number_format((float) $value, 3, ',', '.');
$query = http_build_query(['from' => $filters['from'], 'until' => $filters['until'], 'employee_id' => $filters['employee_id']]);
$exports = static fn (string $section) => '<span class="report-exports"><a href="'.esc(site_url('panel/raporlar/disari-aktar/'.$section.'/csv').'?'.$query, 'attr').'">CSV</a><a href="'.esc(site_url('panel/raporlar/disari-aktar/'.$section.'/xlsx').'?'.$query, 'attr').'">XLSX</a></span>';
?>
<div class="page-actions"><div><p class="page-lead">Satış, sipariş, stok, prim ve temel kârlılığı tek yerde izleyin.</p></div></div>

<section class="panel-card report-filter-card">
    <form class="filter-bar report-filter" method="get">
        <label class="field"><span>Başlangıç</span><input type="date" name="from" value="<?= esc($filters['from']) ?>"></label>
        <label class="field"><span>Bitiş</span><input type="date" name="until" value="<?= esc($filters['until']) ?>"></label>
        <label class="field"><span>Personel</span><select name="employee_id"><option value="0">Tüm personel</option><?php foreach ($employees as $employee): ?><option value="<?= $employee['id'] ?>" <?= $filters['employee_id'] === (int) $employee['id'] ? 'selected' : '' ?>><?= esc($employee['full_name']) ?></option><?php endforeach; ?></select></label>
        <button class="button" type="submit">Uygula</button><a class="text-link" href="<?= site_url('panel/raporlar') ?>">Temizle</a>
    </form>
</section>

<div class="stats-grid report-periods">
    <?php foreach (['daily' => 'Bugün', 'weekly' => 'Bu hafta', 'monthly' => 'Bu ay'] as $key => $label): ?><article class="stat-card"><span><?= $label ?></span><strong><?= $money($report['periods'][$key]['net_sales']) ?></strong><small><?= $report['periods'][$key]['order_count'] ?> sipariş · vergi hariç net satış</small></article><?php endforeach; ?>
</div>
<div class="stats-grid report-totals">
    <article class="stat-card"><span>Filtrelenen sipariş</span><strong><?= $report['summary']['order_count'] ?></strong></article>
    <article class="stat-card"><span>Filtrelenen net satış</span><strong><?= $money($report['summary']['net_sales']) ?></strong></article>
    <article class="stat-card"><span>Filtrelenen genel toplam</span><strong><?= $money($report['summary']['grand_total']) ?></strong><small><?= $money($report['summary']['tax_total']) ?> vergi</small></article>
</div>

<section class="panel-card report-section">
    <div class="report-section__head"><div><span class="eyebrow">Zaman dağılımı</span><h2>Günlük satış özeti</h2></div></div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Tarih</th><th>Sipariş</th><th>Net satış</th><th>Genel toplam</th></tr></thead><tbody><?php if (! $report['daily']): ?><tr><td colspan="5">Seçilen aralıkta satış bulunmuyor.</td></tr><?php endif; ?><?php foreach ($report['daily'] as $row): ?><tr><td data-label="ID"><?= (int) $row['id'] ?></td><td data-label="Tarih"><?= esc(date('d.m.Y', strtotime($row['sale_date']))) ?></td><td data-label="Sipariş"><?= $row['order_count'] ?></td><td data-label="Net satış"><?= $money($row['net_sales']) ?></td><td data-label="Genel toplam"><?= $money($row['grand_total']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?= view('components/table_pagination', ['pagination' => $paginations['daily']]) ?>
</section>

<?php foreach ([
    ['personel', 'employees', 'Personel bazında satış', $report['employees'], [['ID','id'],['Personel','employee_name'],['Sipariş','order_count'],['Miktar','quantity'],['Net satış','net_sales'],['Genel toplam','grand_total']]],
    ['musteri', 'customers', 'Müşteri bazında satış', $report['customers'], [['ID','id'],['Müşteri','customer_name'],['Sipariş','order_count'],['Miktar','quantity'],['Net satış','net_sales'],['Genel toplam','grand_total']]],
] as [$section, $paginationKey, $title, $rows, $columns]): ?>
<section class="panel-card report-section"><div class="report-section__head"><h2><?= esc($title) ?></h2><?= $exports($section) ?></div><div class="table-wrap"><table class="data-table"><thead><tr><?php foreach ($columns as [$label]): ?><th><?= esc($label) ?></th><?php endforeach; ?></tr></thead><tbody><?php if (! $rows): ?><tr><td colspan="<?= count($columns) ?>">Kayıt bulunmuyor.</td></tr><?php endif; ?><?php foreach ($rows as $row): ?><tr><?php foreach ($columns as [$label, $key]): ?><td data-label="<?= esc($label) ?>"><?php if (in_array($key, ['net_sales','grand_total'], true)): ?><?= $money($row[$key]) ?><?php elseif ($key === 'quantity'): ?><?= $qty($row[$key]) ?><?php else: ?><?= esc($row[$key]) ?><?php endif; ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div><?= view('components/table_pagination', ['pagination' => $paginations[$paginationKey]]) ?></section>
<?php endforeach; ?>

<section class="panel-card report-section"><div class="report-section__head"><div><h2>Ürün ve varyant bazında satış</h2><?php if ($canViewCost): ?><p>Brüt kâr, satış satırındaki miktar ile güncel tanımlı alış maliyeti kullanılarak hesaplanır.</p><?php endif; ?></div><?= $exports('urun') ?></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Ürün</th><th>Varyant</th><th>SKU</th><th>Miktar</th><th>Net satış</th><?php if ($canViewCost): ?><th>Maliyet</th><th>Brüt kâr</th><th>Marj</th><?php endif; ?></tr></thead><tbody><?php if (! $report['products']): ?><tr><td colspan="9">Kayıt bulunmuyor.</td></tr><?php endif; ?><?php foreach ($report['products'] as $row): ?><tr><td data-label="ID"><?= (int) $row['id'] ?></td><td data-label="Ürün"><?= esc($row['product_name']) ?></td><td data-label="Varyant"><?= esc($row['variant_name']) ?></td><td data-label="SKU"><?= esc($row['sku']) ?></td><td data-label="Miktar"><?= $qty($row['quantity']) ?></td><td data-label="Net satış"><?= $money($row['net_sales']) ?></td><?php if ($canViewCost): ?><td data-label="Maliyet"><?= $money($row['cost_total']) ?></td><td data-label="Brüt kâr"><?= $money($row['gross_profit']) ?></td><td data-label="Marj">%<?= number_format($row['gross_margin_percent'], 2, ',', '.') ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div><?= view('components/table_pagination', ['pagination' => $paginations['products']]) ?></section>

<section class="panel-card report-section"><div class="report-section__head"><h2>İlgilenilecek siparişler</h2><?= $exports('siparis') ?></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Belge</th><th>Müşteri</th><th>Personel</th><th>Durum</th><th>Tarih</th><th>Kalan</th></tr></thead><tbody><?php if (! $report['orders']): ?><tr><td colspan="7">Bekleyen işlem bulunmuyor.</td></tr><?php endif; ?><?php foreach ($report['orders'] as $row): ?><tr><td data-label="ID"><?= (int) $row['id'] ?></td><td data-label="Belge"><?= esc($row['document_number']) ?></td><td data-label="Müşteri"><?= esc($row['customer_name']) ?></td><td data-label="Personel"><?= esc($row['employee_name']) ?></td><td data-label="Durum"><span class="badge badge--warning"><?= esc($row['status_label']) ?></span></td><td data-label="Tarih"><?= esc(date('d.m.Y', strtotime($row['created_at']))) ?></td><td data-label="Kalan"><?= $qty($row['remaining_quantity']) ?></td></tr><?php endforeach; ?></tbody></table></div><?= view('components/table_pagination', ['pagination' => $paginations['orders']]) ?></section>

<section class="panel-card report-section"><div class="report-section__head"><h2>Depo bazında mevcut ve kritik stok</h2><?= $exports('stok') ?></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Depo</th><th>Ürün / varyant</th><th>Mevcut</th><th>Ayrılmış</th><th>Kullanılabilir</th><th>Eşik</th><th>Durum</th></tr></thead><tbody><?php foreach ($report['stock'] as $row): ?><tr><td data-label="ID"><?= (int) $row['id'] ?></td><td data-label="Depo"><?= esc($row['warehouse_name']) ?></td><td data-label="Ürün"><strong><?= esc($row['product_name']) ?></strong><small class="cell-note"><?= esc($row['variant_name'].' · '.$row['sku']) ?></small></td><td data-label="Mevcut"><?= $qty($row['on_hand']) ?></td><td data-label="Ayrılmış"><?= $qty($row['reserved']) ?></td><td data-label="Kullanılabilir"><?= $qty($row['available']) ?></td><td data-label="Eşik"><?= $qty($row['threshold']) ?></td><td data-label="Durum"><span class="badge <?= $row['is_critical'] ? 'badge--warning' : 'badge--success' ?>"><?= esc($row['stock_status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div><?= view('components/table_pagination', ['pagination' => $paginations['stock']]) ?></section>

<section class="panel-card report-section"><div class="report-section__head"><h2>Prim özeti</h2><?= $exports('prim') ?></div><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Personel</th><th>Durum</th><th>Kayıt</th><th>Matrah</th><th>Prim</th></tr></thead><tbody><?php if (! $report['commissions']): ?><tr><td colspan="6">Prim kaydı bulunmuyor.</td></tr><?php endif; ?><?php foreach ($report['commissions'] as $row): ?><tr><td data-label="ID"><?= (int) $row['id'] ?></td><td data-label="Personel"><?= esc($row['employee_name']) ?></td><td data-label="Durum"><?= esc($row['status_label']) ?></td><td data-label="Kayıt"><?= $row['entry_count'] ?></td><td data-label="Matrah"><?= $money($row['base_amount']) ?></td><td data-label="Prim"><strong><?= $money($row['commission_amount']) ?></strong></td></tr><?php endforeach; ?></tbody></table></div><?= view('components/table_pagination', ['pagination' => $paginations['commissions']]) ?></section>
<?= $this->endSection() ?>
