<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<section class="welcome-card">
    <div>
        <span class="eyebrow">İyi çalışmalar</span>
        <h2>Satış ekibiniz ve müşteri ilişkileriniz tek yerde.</h2>
        <p>Teklifleri, siparişleri, müşterileri ve sorumlu personeli güncel olarak izleyin.</p>
    </div>
    <?php if ($user?->can('employees.view')): ?>
        <a class="button button--light" href="<?= site_url('panel/personel') ?>">Personeli görüntüle <span aria-hidden="true">→</span></a>
    <?php endif; ?>
</section>

<?php if ($dashboardMetrics !== null): ?>
<div class="section-heading"><div><span class="eyebrow">Bugünün yönetim özeti</span><h2>Önemli göstergeler</h2></div><a class="text-link" href="<?= site_url('panel/raporlar') ?>">Tüm raporlar →</a></div>
<div class="stats-grid dashboard-kpis">
    <article class="stat-card"><span>Bu ay net satış</span><strong><?= number_format((float) $dashboardMetrics['monthNetSales'], 2, ',', '.') ?> ₺</strong><small><?= $dashboardMetrics['monthOrderCount'] ?> sipariş</small></article>
    <article class="stat-card"><span>Onay bekleyen</span><strong><?= $dashboardMetrics['pendingApprovalCount'] ?></strong><small>Satış kontrolü gerekli</small></article>
    <article class="stat-card"><span>Kritik stok</span><strong><?= $dashboardMetrics['criticalStockCount'] ?></strong><small><?= $dashboardMetrics['procurementWaitingCount'] ?> tedarik bekleyen · <?= $dashboardMetrics['partiallyShippedCount'] ?> kısmi sevk</small></article>
</div>
<?php if ($dashboardMetrics['recentAttentionOrders']): ?><section class="panel-card dashboard-alerts"><div class="report-section__head"><div><h2>İlgilenilecek siparişler</h2><p>Onay, tedarik veya sevkiyat işlemi bekleyen son kayıtlar.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Belge</th><th>Müşteri</th><th>Personel</th><th>Durum</th><th>Tarih</th></tr></thead><tbody><?php foreach ($dashboardMetrics['recentAttentionOrders'] as $row): ?><tr><td data-label="Belge"><?= esc($row['document_number']) ?></td><td data-label="Müşteri"><?= esc($row['customer_name']) ?></td><td data-label="Personel"><?= esc($row['employee_name']) ?></td><td data-label="Durum"><span class="badge badge--warning"><?= esc($row['status_label']) ?></span></td><td data-label="Tarih"><?= esc(date('d.m.Y', strtotime($row['created_at']))) ?></td></tr><?php endforeach; ?></tbody></table></div></section><?php endif; ?>
<?php endif; ?>

<div class="section-heading">
    <div><span class="eyebrow">Satış çalışma alanı</span><h2>Hızlı erişim</h2></div>
</div>
<div class="action-grid">
    <?php if ($user?->can('employees.view')): ?>
        <a class="action-card" href="<?= site_url('panel/personel') ?>">
            <span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3ZM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg></span>
            <div><h3>Personel yönetimi</h3><p>Ekibi, yetkileri ve kullanıcı bağlantılarını yönetin.</p></div><span class="action-card__arrow">→</span>
        </a>
    <?php endif; ?>
    <?php if ($user?->can('customers.view-all') || $user?->can('customers.view-own')): ?><a class="action-card" href="<?= site_url('panel/musteriler') ?>">
        <span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg></span>
        <div><h3>Müşteri yönetimi</h3><p>Portföyü, sorumluları ve görüşmeleri yönetin.</p></div><span class="action-card__arrow">→</span>
    </a><?php endif; ?>
    <?php if ($user?->can('orders.create') || $user?->can('orders.view-all') || $user?->can('orders.fulfill')): ?><a class="action-card" href="<?= site_url('panel/siparisler') ?>">
        <span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2ZM1 2v2h2l3.6 7.59-1.35 2.45A2 2 0 0 0 7 17h12v-2H7.42l.83-1.5h7.3a2 2 0 0 0 1.75-1.03L20.88 5H5.21l-.94-2H1Zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2Z"/></svg></span>
        <div><h3>Teklif ve siparişler</h3><p>Yeni kayıtları ve onay akışını izleyin.</p></div><?php if($pendingOrderCount>0): ?><span class="badge badge--warning"><?= esc($pendingOrderCount) ?> onay bekliyor</span><?php else: ?><span class="action-card__arrow">→</span><?php endif; ?>
    </a><?php endif; ?>
    <?php if ($user?->can('reports.view')): ?><a class="action-card" href="<?= site_url('panel/raporlar') ?>"><span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18v-2H5V3H3Zm4 13h3V9H7v7Zm5 0h3V5h-3v11Zm5 0h3v-4h-3v4Z"/></svg></span><div><h3>Raporlar</h3><p>Satış, stok, prim ve kârlılığı filtreleyin.</p></div><span class="action-card__arrow">→</span></a><?php endif; ?>
</div>
<?= $this->endSection() ?>
