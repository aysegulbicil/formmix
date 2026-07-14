# Adım Adım Uygulama Planı

**Son güncelleme:** 14 Temmuz 2026  
**Şu anki adım:** Adım 2 — Müşteri ve personel yönetimi  
**Genel durum:** Adım 0 ve Adım 1 tamamlandı; Adım 2 başlatıldı

## Bu dosya nasıl kullanılacak?

Bu dosya projenin ana takip listesidir. Aynı anda yalnızca bir ana adım üzerinde çalışılır. Bir adımın altındaki bütün maddeler tamamlanıp test edilmeden sonraki adıma geçilmez.

Her adım bittiğinde aşağıdakiler bu dosyaya yazılır:

- Tamamlanma tarihi
- Yapılan işlerin kısa özeti
- Uygulanan testler
- Açık kalan konu varsa nedeni
- Onay veren kişi

---

## Adım 0 — İş kurallarını netleştirme

**Durum:** `[x] Tamamlandı — 13 Temmuz 2026`  
**Amaç:** Kod yazmadan önce işletmenin nasıl çalıştığını kesinleştirmek.

### Yapılacaklar

- [x] Müşteri kaydı için zorunlu bilgiler belirlendi. Ayrıntı: Karar Defteri, karar 9.
- [x] Bir müşterinin hangi şartla personele ait sayılacağı belirlendi. Ayrıntı: Karar Defteri, karar 4-8.
- [x] Personel değişiminde eski personelin hakkı belirlendi. Ayrıntı: Karar Defteri, karar 6.
- [x] Sipariş durumları ve bu durumları kimin değiştireceği belirlendi. Ayrıntı: İş Akışları ve Karar Defteri, karar 12-13.
- [x] Fiyat ve indirim yetkileri belirlendi. Ayrıntı: Karar Defteri, karar 10.
- [x] Stokta beden, renk ve baskı seçeneklerinin nasıl tutulacağı belirlendi. Ayrıntı: Karar Defteri, karar 14-15.
- [x] Tahsilat ve vade uygulaması belirlendi. Ayrıntı: Karar Defteri, karar 16.
- [x] Prim hesabının hangi olayda kesinleşeceği belirlendi. Ayrıntı: Karar Defteri, karar 17.
- [x] İptal ve iadelerin prime etkisi belirlendi. Ayrıntı: Karar Defteri, karar 18.
- [x] Kullanıcı görevleri ve yetkileri onaylandı. Ayrıntı: Kullanıcılar ve Yetkiler belgesi.

### Tamamlanmış sayılması için

- `06_KARAR_DEFTERI.md` içindeki başlangıç kararlarının tamamı cevaplanmış olmalıdır.
- `03_IS_AKISLARI.md` işletmenin gerçek çalışma şekline uygun bulunmalıdır.
- İşletme adına karar verecek kişi kapsamı yazılı olarak onaylamalıdır.

### Adım sonu kaydı

- Tamamlanma tarihi: 13 Temmuz 2026
- Onaylayan: İşletme adına kullanıcı
- Not: Kalan iş kuralları güvenli ve sonradan değiştirilebilir önerilerle toplu olarak onaylandı. Kararlar 4-22 Karar Defteri'nde kayıtlıdır.

---

## Adım 1 — Teknik temel ve güvenlik

**Durum:** `[x] Tamamlandı — 14 Temmuz 2026`  
**Amaç:** Sonraki bütün bölümlerin üzerine kurulacağı güvenli temeli hazırlamak.

### Yapılacaklar

- [x] Yerel geliştirme ve Docker çalışma ortamı ayrıldı; canlı sunucu ayarları ortam değişkenleriyle ayrıca verilecek.
- [x] Yerel SQLite ve Docker MySQL bağlantıları kuruldu ve test edildi.
- [x] Giriş, ayar, çalışan ve değişiklik geçmişi tablolarının sıralı kurulum dosyaları SQLite ve Docker MySQL üzerinde çalıştırıldı.
- [x] Kullanıcı girişi, güvenli çıkış ve e-posta ile giriş bağlantısı uygulandı; yerel posta kutusunda gerçek iletiyle doğrulandı.
- [x] Kullanıcı görevleri ve yetkileri geçici örnek kullanıcılarla izin verilen ve reddedilen işlemler üzerinden doğrulandı.
- [x] Değişiklik geçmişi tablosu ve kayıt aracı yazma, okuma ve temizleme testiyle doğrulandı.
- [x] Zaman damgalı yedek alma ve ayrı geçici veritabanına geri yükleme yöntemi hazırlandı.
- [x] Hata kayıtları, form koruması, güvenli başlıklar, dış kayıt engeli ve giriş istek sınırı uygulandı.

