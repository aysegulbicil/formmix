# FORMMIX — Arayüz Analiz ve Geliştirme Planı

**Hazırlayan:** UI/UX & Frontend incelemesi
**Tarih:** 19 Haziran 2026
**Kapsam:** Sadece görsel/etkileşim katmanı (CSS, JS, view partial/component). Route, controller, CI4 çekirdeği ve içerik **değişmez**. Bu belge yalnızca analiz ve plandır; **kod değişikliği içermez.**

---

## Özet (önce bunu okuyun)

Site sağlam bir temel üzerine kurulu. Zaten bir **tasarım token sistemi**, **scroll-reveal animasyonları**, **hero giriş animasyonu**, **kart hover efektleri**, **sticky header** ve **`prefers-reduced-motion` desteği** mevcut. Yani yapılacak iş bir "baştan yazma" değil, **hedefli bir cilalama ve premium dokunuş** çalışmasıdır. Bu, "siteyi bozma" kuralınızla birebir örtüşür: her şey mevcut yapının üzerine, izole ve geri alınabilir biçimde eklenir.

Tek cümlelik tavsiye: **Yeni ağır kütüphane (GSAP) eklemeyin; mevcut hafif IntersectionObserver sistemini genişletin.** Detay aşağıda.

---

## 1) Mevcut Durum Analizi

**Mimari:** CodeIgniter 4, `extend/section` layout sistemi, temiz `partials` + `components` ayrımı, dosya tabanlı içerik (`app/Data/*.php`), `site_helper.php` içinde `icon()`, `site()`, `whatsapp_link()` gibi yardımcılar. Tek CSS dosyası (651 satır) + tek vanilla JS dosyası (kütüphanesiz).

**Halihazırda var olan (korunacak) güçlü yanlar:**

- Tasarım token'ları: renk paleti (lacivert/turuncu/antrasit/gri), tipografi (Montserrat + Inter), radius, gölge, geçiş değişkeni `--t`.
- Sticky header + scroll'da gölge (`is-scrolled`), hamburger → X morph animasyonu.
- Hero giriş animasyonu (`heroIn` keyframe, kademeli `animation-delay`).
- Scroll-reveal sistemi: `IntersectionObserver` + ızgara içi **stagger** + `prefers-reduced-motion` koruması.
- Kart hover "lift" efektleri (value / product / sector / price) ve ürün görselinde hover zoom.
- Özel imleç (dot + ring), yalnız `hover:hover + pointer:fine` cihazlarda.
- Erişilebilirlik temeli: `focus-visible`, `aria-expanded/controls`, alt metinler, form honeypot.
- Sağlıklı responsive: 1024 / 860 / 560px kırılımları, `wa-float` sabit WhatsApp butonu.

**Alan bazlı durum:**

| Alan | Durum |
|---|---|
| Header & mobil menü | Sağlam; topbar masaüstünde, mobilde gizli. Mobil menü animasyonsuz. |
| Hero | Split grid, gradient + radial ışık, giriş animasyonu var; **görsel statik**. |
| Ürün kartları | Temiz, hover + görsel zoom var; CTA hep görünür, overlay yok. |
| Kampanya | 3 fiyat kartı; featured kart yalnızca border ile ayrışıyor (zayıf vurgu). |
| Katalog | PDF `iframe` gömme; mobilde deneyim zayıf. Mevcut katalog görselleri kullanılmıyor. |
| CTA bölümleri | Gradient band, iyi; buton mikro-animasyonu sınırlı. |
| Footer | 4 kolon, dolu ve dengeli. |
| Mobil görünüm | Kırılımlar sağlıklı; menü ve katalog deneyimi geliştirilebilir. |
| Spacing / tipografi / renk | Token tabanlı, dengeli; bölüm ritmi tek tip (hep 72px). |

---

## 2) Eksik veya Zayıf Alanlar

