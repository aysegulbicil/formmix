<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<section class="welcome-card">
    <div>
        <span class="eyebrow">İyi çalışmalar</span>
        <h2>Ekibiniz ve müşteri ilişkileriniz tek yerde.</h2>
        <p>Personel kayıtlarını yönetin, kullanıcı hesaplarını görevlere bağlayın ve saha ekibiniz için güvenli temeli hazırlayın.</p>
    </div>
    <?php if ($user?->can('employees.view')): ?>
        <a class="button button--light" href="<?= site_url('panel/personel') ?>">Personeli görüntüle <span aria-hidden="true">→</span></a>
    <?php endif; ?>
</section>

<div class="section-heading">
    <div><span class="eyebrow">Adım 2</span><h2>Hızlı erişim</h2></div>
</div>
<div class="action-grid">
    <?php if ($user?->can('employees.view')): ?>
        <a class="action-card" href="<?= site_url('panel/personel') ?>">
            <span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3ZM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5Z"/></svg></span>
            <div><h3>Personel yönetimi</h3><p>Ekibi, yetkileri ve kullanıcı bağlantılarını yönetin.</p></div><span class="action-card__arrow">→</span>
        </a>
    <?php endif; ?>
    <div class="action-card action-card--muted">
        <span class="action-card__icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4Z"/></svg></span>
        <div><h3>Müşteri yönetimi</h3><p>Personel modülünden sonra kullanıma açılacak.</p></div><span class="badge badge--neutral">Yakında</span>
    </div>
</div>
<?= $this->endSection() ?>
