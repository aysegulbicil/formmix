# İş Akışları

Bu dosya sistemde bir işin baştan sona nasıl ilerleyeceğini anlatır. Akışlar Adım 0 sonunda işletme tarafından onaylanmıştır.

## 1. Yeni müşteri ve personel ataması

1. Saha personeli yeni firma bilgilerini girer.
2. İlk kayıtta firma adı, yetkili kişi, telefon, il ve ilçe zorunludur. E-posta, açık adres ve not daha sonra tamamlanabilir.
3. Sistem telefon veya vergi numarası benzer olan kayıtları gösterir.
4. Aynı firma yoksa yeni müşteri adayı oluşturulur.
5. Kaydı açan personel, yönetici değiştirene kadar müşterinin sorumlusu olur.
6. Yönetici gerekli görürse müşteriyi başka bir personele devredebilir.
7. Müşteri başka personele verilirse eski atama kapatılır, yeni atama başlatılır.
8. Eski kayıt silinmez; geçmişte kimin ilgilendiği görülebilir.
9. Müşteride 60 gün boyunca ziyaret, görüşme, teklif veya sipariş işlemi olmazsa yöneticiye bildirim gönderilir.
10. Bu bildirim müşteriyi otomatik olarak başka personele devretmez; devir kararını yönetici verir.
11. Devir işleminin tarihi, nedeni, eski ve yeni personeli ve işlemi yapan yönetici kaydedilir.
12. Devirden önce oluşturulan siparişler eski personelde, devirden sonra oluşturulan siparişler yeni personelde kalır.
13. Bir müşterinin aynı anda yalnızca bir ana sorumlusu bulunur.
14. Saha personeli kendi müşterilerinin ayrıntılarını görür. Başka personele ait müşterinin ayrıntılarını göremez; yeni kayıt sırasında yalnızca müşterinin zaten kayıtlı ve atanmış olduğu uyarısını alır.
15. Henüz bir personele atanmamış müşterileri yönetici dağıtır.

## 2. Saha ziyareti

1. Personel kendi müşteri listesinden firmayı seçer.
2. Ziyareti başlatır.
3. Görüşme sonucunu seçer: ulaşılamadı, bilgi verildi, teklif istendi, sipariş alındı veya daha sonra görüşülecek.
4. Gerekli notu ve sonraki görüşme tarihini girer.
5. Sipariş varsa aynı müşteri üzerinden sipariş ekranına geçer.
6. Ziyareti bitirir.
7. Yönetici ziyaret sonuçlarını personel bazında görebilir.
8. Ortak ziyaret varsa diğer personel yardımcı olarak eklenebilir. Yardımcı kaydı müşterinin ana sorumlusunu veya primi otomatik olarak değiştirmez.
9. Kullanıcı izin verirse ziyaret başlangıç ve bitiş anında konum kaydedilir. Sürekli konum takibi yapılmaz.
10. İnternet yoksa ziyaret taslak olarak telefonda korunur ve bağlantı geldiğinde gönderilir.

## 3. Tekliften siparişe

1. Personel müşteriyi seçer.
2. Ürün, beden, renk, baskı seçeneği ve adet girer.
3. Sistem geçerli fiyatı getirir.
4. Liste fiyatı vergi hariçtir. Ara toplam, vergi ve ödenecek genel toplam ayrı gösterilir.
5. Saha personeli başlangıçta en fazla %5 indirim yapabilir.
6. %5 üzerindeki indirim satış yöneticisinin, %15 üzerindeki indirim işletme sahibinin onayına gider.
7. Bu sınırlar yönetici tarafından personele göre değiştirilebilir.
8. Yetki dışı indirim içeren teklif veya sipariş silinmez; onay bekliyor durumuna geçer.
9. Müşteri kabul ederse teklif siparişe çevrilir.
10. Saha personelinin gönderdiği sipariş satış yöneticisinin kontrolüne düşer. Siparişi giren kişi kendi siparişini onaylayamaz.
11. Sipariş merkez ekranına düşer.
12. Siparişi kimin oluşturduğu ve satışın kime ait olduğu ayrı ayrı saklanır.
13. İnternet yoksa sipariş taslağı telefonda korunur; bağlantı geldiğinde bir kez gönderilir.

## 4. Sipariş hazırlama ve teslimat

