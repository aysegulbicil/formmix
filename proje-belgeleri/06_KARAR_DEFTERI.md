# Karar Defteri

Bu dosya, geliştirmeye başlamadan önce işletmenin cevaplaması gereken soruları ve proje sırasında alınan kararları tutar. Karar değişirse eski satır silinmez; yeni tarihli bir karar eklenir.

## Başlangıçta cevaplanması gereken sorular

### Müşteri ve personel

- [x] Bir müşteriyi ilk kaydeden personel otomatik olarak o müşterinin sorumlusu olacak mı? **Evet. Yönetici değiştirene kadar müşterinin sorumlusu olacaktır.**
- [x] Müşteri ne kadar süre işlem yapılmazsa başka personele verilebilir? **60 gün işlem yapılmazsa yöneticiye bildirim gönderilir. Sistem otomatik devir yapmaz; devre yönetici karar verir.**
- [x] Başka personele devredilen müşterinin eski personele prim hakkı devam eder mi? **Devirden önce oluşturulan siparişlerin primi eski personele, devirden sonra oluşturulan siparişlerin primi yeni personele ait olur. Eski personel müşteriyi ilk bulan kişi olduğu için süresiz prim almaz.**
- [x] Aynı müşteriye birden fazla personel atanabilir mi? **Hayır. Her müşterinin aynı anda yalnızca bir ana sorumlusu olur. Başka personel ortak ziyarete yardımcı olarak eklenebilir; bu kayıt müşteri sahipliğini veya primi kendiliğinden değiştirmez. Yönetici gerekirse sipariş özelinde prim paylaşımı yapabilir.**
- [x] Saha personeli sadece kendi müşterisini mi, bölgesindeki tüm müşterileri mi görecek? **Kendi müşterilerinin tüm bilgilerini görecek. Başka personele ait müşteri bilgilerini görmeyecek; yeni kayıt sırasında yalnızca müşterinin sistemde kayıtlı ve atanmış olduğu uyarısını alacak. Atanmamış müşterileri yönetici dağıtacak.**
- [x] Yeni müşteri açarken hangi bilgiler zorunlu olacak? **İlk kayıtta firma adı, yetkili kişi, telefon, il ve ilçe zorunlu olacak. E-posta, açık adres ve not isteğe bağlı olacak. Sipariş onayından önce teslimat adresi; fatura öncesinde resmi unvan, vergi dairesi, vergi numarası ve fatura adresi zorunlu olacak. Aynı telefon numarası kullanılırsa sistem uyarı verecek.**

### Fiyat ve sipariş

- [x] Her personelin uygulayabileceği en yüksek indirim oranı nedir? **Başlangıçta saha personeli en fazla %5, satış yöneticisi en fazla %15 indirim yapabilecek. %15 üzerini yalnızca işletme sahibi onaylayacak. Oranlar yönetici ekranından personele göre değiştirilebilecek. Yetkiyi aşan sipariş silinmeyecek, onay bekleyecek.**
- [x] Fiyatlar vergi dahil mi, vergi hariç mi gösterilecek? **Liste fiyatları vergi hariç saklanacak. Teklif ve siparişte vergi hariç ara toplam, vergi ve ödenecek genel toplam ayrı gösterilecek. Vergi oranı ürüne göre tanımlanabilecek.**
- [x] Siparişi kim onaylayacak? **Saha personelinin gönderdiği sipariş satış yöneticisinin kontrolüne düşecek. %15 üzeri indirimde işletme sahibi onayı gerekecek. Siparişi giren kişi kendi siparişini onaylayamayacak.**
- [x] Stokta olmayan ürün sipariş edilebilecek mi? **Evet. Sipariş alınabilecek ancak ürün tedarik bekliyor olarak işaretlenecek, tahmini teslim tarihi verilecek ve stok eksiye düşürülmeyecek.**
- [x] Sipariş hangi aşamadan sonra iptal edilemeyecek? **Hazırlama başlamadan önce satış yöneticisi iptal edebilecek. Hazırlama başladıktan sonra yalnızca işletme sahibi, zorunlu neden ve oluşan maliyeti yazarak iptal edebilecek. Kayıt silinmeyecek.**
- [x] Kısmi teslimat yapılabilecek mi? **Evet. Teslim edilen ve kalan miktarlar ayrı izlenecek. Sipariş bütün ürünler tamamlanana kadar açık kalacak.**

