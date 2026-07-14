<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; $isEdit = $product !== null; ?>
<div class="form-heading"><a class="back-link" href="<?= site_url('panel/urunler') ?>">← Ürün listesi</a><p class="page-lead"><?= $isEdit ? 'Ürün, fiyat, stok eşiği ve yeni seçenekleri güncelleyin.' : 'Siparişlerde kullanılacak ürün ve fiyat temelini oluşturun.' ?></p></div>
<?php if ($errors): ?><div class="alert alert--error" role="alert"><strong>Formu kontrol edin.</strong><span><?= esc($errors['form'] ?? reset($errors)) ?></span></div><?php endif; ?>

<form method="post" action="<?= $isEdit ? site_url('panel/urunler/' . $product['id'] . '/duzenle') : site_url('panel/urunler/yeni') ?>" class="product-form">
    <?= csrf_field() ?>
    <section class="form-card product-form__identity">
        <div class="form-card__head"><span class="step-number">1</span><div><h2>Ürün bilgileri</h2><p>Kod, ad, kategori ve satış durumu.</p></div></div>
        <div class="form-grid">
            <label class="field"><span>Ürün kodu <b>*</b></span><input name="product_code" value="<?= esc(old('product_code', $product['product_code'] ?? '')) ?>" maxlength="40" required placeholder="Örn. FM-POLO"><small><?= esc($errors['product_code'] ?? 'Benzersiz ve kısa bir kod kullanın.') ?></small></label>
            <label class="field"><span>Ürün adı <b>*</b></span><input name="name" value="<?= esc(old('name', $product['name'] ?? '')) ?>" maxlength="180" required><small><?= esc($errors['name'] ?? '') ?></small></label>
            <label class="field"><span>Kategori</span><select name="category_id"><option value="">Kategorisiz</option><?php foreach ($categories as $item): ?><option value="<?= esc($item['id']) ?>" <?= (string) old('category_id', $product['category_id'] ?? '') === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select><small>Mevcut kategorilerden seçin.</small></label>
            <label class="field"><span>Yeni kategori</span><input name="new_category_name" value="<?= esc(old('new_category_name')) ?>" maxlength="120" placeholder="Gerekirse yeni kategori adı"><small>Yazılırsa yukarıdaki seçimin yerine kullanılır.</small></label>
            <label class="field field--wide"><span>Açıklama</span><textarea name="description" rows="4" maxlength="3000"><?= esc(old('description', $product['description'] ?? '')) ?></textarea></label>
            <label class="field field--wide"><span>Ürün görseli yolu</span><input name="image_path" value="<?= esc(old('image_path', $product['image_path'] ?? '')) ?>" maxlength="500" placeholder="assets/images/urun.jpg"><small>Public klasörüne göre mevcut görsel yolu.</small></label>
            <label class="switch-row"><span><strong>Satışa açık</strong><small>Pasif ürün yeni siparişlerde seçilemez.</small></span><input type="checkbox" name="is_active" value="1" <?= old('is_active', $product['is_active'] ?? 1) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
            <label class="switch-row"><span><strong>Stok takibi</strong><small>Ürünün varyant bazında stoğu izlenecek.</small></span><input type="checkbox" name="track_stock" value="1" <?= old('track_stock', $product['track_stock'] ?? 1) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
        </div>
    </section>

    <section class="form-card product-form__pricing">
        <div class="form-card__head"><span class="step-number">2</span><div><h2>Fiyat ve stok temeli</h2><p>Liste fiyatları vergi hariç saklanır.</p></div></div>
        <div class="form-grid">
            <?php if ($canViewCost): ?><label class="field"><span>Alış fiyatı <b>*</b></span><div class="input-suffix"><input name="cost_price" value="<?= esc(old('cost_price', $product['cost_price'] ?? '0')) ?>" inputmode="decimal" required><em>₺</em></div><small>Yalnızca işletme sahibi ve muhasebe görebilir.</small></label><?php endif; ?>
            <label class="field"><span>Liste satış fiyatı <b>*</b></span><div class="input-suffix"><input name="list_price" value="<?= esc(old('list_price', $product['list_price'] ?? '0')) ?>" inputmode="decimal" required><em>₺</em></div><small>Vergi hariç satış temeli.</small></label>
            <label class="field"><span>Vergi oranı <b>*</b></span><div class="input-suffix"><input name="tax_rate" value="<?= esc(old('tax_rate', $product['tax_rate'] ?? '20')) ?>" inputmode="decimal" required><em>%</em></div></label>
            <label class="field"><span>Kritik stok seviyesi</span><input name="critical_stock_level" value="<?= esc(old('critical_stock_level', $product['critical_stock_level'] ?? '0')) ?>" inputmode="decimal"><small>Stok adımında uyarı eşiği olarak kullanılacak.</small></label>
            <label class="field field--wide"><span>Hazırlama biçimi</span><select name="customization_mode" required><option value="plain_only" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'plain_only' ? 'selected' : '' ?>>Yalnızca baskısız</option><option value="optional" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'optional' ? 'selected' : '' ?>>Baskısız veya müşteriye özel</option><option value="custom_only" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'custom_only' ? 'selected' : '' ?>>Yalnızca müşteriye özel</option></select><small>Müşteriye özel hazırlanan varyantlar ayrı stok koduyla tutulabilir.</small></label>
        </div>
    </section>

    <section class="form-card product-form__variants">
        <div class="form-card__head"><span class="step-number">3</span><div><h2>Yeni varyantlar</h2><p>Her beden-renk birleşimi ayrı stok koduyla eklenir.</p></div></div>
        <label class="field"><span>Varyant satırları</span><textarea name="variant_lines" rows="6" placeholder="FM-POLO-M-LAC | M | Lacivert | Baskısız | 350,00"><?= esc(old('variant_lines')) ?></textarea><small>Her satır: Stok kodu | Beden | Renk | Baskısız veya Özel | İsteğe bağlı satış fiyatı. Düzenlemede yalnızca yeni satırlar eklenir.</small></label>
        <?php if ($variants): ?>
            <details class="variant-details"><summary>Mevcut <?= count($variants) ?> varyantı göster</summary><div class="table-wrap"><table class="data-table compact-table"><thead><tr><th>Stok kodu</th><th>Beden</th><th>Renk</th><th>Hazırlama</th><th>Özel fiyat</th></tr></thead><tbody><?php foreach ($variants as $variant): ?><tr><td data-label="Stok kodu"><strong><?= esc($variant['sku']) ?></strong></td><td data-label="Beden"><?= esc($variant['size'] ?: '—') ?></td><td data-label="Renk"><?= esc($variant['color'] ?: '—') ?></td><td data-label="Hazırlama"><?= $variant['preparation_type'] === 'customized' ? 'Müşteriye özel' : 'Baskısız' ?></td><td data-label="Özel fiyat"><?= $variant['list_price_override'] !== null ? number_format((float) $variant['list_price_override'], 2, ',', '.') . ' ₺' : 'Liste fiyatı' ?></td></tr><?php endforeach; ?></tbody></table></div></details>
        <?php endif; ?>
    </section>

    <div class="form-actions product-form__actions"><?php if ($isEdit): ?><button class="button button--danger-ghost" type="submit" form="status-form"><?= $product['is_active'] ? 'Satışa kapat' : 'Satışa aç' ?></button><?php endif; ?><span></span><a class="button button--secondary" href="<?= site_url('panel/urunler') ?>">Vazgeç</a><button class="button" type="submit"><?= $isEdit ? 'Değişiklikleri kaydet' : 'Ürünü kaydet' ?></button></div>