### Kontrol edilecekler

- [x] Oturumu olmayan kişi yönetim sayfasını açamıyor; giriş sayfasına yönlendiriliyor.
- [x] Bir kullanıcı başka görevdeki kişiye ait işlemi yapamıyor; beş görev için izin ve ret örnekleri test edildi.
- [x] Giriş sayfaları IP başına dakikada 10 istekle sınırlandırılıyor; 429 yanıtı ile doğrulandı.
- [x] Yedeklenen veritabanı geçici deneme veritabanına geri yüklendi ve 11 tablo doğrulandı.

### Tamamlanmış sayılması için

Giriş, yetki, kayıt geçmişi ve yedek geri yükleme testleri başarılı olmalıdır.

### Adım sonu kaydı

- Tamamlanma tarihi: 14 Temmuz 2026
- Onaylayan: Kullanıcının güvenli varsayılanlarla ilerleme onayı doğrultusunda teknik doğrulama
- Yapılan testler: giriş koruması, istek sınırı, beş görev için yetki matrisi, işlem geçmişi yazma/okuma, e-posta ile giriş bağlantısı, MySQL yedek alma ve 11 tabloyu geri yükleme
- Açık konu: Canlı sunucu adresi ve gerçek SMTP bilgileri yayın aşamasında verilecek; yerel geliştirmeyi engellemiyor.

---

## Adım 2 — Müşteri ve personel yönetimi

**Durum:** `[~] Devam ediyor — personel yönetimi tamamlandı`  
**Amaç:** Kimin hangi müşteriyle ilgilendiğini güvenilir biçimde takip etmek.

### Yapılacaklar

- [x] Müşteri, yetkili kişi, güncel sorumlu, atama geçmişi ve görüşme kayıtlarının veritabanı yapısı oluşturuldu; MySQL ve SQLite üzerinde kuruldu, geçici kayıtlarla doğrulandı.
- [x] Personel listeleme, arama, ekleme, düzenleme ve etkin/pasif yönetimi yapıldı; kullanıcı hesabı/görev bağlantısı, indirim sınırı, tahsilat bildirimi yetkisi, işlem geçmişi ve telefon uyumlu arayüz doğrulandı.
- [ ] Müşteri ekleme ve düzenleme ekranı yapılacak.
- [ ] Telefon ve vergi numarasıyla benzer müşteri uyarısı yapılacak.
- [ ] Müşteri-personel ataması yapılacak.
- [ ] Atama başlangıç ve bitiş tarihleri tutulacak.
- [ ] Görüşme notları ve yapılacak işler kaydedilecek.
- [ ] Müşteri arama ve süzme ekranı yapılacak.
- [ ] Mevcut iletişim formu taleplerinin müşteri adayına dönüşmesi planlanacak.

### Tamamlanmış sayılması için

Örnek müşterilerle kayıt, atama, personel değişimi, geçmiş görüntüleme ve yetki testleri başarılı olmalıdır.

### Ara ilerleme kaydı — 14 Temmuz 2026

- Personel listesi; ad, kod, telefon ve e-posta araması ile aktif/pasif süzme desteğiyle tamamlandı.
- Personel ekleme, düzenleme, etkin/pasif yapma, indirim sınırı ve tahsilat bildirimi yetkisi tamamlandı.
- Yeni veya mevcut kullanıcı hesabını personele bağlama ve görev atama yalnızca `users.manage` izni olan işletme sahibine açıldı.
- İşletme sahibi ve satış yöneticisi personel kayıtlarını yönetebilir; diğer görevler personel ekranına erişemez.
- Oluşturma, güncelleme ve durum değişiklikleri işlem geçmişine kaydediliyor.
- PHP sözdizimi, rota, beş görev için izin/ret, müşteri-personel veri temeli, CSRF korumalı gerçek personel kaydı ve 390×844 telefon görünümü testleri başarılı.
- Sonraki bölüm: müşteri ekleme, düzenleme ve mükerrer kayıt uyarıları.

