<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>
<?php
$errors = session('errors') ?? [];
$isEdit = $customer !== null;
$backUrl = $isEdit ? site_url('panel/musteriler/' . $customer['id']) : site_url('panel/musteriler');
$commercialOpen = $isEdit || $errors !== [];
?>

<div class="form-heading customer-form-heading">
    <a class="back-link" href="<?= $backUrl ?>">← Müşterilere dön</a>
    <div><span class="eyebrow"><?= $isEdit ? 'Müşteri yönetimi' : 'Yeni müşteri' ?></span><h2><?= $isEdit ? 'Firma kaydını güncelleyin' : 'Müşteri bilgilerini tek ekranda tamamlayın' ?></h2><p class="page-lead"><?= $isEdit ? 'Firma, yetkili ve ticari bilgileri birlikte düzenleyin.' : 'Zorunlu alanları doldurun; diğer bilgileri daha sonra tamamlayabilirsiniz.' ?></p></div>
</div>

<?php if ($errors): ?><div class="alert alert--error"><strong>Formu kontrol edin.</strong><span><?= esc($errors['duplicate'] ?? $errors['contact'] ?? $errors['form'] ?? 'Eksik veya hatalı alanlar bulunuyor.') ?></span></div><?php endif; ?>
<div class="duplicate-alert" data-duplicate-alert hidden></div>