### Stok ve alış

- [x] Kaç depo veya stok alanı bulunuyor? **İlk kurulum bir ana depo ile başlayacak. Sistem daha sonra birden fazla depo eklenebilecek şekilde hazırlanacak.**
- [x] Beden ve renk ayrı stok olarak mı takip edilecek? **Evet. Beden ve renk birleşimi ayrı stok kalemi olarak izlenecek.**
- [x] Baskısız ürün ile baskılı hazır ürün ayrı mı tutulacak? **Evet. Baskısız ürün ile müşteriye özel hazırlanmış veya baskılı ürün ayrı durumda ve gerektiğinde ayrı stok kodunda tutulacak.**
- [x] Eksi stok kullanımına izin verilecek mi? **Hayır. Stokta olmayan sipariş alınabilir fakat tedarik bekliyor olarak kalır; gerçek stok miktarı eksiye düşmez. Sayım düzeltmesi yalnızca yetkili kullanıcı tarafından gerekçeyle yapılır.**
- [x] Alış fiyatını kimler görebilecek? **İşletme sahibi, muhasebe ve ayrıca yetki verilen satın alma kullanıcısı görebilecek. Saha personeli ve depo alış fiyatını göremeyecek.**

### Tahsilat

- [x] Vadeli satış yapılacak mı? **Evet. Müşteriye göre vade ve gerektiğinde borç sınırı tanımlanabilecek.**
- [x] Varsayılan vade kaç gün olacak? **Başlangıçta 30 gün olacak; müşteri veya sipariş bazında yetkili kullanıcı değiştirebilecek.**
- [x] Saha personeli tahsilat alabilecek mi? **Yetki verilen saha personeli tahsilat bildirimi girebilecek. Bu kayıt muhasebe onaylayana kadar müşteri borcunu kapatmayacak. Nakit alındıysa makbuz ve açıklama zorunlu olacak.**
- [x] Nakit, banka, kredi kartı ve çek işlemlerinden hangileri kullanılacak? **Nakit, banka havalesi/EFT, kredi kartı ve çek desteklenecek. Yeni ödeme türleri sonradan eklenebilecek.**
- [x] Tahsilat tek tek siparişlere mi, genel müşteri borcuna mı kapatılacak? **Öncelikle seçilen siparişlere kapatılacak. Sipariş seçilmezse en eski vadesi gelen borçtan başlanacak. Fazla ödeme müşteri alacağı olarak saklanacak.**

### Prim

- [x] Prim siparişte mi, teslimatta mı, tahsilatta mı hak edilecek? **Siparişte bekleyen prim oluşacak; yalnızca muhasebe tarafından onaylanan tahsilat oranında hak edilecek. Kısmi tahsilatta prim de kısmi oluşacak.**
- [x] Prim satış tutarından mı, kârdan mı hesaplanacak? **İlk sürümde tahsil edilen vergi hariç net satış tutarından hesaplanacak. Başlangıç oranı %3 olacak ve yönetici tarafından değiştirilebilecek. Kâra göre prim daha sonra ayrıca tanımlanabilecek.**
- [x] Vergi ve indirim prim hesabına dahil olacak mı? **Vergi prime dahil olmayacak. İndirim satış tutarını düşürdüğü için prim, indirim sonrası vergi hariç net tutardan hesaplanacak.**
- [x] İade ve iptal primi nasıl geri alınacak? **Eski prim kaydı silinmeyecek. İade veya iptal kadar eksi düzeltme kaydı oluşturulacak; ödenmişse sonraki dönemden düşülecek.**
- [x] Müşteriyi bulan ile siparişi alan farklıysa prim nasıl paylaşılacak? **Prim normalde siparişin bağlı olduğu ana müşteri sorumlusuna ait olacak. Siparişi sisteme girmek tek başına prim hakkı doğurmayacak. Yönetici sipariş onayından önce özel paylaşım tanımlayabilecek.**
- [x] Ürün grubuna veya personele göre farklı oran kullanılacak mı? **Evet. Sistem genel oran, personel oranı ve ürün grubu oranını destekleyecek. Özel oran yoksa genel oran kullanılacak.**
- [x] Prim dönemi haftalık mı, aylık mı olacak? **Aylık olacak. Kapanan dönem sonradan değiştirilmek yerine düzeltme kaydıyla takip edilecek.**

