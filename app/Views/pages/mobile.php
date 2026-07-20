<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<section class="mobile-download-hero">
    <div class="container mobile-download-grid">
        <div class="mobile-download-copy">
            <span class="mobile-download-eyebrow">FORMMIX ANDROID UYGULAMASI</span>
            <h1>Saha operasyonunuz artik cebinizde.</h1>
            <p>Musterilerinize, urun kataloguna, tekliflerinize ve siparislerinize guvenli mobil uygulamadan ulasin. Cevrimdisi taslak olusturun, baglanti geldiginde kontrollu olarak merkeze gonderin.</p>
            <?php if ($release): ?>
                <div class="mobile-release-meta">
                    <span>Surum <strong><?= esc($release['version_name']) ?></strong></span>
                    <span>Yayin <?= esc(date('d.m.Y', strtotime($release['published_at']))) ?></span>
                </div>
                <a class="btn btn--primary btn--lg mobile-download-button" href="<?= esc($release['download_url']) ?>" rel="nofollow" download>
                    Android APK'yi indir
                </a>
                <?php if (! empty($release['release_notes'])): ?><p class="mobile-release-notes"><?= nl2br(esc($release['release_notes'])) ?></p><?php endif; ?>
            <?php else: ?>
                <div class="mobile-coming-soon">Ilk imzali APK hazirlaniyor. Yayinlandiginda indirme butonu burada acilacak.</div>
            <?php endif; ?>
        </div>
        <div class="mobile-phone" aria-hidden="true">
            <div class="mobile-phone-speaker"></div>
            <div class="mobile-phone-screen">
                <img src="<?= asset('images/logo.png') ?>" alt="">
                <span>Mobil Satis ve Operasyon</span>
                <div class="mobile-phone-cards"><i></i><i></i><i></i></div>
            </div>
        </div>
    </div>
</section>
<section class="section">
    <div class="container mobile-install-layout">
        <div>
            <span class="section-eyebrow">KURULUM</span>
            <h2>APK nasil kurulur?</h2>
            <ol class="mobile-install-steps">
                <li><strong>APK'yi indirin.</strong><span>Yukaridaki indirme butonuna dokunun.</span></li>
                <li><strong>Kuruluma izin verin.</strong><span>Android isterse tarayici icin “Bu kaynaktan yuklemeye izin ver” secenegini acin.</span></li>
                <li><strong>Uygulamayi kurun.</strong><span>Indirilen dosyayi acip Kur butonuna dokunun.</span></li>
                <li><strong>FORMMIX hesabinizla girin.</strong><span>Panel e-posta ve parolanizi kullanin.</span></li>
            </ol>
        </div>
        <aside class="mobile-security-card">
            <h2>Guvenli dagitim</h2>
            <p>APK, FORMMIX'in kalici Android imza anahtariyla imzalanir. Uygulama verileri yalniz HTTPS uzerinden aktarilir.</p>
            <?php if ($release): ?><small>SHA-256</small><code><?= esc($release['sha256']) ?></code><?php endif; ?>
            <p>Yeni surumler uygulama acilisinda kontrol edilir. Guncelleme yine bu sayfadan indirilir.</p>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>
