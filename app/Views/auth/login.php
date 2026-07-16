<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Giriş<?= $this->endSection() ?>

<?= $this->section('main') ?>
<div class="auth-motion" aria-hidden="true"><i></i><i></i><i></i><span></span></div>
<section class="auth-shell" aria-labelledby="login-title">
    <div class="auth-card">
        <a class="auth-card__logo" href="<?= site_url('/') ?>" aria-label="FORMMIX ana sayfa"><img src="<?= base_url('assets/images/logo.svg') ?>" alt="FORMMIX"></a>
        <header class="auth-card__head">
            <span class="auth-eyebrow">Yönetim paneli</span>
            <h2 id="login-title">Tekrar hoş geldiniz</h2>
            <p>Panel hesabınızla güvenli biçimde giriş yapın.</p>
        </header>

        <?php if (session('error') !== null || session('errors') !== null): ?>
            <div class="auth-alert auth-alert--error" role="alert">
                <strong>Giriş yapılamadı</strong>
                <?php $loginErrors = session('error') ?? session('errors'); ?>
                <?php if (is_array($loginErrors)): ?>
                    <?php foreach ($loginErrors as $error): ?><span><?= esc($error) ?></span><?php endforeach; ?>
                <?php else: ?>
                    <span><?= esc($loginErrors) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (session('message') !== null): ?>
            <div class="auth-alert auth-alert--success" role="status"><?= esc(session('message')) ?></div>
        <?php endif; ?>

        <form class="auth-form" action="<?= url_to('login') ?>" method="post">
            <?= csrf_field() ?>

            <label class="auth-field" for="login-email">
                <span>E-posta adresi</span>
                <div class="auth-input">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z"/></svg>
                    <input id="login-email" type="email" name="email" inputmode="email" autocomplete="email" value="<?= esc(old('email')) ?>" placeholder="ornek@formmix.com" required autofocus>
                </div>
            </label>

            <label class="auth-field" for="login-password">
                <span>Şifre</span>
                <div class="auth-input">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 8h-1V6a4 4 0 0 0-8 0v2H7a2 2 0 0 0-2 2v10h14V10a2 2 0 0 0-2-2Zm-7-2a2 2 0 0 1 4 0v2h-4V6Zm3 9.73V18h-2v-2.27a2 2 0 1 1 2 0Z"/></svg>
                    <input id="login-password" type="password" name="password" autocomplete="current-password" placeholder="Şifrenizi girin" required>
                </div>
            </label>

            <button class="auth-submit" type="submit"><span>Panele giriş yap</span><i aria-hidden="true">→</i></button>
        </form>

        <footer class="auth-card__footer"><span aria-hidden="true">●</span> Güvenli panel bağlantısı</footer>
    </div>
</section>
<?= $this->endSection() ?>
