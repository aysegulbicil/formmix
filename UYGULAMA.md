# FORMMIX Satış ve İş Takip Sistemi

Bu dosya, yeni satış ve iş takip sistemi çalışmalarının başlangıç noktasıdır.

## Şu anki durum

- Proje kapsamı hazırlandı.
- Adım adım geliştirme planı hazırlandı.
- İş akışları ve kullanıcı yetkileri taslak olarak hazırlandı.
- Test ve onay kuralları hazırlandı.
- **Adım 0 tamamlandı:** İşletmenin çalışma, sipariş, stok, tahsilat ve prim kuralları onaylandı.
- **Adım 1 tamamlandı:** Giriş, yetki, işlem geçmişi, e-posta ve yedek geri yükleme testleri geçti.
- **Şu an Adım 2'deyiz:** Müşteri ve personel yönetimini hazırlıyoruz; veri temeli oluşturuldu.

## Nereden devam edeceğiz?

1. [Karar Defteri](proje-belgeleri/06_KARAR_DEFTERI.md) içindeki başlangıç kararları tamamlandı.
2. [İş Akışları](proje-belgeleri/03_IS_AKISLARI.md) onaylanan kurallara göre güncellendi.
3. [Uygulama Planı](proje-belgeleri/02_UYGULAMA_PLANI.md) içindeki Adım 2 uygulanıyor.

Tüm belgelerin açıklaması ve çalışma kuralları için [Önce Burayı Okuyun](proje-belgeleri/00_OKU_BURADAN.md) dosyasını açın.

## Docker ile çalıştırma

Uygulama Docker üzerinde `http://localhost:8082` adresinde çalışır. Kurulum ve günlük kullanım komutları için [DOCKER.md](DOCKER.md) dosyasını açın.

```powershell
docker compose up -d
```

## Temel kural

Bir adım geliştirilip test edilmeden ve gerekiyorsa işletme onayı alınmadan sonraki adıma geçilmeyecektir. Böylece müşteri, stok, tahsilat ve prim kayıtlarının birbiriyle çelişmesi önlenecektir.
