# Adım Adım Uygulama Planı

**Son güncelleme:** 14 Temmuz 2026  
**Şu anki adım:** Adım 10 hazırlığı tamamlandı — manuel kabul testi bekliyor
**Genel durum:** Adım 7 kullanıcı kararıyla atlandı; canlı veri aktarımı ve yayına alma kullanıcı onayına kadar yapılmayacak

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

**Durum:** `[x] Tamamlandı — 14 Temmuz 2026`

**Amaç:** Kimin hangi müşteriyle ilgilendiğini güvenilir biçimde takip etmek.

### Yapılacaklar

- [x] Müşteri, yetkili kişi, güncel sorumlu, atama geçmişi ve görüşme kayıtlarının veritabanı yapısı oluşturuldu; MySQL ve SQLite üzerinde kuruldu, geçici kayıtlarla doğrulandı.
- [x] Personel listeleme, arama, ekleme, düzenleme ve etkin/pasif yönetimi yapıldı; kullanıcı hesabı/görev bağlantısı, indirim sınırı, tahsilat bildirimi yetkisi, işlem geçmişi ve telefon uyumlu arayüz doğrulandı.
- [x] Müşteri ekleme, ayrıntı ve düzenleme ekranları yapıldı.
- [x] Telefon ve vergi numarasıyla anlık benzer müşteri uyarısı ve sunucu tarafı mükerrer kayıt engeli yapıldı.
- [x] Müşteri-personel ataması ve gerekçeli devir işlemi yapıldı.
- [x] Atama başlangıç ve bitiş tarihleri geçmiş kaydı olarak tutuldu.
- [x] Görüşme notları, görüşme türü ve sonraki işlem tarihi kaydedildi.
- [x] Müşteri arama; durum ve sorumlu personele göre süzme ekranı yapıldı.
- [x] İletişim formu taleplerinin otomatik müşteri oluşturmaması; ayrı aday havuzunda yönetici tarafından il, ilçe ve yetkili bilgileri tamamlandıktan sonra müşteriye dönüştürülmesi planlandı.

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

### Adım sonu kaydı

- Tamamlanma tarihi: 14 Temmuz 2026
- Personel yönetimi; kullanıcı/görev bağlantısı, satış sınırları, aktiflik ve işlem geçmişiyle tamamlandı.
- Müşteri yönetimi; kayıt, düzenleme, arama, süzme, mükerrer uyarı, sorumlu atama, gerekçeli devir, atama geçmişi ve görüşme takibiyle tamamlandı.
- Saha personelinin yalnızca kendi müşterilerini görmesi; yönetici, satış yöneticisi ve muhasebenin görev matrisine göre tüm müşterileri görebilmesi uygulandı.
- Oluşturma, düzenleme, atama/devir ve görüşme işlemleri işlem geçmişine bağlandı.
- Yapılan testler: PHP sözdizimi, rota listesi, beş görev için izin/ret, MySQL müşteri veri ilişkileri, gerçek CSRF korumalı müşteri kaydı, mükerrer telefon uyarısı, görüşme kaydı, sorumlu devri ve 390×844 telefon görünümü.
- Test kayıtları doğrulama sonunda temizlendi; mevcut işletme kayıtları korundu.
- Açık konu: İletişim talepleri için ayrı aday havuzu, iletişim formunun veritabanına alınacağı sonraki uygun geliştirmede uygulanacak; dönüşüm kuralı bu adımda belirlendi.
- Sonraki adım: Adım 3 — Ürün, seçenek ve fiyat yönetimi.

---

## Adım 3 — Ürün, seçenek ve fiyat yönetimi

**Durum:** `[x] Tamamlandı — 14 Temmuz 2026`
**Amaç:** Siparişin doğru ürün ve fiyatla girilebilmesini sağlamak.

### Yapılacaklar

- [x] Kurumsal sitedeki altı mevcut ürün veritabanına taşındı; katalogda doğrulanan polo yaka için S-4XL ve 18 rengin 126 ayrı varyantı oluşturuldu.
- [x] Ürün kodu, kategori, beden, renk, diğer seçenekler ve ayrı varyant stok kodu yapısı kuruldu.
- [x] Vergi hariç alış ve liste satış fiyatı, varyant fiyatı, vergi oranı ve kritik stok alanları tanımlandı.
- [x] Müşteri fiyat grupları ile müşteri veya gruba, istenirse varyanta özel tarihli fiyat yapısı ve ekranları hazırlandı.
- [x] Personel indirim sınırlarıyla uyumlu liste fiyatı temeli korundu; grup indiriminin personelin sipariş onay sınırını artırmadığı ekranda açıklandı.
- [x] Ürünü satışa açma ve kapatma yapıldı. Gerçek fiyatı henüz girilmemiş katalog ürünleri yanlışlıkla siparişte kullanılmaması için pasif tutuldu.
- [x] Seçili ürünlere yüzde artış veya azalış uygulayan, her fiyat değişikliğini işlem geçmişine yazan toplu güncelleme hazırlandı.

