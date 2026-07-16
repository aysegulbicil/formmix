<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$qty = static function (float $value): string {
    $decimals = abs($value - round($value)) < 0.0005 ? 0 : 3;

    return number_format($value, $decimals, ',', '.');
};
?>
<div class="page-actions">
    <div>
        <p class="page-lead">Bu alan sadece eldeki stokları görmek içindir. Amaç, kaç ürün olduğunu hızlıca görüp teklif ve sipariş oluştururken rahat karar verebilmektir.</p>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error"><strong>İşlem tamamlanamadı.</strong><span><?= esc(reset($errors)) ?></span></div>
<?php endif; ?>

<div class="stats-grid">
    <article class="stat-card">
        <span>Takip edilen varyant</span>
        <strong><?= count($balances) ?></strong>
        <small>Seçili depoda</small>
    </article>
    <article class="stat-card">
        <span>Kritik stok</span>
        <strong><?= $criticalCount ?></strong>
        <small><?= $criticalCount ? 'Azalan ürünleri takip edin' : 'Kritik eşik altında ürün yok' ?></small>
    </article>
    <article class="stat-card">
        <span>Depo</span>
        <strong><?= count($warehouses) ?></strong>
        <small>Tanımlı stok alanı</small>
    </article>
</div>

<section class="panel-card">
    <div class="report-section__head">
        <div>
            <h2>Mevcut stok görünümü</h2>
            <p>Seçili depoda hangi ürün varyantından ne kadar kaldığını buradan izleyin.</p>
        </div>
    </div>

    <form class="filter-bar" method="get">
        <select name="depo" onchange="this.form.submit()" aria-label="Depo">
            <?php foreach ($warehouses as $warehouse): ?>
                <option value="<?= $warehouse['id'] ?>" <?= (int) $warehouseId === (int) $warehouse['id'] ? 'selected' : '' ?>><?= esc($warehouse['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="table-wrap">
        <table class="data-table stock-table">
            <colgroup>
                <col class="stock-table__product-col">
                <col class="stock-table__qty-col">
                <col class="stock-table__qty-col">
                <col class="stock-table__qty-col">
                <col class="stock-table__status-col">
            </colgroup>
            <thead>
                <tr>
                    <th class="stock-table__product">Ürün / varyant</th>
                    <th class="stock-table__qty">Mevcut</th>
                    <th class="stock-table__qty">Ayrılmış</th>
                    <th class="stock-table__qty">Kullanılabilir</th>
                    <th class="stock-table__status">Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($balances as $row): ?>
                    <tr>
                        <td class="stock-table__product" data-label="Ürün">
                            <strong><?= esc($row['product_name']) ?></strong>
                            <span class="cell-note"><?= esc($row['sku'].' · '.implode(' / ', array_filter([$row['size'], $row['color']]))) ?></span>
                        </td>
                        <td class="stock-table__qty" data-label="Mevcut"><?= $qty((float) $row['on_hand_quantity']) ?></td>
                        <td class="stock-table__qty" data-label="Ayrılmış"><?= $qty((float) $row['reserved_quantity']) ?></td>
                        <td class="stock-table__qty" data-label="Kullanılabilir"><strong><?= $qty((float) $row['available_quantity']) ?></strong></td>
                        <td class="stock-table__status" data-label="Durum">
                            <span class="badge <?= $row['is_critical'] ? 'badge--warning' : 'badge--success' ?>">
                                <?= $row['is_critical'] ? 'Kritik' : 'Uygun' ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (! $balances): ?>
        <div class="empty-state">
            <h2>Stok kaydı bulunamadı</h2>
            <p>Bu depoda henüz görüntülenecek stok hareketi bulunmuyor.</p>
        </div>
    <?php endif; ?>
</section>

<section class="panel-card">
    <div class="report-section__head">
        <div>
            <h2>Hızlı stok girişi</h2>
            <p>Buradan sadece basit stok ekleme veya stok düşme işlemi yapın.</p>
        </div>
    </div>
    <form class="filter-bar inventory-quick-form" method="post" action="<?= site_url('panel/stok/hareket') ?>">
        <?= csrf_field() ?>
        <label>
            <span class="sr-only">İşlem</span>
            <select name="movement_type" required>
                <option value="manual_in">Stok ekle</option>
                <option value="manual_out">Stok düş</option>
            </select>
        </label>
        <label>
            <span class="sr-only">Depo</span>
            <select name="warehouse_id" required>
                <?php foreach ($warehouses as $warehouse): ?>
                    <option value="<?= $warehouse['id'] ?>" <?= (int) $warehouseId === (int) $warehouse['id'] ? 'selected' : '' ?>><?= esc($warehouse['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span class="sr-only">Ürün varyantı</span>
            <select name="product_variant_id" required>
                <option value="">Ürün seçin</option>
                <?php foreach ($variants as $variant): ?>
                    <option value="<?= $variant['variant_id'] ?>"><?= esc($variant['product_name'].' · '.$variant['sku']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span class="sr-only">Miktar</span>
            <input name="quantity" inputmode="decimal" placeholder="Miktar" required>
        </label>
        <label class="inventory-quick-form__reason">
            <span class="sr-only">Neden</span>
            <input name="reason" placeholder="Not / neden" required>
        </label>
        <button class="button" type="submit">Kaydet</button>
    </form>
</section>

<section class="panel-card">
    <div class="section-heading-inline">
        <div>
            <h2>Son stok hareketleri</h2>
            <p>Son yapılan giriş, çıkış ve güncelleme kayıtları.</p>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Hareket</th>
                    <th>Ürün</th>
                    <th>Depo</th>
                    <th>Miktar</th>
                    <th>Kalan</th>
                    <th>Neden</th>
                    <th>Tarih</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movements as $row): ?>
                    <tr>
                        <td data-label="Hareket">
                            <strong><?= esc($row['movement_number']) ?></strong>
                            <span class="cell-note"><?= esc($movementLabels[$row['movement_type']] ?? $row['movement_type']) ?></span>
                        </td>
                        <td data-label="Ürün">
                            <?= esc($row['product_name']) ?>
                            <span class="cell-note"><?= esc($row['sku']) ?></span>
                        </td>
                        <td data-label="Depo"><?= esc($row['warehouse_name']) ?></td>
                        <td data-label="Miktar"><strong><?= (float) $row['quantity'] >= 0 ? '+' : '' ?><?= $qty((float) $row['quantity']) ?></strong></td>
                        <td data-label="Kalan"><?= $qty((float) $row['balance_after']) ?></td>
                        <td data-label="Neden"><?= esc($row['reason']) ?></td>
                        <td data-label="Tarih">
                            <?= esc(date('d.m.Y H:i', strtotime($row['created_at']))) ?>
                            <span class="cell-note"><?= esc($row['username'] ?: ('Kullanıcı #'.$row['user_id'])) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