- **Hero görseli tamamen statik** — metinler animasyonlu ama görsel "düz" duruyor, derinlik yok.
- **Mobil menü anında açılıp kapanıyor** (geçiş yok), arka plan **scroll-lock** yok, overlay/dışına tıklama ile kapanma yok.
- **Topbar mobilde tümüyle kayboluyor** → telefon ve çalışma saati hızlı erişimi düşüyor (`wa-float` kısmen telafi ediyor).
- **Featured kampanya kartı yeterince baskın değil** — "En Avantajlı" paket sadece 2px turuncu border ile ayrışıyor; satış odağı görsel olarak öne çıkmıyor.
- **Ürün kartı etkileşimi yüzeysel** — badge statik, hover'da CTA vurgusu/overlay yok.
- **Katalog mobilde zayıf** — gömülü PDF mobilde zoom/scroll sorunlu; mevcut katalog görselleri değerlendirilmiyor.
- **Buton mikro-animasyonu sınırlı** — yalnız `translateY + gölge`; ikon hareketi/parıltı yok.
- **Özel imleç kurumsal B2B kimliğiyle çelişebilir** — "premium" yerine "gimmick" algısı riski + sürekli `requestAnimationFrame` döngüsü.
- **Tekrarlayan HTML blokları** — `page-hero` (4 sayfada), `cta-band` (3+ yerde), `section__head` elle kopyalanmış; partial/component değil.
- **Bölüm ritmi tek tip** — her `section` 72px; görsel hiyerarşi düzleşiyor.

---

## 3) Önerilen Animasyonlar (hafif, kurumsal, satışa hizmet eden)

Tümü için kural: **süre 200–600ms, yalnız `transform`/`opacity` (reflow yok), `ease-out`/cubic-bezier, `prefers-reduced-motion`'da kapalı.**

**Hero**
- Görselde çok yavaş "float" (≈6s, ±10px) + giriş anında hafif `scale(1.03→1)`.
- Başlıktaki turuncu "Markanızı" kelimesinde tek seferlik **underline-draw** (0.6s).
- Arka plan ışığında çok düşük yoğunlukta "nefes alma" (opsiyonel).

**Scroll reveal (mevcut sistemi genişlet, kütüphane gerekmez)**
- Yön çeşitliliği: mevcut fade-up'a ek olarak split bölümlerde (about-grid vb.) fade-left/right.
- Mevcut stagger'ı koru.

**Ürün kartları**
- Hover'da CTA "Teklif Al" hafif belirginleşme + badge'de küçük "pop".
- Görsel zoom (var) üzerine hover'da beliren ince üst gradient overlay.
- Hover'da kart kenarında ince turuncu glow.

**CTA butonları (mikro)**
- İkonun hover'da 2px kayması, soldan sağa tek seferlik "shine" sweep, `:active`'de `scale(0.98)` (dokunsal his).

**Kampanya**
- Featured kart: `scale(1.04)` + belirgin gölge/gradient + "En Avantajlı" ribbon'da çok hafif pulse.
- Fiyat rakamlarında (2.500, 5.000…) viewport'a girince **count-up sayaç** (≈500ms, bir kez).

**Genel**
- Sticky header'da scroll ile hafif küçülme (74→64px) + logo ölçeği — premium his.
- Bölüm eyebrow'larında letter-spacing ince genişleme (geçiş zaten tanımlı).

---

## 4) Önerilen Component / Partial Yapısı

Amaç: tekrarlayan HTML'i tek kaynağa indirmek ve esnekliği artırmak — **route/controller'a dokunmadan**, mevcut `view()` pattern'iyle birebir uyumlu, çıktı HTML'i birebir koruyarak.

**Yeni partial/component'ler:**

- `partials/page_hero.php` — eyebrow + başlık + metin + breadcrumb (products/catalog/about/contact tekrarını birleştirir).
- `components/cta_band.php` — CTA bandı (home/products/about tekrarı). Parametre: başlık, metin, butonlar.
- `components/section_head.php` — eyebrow + title + lead (her bölümde tekrar eden blok).
- `components/price_card.php` — kampanya kartını `home.php` içindeki inline HTML'den component'e taşı (zaten `product_card` gibi bir desen mevcut).
- `partials/topbar.php` — header'dan ayrıştır; mobilde sade bir varyant beslemek için.

**CSS organizasyonu (davranış değişmez, yalnız düzen):**
- `style.css` mantıksal katmanlara bölünebilir veya `@layer` ile: tokens / base / components / utilities / motion. Tek dosya kalabilir.
- Motion kuralları mevcut "HAREKET/ANIMASYON" bölümünde toplanıp genişletilir.

---

## 5) Mobil Uyumluluk İyileştirmeleri