### Tamamlanmış sayılması için

Personel sadece satışa açık ürünleri ve kendisine izin verilen fiyatları görmeli; yetkisini aşan indirimi onaysız uygulayamamalıdır.

### Adım sonu kaydı

- Tamamlanma tarihi: 14 Temmuz 2026
- Mevcut ürün kaynağı ve FORMMIX kataloğu incelendi; belgede bulunmayan beden, renk ve gerçek işletme fiyatları uydurulmadı.
- Ürün, kategori, varyant, müşteri fiyat grubu, grup üyeliği ve müşteri/grup özel fiyat tabloları oluşturuldu.
- Baskısız ve müşteriye özel hazırlanmış ürünler ayrı hazırlama durumu, müşteri bağlantısı ve gerektiğinde ayrı stok koduyla tutulabilecek şekilde tasarlandı.
- İşletme sahibi bütün ürün ve fiyat işlemlerini yapabilir. Satış yöneticisi alış fiyatını görmeden ürün ve satış fiyatını yönetebilir. Muhasebe alış fiyatını görür fakat ürün değiştiremez. Saha ve depo yalnızca satış fiyatını görür.
- Ürün oluşturma, düzenleme, aktiflik, toplu fiyat, fiyat grubu ve özel fiyat değişiklikleri işlem geçmişine bağlandı.
- Yapılan testler: PHP söz dizimi, rota listesi, `git diff --check`, SQLite migrationı, Docker MySQL migrationı, altı katalog ürünü ve 126 polo varyantı doğrulaması, beş görev için izin/ret testi, gerçek CSRF korumalı ürün ve varyant kaydı, 1280 px masaüstü ve 390x844 telefon görünümü, yatay taşma, tarayıcı konsolu ve kurumsal ürün sayfası gerileme kontrolü.
- Geçici test ürünü ve işlem geçmişi test sonunda temizlendi; mevcut işletme kayıtları korundu.
- Açık konu: Gerçek alış ve liste satış tutarları işletme tarafından girilecek. Tutarı bilinmeyen katalog ürünleri bu nedenle pasif ve "Fiyat bekliyor" durumundadır.
- Sonraki çalışma: Adım 5 — Teklif ve sipariş. Adım 4 fiziksel ziyaret ihtiyacı bulunmadığı için işletme kararıyla ertelendi.

---

## Adım 4 — Saha ziyareti

**Durum:** `[-] Ertelendi — şu an gerekli değil`
**Amaç:** Kapı kapı çalışan personelin günlük çalışmalarını kolayca kaydetmek.

**Erteleme notu:** Fiziksel müşteri ziyareti ve konum kaydı işletme kararıyla ilk sürümde şimdilik ertelendi. Herhangi bir ziyaret migrationı, tablosu, konum kaydı veya ziyaret ekranı geliştirilmedi. Mevcut müşteri görüşme/not ve sonraki işlem tarihi yapısı kullanılacaktır; ihtiyaç oluşursa bu bölüm ayrıca yeniden değerlendirilecektir.

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

**Durum:** `[x] Başlangıç kapsamı tamamlandı — 14 Temmuz 2026`
**Amaç:** Saha personelinin telefondan sipariş girmesi ve merkezin anında görmesi.

### Yapılacaklar

- [x] Teklif oluşturma ve onaylı teklifi bağlantısını koruyarak yeni siparişe çevirme yapıldı.
- [x] Satışa açık ürün, varyant, adet ve indirim girişi; sunucudan fiyat çözümleme yapıldı.
- [x] Vergi hariç ara toplam, indirim, vergi ve genel toplam sunucuda yeniden hesaplanıyor.
- [x] Personel indirim sınırı ve yüzde 15 üzeri işletme sahibi onayı uygulandı.
- [x] Taslak, onay bekliyor, onaylandı ve iptal edildi durumları gerekçeli geçmişle tutuluyor.
- [!] Siparişe belge veya görsel ekleme için izin verilen dosya türleri, dosya boyutu ve saklama süresi işletmeden bekleniyor; başlangıç sipariş akışına dahil edilmedi.
- [x] Onay bekleyen sipariş sayısı merkez genel bakış ekranında gösteriliyor; yeni kayıt listede anında görünüyor.
- [x] Gerekçeli iptal kaydı yapıldı. İade, teslimat ve stok etkisi nedeniyle depo/sevkiyat adımında ele alınacak.
- [x] Cihaz taslağı benzersiz `client_reference` ile saklanıyor; aynı kayıt ikinci kez oluşturulmuyor ve gönderimde düğmeler kilitleniyor.

