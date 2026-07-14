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
- **Sıradaki çalışma Adım 3:** Ürün, seçenek ve fiyat yönetimi.

## Nereden devam edeceğiz?

1. [Karar Defteri](proje-belgeleri/06_KARAR_DEFTERI.md) içindeki başlangıç kararları tamamlandı.
2. [İş Akışları](proje-belgeleri/03_IS_AKISLARI.md) onaylanan kurallara göre güncellendi.
3. [Uygulama Planı](proje-belgeleri/02_UYGULAMA_PLANI.md) içindeki Adım 2 tamamlandı; Adım 3 sırada.

Tüm belgelerin açıklaması ve çalışma kuralları için [Önce Burayı Okuyun](proje-belgeleri/00_OKU_BURADAN.md) dosyasını açın.

## Docker ile çalıştırma

Uygulama Docker üzerinde `http://localhost:8082` adresinde çalışır. Kurulum ve günlük kullanım komutları için [DOCKER.md](DOCKER.md) dosyasını açın.

```powershell
docker compose up -d
```

## Temel kural

Bir adım geliştirilip test edilmeden ve gerekiyorsa işletme onayı alınmadan sonraki adıma geçilmeyecektir. Böylece müşteri, stok, tahsilat ve prim kayıtlarının birbiriyle çelişmesi önlenecektir.