</form>
<?php if ($isEdit): ?><form id="status-form" method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/durum') ?>" onsubmit="return confirm('Ürünün satış durumunu değiştirmek istediğinize emin misiniz?')"><?= csrf_field() ?></form><?php endif; ?>

<?php if ($isEdit): ?>
<section class="form-card special-price-card">
    <div class="form-card__head"><span class="step-number">4</span><div><h2>Müşteri ve grup özel fiyatları</h2><p>Liste fiyatını bozmadan, belirli müşteri veya gruba tarihli fiyat tanımlayın.</p></div></div>
    <?php if (isset($errors['special_price'])): ?><div class="alert alert--error"><span><?= esc($errors['special_price']) ?></span></div><?php endif; ?>
    <form method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/ozel-fiyat') ?>" class="special-price-form">
        <?= csrf_field() ?>
        <label class="field"><span>Hedef <b>*</b></span><select name="target" required><option value="">Müşteri veya grup seçin</option><optgroup label="Fiyat grupları"><?php foreach ($priceGroups as $group): ?><option value="group|<?= esc($group['id']) ?>"><?= esc($group['name']) ?></option><?php endforeach; ?></optgroup><optgroup label="Müşteriler"><?php foreach ($customers as $customer): ?><option value="customer|<?= esc($customer['id']) ?>"><?= esc($customer['company_name']) ?></option><?php endforeach; ?></optgroup></select></label>
        <label class="field"><span>Varyant</span><select name="product_variant_id"><option value="">Tüm varyantlar</option><?php foreach ($variants as $variant): ?><option value="<?= esc($variant['id']) ?>"><?= esc($variant['sku']) ?></option><?php endforeach; ?></select></label>
        <label class="field"><span>Özel birim fiyat <b>*</b></span><div class="input-suffix"><input name="unit_price" inputmode="decimal" required><em>₺</em></div></label>
        <label class="field"><span>Başlangıç</span><input type="datetime-local" name="valid_from"></label>
        <label class="field"><span>Bitiş</span><input type="datetime-local" name="valid_until"></label>
        <button class="button" type="submit">Özel fiyat ekle</button>
    </form>
    <?php if ($specialPrices): ?><div class="table-wrap"><table class="data-table compact-table"><thead><tr><th>Hedef</th><th>Varyant</th><th>Fiyat</th><th>Geçerlilik</th><th>Durum</th><th></th></tr></thead><tbody><?php foreach ($specialPrices as $price): ?><tr><td data-label="Hedef"><?= esc($price['group_name'] ?: $price['company_name']) ?></td><td data-label="Varyant"><?= esc($price['sku'] ?: 'Tüm varyantlar') ?></td><td data-label="Fiyat"><strong><?= number_format((float) $price['unit_price'], 2, ',', '.') ?> ₺</strong></td><td data-label="Geçerlilik"><small><?= esc($price['valid_from'] ?: 'Hemen') ?> → <?= esc($price['valid_until'] ?: 'Süresiz') ?></small></td><td data-label="Durum"><span class="badge <?= $price['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $price['is_active'] ? 'Aktif' : 'Pasif' ?></span></td><td data-label="İşlem"><form method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/ozel-fiyat/' . $price['id'] . '/durum') ?>"><?= csrf_field() ?><button class="text-link" type="submit"><?= $price['is_active'] ? 'Pasif yap' : 'Etkinleştir' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php endif; ?>
<?= $this->endSection() ?>
