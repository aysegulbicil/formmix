# Test ve Onay Kuralları

## Bir iş ne zaman tamamlanmış sayılır?

Bir ekranın açılması veya bir düğmenin çalışması tek başına yeterli değildir. Her iş için aşağıdaki şartlar aranır:

- İstenen işlem doğru sonucu üretir.
- Hatalı bilgi girildiğinde anlaşılır uyarı verir.
- Yetkisiz kullanıcı işlemi yapamaz.
- Telefon ve bilgisayar ekranında kullanılabilir.
- Yapılan önemli değişiklik kayda geçer.
- İlgili eski özellikler bozulmaz.
- Gerçek kullanıcı örneğiyle denenir.
- Sonuç plan dosyasına yazılır.

## Her adımda yapılacak ortak kontroller

- [ ] Doğru kullanıcı işlemi yapabiliyor.
- [ ] Yanlış kullanıcı işlemi yapamıyor.
- [ ] Zorunlu alanlar boş bırakılamıyor.
- [ ] Hatalı değerler anlaşılır Türkçe mesajla reddediliyor.
- [ ] Aynı kayıt yanlışlıkla iki kez oluşmuyor.
- [ ] Sayfa yenilendiğinde kayıt kaybolmuyor.
- [ ] Telefon ekranında yatay taşma olmuyor.
- [ ] İşlem tarihi ve işlemi yapan kullanıcı görülebiliyor.
- [ ] Beklenmeyen hata kullanıcıya teknik ayrıntı göstermiyor.
- [ ] Mevcut kurumsal site çalışmaya devam ediyor.

## Önemli örnek testler

### Müşteri

- [ ] Aynı telefonla ikinci müşteri açılırken uyarı veriliyor.
- [ ] Müşteri başka personele atandığında geçmiş atama korunuyor.
- [ ] Saha personeli başka personele ait müşteriyi göremiyor.

### Sipariş

- [ ] Toplamlar adet, fiyat, indirim ve vergiye göre doğru hesaplanıyor.
- [ ] Yetki sınırını aşan indirim onay olmadan ilerlemiyor.
- [ ] Bağlantı yavaşken düğmeye iki kez basılması iki sipariş oluşturmuyor.
- [x] İptal edilen sipariş ayrılmış stoğu serbest bırakıyor; prim etkisi Adım 8'de doğrulanacak.

### Stok

- [x] Alış girişi stoğu artırıyor.
- [x] Sevkiyat stoğu azaltıyor ve ayrılmış miktarı kapatıyor.
- [x] Kullanılabilir iade doğru stoğa giriyor; kullanılamaz ürün stok dışı gerekçeli hareket olarak tutuluyor.
- [x] Eksi stok engelleniyor; stoksuz sipariş tedarik bekliyor.

### Cari hesap

- [ ] Kısmi tahsilatta kalan borç doğru görünüyor.
- [ ] Yanlış tahsilatın iptal kaydı bakiyeyi doğru düzeltiyor.
- [ ] Vadesi geçen ödeme doğru listeleniyor.

### Prim

- [ ] Normal satış primi doğru hesaplanıyor.
- [ ] Kısmi tahsilat kuralı doğru uygulanıyor.
- [ ] İade primi doğru azaltıyor.
- [ ] Müşteri personel değiştirdiğinde onaylanan paylaşım kuralı uygulanıyor.
- [ ] Geçmiş prim kuralı sonradan değiştirilemiyor veya geçmiş sonucu bozmuyor.

## Yayına çıkmadan önce

- [ ] Canlı ortamın yedeği alındı.
- [ ] Yedekten geri dönüş denendi.
- [ ] Deneme kullanıcıları kapatıldı veya şifreleri değiştirildi.
- [ ] Gerçek kullanıcı yetkileri kontrol edildi.
- [ ] Telefon ve bilgisayar denemeleri tamamlandı.
- [ ] Kritik hata kalmadı.
- [ ] Kullanıcılara kısa eğitim verildi.
- [ ] Sorun halinde aranacak kişi belirlendi.
- [ ] İşletme canlı kullanıma onay verdi.