### Kullanım ve güvenlik

- [x] Kullanıcı sayısı ve görev dağılımı nedir? **Sistem kullanıcı sayısını sabitlemeyecek. Yönetici, satış yöneticisi, saha personeli, muhasebe ve depo görevleri kullanılacak. Gerçek kullanıcı adları canlıya geçişte eklenecek.**
- [x] Ziyaret sırasında konum kaydı istenecek mi? **Kullanıcı açıkça izin verirse yalnızca ziyaret başlangıç ve bitiş anında konum alınacak. Sürekli konum takibi yapılmayacak. Konum izni yoksa ziyaret, gerekçe yazılarak devam edebilecek.**
- [x] İnternet olmayan bölgelerde çalışma zorunlu mu? **Evet. Müşteri, ziyaret ve sipariş taslakları telefonda geçici olarak saklanacak; bağlantı gelince gönderilecek. Onay ve tahsilat kesinleştirme işlemleri internet bağlantısı gerektirecek.**
- [x] Kimler telefon, kimler bilgisayar kullanacak? **Saha personeli öncelikle telefon kullanacak. Yönetici, muhasebe ve depo öncelikle bilgisayar veya tablet kullanacak. Bütün temel ekranlar farklı ekran boyutlarına uyumlu olacak.**
- [x] Şu anda kullanılan bir muhasebe programı var mı? **Henüz program bilgisi verilmedi. İlk sürüm bağımsız çalışacak ve tablo dosyası dışa aktarımı sağlayacak. Kullanılan muhasebe programı kesinleşince bağlantı ayrıca planlanacak.**

## Alınan kararlar

