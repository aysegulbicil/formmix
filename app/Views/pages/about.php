<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?= view('components/page_hero', [
    'eyebrow' => 'Hakkımızda',
    'title'   => 'İşletmenizin ilk izlenimini güçlendiriyoruz',
    'text'    => 'FORMMIX, ekiplerin daha profesyonel ve güven veren bir görünüme kavuşması için kuruldu.',
    'current' => 'Hakkımızda',
]) ?>

<section class="section">
    <div class="container about-grid">
        <div>
            <p class="lead">FORMMIX olarak işletmelerin ekiplerini tek tip, düzenli ve kurumsal bir görünüme kavuşturuyoruz.</p>
            <div class="prose">
                <p>Bir işletmenin müşteriye verdiği ilk izlenim çoğu zaman ekibinin görünüşüyle başlar. Aynı logoyu taşıyan, özenle hazırlanmış kıyafetler; güven, ciddiyet ve profesyonellik hissi yaratır. Biz de tam olarak bunu sağlıyoruz: <strong>logonuza özel baskılı kurumsal iş kıyafetleri.</strong></p>
                <p>Fabrikalardan oto servislerine, restoranlardan kafelere ve güzellik merkezlerine kadar farklı sektörlerin ihtiyaçlarına uygun kumaş, model ve baskı çözümleri sunuyoruz. Polo yaka, baskılı tişört, sweatshirt, yelek, önlük ve iş pantolonu gibi ürünlerle ekibinizin her üyesi markanızı temsil eder.</p>
            </div>
            <blockquote class="quote">“Biz sadece kıyafet satmıyoruz; işletmelerin dışarıya verdiği ilk izlenimi güçlendiriyoruz.”</blockquote>
        </div>
        <div>
            <img src="<?= asset('images/about.jpg') ?>" alt="FORMMIX kurumsal iş kıyafetleri" width="1000" height="820" loading="lazy">
        </div>
    </div>
</section>

<section class="section section--gray">
    <div class="container">
        <?= view('components/section_head', [
            'eyebrow' => 'Çalışma Şeklimiz',
            'title'   => 'Neye önem veriyoruz?',
        ]) ?>
        <div class="about-grid">
            <ul class="feature-list">
                <li><?= icon('check', 22) ?> <span><strong>Logoya özel baskı:</strong> Markanızın kimliğine uygun, kaliteli ve kalıcı baskı/nakış.</span></li>
                <li><?= icon('check', 22) ?> <span><strong>Sektöre uygun çözüm:</strong> Çalışma koşullarınıza göre doğru kumaş ve model önerisi.</span></li>
                <li><?= icon('check', 22) ?> <span><strong>Net süreç:</strong> Tasarım, onay, üretim ve teslimatta şeffaf ve hızlı ilerleyiş.</span></li>
                <li><?= icon('check', 22) ?> <span><strong>Güvenilir teslimat:</strong> Söz verdiğimiz kalite ve termine sadık kalırız.</span></li>
            </ul>
            <div class="stat-grid">
                <div class="stat"><div class="stat__num">5+</div><div class="stat__label">Ürün kategorisi</div></div>
                <div class="stat"><div class="stat__num">7/24</div><div class="stat__label">WhatsApp destek</div></div>
                <div class="stat"><div class="stat__num">%100</div><div class="stat__label">Logoya özel</div></div>
            </div>
        </div>
    </div>
</section>

<?= view('components/cta_band', [
    'title'          => 'Ekibinize özel bir teklif alın',
    'text'           => 'Logonuzu gönderin, size özel tasarım ve fiyatı hemen hazırlayalım.',
    'secondaryHref'  => site_url('urunler'),
    'secondaryLabel' => 'Ürünleri İncele',
]) ?>

<?= $this->endSection() ?>
