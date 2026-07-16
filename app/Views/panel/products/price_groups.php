<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<?php $errors = session('errors') ?? []; ?>
<div class="form-heading"><a class="back-link" href="<?= site_url('panel/urunler') ?>">← Ürün listesi</a><p class="page-lead">Müşterilere ortak indirim veya ürün özel fiyatı uygulamak için gruplar oluşturun.</p></div>
<?php if ($errors): ?><div class="alert alert--error" role="alert"><strong>Formu kontrol edin.</strong><span><?= esc(reset($errors)) ?></span></div><?php endif; ?>

<div class="detail-grid price-group-layout">
    <section class="form-card">
        <div class="form-card__head"><span class="step-number">1</span><div><h2>Yeni fiyat grubu</h2><p>İndirim, vergi hariç liste fiyatı üzerinden hesaplanır.</p></div></div>
        <form method="post" action="<?= site_url('panel/urunler/fiyat-gruplari') ?>" class="employee-form">
            <?= csrf_field() ?>
            <div class="form-grid">
                <label class="field"><span>Grup kodu <b>*</b></span><input name="code" value="<?= esc(old('code')) ?>" maxlength="30" required placeholder="Örn. BAYI"><small><?= esc($errors['code'] ?? 'Benzersiz kısa kod.') ?></small></label>
                <label class="field"><span>Grup adı <b>*</b></span><input name="name" value="<?= esc(old('name')) ?>" maxlength="120" required placeholder="Bayi müşterileri"><small><?= esc($errors['name'] ?? '') ?></small></label>
                <label class="field"><span>Varsayılan indirim</span><div class="input-suffix"><input name="discount_percent" value="<?= esc(old('discount_percent', '0')) ?>" inputmode="decimal" required><em>%</em></div><small>Personelin sipariş indirim yetkisini artırmaz.</small></label>
                <label class="field field--wide"><span>Açıklama</span><textarea name="description" rows="3" maxlength="1000"><?= esc(old('description')) ?></textarea></label>
            </div>
            <div class="form-actions"><span></span><span></span><button class="button" type="submit">Fiyat grubunu kaydet</button></div>
        </form>
    </section>

    <section class="panel-card">
        <div class="section-heading-inline"><div><h2>Tanımlı gruplar</h2><p>Ürün düzenleme ekranında bu gruplara özel fiyat verebilirsiniz.</p></div></div>
        <?php if ($groups === []): ?><div class="empty-state"><span>F</span><h2>Fiyat grubu yok</h2></div><?php else: ?><div class="table-wrap"><table class="data-table"><thead><tr><th>ID</th><th>Grup</th><th>İndirim</th><th>Durum</th><th></th></tr></thead><tbody><?php foreach ($groups as $group): ?><tr><td data-label="ID"><strong>#<?= (int) $group['id'] ?></strong></td><td data-label="Grup"><strong><?= esc($group['name']) ?></strong><small class="cell-note"><?= esc($group['code']) ?><?= $group['description'] ? ' · ' . esc($group['description']) : '' ?></small></td><td data-label="İndirim"><strong>%<?= number_format((float) $group['discount_percent'], 2, ',', '.') ?></strong></td><td data-label="Durum"><span class="badge <?= $group['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $group['is_active'] ? 'Aktif' : 'Pasif' ?></span></td><td data-label="İşlem"><form method="post" action="<?= site_url('panel/urunler/fiyat-gruplari/' . $group['id'] . '/durum') ?>"><?= csrf_field() ?><button class="text-link" type="submit"><?= $group['is_active'] ? 'Pasif yap' : 'Etkinleştir' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
        <?= view('components/table_pagination', ['pagination' => $pagination]) ?>
    </section>
</div>
<?= $this->endSection() ?>