- **Mobil menüye yumuşak aç/kapa** (height/opacity veya slide-down ~250ms) + **body scroll-lock** + overlay/dışına tıklayınca kapanma.
- **Topbar yerine** mobilde header içinde küçük telefon ikonu (hızlı arama) — bilgi kaybını telafi.
- **Katalog mobil:** gömülü PDF yerine "Tam Ekran Aç / İndir" CTA'larını öne al; opsiyonel olarak optimize (WebP) görsel katalog galerisi.
- **Dokunmatik hedefler** min 44px (çoğu uygun; mobil menü item padding'i doğrulanır).
- **Tipografi:** gövde 16px iyi; mobilde `section__lead` 17→16px, hero metninde satır uzunluğu sınırı.
- **Featured fiyat kartı** mobilde `scale` yerine border vurgusu (yatay taşmayı önlemek için).
- **`wa-float`** için `env(safe-area-inset)` ile alt boşluk (çentikli cihazlarda çakışmasın).
- Yeni animasyonlarda `transform` taşmasının yatay scroll yaratmadığı kontrol edilir (`overflow-x:hidden` zaten var).

---

## 6) Performans Riskleri

- **GSAP eklemek bu site için aşırı** (~70KB+ core, ScrollTrigger ayrı). **Öneri: eklemeyin.** Mevcut IntersectionObserver yaklaşımı zaten hafif ve yeterli.
- **AOS (~14KB)** declarative kolaylık sağlar ama mevcut özel reveal ile **işlev çakışır** (çift sistem). Karar: ya AOS'a geçilir ya mevcut genişletilir — **ikisi birden kullanılmaz**. (Öneri: vanilla'yı genişlet.)
- **Özel imleç** sürekli `requestAnimationFrame` döngüsü çalıştırıyor (idle'da bile). Düşük ama sıfır olmayan CPU/pil maliyeti + kurumsal his tartışması.
- **Katalog görselleri ~32MB / 22 PNG.** Görsel galeri yapılırsa **mutlaka WebP + lazy-load + doğru boyutlandırma**; aksi halde ciddi yük.
- **Animasyon disiplini:** yalnız `transform`/`opacity`; `width/height/top` gibi reflow tetikleyenlerden kaçın. `will-change` yalnız animasyon süresince.
- **Google Fonts:** 2 aile / 7 ağırlık, `display=swap` var (iyi). Kullanılmayan ağırlıkları azaltmak FCP'yi iyileştirir.
- **Görseller:** hero ~70KB, about ~91KB (uygun). Yeni görsellerde aynı disipline uyulur.

---

## 7) Uygulama Planı (kademeli, her faz onaylı ve geri alınabilir)

İlke: küçük, izole, geri alınabilir adımlar. Her fazdan sonra **masaüstü + mobil göz kontrolü**. Route/controller/içerik değişmez. Tüm hareket `prefers-reduced-motion`'da kapalı.

- **Faz 0 — Hazırlık (risksiz):** branch/yedek, CSS `?v` artır, motion bölümünü ayır.
- **Faz 1 — Component/partial refaktörü (görünüm aynı):** `page_hero`, `cta_band`, `section_head`, `price_card`, `topbar` oluştur; tekrarları bunlarla değiştir. Çıktı HTML birebir korunur.
- **Faz 2 — Mikro-etkileşimler (CSS ağırlıklı, düşük risk):** buton shine/ikon-shift/active-scale, kart hover CTA & glow, eyebrow letter-spacing, header shrink.
- **Faz 3 — Hero & scroll motion:** hero görsel float + giriş scale, underline-draw, reveal yön çeşitliliği.
- **Faz 4 — Kampanya vurgusu:** featured kart scale/gradient, ribbon pulse, fiyat count-up.
- **Faz 5 — Mobil cila:** mobil menü yumuşak aç/kapa + scroll-lock + overlay; mobil telefon ikonu; katalog mobil CTA önceliği.
- **Faz 6 — Karar gerektirenler (opsiyonel):** özel imleç (kaldır/sadeleştir/koru); animasyon yaklaşımı (vanilla'yı genişlet vs AOS); görsel katalog galerisi.
- **Faz 7 — Doğrulama:** Lighthouse (mobil/masaüstü), reduced-motion testi, 360/768/1200px göz kontrolü, tüm sayfalarda kırılma yok.

---

## Sonraki Adım

Onayınızı bekleyen kararlar: (1) animasyon yaklaşımı — **mevcut vanilla sistemi genişletmek** (önerilen) mi, AOS'a geçmek mi; (2) hangi fazdan başlanacağı; (3) özel imlecin kaderi. Onay verdiğiniz fazları tek tek, görünümü bozmadan uygularım.
