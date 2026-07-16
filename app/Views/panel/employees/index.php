<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<div class="page-actions">
    <div>
        <p class="page-lead">Ekip üyelerini, satış sınırlarını ve hesap bağlantılarını yönetin.</p>
    </div>
    <?php if (auth()->user()?->can('employees.manage')): ?>
        <a class="button" href="<?= site_url('panel/personel/yeni') ?>"><span class="button__plus">+</span> Yeni personel</a>
    <?php endif; ?>
</div>

<div class="stats-grid">
    <article class="stat-card"><span>Toplam personel</span><strong><?= esc($stats['total']) ?></strong><small>Tüm kayıtlar</small></article>
    <article class="stat-card"><span>Aktif personel</span><strong><?= esc($stats['active']) ?></strong><small class="text-success">Çalışmaya açık</small></article>
    <article class="stat-card"><span>Hesabı bağlı</span><strong><?= esc($stats['linked']) ?></strong><small>Panele giriş yapabilir</small></article>
</div>

<section class="panel-card">
    <form class="filter-bar" method="get" action="<?= site_url('panel/personel') ?>">
        <label class="search-field">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.5 3a6.5 6.5 0 1 0 3.98 11.64L19.85 21 21 19.85l-6.36-6.37A6.5 6.5 0 0 0 9.5 3Zm0 2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Z"/></svg>
            <span class="sr-only">Personel ara</span>
            <input type="search" name="q" value="<?= esc($search) ?>" placeholder="Ad, kod, telefon veya e-posta ara">
        </label>
        <label>
            <span class="sr-only">Duruma göre süz</span>
            <select name="durum">
                <option value="">Tüm durumlar</option>
                <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="pasif" <?= $status === 'pasif' ? 'selected' : '' ?>>Pasif</option>
            </select>
        </label>
        <button class="button button--secondary" type="submit">Süz</button>
        <?php if ($search !== '' || $status !== ''): ?><a class="text-link" href="<?= site_url('panel/personel') ?>">Temizle</a><?php endif; ?>
    </form>

    <?php if ($employees === []): ?>
        <div class="empty-state"><span>FM</span><h2>Personel bulunamadı</h2><p>Arama ölçütlerini değiştirin veya ilk personel kaydını oluşturun.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>ID</th><th>Personel</th><th>Görev</th><th>Satış yetkisi</th><th>Hesap</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
                <tbody>
                <?php foreach ($employees as $item): ?>
                    <tr>
                        <td data-label="ID"><?= (int) $item['id'] ?></td>
                        <td data-label="Personel"><div class="person-cell"><span class="person-avatar"><?= esc(mb_strtoupper(mb_substr($item['full_name'], 0, 1))) ?></span><div><strong><?= esc($item['full_name']) ?></strong><small><?= esc($item['employee_code']) ?><?= $item['phone'] ? ' · ' . esc($item['phone']) : '' ?></small></div></div></td>
                        <td data-label="Görev"><?= $item['role_title'] ? '<span class="role-pill">' . esc($item['role_title']) . '</span>' : '<span class="muted">Atanmadı</span>' ?></td>
                        <td data-label="Satış yetkisi"><strong>%<?= esc(rtrim(rtrim(number_format((float) $item['max_discount_percent'], 2, ',', '.'), '0'), ',')) ?></strong><small class="cell-note"><?= $item['can_collect_payment'] ? 'Tahsilat bildirimi açık' : 'Tahsilat bildirimi kapalı' ?></small></td>
                        <td data-label="Hesap"><?php if ($item['account_email']): ?><span class="account-state account-state--linked"><i></i> Bağlı</span><small class="cell-note"><?= esc($item['account_email']) ?></small><?php else: ?><span class="account-state"><i></i> Bağlı değil</span><?php endif; ?></td>
                        <td data-label="Durum"><span class="badge <?= $item['is_active'] ? 'badge--success' : 'badge--neutral' ?>"><?= $item['is_active'] ? 'Aktif' : 'Pasif' ?></span></td>
                        <td class="row-actions" data-label="İşlem">
                            <?php if (auth()->user()?->can('employees.manage')): ?>
                                <a class="icon-button" href="<?= site_url('panel/personel/' . $item['id'] . '/duzenle') ?>" aria-label="<?= esc($item['full_name']) ?> kaydını düzenle" title="Düzenle"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 17.25-.03 3.78 3.78-.03L17.81 9.94l-3.75-3.75L3 17.25ZM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83Z"/></svg></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?= view('components/table_pagination', ['pagination' => $pagination]) ?>
</section>
<?= $this->endSection() ?>