---

## Adım 3 — Ürün, seçenek ve fiyat yönetimi

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Siparişin doğru ürün ve fiyatla girilebilmesini sağlamak.

### Yapılacaklar

- [ ] Mevcut ürünler veritabanına taşınacak.
- [ ] Ürün kodu, beden, renk ve diğer seçenekler tanımlanacak.
- [ ] Alış ve satış fiyatları tanımlanacak.
- [ ] Müşteri grupları ve özel fiyatlar tanımlanacak.
- [ ] İndirim sınırları tanımlanacak.
- [ ] Ürünü satışa açma ve kapatma yapılacak.
- [ ] Toplu fiyat güncelleme yöntemi hazırlanacak.

### Tamamlanmış sayılması için

Personel sadece satışa açık ürünleri ve kendisine izin verilen fiyatları görmeli; yetkisini aşan indirimi onaysız uygulayamamalıdır.

---

## Adım 4 — Saha ziyareti

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Kapı kapı çalışan personelin günlük çalışmalarını kolayca kaydetmek.

### Yapılacaklar

- [ ] Telefon ekranına uygun saha ana sayfası yapılacak.
- [ ] Personelin kendi müşteri listesi gösterilecek.
- [ ] Yeni müşteri adayı ekleme yapılacak.
- [ ] Ziyaret başlatma ve bitirme yapılacak.
- [ ] Görüşme sonucu ve not kaydı yapılacak.
- [ ] Sonraki görüşme tarihi eklenebilecek.
- [ ] Konum kaydı kullanılacaksa açık izin ve kullanım şekli belirlenecek.
- [ ] İnternet kesilirse en azından taslak bilgiler kaybolmayacak.

### Tamamlanmış sayılması için

Gerçek bir telefonla müşteri bulma, ziyaret kaydetme ve bağlantı kesintisi denemeleri başarılı olmalıdır.

---

## Adım 5 — Teklif ve sipariş

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Saha personelinin telefondan sipariş girmesi ve merkezin anında görmesi.

### Yapılacaklar

- [ ] Teklif oluşturma ve teklifi siparişe çevirme yapılacak.
- [ ] Ürün, seçenek, adet, fiyat ve indirim girişi yapılacak.
- [ ] Sipariş toplamları sistem tarafından hesaplanacak.
- [ ] Yetki dışı indirim yönetici onayına gönderilecek.
- [ ] Sipariş durumu geçmişi tutulacak.
- [ ] Siparişe belge veya görsel eklenebilecek.
- [ ] Merkez ekranında yeni sipariş bildirimi gösterilecek.
- [ ] İptal ve iade işlemleri kaydedilecek.
- [ ] Aynı siparişin bağlantı sorunu nedeniyle iki kez oluşması önlenecek.

### Tamamlanmış sayılması için

Sahadaki telefondan girilen örnek sipariş merkezde doğru müşteri, personel, ürün, fiyat ve toplamla birkaç saniye içinde görünmelidir.

---

## Adım 6 — Alış, depo ve stok

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Alınan ve satılan ürünlerin miktarını ve neden hareket ettiğini izlemek.

### Yapılacaklar

- [ ] Tedarikçi kayıtları yapılacak.
- [ ] Alış siparişi ve mal kabul ekranları yapılacak.
- [ ] Depo tanımları yapılacak.
- [ ] Stok giriş, çıkış, transfer, iade ve düzeltme işlemleri yapılacak.
- [ ] Satış siparişine ürün ayırma yapılacak.
- [ ] Kritik stok uyarıları yapılacak.
- [ ] Stok sayımı ve fark kaydı yapılacak.

### Tamamlanmış sayılması için

Her stok değişiminin miktarı, nedeni, tarihi ve işlemi yapan kişi görülebilmelidir. Örnek alış, satış, iade ve sayım sonunda kalan miktar doğru olmalıdır.

---

## Adım 7 — Cari hesap, tahsilat ve ödeme

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Müşteri ve tedarikçi borçlarını temel seviyede takip etmek.

### Yapılacaklar