## Test kayıt örneği

Her tamamlanan adım için aşağıdaki biçim kullanılacaktır:

```text
Adım:
Test tarihi:
Testi yapan:
Kullanılan cihaz:
Deneme işlemleri:
Başarılı sonuçlar:
Bulunan sorunlar:
Sorunların durumu:
İşletme onayı:
```

## Adım 9 ara test kaydı

```text
Adım: 9 — Raporlar ve yönetim ana sayfası
Test tarihi: 14 Temmuz 2026
Testi yapan: Otomatik geliştirme doğrulaması
Kullanılan ortam: Yerel SQLite, Docker MySQL ve PHP CLI
Deneme işlemleri: Satış özeti, personel/müşteri/ürün filtresi, brüt kâr, bekleyen sipariş, kritik stok, prim özeti, CSV/XLSX ve görev yetkileri
Başarılı sonuçlar: Rapor hesapları, maliyet gizleme, dışa aktarım, PHP lint, rotalar, SQLite ve Docker MySQL migration, yetki matrisi, servis sağlıkları ve git diff --check
Bulunan sorunlar: Otomatik yerel tarayıcı erişimi ortam güvenlik politikası nedeniyle reddedildi
Sorunların durumu: Kullanıcı masaüstü, tablet ve telefon görünümünü kontrol ederek başarılı buldu
İşletme onayı: Kullanıcı tarafından 14 Temmuz 2026 tarihinde verildi; Adım 9 tamamlandı
```

## Adım 10 hazırlık test kaydı

```text
Adım: 10 — Gerçek kullanıcı denemesi ve yayına alma hazırlığı
Test tarihi: 14 Temmuz 2026
Testi yapan: Otomatik geliştirme doğrulaması
Kullanılan ortam: Yerel SQLite ve Docker MySQL
Deneme işlemleri: 18 kontrol maddesi migration'ı, sorun/kontrol yazma ve geri alma, zorunlu kapılar, görev yetkisi, kullanım rehberi rotası ve teknik ön kontrol
Başarılı sonuçlar: SQLite ve MySQL migration, geçici yazma/rollback, owner erişimi, diğer görevlerin reddi, rota, PHP lint, yedek betiği ve servis sağlık kontrolleri
Bulunan sorunlar: Docker başlangıç migration'ı ile eşzamanlı ikinci migrate komutu aynı tabloyu oluşturmaya çalıştı; migration kaydı ve 18 madde eksiksiz doğrulandı. Yerel Docker production/HTTP ayrımı canlı alan adı HTTPS kontrolünden ayrıldı.
Sorunların durumu: Düzeltildi ve tekrar doğrulandı
İşletme onayı: Manuel kabul testleri ve gerçek canlıya alma için henüz verilmedi
```

### Kabul testi veri kaydı

- Docker MySQL'e `KABUL-*` önekli bir müşteri, bir satış personeli, bir ürün, iki varyant, bir tedarikçi ve bir alış siparişi eklendi.
- Onaylandı, onay bekliyor, tedarik bekliyor, kısmi sevk ve sevk edildi durumlarında beş sipariş oluşturuldu; genel toplamlar sırasıyla 1.296,00; 720,00; 1.152,00; 864,00 ve 432,00 TL olarak doğrulandı.
- İki varyantın stok bakiyesi, kritik stok örneği ve sevk edilmiş siparişe bağlı hak edilmiş prim kaydı oluşturuldu.
- Tohum komutu ikinci kez çalıştırıldı ve yeni kayıt oluşmadı.
- `test-data` hazırlık maddesi başarılı durumuna geçti; 17 manuel madde bekliyor.
- Temizleme komutu hazırlandı fakat çalıştırılmadı.