1. Yönetim siparişi kontrol eder ve onaylar.
2. Depoda ürün varsa siparişe ayrılır. Ürün yoksa sipariş tedarik bekliyor olarak işaretlenir ve stok eksiye düşmez.
3. Ürün hazırlanır veya gerekiyorsa tedarik/üretim süreci başlatılır.
4. Hazırlama başlamadan önce satış yöneticisi iptal yapabilir. Hazırlama başladıktan sonra yalnızca işletme sahibi, neden ve maliyet yazarak iptal yapabilir.
5. Hazır olan sipariş sevk edilir.
6. Teslim bilgisi kaydedilir.
7. Eksik teslimat varsa teslim edilen ve kalan miktar ayrı tutulur; sipariş açık kalır.
8. İade olursa ayrı bir iade kaydı oluşturulur; eski sipariş değiştirilmez.

## 5. Alış ve stok

1. İhtiyaç için tedarikçiye alış siparişi açılır.
2. Ürün geldiğinde gerçek gelen miktar kaydedilir.
3. Stok giriş hareketi oluşur.
4. Beden ve renk birleşimi ayrı stok kalemi olarak izlenir.
5. Baskısız ürün ile müşteriye özel hazırlanmış ürün ayrı durumda tutulur.
6. Satış için ayrılan veya sevk edilen ürünlerde stok hareketi oluşur.
7. İade gelen ürün kontrol sonrası kullanılabilir veya kullanılamaz olarak kaydedilir.
8. Sayım farkı varsa yetkili kullanıcı nedeni yazarak düzeltme yapar.
9. Stok eksiye düşmez ve hiçbir stok hareketi açıklamasız silinmez.

## 6. Tahsilat

1. Onaylanan satış müşteri hesabında, başlangıçta 30 gün vadeli borç oluşturur. Yetkili kullanıcı vadeyi değiştirebilir.
2. Tahsilat geldiğinde ödeme yöntemi, tarih ve tutar girilir.
3. Yetkili saha personelinin girdiği tahsilat bildirimi, muhasebe onaylayana kadar borcu kapatmaz.
4. Tahsilat seçilen siparişlerle eşleştirilir. Seçim yoksa en eski vadesi gelen borçtan başlanır.
5. Kısmi ödeme varsa kalan borç açık kalır; fazla ödeme müşteri alacağı olarak saklanır.
6. Yanlış tahsilat silinmez; iptal kaydıyla düzeltilir.
7. Vadesi geçen bakiye yönetim ve muhasebe ekranında gösterilir.

## 7. Prim

Onaylanan başlangıç akışı aşağıdadır:

1. Sipariş onaylandığında bekleyen prim kaydı oluşur.
2. Muhasebenin onayladığı tahsilat oranında prim hak edilmiş duruma geçer. Kısmi tahsilatta prim de kısmi oluşur.
3. Başlangıç oranı %3'tür. Prim, indirim sonrası vergi hariç net satış tutarından hesaplanır.
4. İade veya iptal olursa eski kayıt silinmeden eksi düzeltme kaydı oluşturulur.
5. Ay sonunda personel prim dökümü kontrol edilir.
6. Onaylanan aylık dönem kapatılır.
7. Kapanan dönem değiştirilemez; gerekli fark sonraki döneme düzeltme olarak yazılır.
8. Ödenen primin tarihi ve tutarı kaydedilir.

Prim kaydında her zaman kaynak sipariş, hesaplanan tutar, oran, kural ve düzeltmeler görülebilir.

Müşteri başka personele devredilirse devirden önce oluşturulmuş siparişlerin primi eski personele, devirden sonra oluşturulan siparişlerin primi yeni personele ait olur. Müşteriyi ilk bulan personele süresiz prim verilmez.

Ortak çalışmada yönetici gerekli görürse yalnızca ilgili sipariş için prim paylaşımı tanımlayabilir. Bu paylaşım müşterinin ana sorumlusunu değiştirmez.

## 8. Sipariş durumları

Başlangıç için önerilen durumlar:

| Durum | Açıklama |
|---|---|
| Taslak | Personel henüz tamamlamadı. Merkez işlemez. |
| Onay bekliyor | Fiyat veya indirim kontrolü gerekiyor. |
| Onaylandı | Sipariş kabul edildi. |
| Hazırlanıyor | Depo, tedarik veya üretim işlemi sürüyor. |
| Sevke hazır | Sipariş gönderilmeye hazır. |
| Sevk edildi | Sipariş müşteriye gönderildi. |
| Kısmen teslim | Siparişin bir kısmı teslim edildi. |
| Teslim edildi | Sipariş tamamen teslim edildi. |
| İptal edildi | Sipariş devam etmeyecek. Nedeni zorunludur. |

Tahsilat durumu sipariş durumundan ayrı tutulmalıdır. Bir sipariş teslim edilmiş ancak henüz tahsil edilmemiş olabilir.
