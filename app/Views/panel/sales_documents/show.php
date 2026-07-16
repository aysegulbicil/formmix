<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$whatsappUrl = (string) (session('whatsappUrl') ?? '');
$statusClass = static function (string $status): string {
    return match ($status) {
        'approved', 'delivered' => 'badge--success',
        'procurement_waiting', 'reserved', 'partially_shipped', 'shipped' => 'badge--warning',
        'cancelled' => 'badge--danger',
        default => 'badge--neutral',
    };
};
$hasWhatsapp = ! empty($primaryContact['phone_normalized'] ?? '');
$isOrder = $document['document_type'] === 'order';
$formatQuantity = static function (float $quantity): string {
    $decimals = abs($quantity - round($quantity)) < 0.0005 ? 0 : 3;

    return number_format($quantity, $decimals, ',', '.').' adet';
};

$processSteps = [
    'approved' => 'Sipariş oluşturuldu',
    'preparing' => 'Hazırlanıyor',
    'shipped' => 'Kargoya verildi',
    'delivered' => 'Ulaştı',
];
$statusToStep = [
    'approved' => 0,
    'procurement_waiting' => 1,
    'reserved' => 1,
    'partially_shipped' => 2,
    'shipped' => 2,
    'delivered' => 3,
];
$historyToStep = [
    'approved' => 'approved',
    'procurement_waiting' => 'preparing',
    'reserved' => 'preparing',
    'partially_shipped' => 'shipped',
    'shipped' => 'shipped',
    'delivered' => 'delivered',
];
$stepDates = array_fill_keys(array_keys($processSteps), null);
foreach ($history as $historyRow) {
    $stepKey = $historyToStep[$historyRow['new_status']] ?? null;
    if ($stepKey !== null && $stepDates[$stepKey] === null) {
        $stepDates[$stepKey] = $historyRow['created_at'];
    }
}
$currentStep = $statusToStep[$document['status']] ?? -1;
?>

<div data-clear-sales-ref="<?= esc($savedReference) ?>"></div>

<div class="detail-heading">
    <div>
        <a class="back-link" href="<?= site_url('panel/siparisler') ?>">← Teklif ve siparişler</a>
        <p>
            <span class="badge <?= $statusClass((string) $document['status']) ?>"><?= esc($statuses[$document['status']] ?? $document['status']) ?></span>
            <span class="muted"><?= esc($types[$document['document_type']] ?? $document['document_type']) ?></span>
        </p>
    </div>
    <div class="action-group">
        <?php if ($canEdit): ?>
            <a class="button button--secondary" href="<?= site_url('panel/siparisler/'.$document['id'].'/duzenle') ?>">Düzenle</a>
        <?php endif; ?>
        <?php if ($document['document_type'] === 'quote' && $document['status'] === 'approved' && auth()->user()?->can('orders.create')): ?>
            <form method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/siparise-cevir') ?>">
                <?= csrf_field() ?>
                <button class="button" type="submit">Siparişe çevir</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error"><strong>İşlem tamamlanamadı.</strong><span><?= esc(reset($errors)) ?></span></div>
<?php endif; ?>

<?php if ($whatsappUrl !== ''): ?>
    <div class="alert alert--success whatsapp-ready" data-whatsapp-url="<?= esc($whatsappUrl) ?>">
        <span>Durum güncellendi. WhatsApp mesajı hazırlandı.</span>
        <a class="button button--secondary" href="<?= esc($whatsappUrl) ?>" target="_blank" rel="noopener">WhatsApp mesajını aç</a>
    </div>
<?php endif; ?>

