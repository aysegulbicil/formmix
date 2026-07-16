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
        <label class="search-field search-field--icon"><span class="sr-only">Ürün ara</span><input type="search" name="q" value="<?= esc($search) ?>" placeholder="Ürün adı veya kodu ara"></label>
        <label><span class="sr-only">Kategori</span><select name="kategori"><option value="">Tüm kategoriler</option><?php foreach ($categories as $item): ?><option value="<?= esc($item['id']) ?>" <?= (string) $category === (string) $item['id'] ? 'selected' : '' ?>><?= esc($item['name']) ?></option><?php endforeach; ?></select></label>
        <label><span class="sr-only">Durum</span><select name="durum"><option value="">Tüm durumlar</option><option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="pasif" <?= $status === 'pasif' ? 'selected' : '' ?>>Pasif</option></select></label>
        <button class="button button--secondary" type="submit">Süz</button>
        <?php if ($search !== '' || $category !== '' || $status !== ''): ?><a class="text-link" href="<?= site_url('panel/urunler') ?>">Temizle</a><?php endif; ?>
    </form>

    <?php if ($products === []): ?>
        <div class="empty-state"><span>Ü</span><h2>Ürün bulunamadı</h2><p>Arama ölçütlerini değiştirin veya yeni ürün oluşturun.</p></div>
    <?php else: ?>
        <form method="post" action="<?= site_url('panel/urunler/toplu-fiyat') ?>" data-swal-confirm="Seçili ürünlerin liste fiyatları güncellensin mi?" data-swal-confirm-title="Toplu fiyat güncelleme">
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
                            <td data-label="Durum"><div class="product-status-stack"><span class="badge <?= $item['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $item['is_active'] ? 'Satışa açık' : 'Pasif' ?></span><span class="badge <?= $item['show_on_website'] ? 'badge--info' : 'badge--neutral' ?>"><?= $item['show_on_website'] ? 'Webde görünür' : 'Webde gizli' ?></span></div></td>
                            <td class="row-actions" data-label="İşlem">
                                <?php if ($canManage): ?>
                                    <div class="row-action-group">
                                        <a class="icon-button" href="<?= site_url('panel/urunler/' . $item['id'] . '/duzenle') ?>" aria-label="<?= esc($item['name']) ?> ürününü düzenle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17.25-.03 3.78 3.78-.03L17.81 9.94l-3.75-3.75L3 17.25ZM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83Z"/></svg></a>
                                        <button class="icon-button icon-button--danger" type="submit" form="archive-product-<?= esc($item['id']) ?>" aria-label="<?= esc($item['name']) ?> ürününü arşivle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12ZM8 9h8v10H8V9Zm7.5-5-1-1h-5l-1 1H5v2h14V4h-3.5Z"/></svg></button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($canManage): ?><div class="bulk-bar bulk-price-bar"><div class="bulk-price-bar__copy"><strong>Toplu liste fiyatı güncelleme</strong><small>Seçili ürünlerin mevcut fiyatına yüzde uygular; değişiklikler geçmişe yazılır.</small></div><div class="bulk-price-bar__actions"><label class="input-suffix bulk-price-bar__input"><input name="change_percent" inputmode="decimal" placeholder="Örn. 10 veya -5" aria-label="Fiyat değişim yüzdesi"><em>%</em></label><button class="button button--secondary" type="submit">Fiyatları güncelle</button></div></div><?php endif; ?>
        </form>
        <?php if ($canManage): ?>
            <?php foreach ($products as $item): ?>
                <form id="archive-product-<?= esc($item['id']) ?>" method="post" action="<?= site_url('panel/urunler/' . $item['id'] . '/arsivle') ?>" data-swal-confirm="<?= esc($item['name']) ?> ürünü listeden kaldırılsın mı? Geçmiş sipariş ve stok kayıtları korunacaktır." data-swal-confirm-title="Ürünü arşivle">
                    <?= csrf_field() ?>
                </form>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
