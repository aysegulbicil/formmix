# FORMMIX Satış ve İş Takip Sistemi

**Belge tarihi:** 14 Temmuz 2026  
**Durum:** Adım 2 — Müşteri ve personel yönetimi  
**Ana takip belgesi:** [02_UYGULAMA_PLANI.md](02_UYGULAMA_PLANI.md)

## Bu klasör neden var?

Bu klasör, geliştirilecek sistemin ne yapacağını ve hangi sırayla yapılacağını açıkça anlatır. Amaç; bir aşama tamamlanmadan diğerine geçmemek, sonradan unutulan işler yüzünden sistemi yeniden kurmak zorunda kalmamak ve alınan kararları kaybetmemektir.

Bu belgelerde mümkün olduğunca sade Türkçe kullanılmıştır. Teknik bir kelime kullanılması gerektiğinde yanında kısa açıklaması verilmiştir.

## Dosyalar

| Dosya | Ne için kullanılır? |
|---|---|
| [01_PROJE_KAPSAMI.md](01_PROJE_KAPSAMI.md) | Sistemin yapacağı ve ilk sürümde yapmayacağı işleri açıklar. |
| [02_UYGULAMA_PLANI.md](02_UYGULAMA_PLANI.md) | Hangi adımda olduğumuzu ve sıradaki işi gösterir. Her çalışmada önce bu dosya açılır. |
| [03_IS_AKISLARI.md](03_IS_AKISLARI.md) | Müşteri, ziyaret, sipariş, stok, tahsilat ve prim süreçlerini anlatır. |
| [04_KULLANICI_YETKILERI.md](04_KULLANICI_YETKILERI.md) | Kimin hangi bilgiyi görebileceğini ve hangi işlemi yapabileceğini gösterir. |
| [05_TEST_VE_ONAY.md](05_TEST_VE_ONAY.md) | Bir işin gerçekten tamamlandığını nasıl kontrol edeceğimizi açıklar. |
| [06_KARAR_DEFTERI.md](06_KARAR_DEFTERI.md) | İşletme tarafından verilmesi gereken kararları ve alınan kararların tarihçesini tutar. |
| [07_TEKNIK_KURULUM.md](07_TEKNIK_KURULUM.md) | Geliştirme, canlı ortam, veritabanı ve ilk kullanıcı kurulumunu açıklar. |

## Çalışma kuralımız

Her geliştirme çalışmasında şu sıra izlenir:

1. `02_UYGULAMA_PLANI.md` açılır ve yalnızca sıradaki adım ele alınır.
2. O adımın açıklaması ve tamamlanma şartları okunur.
3. Karar verilmesi gereken bir konu varsa `06_KARAR_DEFTERI.md` güncellenir.
4. Gerekli geliştirme yapılır.
5. `05_TEST_VE_ONAY.md` içindeki ilgili kontroller uygulanır.
6. Test sonucu plana yazılır.
7. İşletme onayı gerekiyorsa onay alınır.
8. Ancak bundan sonra sıradaki adıma geçilir.

## Durum işaretleri

- `[ ]` Başlanmadı
- `[~]` Üzerinde çalışılıyor
- `[x]` Tamamlandı ve kontrol edildi
- `[!]` Karar veya bilgi bekleniyor

## Önemli sınır

Mevcut FORMMIX sitesi çalışmaya devam edecektir. Yeni satış ve iş takip sistemi, sitenin yönetim ve saha bölümü olarak kurulacaktır. İlk sürümde resmi muhasebe programının tamamı yeniden yazılmayacaktır. Cari hesap, tahsilat, ödeme ve temel kârlılık takibi yapılacak; e-fatura ve resmi muhasebe işlemleri için daha sonra uygun bir muhasebe programıyla bağlantı kurulacaktır.