| No | Tarih | Konu | Karar | Kararı veren | Etkilenen adım |
|---:|---|---|---|---|---|
| 1 | 13.07.2026 | Geliştirme yöntemi | Sistem küçük ve kontrol edilen adımlarla geliştirilecek. Her adım test edilmeden sonrakine geçilmeyecek. | Proje başlangıç kararı | Tümü |
| 2 | 13.07.2026 | Telefon kullanımı | İlk sürüm telefon ekranına uygun web uygulaması olarak planlanacak. Ayrı mağaza uygulaması daha sonra değerlendirilecek. | İşletme onayı alındı | 4-5 |
| 3 | 13.07.2026 | Muhasebe kapsamı | İlk sürüm cari, tahsilat, ödeme ve temel kârlılık takibi yapacak. Resmi muhasebe ve e-fatura bağlantısı sonraki aşamada değerlendirilecek. | İşletme onayı alındı | 7 |
| 4 | 13.07.2026 | İlk müşteri sorumluluğu | Bir müşteriyi sisteme ilk kaydeden saha personeli, yönetici değiştirene kadar o müşterinin sorumlusu olacak. | İşletme onayı alındı | 0, 2, 4, 5, 8 |
| 5 | 13.07.2026 | İşlem yapılmayan müşteri | Bir müşteride 60 gün işlem yapılmazsa yöneticiye bildirim gönderilecek. Müşteri sistem tarafından otomatik devredilmeyecek; devri yalnızca yönetici yapacak. | İşletme onayı alındı | 0, 2, 4, 9 |
| 6 | 13.07.2026 | Müşteri devrinde prim | Devirden önce oluşturulan siparişlerin primi eski personele, devir tarihinden sonra oluşturulan siparişlerin primi yeni personele ait olacak. Müşteriyi ilk bulan personele süresiz prim verilmeyecek. Devir tarihi, nedeni ve işlemi yapan yönetici saklanacak. | İşletme onayı alındı | 0, 2, 5, 8 |
| 7 | 13.07.2026 | Tek ana müşteri sorumlusu | Her müşterinin aynı anda yalnızca bir ana sorumlusu olacak. Başka personel ortak ziyarete yardımcı olarak eklenebilecek; bu durum sahipliği veya primi otomatik değiştirmeyecek. Yönetici gerekirse sipariş özelinde prim paylaşımı yapabilecek. | İşletme onayı alındı | 0, 2, 4, 5, 8 |
| 8 | 13.07.2026 | Müşteri görünürlüğü | Saha personeli kendi müşterilerinin tüm bilgilerini görecek. Başka personele ait müşterinin ayrıntılarını görmeyecek; yeni kayıt sırasında yalnızca mükerrer kayıt uyarısı alacak. Atanmamış müşterileri yönetici dağıtacak. | İşletme onayı alındı | 0, 1, 2, 4 |
| 9 | 13.07.2026 | Zorunlu müşteri bilgileri | İlk kayıtta firma adı, yetkili, telefon, il ve ilçe zorunlu olacak. Sipariş onayında teslimat adresi; fatura öncesinde resmi firma ve vergi bilgileri tamamlanacak. Aynı telefon numarası için mükerrer kayıt uyarısı verilecek. | İşletme onayı alındı | 0, 2, 5, 7 |
| 10 | 13.07.2026 | İndirim yetkileri | Saha personeli başlangıçta en fazla %5, satış yöneticisi en fazla %15 indirim yapabilecek. %15 üzerini işletme sahibi onaylayacak. Oranlar sonradan personele göre değiştirilebilecek; sınırı aşan sipariş onay bekleyecek. | İşletme onayı alındı | 0, 3, 5 |
| 11 | 13.07.2026 | Fiyat ve vergi | Liste fiyatları vergi hariç saklanacak; ara toplam, vergi ve genel toplam ayrı gösterilecek. Vergi oranı ürüne göre tanımlanabilecek. | Genel öneriler işletme tarafından onaylandı | 0, 3, 5, 7 |
| 12 | 13.07.2026 | Sipariş onayı ve iptali | Saha siparişi satış yöneticisi tarafından onaylanacak; %15 üzeri indirim işletme sahibine gidecek. Hazırlama sonrası iptali yalnızca işletme sahibi gerekçeyle yapacak. Kayıtlar silinmeyecek. | Genel öneriler işletme tarafından onaylandı | 0, 5, 6 |
| 13 | 13.07.2026 | Stoksuz ve kısmi teslimat | Stoksuz ürün sipariş edilebilecek fakat tedarik bekleyecek ve stok eksiye düşmeyecek. Kısmi teslimat desteklenecek. | Genel öneriler işletme tarafından onaylandı | 0, 5, 6 |
| 14 | 13.07.2026 | Stok yapısı | Bir ana depoyla başlanacak; çoklu depo desteklenecek. Beden-renk ayrı, baskısız ve müşteriye özel hazırlanmış ürünler farklı durumda izlenecek. | Genel öneriler işletme tarafından onaylandı | 0, 3, 6 |
| 15 | 13.07.2026 | Stok ve alış güvenliği | Eksi stok olmayacak. Sayım düzeltmesi gerekçeli ve yetkili olacak. Alış fiyatını işletme sahibi, muhasebe ve yetkili satın alma kullanıcısı görecek. | Genel öneriler işletme tarafından onaylandı | 0, 1, 6 |
| 16 | 13.07.2026 | Vade ve tahsilat | Varsayılan vade 30 gün olacak. Saha tahsilat bildirimi muhasebe onayıyla kesinleşecek. Tahsilat seçilen siparişe, seçim yoksa en eski vadeli borca kapanacak. | Genel öneriler işletme tarafından onaylandı | 0, 5, 7 |
| 17 | 13.07.2026 | Prim matrahı ve zamanı | Başlangıç primi %3 olacak; tahsil edilen, indirim sonrası, vergi hariç net satış üzerinden oransal hak edilecek. Oranlar yönetimden değiştirilebilecek. | Genel öneriler işletme tarafından onaylandı | 0, 8 |
| 18 | 13.07.2026 | Prim düzeltme ve paylaşım | İade ve iptal eksi kayıtla düzeltilecek. Prim ana müşteri sorumlusuna ait olacak; yönetici sipariş öncesinde özel paylaşım tanımlayabilecek. | Genel öneriler işletme tarafından onaylandı | 0, 8 |
| 19 | 13.07.2026 | Prim dönemi | Primler aylık kapatılacak. Kapanan dönem doğrudan değiştirilmeden sonraki döneme düzeltme kaydı yazılacak. | Genel öneriler işletme tarafından onaylandı | 0, 8 |
| 20 | 13.07.2026 | Konum ve bağlantısız çalışma | Sürekli konum takibi yapılmayacak; izinle ziyaret başı ve sonunda konum alınacak. Bağlantı yokken taslaklar korunup sonra gönderilecek. | Genel öneriler işletme tarafından onaylandı | 0, 4, 5 |
| 21 | 13.07.2026 | Kullanıcı ve cihazlar | Kullanıcı sayısı sabitlenmeyecek. Saha telefondan; yönetim, muhasebe ve depo bilgisayar/tabletten çalışacak. Ekranlar farklı boyutlara uyumlu olacak. | Genel öneriler işletme tarafından onaylandı | 0, 1, 4-10 |
| 22 | 13.07.2026 | Muhasebe programı bağlantısı | İlk sürüm bağımsız çalışacak ve dışa aktarım sunacak. Kullanılan muhasebe programı belli olduğunda bağlantı ayrıca planlanacak. | Genel öneriler işletme tarafından onaylandı | 0, 7, 9 |
| 23 | 14.07.2026 | Fiziksel müşteri ziyareti | Fiziksel müşteri ziyareti ve konum kaydı ilk sürümde şimdilik ertelendi. Mevcut müşteri görüşme/not sistemi kullanılacak. İhtiyaç oluşursa ziyaret bölümü ayrıca yeniden değerlendirilecek. Bu karar, 20 numaralı kararın ziyaret ve konum bölümünü ilk sürüm için erteler; sipariş taslaklarının bağlantısız korunması kararı geçerlidir. | İşletme kararı | 4, 5 |
| 24 | 14.07.2026 | Teklif ve sipariş bağlantısı | Teklif ve sipariş aynı satış belgesi yapısında belge türüyle ayrılacak. Onaylı teklif siparişe çevrildiğinde teklif değiştirilmeyecek; kaynak teklife bağlı yeni sipariş oluşturulacak. | Onaylanan güvenli teknik yaklaşım | 5 |
| 25 | 14.07.2026 | Başlangıç sipariş durumları | İlk aşamada taslak, onay bekliyor, onaylandı ve iptal edildi durumları kullanılacak. Hazırlama, sevkiyat, teslimat ve iade hareketleri depo/sevkiyat adımında ele alınacak. | Onaylanan başlangıç kapsamı | 5, 6 |
| 26 | 14.07.2026 | Bağlantısız sipariş taslağı | Telefon taslağı cihazda benzersiz kimlikle saklanacak. Sunucu aynı kimlikle ikinci belge oluşturmayacak; başarılı kayıttan sonra cihaz taslağı temizlenecek. | Onaylanan güvenli teknik yaklaşım | 5 |
| 27 | 14.07.2026 | Alış, depo ve stok uygulaması | Adım 6 kullanıcı onayıyla başlatıldı. Tek ana depoyla başlayan çoklu depo yapısı, eksi stok yasağı, kısmi ayırma/sevkiyat ve silinmeyen gerekçeli hareket geçmişi uygulanacak. | Kullanıcı onayı ve önceki kararlar 13-15 | 6 |
| 28 | 14.07.2026 | Cari adımını atlama | Adım 7 şu an gerekli görülmedi; doğrudan sade prim başlangıç kapsamına geçilecek. Tahsilat bazlı prim bu nedenle bekleyecek. | Kullanıcı kararı | 7, 8 |
| 29 | 14.07.2026 | Adım 9 rapor kapsamı | Raporlar mevcut satış, stok ve prim verilerinden üretilecek. Adım 7 atlandığı için tahsilat, cari bakiye ve geciken ödeme raporları geliştirilmeyecek. Maliyet ve brüt kâr yalnızca `products.view-cost` izniyle ekranda ve dışa aktarımda gösterilecek. | Kullanıcı talimatı | 9 |
| 30 | 14.07.2026 | Adım 10 yayın sınırı | Yayına hazırlık altyapısı, manuel test listesi, sorun takibi, rehber ve geri dönüş planı uygulanacak; gerçek veri aktarımı ve canlıya alma kullanıcı manuel testleri tamamlayıp ayrıca onay verene kadar yapılmayacak. | Kullanıcı kararı | 10 |

## Karar ekleme kuralı

Yeni karar eklenirken şu bilgiler mutlaka yazılır:

- Kararın tarihi
- Açık ve tek anlamlı karar cümlesi
- Kararı veren kişi
- Hangi geliştirme adımını etkilediği
- Önceki bir kararı değiştiriyorsa eski kararın numarası
