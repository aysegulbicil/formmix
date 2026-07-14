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
- [ ] İptal edilen sipariş stok ve prim hesaplarını doğru düzeltiyor.

### Stok

- [ ] Alış girişi stoğu artırıyor.
- [ ] Sevkiyat stoğu azaltıyor.
- [ ] İade, ürünün durumuna göre doğru stoğa giriyor.
- [ ] Eksi stok izni işletme kuralına uygun çalışıyor.

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