- [ ] Müşteri ve tedarikçi hareketleri oluşturulacak.
- [ ] Tahsilat ve ödeme girişi yapılacak.
- [ ] Kısmi tahsilat desteklenecek.
- [ ] Vade ve gecikme takibi yapılacak.
- [ ] Kasa ve banka seçimi yapılacak.
- [ ] İptal edilen ödeme için ters kayıt oluşturulacak.
- [ ] Cari hesap dökümü alınabilecek.

### Tamamlanmış sayılması için

Örnek sipariş, kısmi tahsilat, kalan borç, iade ve ödeme iptali sonunda müşteri bakiyesi doğru çıkmalıdır.

---

## Adım 8 — Prim sistemi

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Personelin hak ettiği primi açık, kontrol edilebilir ve itiraz durumunda açıklanabilir biçimde hesaplamak.

### Yapılacaklar

- [ ] Prim kuralları tarih aralığıyla tanımlanacak.
- [ ] Prim matrahının satış mı, kâr mı, tahsilat mı olduğu seçilebilecek.
- [ ] Personel ve ürün grubuna göre oran tanımlanabilecek.
- [ ] İade ve iptal düzeltmeleri yapılacak.
- [ ] Müşteriyi bulan ve siparişi alan farklıysa paylaşım uygulanacak.
- [ ] Prim önce bekleyen, sonra hak edilmiş duruma geçecek.
- [ ] Dönem kapatma ve ödeme kaydı yapılacak.

### Tamamlanmış sayılması için

En az on farklı örnek durum elle hesaplanacak; sistem sonucu ile elle hesaplanan sonuç birebir aynı olacaktır.

---

## Adım 9 — Raporlar ve yönetim ana sayfası

**Durum:** `[ ] Başlanmadı`  
**Amaç:** İşletmenin günlük durumunu tek bakışta göstermek.

### Yapılacaklar

- [ ] Günlük, haftalık ve aylık satışlar gösterilecek.
- [ ] Personel, müşteri ve ürün bazında satış raporu yapılacak.
- [ ] Bekleyen sipariş ve geciken iş uyarıları yapılacak.
- [ ] Stok ve kritik stok raporu yapılacak.
- [ ] Tahsilat ve geciken ödeme raporu yapılacak.
- [ ] Ziyaret ve müşteri kazanım raporu yapılacak.
- [ ] Prim özeti yapılacak.
- [ ] Temel kârlılık raporu yapılacak.
- [ ] Raporların tablo dosyası olarak dışarı alınması sağlanacak.

### Tamamlanmış sayılması için

Rapor sonuçları örnek veritabanı kayıtlarıyla tek tek karşılaştırılmalı ve yetkiye göre gizlenmesi gereken bilgiler gizlenmelidir.

---

## Adım 10 — Gerçek kullanıcı denemesi ve yayına alma

**Durum:** `[ ] Başlanmadı`  
**Amaç:** Sistemi bütün personele açmadan önce küçük bir grupla güvenli biçimde denemek.

### Yapılacaklar

- [ ] Gerçeğe yakın deneme verileri hazırlanacak.
- [ ] Bir yönetici, bir saha personeli, bir muhasebe ve bir depo kullanıcısıyla deneme yapılacak.
- [ ] Kullanım sırasında yaşanan sorunlar kaydedilecek.
- [ ] Kritik sorunlar giderilecek ve tekrar test edilecek.
- [ ] Kullanıcılar için kısa kullanım anlatımları hazırlanacak.
- [ ] Canlı veriler aktarılacak.
- [ ] Son yedek ve geri dönüş planı hazırlanacak.
- [ ] Sistem önce sınırlı kullanıcıya, sonra herkese açılacak.

### Tamamlanmış sayılması için

Kritik hata kalmamalı, yedekten dönüş denenmiş olmalı ve işletme canlı kullanıma yazılı onay vermelidir.

---

## Daha sonraki geliştirmeler

Bunlar ilk sürüm başarıyla kullanıldıktan sonra ayrıca değerlendirilecektir:

- Muhasebe veya e-fatura programıyla bağlantı
- WhatsApp ve telefon bildirimi
- Gelişmiş rota planlama
- Müşteri sipariş ekranı
- Mağazadan indirilen telefon uygulaması
- Gelişmiş üretim planlama