<form method="post" action="<?= $isEdit ? site_url('panel/musteriler/' . $customer['id'] . '/duzenle') : site_url('panel/musteriler/yeni') ?>" class="customer-form customer-form--unified" data-duplicate-url="<?= site_url('panel/musteriler/tekrar-kontrol') ?>" data-customer-id="<?= esc($customer['id'] ?? '') ?>">
    <?= csrf_field() ?>
    <section class="form-card customer-form-shell">
        <div class="customer-form-shell__intro"><div><span class="eyebrow">Müşteri kartı</span><h2><?= $isEdit ? esc($customer['company_name']) : 'Yeni firma kaydı' ?></h2></div><span class="customer-form-shell__required"><i>*</i> Zorunlu alan</span></div>

        <div class="customer-form-layout">
            <section class="customer-form-section">
                <header class="customer-form-section__head"><span class="step-number">1</span><div><h3>Firma bilgileri</h3><p>Müşteriyi tanımlayan temel bilgiler</p></div></header>
                <div class="form-grid">
                    <label class="field"><span>Firma adı <b>*</b></span><input name="company_name" value="<?= esc(old('company_name', $customer['company_name'] ?? '')) ?>" required maxlength="180" placeholder="Firma adı"><small><?= esc($errors['company_name'] ?? '') ?></small></label>
                    <label class="field"><span>Resmî unvan</span><input name="official_title" value="<?= esc(old('official_title', $customer['official_title'] ?? '')) ?>" maxlength="220" placeholder="Fatura unvanı"><small>Sipariş aşamasında tamamlanabilir.</small></label>
                    <label class="field"><span>İl <b>*</b></span><input name="city" value="<?= esc(old('city', $customer['city'] ?? '')) ?>" required placeholder="İstanbul"><small><?= esc($errors['city'] ?? '') ?></small></label>
                    <label class="field"><span>İlçe <b>*</b></span><input name="district" value="<?= esc(old('district', $customer['district'] ?? '')) ?>" required placeholder="Ümraniye"><small><?= esc($errors['district'] ?? '') ?></small></label>
                    <label class="field"><span>Firma e-postası</span><input type="email" name="email" value="<?= esc(old('email', $customer['email'] ?? '')) ?>" placeholder="firma@ornek.com"><small><?= esc($errors['email'] ?? '') ?></small></label>
                    <label class="field"><span>Durum</span><select name="status"><?php foreach ($statuses as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('status', $customer['status'] ?? 'candidate') === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select><small>Müşteri çalışma durumunu belirler.</small></label>
                </div>
            </section>

            <section class="customer-form-section">
                <header class="customer-form-section__head"><span class="step-number">2</span><div><h3>Yetkili kişi</h3><p>Firma içindeki ana iletişim kişisi</p></div></header>
                <div class="form-grid">
                    <label class="field"><span>Ad soyad <b>*</b></span><input name="contact_name" value="<?= esc(old('contact_name', $contact['full_name'] ?? '')) ?>" required placeholder="Yetkili kişi"><small></small></label>
                    <label class="field"><span>Görevi</span><input name="contact_job_title" value="<?= esc(old('contact_job_title', $contact['job_title'] ?? '')) ?>" placeholder="Satın alma sorumlusu"><small></small></label>
                    <label class="field"><span>Telefon <b>*</b></span><input name="contact_phone" value="<?= esc(old('contact_phone', $contact['phone'] ?? '')) ?>" required inputmode="tel" placeholder="05xx xxx xx xx" data-duplicate-phone><small>Aynı numara için sistem uyarır.</small></label>
                    <label class="field"><span>E-posta</span><input type="email" name="contact_email" value="<?= esc(old('contact_email', $contact['email'] ?? '')) ?>" placeholder="yetkili@firma.com"><small></small></label>
                </div>
            </section>

            <details class="customer-form-details" <?= $commercialOpen ? 'open' : '' ?>>
                <summary><span class="step-number">3</span><span><strong>Adres ve ticari bilgiler</strong><small>İsteğe bağlı alanlar · gerektiğinde tamamlayın</small></span><i aria-hidden="true"></i></summary>
                <div class="customer-form-details__content"><div class="form-grid customer-commercial-grid">
                    <label class="field customer-address-field"><span>Açık adres</span><textarea name="address" rows="4" placeholder="Firma adresi"><?= esc(old('address', $customer['address'] ?? '')) ?></textarea><small>Teslimat adresi siparişte ayrıca değiştirilebilir.</small></label>
                    <label class="field"><span>Vergi dairesi</span><input name="tax_office" value="<?= esc(old('tax_office', $customer['tax_office'] ?? '')) ?>"><small></small></label>
                    <label class="field"><span>Vergi numarası</span><input name="tax_number" value="<?= esc(old('tax_number', $customer['tax_number'] ?? '')) ?>" inputmode="numeric" data-duplicate-tax><small>Aynı numara için sistem uyarır.</small></label>
                    <label class="field"><span>Vade günü <b>*</b></span><input type="number" name="payment_term_days" min="0" max="365" value="<?= esc(old('payment_term_days', $customer['payment_term_days'] ?? 30)) ?>" required><small>Bilgilendirme alanıdır; cari modül bu sürümde kapalıdır.</small></label>
                    <label class="field"><span>Borç sınırı</span><div class="input-suffix"><input name="credit_limit" inputmode="decimal" value="<?= esc(old('credit_limit', $customer['credit_limit'] ?? '')) ?>"><em>₺</em></div><small>Cari modül etkinleştirilirse kullanılacaktır.</small></label>
                    <?php if ($canAssign && ! $isEdit): ?><label class="field field--wide"><span>İlk sorumlu</span><select name="current_owner_employee_id"><option value="">Daha sonra ata</option><?php foreach ($employees as $person): ?><option value="<?= esc($person['id']) ?>" <?= old('current_owner_employee_id') == $person['id'] ? 'selected' : '' ?>><?= esc($person['full_name']) ?></option><?php endforeach; ?></select><small>Saha personeli kendi açtığı müşteriye otomatik atanır.</small></label><?php endif; ?>
                </div></div>
            </details>
        </div>

        <footer class="customer-form-actions"><a class="button button--secondary" href="<?= $backUrl ?>">Vazgeç</a><button class="button" type="submit"><?= $isEdit ? 'Değişiklikleri kaydet' : 'Müşteriyi kaydet' ?></button></footer>
    </section>
</form>
<?= $this->endSection() ?>
