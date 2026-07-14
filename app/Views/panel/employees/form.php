<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$isEdit = $employee !== null;
$accountValue = old('account_user_id', $employee['user_id'] ?? '');
?>
<div class="form-heading">
    <a class="back-link" href="<?= site_url('panel/personel') ?>">← Personel listesi</a>
    <p class="page-lead"><?= $isEdit ? 'Personel bilgilerini ve çalışma yetkilerini güncelleyin.' : 'Ekibinize yeni bir personel ekleyin.' ?></p>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error" role="alert"><strong>Formu kontrol edin.</strong><span><?= esc($errors['form'] ?? 'Eksik veya hatalı alanlar bulunuyor.') ?></span></div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? site_url('panel/personel/' . $employee['id'] . '/duzenle') : site_url('panel/personel/yeni') ?>" class="employee-form">
    <?= csrf_field() ?>
    <section class="form-card">
        <div class="form-card__head"><span class="step-number">1</span><div><h2>Temel bilgiler</h2><p>Personeli ekip içinde tanımlayan bilgiler.</p></div></div>
        <div class="form-grid">
            <label class="field"><span>Personel kodu <b>*</b></span><input name="employee_code" value="<?= esc(old('employee_code', $employee['employee_code'] ?? '')) ?>" maxlength="30" required placeholder="Örn. FM-024"><small><?= esc($errors['employee_code'] ?? 'Kısa ve benzersiz bir kod kullanın.') ?></small></label>
            <label class="field"><span>Ad soyad <b>*</b></span><input name="full_name" value="<?= esc(old('full_name', $employee['full_name'] ?? '')) ?>" maxlength="150" required autocomplete="name" placeholder="Ad Soyad"><small><?= esc($errors['full_name'] ?? '') ?></small></label>
            <label class="field"><span>Telefon</span><input name="phone" value="<?= esc(old('phone', $employee['phone'] ?? '')) ?>" maxlength="30" inputmode="tel" autocomplete="tel" placeholder="05xx xxx xx xx"><small><?= esc($errors['phone'] ?? '') ?></small></label>
            <label class="switch-row"><span><strong>Aktif personel</strong><small>Pasif personel yeni işlemlerde seçilemez.</small></span><input type="checkbox" name="is_active" value="1" <?= old('is_active', $employee['is_active'] ?? 1) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
        </div>
    </section>

    <section class="form-card">
        <div class="form-card__head"><span class="step-number">2</span><div><h2>Satış yetkileri</h2><p>Personelin sahada kullanabileceği sınırlar.</p></div></div>
        <div class="form-grid">
            <label class="field"><span>En yüksek indirim oranı <b>*</b></span><div class="input-suffix"><input name="max_discount_percent" value="<?= esc(old('max_discount_percent', $employee['max_discount_percent'] ?? '0')) ?>" inputmode="decimal" required><em>%</em></div><small><?= esc($errors['max_discount_percent'] ?? '0 ile 100 arasında bir oran.') ?></small></label>
            <label class="switch-row"><span><strong>Tahsilat bildirimi</strong><small>Personel müşteriden aldığı ödemeyi bildirebilir.</small></span><input type="checkbox" name="can_collect_payment" value="1" <?= old('can_collect_payment', $employee['can_collect_payment'] ?? 0) ? 'checked' : '' ?>><i aria-hidden="true"></i></label>
        </div>
    </section>

    <?php if ($canManageUsers): ?>
        <section class="form-card">
            <div class="form-card__head"><span class="step-number">3</span><div><h2>Panel hesabı</h2><p>İsteğe bağlı giriş hesabı ve görev bağlantısı.</p></div></div>
            <div class="form-grid">
                <label class="field field--wide"><span>Kullanıcı hesabı</span><select name="account_user_id" data-account-select><option value="">Hesap bağlama</option><?php foreach ($users as $account): ?><option value="<?= esc($account['id']) ?>" <?= (string) $accountValue === (string) $account['id'] ? 'selected' : '' ?>><?= esc($account['email']) ?></option><?php endforeach; ?><option value="new" <?= $accountValue === 'new' ? 'selected' : '' ?>>+ Yeni kullanıcı hesabı oluştur</option></select><small>Bir hesap yalnızca bir personele bağlanabilir.</small></label>
                <div class="form-grid field--wide account-new" data-account-new <?= $accountValue === 'new' ? '' : 'hidden' ?>>
                    <label class="field"><span>Giriş e-postası <b>*</b></span><input type="email" name="login_email" value="<?= esc(old('login_email')) ?>" autocomplete="email" placeholder="personel@formmix.com"><small><?= esc($errors['login_email'] ?? '') ?></small></label>
                    <label class="field"><span>Başlangıç parolası <b>*</b></span><input type="password" name="login_password" autocomplete="new-password" minlength="12" placeholder="En az 12 karakter"><small><?= esc($errors['login_password'] ?? ($isEdit && $employee['user_id'] ? 'Boş bırakırsanız parola değişmez.' : '')) ?></small></label>
                </div>
                <label class="field field--wide" data-account-role <?= $accountValue === '' ? 'hidden' : '' ?>><span>Görev <b>*</b></span><select name="role"><option value="">Görev seçin</option><?php foreach ($roles as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('role', $currentRole) === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select><small><?= esc($errors['role'] ?? 'Görev, kullanıcının sistemde neleri görebileceğini belirler.') ?></small></label>
            </div>
        </section>
    <?php endif; ?>

    <div class="form-actions">
        <?php if ($isEdit): ?>
            <button class="button button--danger-ghost" type="submit" form="status-form"><?= $employee['is_active'] ? 'Pasif yap' : 'Etkinleştir' ?></button>
        <?php endif; ?>
        <span></span><a class="button button--secondary" href="<?= site_url('panel/personel') ?>">Vazgeç</a><button class="button" type="submit"><?= $isEdit ? 'Değişiklikleri kaydet' : 'Personeli kaydet' ?></button>
    </div>
</form>
<?php if ($isEdit): ?><form id="status-form" method="post" action="<?= site_url('panel/personel/' . $employee['id'] . '/durum') ?>" onsubmit="return confirm('Personelin durumunu değiştirmek istediğinize emin misiniz?')"><?= csrf_field() ?></form><?php endif; ?>
<?= $this->endSection() ?>
