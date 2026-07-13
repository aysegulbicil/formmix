<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php
$errors   = session('errors') ?? [];
$sent     = session('sent') ?? false;
$products = site_data('products');
$selected = old('product');
?>

<?= view('components/page_hero', [
    'eyebrow' => 'İletişim',
    'title'   => 'Teklif alın, hemen başlayalım',
    'text'    => 'En hızlı yol WhatsApp. Dilerseniz formu doldurun, size biz ulaşalım.',
    'current' => 'İletişim',
]) ?>

<section class="section">
    <div class="container contact-grid">

        <!-- Form -->
        <div class="card-box">
            <h2 class="section__title" style="font-size:24px; margin-bottom:6px;">Teklif Formu</h2>
            <p style="color:var(--muted); margin-bottom:24px;">Bilgilerinizi bırakın, en kısa sürede dönüş yapalım.</p>

            <?php if ($sent): ?>
                <div class="alert alert--success" role="status">
                    <?= icon('check', 20) ?>
                    <span>Mesajınız alındı. En kısa sürede size dönüş yapacağız. Hızlı yanıt için WhatsApp'tan da yazabilirsiniz.</span>
                </div>
            <?php endif; ?>

            <?php if (! empty($errors)): ?>
                <div class="alert alert--error" role="alert">
                    <?= icon('arrow', 20) ?>
                    <span>Lütfen formdaki işaretli alanları kontrol edin.</span>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('iletisim') ?>" method="post" novalidate>
                <!-- Bal küpü (spam koruması) — kullanıcı görmez -->
                <div style="position:absolute; left:-9999px;" aria-hidden="true">
                    <label>Web siteniz <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Ad Soyad <span class="req">*</span></label>
                        <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" type="text" id="name" name="name" value="<?= esc(old('name')) ?>" required>
                        <?php if (isset($errors['name'])): ?><span class="form-error"><?= esc($errors['name']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="company">Firma Adı</label>
                        <input class="form-control" type="text" id="company" name="company" value="<?= esc(old('company')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="phone">Telefon <span class="req">*</span></label>
                        <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" type="tel" id="phone" name="phone" value="<?= esc(old('phone')) ?>" required>
                        <?php if (isset($errors['phone'])): ?><span class="form-error"><?= esc($errors['phone']) ?></span><?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">E-posta <span class="req">*</span></label>
                        <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= esc(old('email')) ?>" required>
                        <?php if (isset($errors['email'])): ?><span class="form-error"><?= esc($errors['email']) ?></span><?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="product">İstenen Ürün</label>
                    <select class="form-control" id="product" name="product">
                        <option value="">Seçiniz (opsiyonel)</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= esc($p['name']) ?>" <?= $selected === $p['name'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
                        <?php endforeach; ?>
                        <option value="Karışık / Diğer" <?= $selected === 'Karışık / Diğer' ? 'selected' : '' ?>>Karışık / Diğer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">Mesaj</label>
                    <textarea class="form-control" id="message" name="message" placeholder="Adet, renk, logo ve teslim tarihi gibi detayları yazabilirsiniz."><?= esc(old('message')) ?></textarea>
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--block">Teklif Talebini Gönder</button>
                <p class="form-note">Göndererek size dönüş yapmamız için iletişim bilgilerinizi paylaşmış olursunuz.</p>
            </form>
        </div>

        <!-- İletişim bilgileri -->
        <div>
            <a class="btn btn--wa btn--lg btn--block" href="<?= whatsapp_link() ?>" target="_blank" rel="noopener" style="margin-bottom:22px;">
                <?= icon('whatsapp', 20) ?> WhatsApp'tan Teklif Al
            </a>

            <div class="card-box">
                <ul class="info-list">
                    <li class="info-item">
                        <span class="info-item__icon"><?= icon('phone', 22) ?></span>
                        <span>
                            <span class="info-item__label">Telefon</span>
                            <span class="info-item__value"><a href="<?= phone_link() ?>"><?= esc(site('phoneDisplay')) ?></a></span>
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-item__icon"><?= icon('whatsapp', 22) ?></span>
                        <span>
                            <span class="info-item__label">WhatsApp</span>
                            <span class="info-item__value"><a href="<?= whatsapp_link() ?>" target="_blank" rel="noopener">Teklif almak için yazın</a></span>
                        </span>
                    </li>
                    <li class="info-item">
                        <span class="info-item__icon"><?= icon('clock', 22) ?></span>
                        <span>
                            <span class="info-item__label">Çalışma Saatleri</span>
                            <span class="info-item__value"><?= esc(site('workingHours')) ?></span>
                        </span>
                    </li>
                    <?php if (trim((string) site('address')) !== ''): ?>
                    <li class="info-item">
                        <span class="info-item__icon"><?= icon('map', 22) ?></span>
                        <span>
                            <span class="info-item__label">Adres</span>
                            <span class="info-item__value"><?= esc(site('address')) ?></span>
                        </span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div>
</section>

<?= $this->endSection() ?>