### Tamamlanmış sayılması için

Sahadaki telefondan girilen örnek sipariş merkezde doğru müşteri, personel, ürün, fiyat ve toplamla birkaç saniye içinde görünmelidir.

### Adım sonu kaydı

- Tamamlanma tarihi: 14 Temmuz 2026
- Teklif ve siparişler aynı ana tabloda belge türüyle ayrıldı; tekliften sipariş üretildiğinde kaynak teklif bağlantısı korunuyor.
- Müşteri sorumlusu, satışın ait olduğu personel ve kaydı oluşturan kullanıcı belge anında ayrı alanlarda saklanıyor.
- Ürün adı, ürün kodu, varyant, birim fiyat ve vergi oranı satırda anlık kopya olarak tutuluyor; sonraki ürün fiyatı değişiklikleri eski belgeyi değiştirmiyor.
- Müşteri/varyant ve fiyat grubu önceliklerini tarih aralığıyla çözen fiyat servisi ile tek merkezli hesaplama servisi tamamlandı.
- Saha personeli yalnızca kendi müşterisi ve belgeleriyle çalışıyor; muhasebe bütün satış belgelerini, depo yalnızca onaylı siparişleri görüyor.
- Mobil taslak cihazda korunuyor; bağlantı geri geldiğinde aynı `client_reference` ile ikinci kayıt oluşmuyor.
- Yapılan testler: PHP ve JavaScript söz dizimi, `git diff --check`, SQLite ve Docker MySQL migrationı, dört veri temeli doğrulama komutu, beş görev izin/ret matrisi, gerçek CSRF korumalı teklif ve sipariş kaydı, ürün/varyant seçimi, fiyat önceliği, indirim/vergi/toplam, yüzde 20 yüksek indirim onay kaydı, kendi belgesini onaylama reddi, başka personele ait müşteri reddi, tekliften siparişe dönüşüm, cihaz taslağını yeniden yükleme, 1280 px ve 390x844 görünüm, yatay taşma ve tarayıcı konsolu.
- Geçici kullanıcı, müşteri, ürün, teklif, sipariş ve işlem kayıtları test sonunda temizlendi; mevcut işletme kayıtları korundu.
- Açık işletme bilgileri: Gerçek ürün fiyatları; sipariş eki için dosya türü, boyut ve saklama süresi. Fiyatı bilinmeyen ürünler satışa kapalı kalır.
- Alış, depo ve stok adımı kullanıcı onayıyla başlatıldı ve tamamlandı.

---

## Adım 6 — Alış, depo ve stok

**Durum:** `[x] Tamamlandı — 14 Temmuz 2026`
**Amaç:** Alınan ve satılan ürünlerin miktarını ve neden hareket ettiğini izlemek.

### Yapılacaklar

- [x] Tedarikçi kayıtları yapıldı.
- [x] Alış siparişi ve kısmi/tam mal kabul ekranları yapıldı.
- [x] Ana depo tohumu ve çoklu depo tanımları yapıldı.
- [x] Stok giriş, çıkış, transfer, iade ve gerekçeli düzeltme işlemleri yapıldı.
- [x] Satış siparişine kısmi/tam ürün ayırma, tedarik bekleme ve sevkiyat yapıldı.
- [x] Ürün/varyant eşiğine göre kritik stok uyarıları yapıldı.
- [x] Stok sayımı ve fark hareketi kaydı yapıldı.

### Tamamlanmış sayılması için

Her stok değişiminin miktarı, nedeni, tarihi ve işlemi yapan kişi görülebilmelidir. Örnek alış, satış, iade ve sayım sonunda kalan miktar doğru olmalıdır.

### Adım sonu kaydı