<?php if ($isOrder): ?>
    <section class="panel-card order-progress-card <?= $document['status'] === 'cancelled' ? 'is-cancelled' : '' ?>" aria-label="Sipariş durumu">
        <div class="order-progress-card__head">
            <div>
                <h2>Sipariş süreci</h2>
                <p>Butonlarla ilerledikçe mevcut aşama burada güncellenir.</p>
            </div>
            <?php if ($document['status'] === 'cancelled'): ?>
                <span class="badge badge--danger">Sipariş iptal edildi</span>
            <?php endif; ?>
        </div>
        <ol class="order-progress">
            <?php foreach ($processSteps as $stepKey => $stepLabel): ?>
                <?php
                $stepIndex = array_search($stepKey, array_keys($processSteps), true);
                $stepState = $document['status'] === 'cancelled'
                    ? ''
                    : ($stepIndex < $currentStep ? 'is-complete' : ($stepIndex === $currentStep ? 'is-current' : ''));
                ?>
                <li class="order-progress__step <?= $stepState ?>" <?= $stepIndex === $currentStep ? 'aria-current="step"' : '' ?>>
                    <span class="order-progress__marker"><?= $stepIndex < $currentStep ? '✓' : $stepIndex + 1 ?></span>
                    <strong><?= esc($stepLabel) ?></strong>
                    <small><?= $stepDates[$stepKey] ? esc(date('d.m.Y H:i', strtotime($stepDates[$stepKey]))) : 'Bekliyor' ?></small>
                </li>
            <?php endforeach; ?>
        </ol>
        <?php if ($history): ?>
            <details class="order-history-details">
                <summary>Tüm durum geçmişini göster</summary>
                <div class="assignment-list">
                    <?php foreach ($history as $row): ?>
                        <div>
                            <strong><?= esc($statuses[$row['new_status']] ?? $row['new_status']) ?></strong>
                            <span><?= esc(date('d.m.Y H:i', strtotime($row['created_at']))) ?></span>
                            <small><?= esc($row['reason'] ?? '') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>
    </section>
<?php endif; ?>

