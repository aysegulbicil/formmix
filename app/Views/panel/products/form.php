<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$isEdit = $product !== null;
$newCategoryName = (string) old('new_category_name', '');
$categoryValue = $newCategoryName !== '' ? '__new__' : (string) old('category_id', $product['category_id'] ?? '');
?>

<div class="form-heading product-form-heading"><a class="back-link" href="<?= site_url('panel/urunler') ?>">← Ürünlere dön</a><div><span class="eyebrow"><?= $isEdit ? 'Ürün yönetimi' : 'Yeni ürün' ?></span><h2><?= $isEdit ? esc($product['name']) : 'Ürün bilgilerini tek ekranda tamamlayın' ?></h2><p class="page-lead"><?= $isEdit ? 'Ürün, fiyat, stok ve varyant bilgilerini birlikte güncelleyin.' : 'Siparişlerde kullanılacak ürünün temelini, fiyatını ve seçeneklerini oluşturun.' ?></p></div></div>
<?php if ($errors): ?><div class="alert alert--error" role="alert"><strong>Formu kontrol edin.</strong><span><?= esc($errors['form'] ?? reset($errors)) ?></span></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" action="<?= $isEdit ? site_url('panel/urunler/' . $product['id'] . '/duzenle') : site_url('panel/urunler/yeni') ?>" class="product-form product-form--unified">
    <?= csrf_field() ?>
    <section class="form-card product-form-shell">
        <div class="product-form-shell__intro"><div><span class="eyebrow">Ürün kartı</span><h2><?= $isEdit ? esc($product['product_code']) : 'Yeni ürün kaydı' ?></h2></div><span><i>*</i> Zorunlu alan</span></div>

        <div class="product-form-layout">
            <section class="product-form-section product-form__identity">
                <header class="product-form-section__head"><span class="step-number">1</span><div><h3>Ürün bilgileri</h3><p>Kod, ad, kategori, görsel ve satış durumu</p></div></header>
                <div class="form-grid">
                    <label class="field"><span>Ürün kodu <b>*</b></span><input name="product_code" value="<?= esc(old('product_code', $product['product_code'] ?? '')) ?>" maxlength="40" required placeholder="Örn. FM-POLO"><small><?= esc($errors['product_code'] ?? 'Benzersiz ve kısa bir kod kullanın.') ?></small></label>
                    <label class="field"><span>Ürün adı <b>*</b></span><input name="name" value="<?= esc(old('name', $product['name'] ?? '')) ?>" maxlength="180" required placeholder="Ürün adı"><small><?= esc($errors['name'] ?? '') ?></small></label>
                    <label class="field field--wide"><span>Kategori</span><select name="category_id" data-category-select><option value="" <?= $categoryValue === '' ? 'selected' : '' ?>>Kategorisiz</option><?php foreach ($categories as $item): ?><option value="<?= esc($item['id']) ?>" <?= $categoryValue === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option><?php endforeach; ?><option value="__new__" <?= $categoryValue === '__new__' ? 'selected' : '' ?>>+ Yeni kategori ekle</option></select><small>Yeni kategori gerekiyorsa listenin sonundaki seçeneği kullanın.</small></label>
                    <label class="field field--wide product-new-category" data-new-category <?= $categoryValue === '__new__' ? '' : 'hidden' ?>><span>Yeni kategori adı <b>*</b></span><input name="new_category_name" value="<?= esc($newCategoryName) ?>" maxlength="120" placeholder="Örn. Promosyon ürünleri" <?= $categoryValue === '__new__' ? 'required' : '' ?> data-new-category-input><small>Kaydettiğinizde kategori oluşturulup bu ürüne bağlanır.</small></label>
                    <label class="field field--wide"><span>Açıklama</span><textarea name="description" rows="4" maxlength="3000" placeholder="Ürünün kullanım alanı ve temel özellikleri"><?= esc(old('description', $product['description'] ?? '')) ?></textarea></label>

                    <div class="field field--wide product-image-field">
                        <span>Ürün görseli</span>
                        <label class="product-image-picker" for="product-image">
                            <span class="product-image-preview" data-image-preview><?php if (! empty($product['image_path'])): ?><img src="<?= base_url($product['image_path']) ?>" alt="Mevcut ürün görseli"><?php else: ?><i aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm0 16H5v-3.5l3.5-3.5 2.5 2.5 2-2L19 18v1ZM8.5 10A1.5 1.5 0 1 1 8.5 7a1.5 1.5 0 0 1 0 3Z"/></svg></i><?php endif; ?></span>
                            <span class="product-image-picker__copy"><small>ÜRÜN FOTOĞRAFI</small><strong data-image-label><?= ! empty($product['image_path']) ? 'Görseli değiştirmek için tıklayın' : 'Görseli buraya bırakın veya seçin' ?></strong><span data-image-description>Net ve kareye yakın bir ürün görseli kullanın.</span><span class="product-image-picker__formats"><em>JPG</em><em>PNG</em><em>WEBP</em><b>En fazla 5 MB</b></span></span>
                            <span class="product-image-picker__button"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 16h2v-5h3l-4-4-4 4h3v5Zm-6 4h14a2 2 0 0 0 2-2v-5h-2v5H5v-5H3v5a2 2 0 0 0 2 2Z"/></svg><span>Görsel seç</span></span>
                            <input class="product-image-picker__input" id="product-image" type="file" name="product_image" accept="image/jpeg,image/png,image/webp" data-image-input>
                        </label>
                        <div class="product-image-field__footer"><small><?= esc($errors['product_image'] ?? 'Görsel güvenli bir adla kalıcı dosya alanına kaydedilir.') ?></small><?php if (! empty($product['image_path'])): ?><label class="product-image-remove"><input type="checkbox" name="remove_image" value="1"><span>Mevcut görseli kaldır</span></label><?php endif; ?></div>
                    </div>

                    <div class="product-switches field--wide"><label class="switch-row"><span><strong>Satışa açık</strong><small>Pasif ürün yeni siparişlerde seçilemez.</small></span><input type="checkbox" name="is_active" value="1" <?= old('is_active', $product['is_active'] ?? 1) ? 'checked' : '' ?>><i aria-hidden="true"></i></label><label class="switch-row"><span><strong>Stok takibi</strong><small>Ürün varyant bazında izlenir.</small></span><input type="checkbox" name="track_stock" value="1" <?= old('track_stock', $product['track_stock'] ?? 1) ? 'checked' : '' ?>><i aria-hidden="true"></i></label><label class="switch-row switch-row--website"><span><strong>Web sitesinde göster</strong><small>Açıksa ürün kurumsal sitede ve teklif formunda görünür.</small></span><input type="checkbox" name="show_on_website" value="1" <?= old('show_on_website', $product['show_on_website'] ?? 0) ? 'checked' : '' ?>><i aria-hidden="true"></i></label></div>
                </div>
            </section>

            <section class="product-form-section product-form__pricing">
                <header class="product-form-section__head"><span class="step-number">2</span><div><h3>Fiyat ve stok temeli</h3><p>Liste fiyatları vergi hariç saklanır</p></div></header>
                <div class="form-grid">
                    <?php if ($canViewCost): ?><label class="field"><span>Alış fiyatı <b>*</b></span><div class="input-suffix"><input name="cost_price" value="<?= esc(old('cost_price', $product['cost_price'] ?? '0')) ?>" inputmode="decimal" required><em>₺</em></div><small>Yalnızca maliyet yetkili kullanıcılar görür.</small></label><?php endif; ?>
                    <label class="field"><span>Liste satış fiyatı <b>*</b></span><div class="input-suffix"><input name="list_price" value="<?= esc(old('list_price', $product['list_price'] ?? '0')) ?>" inputmode="decimal" required><em>₺</em></div><small>Vergi hariç satış temeli.</small></label>
                    <label class="field"><span>Vergi oranı <b>*</b></span><div class="input-suffix"><input name="tax_rate" value="<?= esc(old('tax_rate', $product['tax_rate'] ?? '20')) ?>" inputmode="decimal" required><em>%</em></div><small>Satır vergisi sunucuda hesaplanır.</small></label>
                    <label class="field"><span>Kritik stok seviyesi</span><input name="critical_stock_level" value="<?= esc(old('critical_stock_level', $product['critical_stock_level'] ?? '0')) ?>" inputmode="decimal"><small>Bu seviyede kritik stok uyarısı oluşur.</small></label>
                    <label class="field field--wide"><span>Hazırlama biçimi</span><select name="customization_mode" required><option value="plain_only" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'plain_only' ? 'selected' : '' ?>>Yalnızca baskısız</option><option value="optional" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'optional' ? 'selected' : '' ?>>Baskısız veya müşteriye özel</option><option value="custom_only" <?= old('customization_mode', $product['customization_mode'] ?? 'optional') === 'custom_only' ? 'selected' : '' ?>>Yalnızca müşteriye özel</option></select><small>Müşteriye özel hazırlanan seçenekler ayrı stok koduyla tutulabilir.</small></label>
                </div>
                <aside class="product-price-note"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 17h2v-6h-2v6Zm0-8h2V7h-2v2Zm1-7a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></svg><p><strong>Fiyat mantığı</strong><span>Siparişte müşteri özel fiyatı, grup fiyatı, varyant fiyatı veya liste fiyatı sunucu tarafından seçilir.</span></p></aside>
            </section>

            <section class="product-form-section product-form__variants product-form-section--wide">
                <header class="product-form-section__head"><span class="step-number">3</span><div><h3>Varyantlar</h3><p>Aynı ürünün stokta ayrı takip edilen beden, renk veya hazırlama seçenekleri</p></div></header>
                <div class="variant-explainer"><span class="variant-explainer__icon">V</span><div><strong>Varyant ne demek?</strong><p>Örneğin “Polo Yaka” ana üründür; “M / Lacivert” ve “L / Beyaz” ayrı varyantlardır. Her varyantın stok kodu ve stok miktarı ayrıdır.</p></div><code>Stok kodu | Beden | Renk | Hazırlama | Fiyat</code></div>
                <label class="field"><span>Yeni varyant satırları</span><textarea name="variant_lines" rows="5" placeholder="FM-POLO-M-LAC | M | Lacivert | Baskısız | 350,00"><?= esc(old('variant_lines')) ?></textarea><small>Her satıra bir seçenek yazın. Varyantsız ürünlerde sistem otomatik “Standart” varyant oluşturur; düzenlemede yalnızca yeni satırlar eklenir.</small></label>
                <?php if ($variants): ?><details class="variant-details"><summary>Mevcut <?= count($variants) ?> varyantı göster</summary><div class="table-wrap"><table class="data-table compact-table"><thead><tr><th>Stok kodu</th><th>Beden</th><th>Renk</th><th>Hazırlama</th><th>Özel fiyat</th></tr></thead><tbody><?php foreach ($variants as $variant): ?><tr><td data-label="Stok kodu"><strong><?= esc($variant['sku']) ?></strong></td><td data-label="Beden"><?= esc($variant['size'] ?: '—') ?></td><td data-label="Renk"><?= esc($variant['color'] ?: '—') ?></td><td data-label="Hazırlama"><?= $variant['preparation_type'] === 'customized' ? 'Müşteriye özel' : 'Baskısız' ?></td><td data-label="Özel fiyat"><?= $variant['list_price_override'] !== null ? number_format((float) $variant['list_price_override'], 2, ',', '.') . ' ₺' : 'Liste fiyatı' ?></td></tr><?php endforeach; ?></tbody></table></div></details><?php endif; ?>
            </section>
        </div>

        <footer class="product-form-footer"><div><?php if ($isEdit): ?><button class="button button--danger-ghost" type="submit" form="archive-form">Ürünü arşivle</button><button class="button button--secondary" type="submit" form="status-form"><?= $product['is_active'] ? 'Satışa kapat' : 'Satışa aç' ?></button><?php endif; ?></div><div><a class="button button--secondary" href="<?= site_url('panel/urunler') ?>">Vazgeç</a><button class="button" type="submit"><?= $isEdit ? 'Değişiklikleri kaydet' : 'Ürünü kaydet' ?></button></div></footer>
    </section>
