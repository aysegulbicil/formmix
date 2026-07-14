# Kullanıcılar ve Yetkiler

## Temel kural

Her kullanıcı sadece işi için gerekli bilgiyi görür ve değiştirir. Bir ekranı görebilmek, o ekrandaki her işlemi yapabilmek anlamına gelmez.

## Başlangıç yetki tablosu

| İşlem | Yönetici | Satış yöneticisi | Saha personeli | Muhasebe | Depo |
|---|---:|---:|---:|---:|---:|
| Tüm müşterileri görme | Evet | Evet | Hayır | Evet | Sınırlı |
| Kendi müşterilerini görme | Evet | Evet | Evet | Evet | Sınırlı |
| Yeni müşteri ekleme | Evet | Evet | Evet | Evet | Hayır |
| Müşteriyi personele atama | Evet | Evet | Hayır | Hayır | Hayır |
| Ziyaret kaydı girme | Evet | Evet | Evet | Hayır | Hayır |
| Teklif hazırlama | Evet | Evet | Evet | Sınırlı | Hayır |
| Sipariş oluşturma | Evet | Evet | Evet | Sınırlı | Hayır |
| Yetki dışı indirimi onaylama | Evet | Evet | Hayır | Hayır | Hayır |
| Ürün ve fiyat değiştirme | Evet | Sınırlı | Hayır | Sınırlı | Hayır |
| Alış işlemi yapma | Evet | Hayır | Hayır | Evet | Sınırlı |
| Stok girişi ve çıkışı | Evet | Hayır | Hayır | Sınırlı | Evet |
| Tahsilat ve ödeme girişi | Evet | Hayır | Bildirim | Evet | Hayır |
| Prim kurallarını değiştirme | Evet | Sınırlı | Hayır | Sınırlı | Hayır |
| Kendi primini görme | Evet | Evet | Evet | Evet | Hayır |
| Tüm primleri görme | Evet | Evet | Hayır | Evet | Hayır |
| Kullanıcı ve yetki yönetimi | Evet | Hayır | Hayır | Hayır | Hayır |

`Sınırlı`, yalnızca iş için gereken alanların gösterileceği veya işlemin ayrıca izne bağlanacağı anlamına gelir.

## Rol açıklamaları

### Yönetici

Sistemin tamamını görebilir. Kullanıcı, yetki, fiyat, prim ve kritik iptal işlemlerini yönetir.

### Satış yöneticisi

Müşteri dağılımını, saha çalışmalarını, teklifleri ve siparişleri yönetir. İzin verilen sınırlar içinde fiyat ve indirim onayı verir.

### Saha personeli

Kendi müşterilerini ve ziyaretlerini tam olarak görür. Teklif ve sipariş oluşturur. Başka personelin müşterilerinin telefon, adres, sipariş ve görüşme ayrıntılarını göremez. Yeni müşteri kaydederken aynı firma sistemde varsa yalnızca müşterinin kayıtlı ve atanmış olduğu uyarısını görür. Yetki verilmişse tahsilat bildirimi girebilir; bu bildirim muhasebe onayı olmadan müşteri borcunu kapatmaz. Genel şirket raporlarını göremez.

### Muhasebe

Cari hesap, tahsilat, ödeme ve vade işlemlerini yapar. Saha personelinden gelen tahsilat bildirimlerini kontrol edip onaylar veya reddeder. Satış ve alış belgelerini görür. Kullanıcı yetkilerini değiştiremez.

### Depo

Onaylanmış siparişlerin hazırlanması, stok hareketleri, mal kabul ve sevkiyat bilgilerini yönetir. Müşterinin yalnızca teslimat için gerekli bilgilerini görür.

## Özel güvenlik kuralları

- Kullanıcı kendi yetkisini değiştiremez.
- Fiyat ve prim kuralı değişiklikleri geçmiş kayıtları bozamaz.
- Sipariş, tahsilat ve stok hareketleri iz bırakmadan silinemez.
- Önemli değişikliklerde önceki ve yeni değer saklanır.
- İşten ayrılan kullanıcının hesabı kapatılır; eski kayıtları korunur.
- Yönetici dahil bütün kullanıcılar ortak hesap yerine kendi hesabını kullanır.