<div class="detail-grid sales-detail">
    <div class="detail-main">
        <section class="form-card info-card">
            <dl class="info-list">
                <div><dt>Müşteri</dt><dd><a href="<?= site_url('panel/musteriler/'.$document['customer_id']) ?>"><?= esc($document['company_name']) ?></a></dd></div>
                <div><dt>Satış personeli</dt><dd><?= esc($document['sales_employee_name'] ?? 'Atanmamış') ?></dd></div>
                <div><dt>Tarih</dt><dd><?= esc(date('d.m.Y H:i', strtotime($document['created_at']))) ?></dd></div>
                <div><dt>Vergi hariç net</dt><dd><?= number_format((float) $document['subtotal'] - (float) $document['discount_total'], 2, ',', '.') ?> ₺</dd></div>
                <div><dt>Vergi</dt><dd><?= number_format((float) $document['tax_total'], 2, ',', '.') ?> ₺</dd></div>
                <div><dt>Genel toplam</dt><dd><?= number_format((float) $document['grand_total'], 2, ',', '.') ?> ₺</dd></div>
            </dl>
            <?php if ($primaryContact): ?>
                <div class="address-box"><span>Müşteri iletişimi</span><p><?= esc($primaryContact['full_name'] ?: $document['company_name']) ?><br><?= esc($primaryContact['phone'] ?: $primaryContact['phone_normalized']) ?></p></div>
            <?php endif; ?>
            <?php if ($document['delivery_address']): ?>
                <div class="address-box"><span>Teslimat adresi</span><p><?= nl2br(esc($document['delivery_address'])) ?></p></div>
            <?php endif; ?>
        </section>

        <section class="panel-card">
            <div class="table-wrap">
                <table class="data-table sales-items-table">
                    <colgroup>
                        <col class="sales-items-table__product-col">
                        <col class="sales-items-table__quantity-col">
                        <col class="sales-items-table__price-col">
                        <col class="sales-items-table__discount-col">
                        <col class="sales-items-table__tax-col">
                        <col class="sales-items-table__total-col">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="sales-items-table__product">Ürün</th>
                            <th class="sales-items-table__quantity">Miktar</th>
                            <th class="sales-items-table__number">Birim fiyat</th>
                            <th class="sales-items-table__number">İndirim</th>
                            <th class="sales-items-table__number">Vergi</th>
                            <th class="sales-items-table__number">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="sales-items-table__product" data-label="Ürün"><strong><?= esc($item['product_name_snapshot']) ?></strong><span class="cell-note"><?= esc($item['product_code_snapshot'].' · '.$item['variant_snapshot']) ?></span></td>
                                <td class="sales-items-table__quantity" data-label="Miktar"><?= esc($formatQuantity((float) $item['quantity'])) ?></td>
                                <td class="sales-items-table__number" data-label="Birim fiyat"><?= number_format((float) $item['unit_price'], 2, ',', '.') ?> ₺</td>
                                <td class="sales-items-table__number" data-label="İndirim">%<?= number_format((float) $item['discount_percent'], 2, ',', '.') ?></td>
                                <td class="sales-items-table__number" data-label="Vergi"><?= number_format((float) $item['tax_amount'], 2, ',', '.') ?> ₺</td>
                                <td class="sales-items-table__number" data-label="Toplam"><strong><?= number_format((float) $item['line_total'], 2, ',', '.') ?> ₺</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="detail-aside">
        <section class="form-card compact-card workflow-actions-card">
            <h2>İşlemler</h2>

            <?php if ($isOrder && $document['status'] === 'draft' && $canEdit): ?>
                <form method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/gonder') ?>">
                    <?= csrf_field() ?>
                    <button class="button button--block" type="submit">Siparişi oluştur</button>
                </form>
            <?php endif; ?>

            <?php if ($isOrder && $canFulfill && in_array($document['status'], ['approved', 'procurement_waiting'], true)): ?>
                <form class="workflow-form" method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/surec') ?>" data-progress-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="preparing">
                    <label class="field">
                        <span>Hazırlık deposu</span>
                        <select name="warehouse_id" required>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?= $warehouse['id'] ?>"><?= esc($warehouse['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="workflow-submit-group">
                        <button class="button button--block" type="submit" name="notify" value="0"><?= $document['status'] === 'procurement_waiting' ? 'Stok durumunu yeniden kontrol et' : 'Hazırlanıyor olarak ilerlet' ?></button>
                        <button class="button button--secondary button--block" type="submit" name="notify" value="1" <?= $hasWhatsapp ? '' : 'disabled' ?>>Hazırlanıyor + WhatsApp</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($isOrder && $canFulfill && $document['status'] === 'reserved'): ?>
                <form class="workflow-form" method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/surec') ?>" data-progress-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="shipped">
                    <label class="field"><span>Sevkiyat açıklaması <b>*</b></span><textarea name="reason" rows="3" required></textarea></label>
                    <div class="workflow-submit-group">
                        <button class="button button--block" type="submit" name="notify" value="0">Kargoya verildi olarak ilerlet</button>
                        <button class="button button--secondary button--block" type="submit" name="notify" value="1" <?= $hasWhatsapp ? '' : 'disabled' ?>>Kargoya verildi + WhatsApp</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($isOrder && in_array($document['status'], ['shipped', 'partially_shipped'], true)): ?>
                <form class="workflow-form" method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/surec') ?>" data-progress-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delivered">
                    <p class="workflow-action-note">Sipariş kargoya verildi. Ulaştığında son adımı tamamlayın.</p>
                    <div class="workflow-submit-group">
                        <button class="button button--block" type="submit" name="notify" value="0">Ulaştı olarak işaretle</button>
                        <button class="button button--secondary button--block" type="submit" name="notify" value="1" <?= $hasWhatsapp ? '' : 'disabled' ?>>Ulaştı + WhatsApp</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($isOrder && $document['status'] === 'delivered'): ?>
                <div class="workflow-complete"><strong>✓ Süreç tamamlandı</strong><span>Sipariş müşteriye ulaştı.</span></div>
            <?php endif; ?>

            <?php if (! $hasWhatsapp && $isOrder && ! in_array($document['status'], ['delivered', 'cancelled'], true)): ?>
                <p class="muted">WhatsApp butonu için müşterinin birincil telefon kaydı gerekli.</p>
            <?php endif; ?>

            <?php if ($canCancel): ?>
                <form class="cancel-form" method="post" action="<?= site_url('panel/siparisler/'.$document['id'].'/iptal') ?>">
                    <?= csrf_field() ?>
                    <label class="field"><span>İptal nedeni <b>*</b></span><textarea name="reason" required rows="3"></textarea></label>
                    <button class="button button--danger-ghost button--block" type="submit">İptal et</button>
                </form>
            <?php endif; ?>
        </section>
    </aside>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/sales-documents.js') ?>" defer></script>
<?= $this->endSection() ?>
