# Yayına Alma ve Geri Dönüş Planı

Bu belge hazırlık planıdır. Kullanıcının açık canlıya alma kararı olmadan hiçbir komut canlı sunucuda çalıştırılmaz ve gerçek veri aktarılmaz.

## Manuel testten önce

- Deneme kayıtlarına ayırt edilebilir kod veya açıklama verin.
- İşletme sahibi, satış yöneticisi, saha, muhasebe ve depo görevleri için ayrı hesap kullanın.
- Deneme başlangıcında veritabanı yedeği alın.
- Test edilecek cihazları ve tarayıcıları kontrol notuna yazın.

## Canlıya çıkış için zorunlu kapılar

- Açık kritik veya yüksek sorun bulunmamalıdır.
- Son yedek ayrı bir veritabanına geri yüklenmiş olmalıdır.
- Production, HTTPS, gerçek alan adı, MySQL ve SMTP ayarları doğrulanmalıdır.
- Gerçek kullanıcı görevleri son kez kontrol edilmelidir.
- İşletme yazılı canlı kullanım onayı vermelidir.

## Planlanan yayın sırası

1. Kullanımın en düşük olduğu zaman aralığı seçilir.
2. Uygulama yazma işlemleri kısa süreli durdurulur.
3. Zaman damgalı son veritabanı ve yüklenen dosya yedeği alınır.
4. Yedeğin boyutu ve güvenli saklama konumu kaydedilir.
5. Yeni sürüm dosyaları kurulur ve migration çalıştırılır.
6. Oturum, müşteri, sipariş, stok, prim ve rapor sağlık kontrolleri yapılır.
7. Önce sınırlı kullanıcı grubu sisteme alınır.
8. Kritik sorun yoksa diğer kullanıcılar açılır.

## Geri dönüş kararı

Aşağıdakilerden biri oluşursa yeni veri girişini durdurup geri dönüş değerlendirilir:

- Kullanıcıların sisteme girememesi
- Sipariş toplamı veya stok bakiyesinde doğrulanmış kritik hata
- Yetkisiz veri erişimi veya maliyet sızıntısı
- Migration hatası ya da veri kaybı şüphesi
- Yedekten düzeltilemeyen tekrarlı sunucu hatası

## Geri dönüş sırası

1. Yeni veri girişini durdurun ve hata saatini kaydedin.
2. Hata başladıktan sonra oluşan kayıtların listesini ayrı alın; doğrudan silmeyin.
3. Uygulama dosyalarını onaylı önceki sürüme döndürün.
4. Gerekliyse son sağlam yedeği ayrı veritabanında bir kez daha doğrulayın.
5. Onay sonrası canlı veritabanını sağlam yedekten geri yükleyin.
6. Oturum, müşteri, sipariş, stok ve rapor kontrollerini tekrarlayın.
7. Kullanıcılara durum ve yeniden kullanım zamanı bildirilmeden sistemi açmayın.

## Saklanacak kayıtlar

- Yayın ve geri dönüş kararı veren kişi
- Başlangıç/bitiş saatleri
- Kullanılan sürüm veya commit kimliği
- Yedek dosyasının adı, boyutu ve saklama yeri
- Çalıştırılan migration sonucu
- Sağlık kontrolü sonucu
- Bulunan sorunlar ve alınan kararlar

Parolalar, SMTP anahtarları ve veritabanı şifreleri bu belgeye veya test notlarına yazılmaz.
