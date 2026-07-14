# FORMMIX Satış ve İş Takip Sistemi

Bu dosya, yeni satış ve iş takip sistemi çalışmalarının başlangıç noktasıdır.

## Şu anki durum

- Proje kapsamı hazırlandı.
- Adım adım geliştirme planı hazırlandı.
- İş akışları ve kullanıcı yetkileri taslak olarak hazırlandı.
- Test ve onay kuralları hazırlandı.
- **Adım 0 tamamlandı:** İşletmenin çalışma, sipariş, stok, tahsilat ve prim kuralları onaylandı.
- **Adım 1 tamamlandı:** Giriş, yetki, işlem geçmişi, e-posta ve yedek geri yükleme testleri geçti.
- **Adım 2 tamamlandı:** Personel, müşteri, sorumlu atama/devir, mükerrer uyarı, görüşme takibi ve mobil ekran testleri geçti.
- **Adım 3 tamamlandı:** Ürün, varyant, müşteri fiyat grubu, özel fiyat ve toplu fiyat güncelleme altyapısı kuruldu; SQLite, MySQL, yetki ve mobil ekran testleri geçti.
- **Adım 4 ertelendi:** Fiziksel müşteri ziyareti ve konum kaydı işletme kararıyla ilk sürümde şimdilik uygulanmayacak; herhangi bir ziyaret kodu geliştirilmedi.
- **Adım 5 başlangıç kapsamı tamamlandı:** Teklif, sipariş, onay, iptal, fiyat çözümleme, mobil taslak ve görev bazlı görünürlük testleri geçti.
- **Adım 6 tamamlandı:** Tedarikçi, alış siparişi, kısmi mal kabul, çoklu depo, varyant stoğu, giriş/çıkış/transfer/iade/düzeltme, sipariş ayırma, sevkiyat, kritik stok ve sayım farkı kuruldu; SQLite ve MySQL senaryoları geçti.
- **Adım 7 atlandı:** Cari hesap, tahsilat ve ödeme kullanıcı kararıyla şu an uygulanmayacak.
- **Adım 8 başlangıç kapsamı tamamlandı:** Satış/kâr bazlı prim kuralı, bekleyen/hak edilmiş durum, dönem kapatma ve ödeme kaydı eklendi.

## Nereden devam edeceğiz?

1. [Karar Defteri](proje-belgeleri/06_KARAR_DEFTERI.md) içindeki başlangıç kararları tamamlandı.
2. [İş Akışları](proje-belgeleri/03_IS_AKISLARI.md) onaylanan kurallara göre güncellendi.
3. [Uygulama Planı](proje-belgeleri/02_UYGULAMA_PLANI.md) içindeki Adım 4 işletme kararıyla ertelendi; Adım 5'in başlangıç kapsamı ve Adım 6 tamamlandı. Sıradaki çalışma Adım 7 cari hesap, tahsilat ve ödemedir.

Tüm belgelerin açıklaması ve çalışma kuralları için [Önce Burayı Okuyun](proje-belgeleri/00_OKU_BURADAN.md) dosyasını açın.

## Docker ile çalıştırma

Uygulama Docker üzerinde `http://localhost:8082` adresinde çalışır. Kurulum ve günlük kullanım komutları için [DOCKER.md](DOCKER.md) dosyasını açın.

```powershell
docker compose up -d
```

## Temel kural

Bir adım geliştirilip test edilmeden ve gerekiyorsa işletme onayı alınmadan sonraki adıma geçilmeyecektir. Böylece müşteri, stok, tahsilat ve prim kayıtlarının birbiriyle çelişmesi önlenecektir.