- Tamamlanma tarihi: 14 Temmuz 2026
- Tek ana depoyla başlayan, çoklu depoya açık varyant bakiyesi kuruldu; mevcut, ayrılmış ve kullanılabilir miktarlar ayrı tutuluyor.
- Eksi stok engellendi. Siparişte eksik ürün tedarik bekliyor; kısmi stok ayrılabiliyor ve kısmi sevkiyat geçmişi korunuyor.
- Her stok hareketinde tür, miktar, hareket sonrası bakiye, neden, tarih, kullanıcı ve kaynak belge saklanıyor; hareketler silinmiyor.
- `formmix:verify-inventory-foundation` senaryosu SQLite ve Docker MySQL üzerinde alış, iki aşamalı mal kabul, kısmi ayırma, sevkiyat, iade, eksi stok, sayım ve transferi doğruladı.
- Sıradaki çalışma: Adım 7 — Cari hesap, tahsilat ve ödeme.

---

## Adım 7 — Cari hesap, tahsilat ve ödeme

**Durum:** `[-] Atlandı — kullanıcı kararıyla şu an gerekli değil`
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

**Durum:** `[x] Başlangıç kapsamı tamamlandı — 14 Temmuz 2026`
**Amaç:** Personelin hak ettiği primi açık, kontrol edilebilir ve itiraz durumunda açıklanabilir biçimde hesaplamak.

### Yapılacaklar

- [x] Prim kuralları tarih aralığıyla tanımlanıyor.
- [!] Satış ve kâr matrahı uygulanıyor; tahsilat matrahı Adım 7 atlandığı için bekliyor.
- [x] Personel ve ürün grubuna göre oran tanımlanıyor.
- [ ] İade ve iptal düzeltmeleri yapılacak.
- [ ] Müşteriyi bulan ve siparişi alan farklıysa paylaşım uygulanacak.
- [x] Prim önce bekleyen, sevkiyat sonrası hak edilmiş duruma geçiyor.
- [x] Dönem kapatma ve ödeme kaydı yapılıyor.

Başlangıç kapsamında aynı belge/kural/personel için mükerrer prim engellendi ve hesap anlık kopyası saklandı. İade/iptal ters kaydı ile müşteri bulan-sipariş alan paylaşımı sonraki genişletmeye bırakıldı.

### Tamamlanmış sayılması için

En az on farklı örnek durum elle hesaplanacak; sistem sonucu ile elle hesaplanan sonuç birebir aynı olacaktır.

---

## Adım 9 — Raporlar ve yönetim ana sayfası

**Durum:** `[x] Tamamlandı — 14 Temmuz 2026`
**Amaç:** İşletmenin günlük durumunu tek bakışta göstermek.

### Yapılacaklar

- [x] Günlük, haftalık ve aylık satışlar gösteriliyor.
- [x] Personel, müşteri, ürün ve varyant bazında satış raporu yapıldı.
- [x] Onay, tedarik ve kısmi sevkiyat bekleyen sipariş uyarıları yapıldı.
- [x] Mevcut, ayrılmış, kullanılabilir ve kritik stok; depo bazında raporlandı.
- [-] Tahsilat ve geciken ödeme raporu Adım 7 atlandığı için geliştirilmedi.
- [-] Ziyaret raporu Adım 4 ertelendiği ve bu adımın onaylı kapsamına alınmadığı için geliştirilmedi.
- [x] Bekleyen, hak edilmiş ve ödenmiş durumları içeren prim özeti yapıldı.
- [x] Temel brüt kârlılık raporu yapıldı; maliyet ve kâr yalnızca `products.view-cost` izniyle gösteriliyor.
- [x] Personel/tarih filtreleri ve aynı filtre/yetkileri koruyan CSV/XLSX dışa aktarımı yapıldı.
- [x] Yönetim ana sayfasına aylık satış, onay bekleyen, kritik stok, tedarik ve kısmi sevk göstergeleri eklendi.

### Ara doğrulama kaydı — 14 Temmuz 2026

- Sunucu tarafı rapor servisi mevcut satış, satış satırı, stok bakiyesi, depo ve prim tabloları üzerinde çalışıyor; yeni migration gerekmedi.
- `formmix:verify-report-foundation` geçici örnek verilerle net satış, personel filtresi, ürün maliyeti, brüt kâr, onay bekleyen sipariş, kritik stok, prim ve CSV/XLSX çıktılarını SQLite üzerinde doğruladı; işlem sonunda test verileri geri alındı.
- `formmix:verify-permissions` yönetici/satış yöneticisi/muhasebe rapor erişimini ve saha/depo reddini; ayrıca maliyet yetkisini doğruladı.
- PHP sözdizimi, rota listesi, SQLite migration ve `git diff --check` kontrolleri geçti.
- Docker uygulaması güncel kodla yeniden oluşturuldu; uygulama, MySQL ve Mailpit servisleri sağlıklı duruma geldi. Migration, rapor hesaplama ve yetki komutları Docker MySQL üzerinde geçti.
- Masaüstü, tablet ve telefon uyumu kullanıcı tarafından görsel olarak kontrol edilip başarılı bulundu.
- Kullanıcı onayıyla Adım 9 tamamlandı; sıradaki çalışma Adım 10 — gerçek kullanıcı denemesi ve yayına alma olarak belirlendi.