</form>

<?php if ($isEdit): ?>
<form id="status-form" method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/durum') ?>" data-swal-confirm="Ürünün satış durumunu değiştirmek istediğinize emin misiniz?" data-swal-confirm-title="Ürün durumunu değiştir"><?= csrf_field() ?></form>
<form id="archive-form" method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/arsivle') ?>" data-swal-confirm="Ürün panel listesinden kaldırılacak; geçmiş sipariş ve veritabanı kayıtları korunacak. Devam edilsin mi?" data-swal-confirm-title="Ürünü arşivle"><?= csrf_field() ?></form>
<?php endif; ?>

<?php if ($isEdit): ?>
<section class="form-card special-price-card">
    <div class="form-card__head"><span class="step-number">4</span><div><h2>Müşteri ve grup özel fiyatları</h2><p>Liste fiyatını bozmadan, belirli müşteri veya gruba tarihli fiyat tanımlayın.</p></div></div>
    <?php if (isset($errors['special_price'])): ?><div class="alert alert--error"><span><?= esc($errors['special_price']) ?></span></div><?php endif; ?>
    <form method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/ozel-fiyat') ?>" class="special-price-form"><?= csrf_field() ?><label class="field"><span>Hedef <b>*</b></span><select name="target" required><option value="">Müşteri veya grup seçin</option><optgroup label="Fiyat grupları"><?php foreach ($priceGroups as $group): ?><option value="group|<?= esc($group['id']) ?>"><?= esc($group['name']) ?></option><?php endforeach; ?></optgroup><optgroup label="Müşteriler"><?php foreach ($customers as $customer): ?><option value="customer|<?= esc($customer['id']) ?>"><?= esc($customer['company_name']) ?></option><?php endforeach; ?></optgroup></select></label><label class="field"><span>Varyant</span><select name="product_variant_id"><option value="">Tüm varyantlar</option><?php foreach ($variants as $variant): ?><option value="<?= esc($variant['id']) ?>"><?= esc($variant['sku']) ?></option><?php endforeach; ?></select></label><label class="field"><span>Özel birim fiyat <b>*</b></span><div class="input-suffix"><input name="unit_price" inputmode="decimal" required><em>₺</em></div></label><label class="field"><span>Başlangıç</span><input type="datetime-local" name="valid_from"></label><label class="field"><span>Bitiş</span><input type="datetime-local" name="valid_until"></label><button class="button" type="submit">Özel fiyat ekle</button></form>
    <?php if ($specialPrices): ?><div class="table-wrap"><table class="data-table compact-table"><thead><tr><th>Hedef</th><th>Varyant</th><th>Fiyat</th><th>Geçerlilik</th><th>Durum</th><th></th></tr></thead><tbody><?php foreach ($specialPrices as $price): ?><tr><td data-label="Hedef"><?= esc($price['group_name'] ?: $price['company_name']) ?></td><td data-label="Varyant"><?= esc($price['sku'] ?: 'Tüm varyantlar') ?></td><td data-label="Fiyat"><strong><?= number_format((float) $price['unit_price'], 2, ',', '.') ?> ₺</strong></td><td data-label="Geçerlilik"><small><?= esc($price['valid_from'] ?: 'Hemen') ?> → <?= esc($price['valid_until'] ?: 'Süresiz') ?></small></td><td data-label="Durum"><span class="badge <?= $price['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $price['is_active'] ? 'Aktif' : 'Pasif' ?></span></td><td data-label="İşlem"><form method="post" action="<?= site_url('panel/urunler/' . $product['id'] . '/ozel-fiyat/' . $price['id'] . '/durum') ?>"><?= csrf_field() ?><button class="text-link" type="submit"><?= $price['is_active'] ? 'Pasif yap' : 'Etkinleştir' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[data-image-input]');
    const preview = document.querySelector('[data-image-preview]');
    const label = document.querySelector('[data-image-label]');
    const description = document.querySelector('[data-image-description]');
    if (input && preview && label) {
        let previewUrl = null;
        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            preview.innerHTML = '';
            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = 'Seçilen ürün görseli önizlemesi';
            preview.appendChild(image);
            label.textContent = file.name;
            if (description) description.textContent = (file.size / 1024 / 1024).toFixed(2).replace('.', ',') + ' MB · Kaydettiğinizde yüklenecek';
        });
    }

    const category = document.querySelector('[data-category-select]');
    const newCategory = document.querySelector('[data-new-category]');
    const newCategoryInput = document.querySelector('[data-new-category-input]');
    if (category && newCategory && newCategoryInput) {
        const syncCategory = function () {
            const visible = category.value === '__new__';
            newCategory.hidden = !visible;
            newCategoryInput.required = visible;
            if (!visible) newCategoryInput.value = '';
            if (visible) newCategoryInput.focus();
        };
        category.addEventListener('change', syncCategory);
    }
});
</script>
<?= $this->endSection() ?>
