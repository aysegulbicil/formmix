<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<div class="customer-page-head">
    <div>
        <span class="eyebrow">Müşteri yönetimi</span>
        <p class="page-lead"><?= $canViewAll ? 'Müşteri portföyünü, sorumluları ve son işlemleri tek ekrandan yönetin.' : 'Size atanan müşterileri ve görüşmelerinizi yönetin.' ?></p>
    </div>
    <?php if (auth()->user()?->can('customers.create')): ?>
        <a class="button" href="<?= site_url('panel/musteriler/yeni') ?>"><span class="button__plus">+</span> Yeni müşteri</a>
    <?php endif; ?>
</div>

<div class="customer-stats">
    <article class="customer-stat customer-stat--total">
        <span class="customer-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg></span>
        <div><span>Toplam müşteri</span><strong><?= esc($stats['total']) ?></strong><small>Görüntüleme alanınız</small></div>
    </article>
    <article class="customer-stat customer-stat--active">
        <span class="customer-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m9 16.2-4.2-4.2-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2Z"/></svg></span>
        <div><span>Aktif müşteri</span><strong><?= esc($stats['active']) ?></strong><small>Çalışılan firmalar</small></div>
    </article>
    <article class="customer-stat customer-stat--pending">
        <span class="customer-stat__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M11 7h2v6h-2V7Zm0 8h2v2h-2v-2Zm1-13C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></svg></span>
        <div><span>Atama bekleyen</span><strong><?= esc($stats['unassigned']) ?></strong><small>Sorumlusu bulunmuyor</small></div>
    </article>
</div>

<section class="customer-directory panel-card customer-list-card">
    <form class="filter-bar customer-filter" method="get">
        <label class="search-field search-field--icon"><span class="sr-only">Müşteri ara</span><input type="search" name="q" value="<?= esc($search) ?>" placeholder="Firma, kod, yetkili veya telefon ara"></label>
        <label><span class="sr-only">Durum</span><select name="durum"><option value="">Tüm durumlar</option><?php foreach ($statuses as $key => $label): ?><option value="<?= esc($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></label>
        <?php if ($canViewAll): ?><label><span class="sr-only">Sorumlu</span><select name="sorumlu"><option value="">Tüm sorumlular</option><?php foreach ($employees as $person): ?><option value="<?= esc($person['id']) ?>" <?= $owner === $person['id'] ? 'selected' : '' ?>><?= esc($person['full_name']) ?></option><?php endforeach; ?></select></label><?php endif; ?>
        <button class="button button--secondary" type="submit">Süz</button>
        <?php if ($search || $status || $owner): ?><a class="text-link" href="<?= site_url('panel/musteriler') ?>">Temizle</a><?php endif; ?>
    </form>

    <?php if (!$customers): ?>
        <div class="empty-state customer-empty"><span>FM</span><h2>Müşteri bulunamadı</h2><p>Arama ölçütlerini değiştirin veya yeni müşteri ekleyin.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table customer-table">
                <thead><tr><th>ID</th><th>Müşteri</th><th>Konum</th><th>Yetkili</th><th>Telefon</th><th>Sorumlu</th><th>Son işlem</th><th>Durum</th><th><span class="sr-only">İşlem</span></th></tr></thead>
                <tbody>
                <?php foreach ($customers as $item): ?>
                    <tr>
                        <td data-label="ID"><?= (int) $item['id'] ?></td>
                        <td data-label="Müşteri"><a class="customer-list-company" href="<?= site_url('panel/musteriler/' . $item['id']) ?>"><span class="company-avatar"><?= esc(mb_strtoupper(mb_substr($item['company_name'], 0, 1))) ?></span><span><strong><?= esc($item['company_name']) ?></strong><small><?= esc($item['customer_code']) ?></small></span></a></td>
                        <td data-label="Konum"><strong><?= esc($item['city']) ?></strong><small class="cell-note"><?= esc($item['district']) ?></small></td>
                        <td data-label="Yetkili"><?= esc($item['contact_name'] ?? '—') ?></td>
                        <td data-label="Telefon"><span class="customer-phone"><?= esc($item['contact_phone'] ?? '—') ?></span></td>
                        <td data-label="Sorumlu"><?= esc($item['owner_name'] ?? 'Atama bekliyor') ?></td>
                        <td data-label="Son işlem"><?= $item['last_activity_at'] ? esc(date('d.m.Y', strtotime($item['last_activity_at']))) : 'Henüz yok' ?></td>
                        <td data-label="Durum"><span class="badge <?= $item['status'] === 'active' ? 'badge--success' : 'badge--neutral' ?>"><?= esc($statuses[$item['status']] ?? $item['status']) ?></span></td>
                        <td class="row-actions" data-label="İşlem"><a class="icon-button customer-open-button" href="<?= site_url('panel/musteriler/' . $item['id']) ?>" aria-label="<?= esc($item['company_name']) ?> müşteri profilini aç"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6-1.4 1.4 4.6 4.6-4.6 4.6L9 18Z"/></svg></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?= view('components/table_pagination', ['pagination' => $pagination]) ?>
</section>
<?= $this->endSection() ?>