### Tamamlanmış sayılması için

Rapor sonuçları örnek veritabanı kayıtlarıyla tek tek karşılaştırılmalı ve yetkiye göre gizlenmesi gereken bilgiler gizlenmelidir.

---

## Adım 10 — Gerçek kullanıcı denemesi ve yayına alma

**Durum:** `[~] Yayına hazırlık altyapısı tamamlandı — manuel test ve canlı onay bekliyor`
**Amaç:** Sistemi bütün personele açmadan önce küçük bir grupla güvenli biçimde denemek.

### Yapılacaklar

- [x] `KABUL-*` kodlu, gerçek kayıtlardan açıkça ayrılan ve güvenli komutla temizlenebilen müşteri, ürün/varyant, stok, tedarikçi, alış, sipariş ve prim deneme verileri oluşturuldu.
- [ ] İşletme sahibi, satış yöneticisi, saha personeli, muhasebe ve depo kullanıcılarıyla manuel kabul testi yapılacak.
- [x] Kullanım sırasında bulunan sorunların önem derecesi, açıklaması, çözümü ve tekrar testiyle saklanacağı ekran yapıldı.
- [~] Kritik sorunların kapanmadan hazırlık durumunu engellemesi uygulandı; gerçek sorunlar manuel test sırasında giderilip tekrar test edilecek.
- [x] Panel içinde görev bazlı kısa kullanım rehberi ve ayrıntılı proje rehberi hazırlandı.
- [-] Canlı veri aktarımı kullanıcı kararıyla manuel testlerden sonraya bırakıldı; bu çalışmada yapılmadı.
- [x] Son yedek ve geri dönüş için zorunlu kontrol kapıları ile uygulanacak plan hazırlandı; gerçek son yedek yayından hemen önce alınacak.
- [-] Sınırlı kullanıcı ve genel yayın kullanıcı kararıyla sonraya bırakıldı; bu çalışmada yapılmadı.

### Yayına hazırlık kaydı — 14 Temmuz 2026

- Yalnızca `settings.manage` yetkili işletme sahibinin erişebildiği `/panel/yayina-hazirlik` ekranı eklendi.
- Deneme hazırlığı, beş görev, üç cihaz, güvenlik, e-posta, yedek, geri dönüş, canlı ortam, eğitim, destek ve yazılı onayı kapsayan 18 kalıcı kontrol maddesi oluşturuldu.
- Sorunlu ve kapsam dışı maddelerde açıklama; sorun kapatmada çözüm ve tekrar test notu zorunlu tutuldu. Yedek, geri dönüş, canlı ortam, kritik sorun ve yazılı onay kapsam dışı bırakılamaz.
- Açık kritik/yüksek sorun, bekleyen/sorunlu madde veya geçmemiş zorunlu kapı varken ekran “hazır” durumuna geçmez.
- Bütün panel kullanıcılarının görevine uygun içeriği gördüğü `/panel/kullanim-rehberi` ekranı eklendi.
- `formmix:verify-release-readiness` tablo, görev/yetki, yazılabilir dizin, yedek betikleri, HTTPS canlı alan adı kuralı ve geçici yazma/geri alma işlemlerini doğruluyor; yayın işlemi yapmıyor.
- SQLite ve Docker MySQL migration’ları, teknik ön kontrol, görev yetkileri, PHP lint, rotalar ve `git diff --check` doğrulandı.
- `formmix:seed-acceptance-data` komutu Docker MySQL üzerinde çalıştırıldı; ikinci çalıştırmada mükerrer kayıt oluşturmadığı doğrulandı.
- Deneme verileri: `KABUL-MUS-001`, `KABUL-URUN-001`, iki varyant, `KABUL-ALS-001` ve onaylı/onay bekleyen/tedarik bekleyen/kısmi sevk/sevk edilmiş beş sipariş.
- Deneme verisi hazırlık maddesi otomatik olarak “Başarılı” yapıldı; kalan 17 manuel madde “Bekliyor” durumundadır.
- Canlıya alma yapılmadı. Deneme verileri gerektiğinde yalnızca `formmix:cleanup-acceptance-data --confirm` komutuyla temizlenebilir.

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
