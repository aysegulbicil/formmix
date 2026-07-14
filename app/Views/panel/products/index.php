<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<div class="page-actions">
    <div><p class="page-lead">Satışa açık ürünleri, seçenekleri ve vergi hariç liste fiyatlarını yönetin.</p></div>
    <?php if ($canManage): ?>
        <div class="action-group"><a class="button button--secondary" href="<?= site_url('panel/urunler/fiyat-gruplari') ?>">Fiyat grupları</a><a class="button" href="<?= site_url('panel/urunler/yeni') ?>"><span class="button__plus">+</span> Yeni ürün</a></div>
    <?php endif; ?>
</div>

<?php if ($errors): ?><div class="alert alert--error" role="alert"><strong>İşlem tamamlanamadı.</strong><span><?= esc($errors['form'] ?? reset($errors)) ?></span></div><?php endif; ?>

<section class="panel-card">
    <form class="filter-bar" method="get" action="<?= site_url('panel/urunler') ?>">
        <label class="search-field"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3a6.5 6.5 0 1 0 3.98 11.64L19.85 21 21 19.85l-6.36-6.37A6.5 6.5 0 0 0 9.5 3Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/></svg><span class="sr-only">Ürün ara</span><input type="search" name="q" value="<?= esc($search) ?>" placeholder="Ürün adı veya kodu ara"></label>
        <label><span class="sr-only">Kategori</span><select name="kategori"><option value="">Tüm kategoriler</option><?php foreach ($categories as $item): ?><option value="<?= esc($item['id']) ?>" <?= (string) $category === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
        <label><span class="sr-only">Durum</span><select name="durum"><option value="">Tüm durumlar</option><option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="pasif" <?= $status === 'pasif' ? 'selected' : '' ?>>Pasif</option></select></label>
        <button class="button button--secondary" type="submit">Süz</button>
        <?php if ($search !== '' || $category !== '' || $status !== ''): ?><a class="text-link" href="<?= site_url('panel/urunler') ?>">Temizle</a><?php endif; ?>
    </form>

    <?php if ($products === []): ?>
        <div class="empty-state"><span>Ü</span><h2>Ürün bulunamadı</h2><p>Arama ölçütlerini değiştirin veya yeni ürün oluşturun.</p></div>
    <?php else: ?>
        <form method="post" action="<?= site_url('panel/urunler/toplu-fiyat') ?>">
            <?= csrf_field() ?>
            <div class="table-wrap">
                <table class="data-table product-table">
                    <thead><tr><?php if ($canManage): ?><th class="check-cell"><input type="checkbox" data-check-all aria-label="Tüm ürünleri seç"></th><?php endif; ?><th>Ürün</th><th>Kategori</th><th>Liste fiyatı</th><?php if ($canViewCost): ?><th>Alış fiyatı</th><?php endif; ?><th>Seçenekler</th><th>Durum</th><th><span class="sr-only">İşlem</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($products as $item): ?>
                        <tr>
                            <?php if ($canManage): ?><td class="check-cell" data-label="Seç"><input type="checkbox" name="product_ids[]" value="<?= esc($item['id']) ?>" data-check-item aria-label="<?= esc($item['name']) ?> ürününü seç"></td><?php endif; ?>
                            <td data-label="Ürün"><div class="product-cell"><?php if ($item['image_path']): ?><img src="<?= base_url($item['image_path']) ?>" alt=""><?php else: ?><span>Ü</span><?php endif; ?><div><strong><?= esc($item['name']) ?></strong><small><?= esc($item['product_code']) ?></small></div></div></td>
                            <td data-label="Kategori"><?= esc($item['category_name'] ?? 'Kategorisiz') ?></td>
                            <td data-label="Liste fiyatı"><?php if ((float) $item['list_price'] > 0): ?><strong><?= number_format((float) $item['list_price'], 2, ',', '.') ?> ₺</strong><small class="cell-note">Vergi hariç · KDV %<?= esc(rtrim(rtrim((string) $item['tax_rate'], '0'), '.')) ?></small><?php else: ?><span class="badge badge--warning">Fiyat bekliyor</span><?php endif; ?></td>
                            <?php if ($canViewCost): ?><td data-label="Alış fiyatı"><strong><?= number_format((float) $item['cost_price'], 2, ',', '.') ?> ₺</strong></td><?php endif; ?>
                            <td data-label="Seçenekler"><strong><?= esc($item['variant_count']) ?> varyant</strong><small class="cell-note"><?= $item['size_count'] ? esc($item['size_count']) . ' beden' : 'Beden yok' ?> · <?= $item['color_count'] ? esc($item['color_count']) . ' renk' : 'Renk yok' ?><?= $item['has_customized'] ? ' · Özel ürün' : '' ?></small></td>
                            <td data-label="Durum"><span class="badge <?= $item['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $item['is_active'] ? 'Satışa açık' : 'Pasif' ?></span></td>
                            <td class="row-actions"><?php if ($canManage): ?><a class="icon-button" href="<?= site_url('panel/urunler/' . $item['id'] . '/duzenle') ?>" aria-label="<?= esc($item['name']) ?> ürününü düzenle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17.25-.03 3.78 3.78-.03L17.81 9.94l-3.75-3.75L3 17.25ZM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83Z"/></svg></a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($canManage): ?><div class="bulk-bar"><div><strong>Toplu liste fiyatı güncelleme</strong><small>Seçili ürünlerin mevcut fiyatına yüzde uygular; değişiklikler geçmişe yazılır.</small></div><label class="input-suffix"><input name="change_percent" inputmode="decimal" placeholder="Örn. 10 veya -5" aria-label="Fiyat değişim yüzdesi"><em>%</em></label><button class="button button--secondary" type="submit" onclick="return confirm('Seçili ürünlerin liste fiyatları güncellensin mi?')">Fiyatları güncelle</button></div><?php endif; ?>
        </form>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
