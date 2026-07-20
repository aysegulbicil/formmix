# FORMMIX web / Android özellik eşliği matrisi

Bu belge, web panelindeki gerçek rotalardan üretilen uygulama kontrol listesidir. `Tam` yalnızca aynı iş kuralını kullanan API ve native mobil karşılığı bulunduğunu; `Kısmi` yalnızca akışın bir bölümünün bulunduğunu belirtir. API yazma işlemleri ayrıca sunucu yetkisi, doğrulama, audit ve uygun yerlerde idempotency / `expected_updated_at` denetimi gerektirir.

## Genel durum

| Modül | Web kapsamı | Mobil/API durumu | Test durumu | Sonuç |
|---|---|---|---|---|
| Kimlik ve cihaz | Oturum, aktif personel, rol, sürüm | Login/logout/me/bootstrap/device/release/push mevcut | `mobile:verify`, TS | Tam |
| Ana sayfa | Rol bazlı operasyon özeti | Yetkiye göre metrik ve menü | TS/API | Tam |
| Personel | Liste, filtre, hesap/rol, CRUD, durum | Liste, ayrıntı, oluşturma, düzenleme ve durum native/API | PHP/TS | Tam |
| Müşteri | Liste, filtre, ayrıntı, CRUD, devir, görüşme | Liste/ayrıntı/oluşturma/düzenleme/görüşme mevcut; devir API ile tamamlandı | `customer:verify`, TS | Tam |
| Ürün ve fiyat | Liste, CRUD, fiyat grubu, özel/toplu fiyat, arşiv | Native ürün/kategori/form/fiyat grubu/özel-toplu fiyat yönetimi ve API | `product:verify`, TS | Tam (web kapsamı) |
| Teklif/sipariş | Liste, CRUD, fiyat, durumlar, dönüşüm | Native liste/ayrıntı/oluşturma/düzenleme/gönderme/finalize/dönüşüm/iptal | `order:verify`, TS | Tam |
| Görevler | Sipariş hazırlama/tasarım/baskı atamaları | Rol bazlı görev listesi ve hazırlama/ayırma, sevk, teslim geçişleri | PHP/TS | Tam |
| Stok/depo | Bakiye, hareket, sayım, depo, ayırma, sevk | Native bakiye, giriş/çıkış/iade/transfer/sayım/depo ve görev geçişleri | `inventory:verify`, TS | Tam |
| Tedarikçi/alış | Tedarikçi, alış, mal kabul | Native tedarikçi oluşturma/liste, alış oluşturma/ayrıntı ve kısmi-tam kabul | PHP/TS | Tam (web kapsamı) |
| Prim | Kural, hesap, dönem, ödeme, kendi/tümü | Native rol filtreli özet ve bütün yönetim işlemleri | `commission:verify`, TS | Tam |
| Rapor | Özet, dönem, satış/personel/müşteri/ürün/sipariş/stok/prim, CSV/XLSX | Aynı `ReportService`, native filtre/kart/liste ve telefonda paylaşım | `report:verify`, TS/bundle | Tam |
| Bildirim | Mobil kutu, okundu, yönlendirme, outbox | API ve native kutu; push/outbox mevcut | `mobile:verify`, TS | Tam |
| Çevrimdışı | Müşteri/ürün önbelleği, taslak/outbox | Şifreli Expo SQLite, açık kullanıcı senkronizasyonu, idempotency ve fiyat çatışması mevcut | TS | Tam (mevcut yazma kapsamı) |
| Uygulama yönetimi | Sürüm, cihaz, senkron, güvenli çıkış | Native Daha fazla ekranı | TS | Tam |

## Rota bazlı envanter

| Web rotası | Controller / metot | İzin | Mobil ekran | API endpoint'i | İşlem | Test | Durum |
|---|---|---|---|---|---|---|---|
| `/panel` | `Dashboard::index` | `panel.access` | Ana sayfa | `GET /api/v1/bootstrap` | özet | mobil verify | Tam |
| `/panel/kullanim-rehberi` | `UserGuide::index` | `panel.access` | Daha fazla / Rehber | yerel içerik | ayrıntı | TS | Tam |
| `/panel/personel` | `Employees::index` | `employees.view` | Personel | `GET /api/v1/employees/manage` | liste/arama/filtre | API/TS/emülatör | Tam |
| `/panel/personel/yeni` | `Employees::create/store` | `employees.manage` | Yeni personel | `POST /api/v1/employees/manage` | native hesap/rol/personel formu | API/TS | Tam |
| `/panel/personel/{id}/duzenle` | `Employees::edit/update` | `employees.manage` | Personel düzenleme | `GET, PUT /api/v1/employees/manage/{id}` | düzenleme/stale | API/TS | Tam |
| `/panel/personel/{id}/durum` | `Employees::toggleStatus` | `employees.manage` | Personel ayrıntı | `POST /api/v1/employees/manage/{id}/status` | aktif/pasif | API/TS | Tam |
| `/panel/musteriler` | `Customers::index` | own/all | Müşteriler | `GET /api/v1/customers` | liste/arama/filtre | customer verify | Tam |
| `/panel/musteriler/tekrar-kontrol` | `Customers::duplicateCheck` | `customers.create` | Yeni müşteri | `POST /api/v1/customers/duplicate-check` | mükerrer kontrol | customer verify | Tam |
| `/panel/musteriler/yeni` | `Customers::create/store` | `customers.create` | Yeni müşteri | `POST /api/v1/customers` | ekleme | customer verify | Tam |
| `/panel/musteriler/{id}` | `Customers::show` | own/all | Müşteri ayrıntı | `GET /api/v1/customers/{id}` | ayrıntı/geçmiş | customer verify | Tam |
| `/panel/musteriler/{id}/duzenle` | `Customers::edit/update` | `customers.create` | Müşteri formu | `PUT /api/v1/customers/{id}` | düzenleme/stale | customer verify | Tam |
| `/panel/musteriler/{id}/sorumlu` | `Customers::assign` | `customers.assign` | Müşteri ayrıntı | `POST /api/v1/customers/{id}/assignment` | gerekçeli devir | API | Tam |
| `/panel/musteriler/{id}/gorusme` | `Customers::addActivity` | own/all | Görüşme formu | `POST /api/v1/customers/{id}/activities` | görüşme | customer verify | Tam |
| `/panel/urunler` | `Products::index` | `products.view` | Ürünler | `GET /api/v1/products` | liste/arama/filtre | product verify | Tam |
| `/panel/urunler/yeni` | `Products::create/store` | `products.manage` | Yeni ürün | `POST /api/v1/products` | ürün/kategori/varyant ekleme | product verify/TS | Tam |
| `/panel/urunler/{id}/duzenle` | `Products::edit/update` | `products.manage` | Ürün düzenleme | `GET, PUT /api/v1/products/{id}` | düzenleme/stale | product verify/TS | Tam |
| `/panel/urunler/{id}/durum` | `Products::toggleStatus` | `products.manage` | Ürün ayrıntı | `POST /api/v1/products/{id}/status` | durum | API/TS | Tam |
| `/panel/urunler/{id}/arsivle` | `Products::archive` | `products.manage` | Ürün ayrıntı | `POST /api/v1/products/{id}/archive` | arşiv | API/TS | Tam |
| `/panel/urunler/toplu-fiyat` | `Products::bulkPriceUpdate` | `products.manage` | Ürünler | `POST /api/v1/products/bulk-price` | filtreye toplu fiyat | API/TS | Tam |
| `/panel/urunler/fiyat-gruplari` | `Products::priceGroups/storePriceGroup` | `products.manage` | Fiyat grupları | `GET, POST /api/v1/price-groups` | liste/ekleme | API/TS | Tam |
| `/panel/urunler/fiyat-gruplari/{id}/durum` | `Products::togglePriceGroup` | `products.manage` | Fiyat grupları | `POST /api/v1/price-groups/{id}/status` | durum | API/TS | Tam |
| `/panel/urunler/{id}/ozel-fiyat` | `Products::storeSpecialPrice` | `products.manage` | Ürün özel fiyatları | `POST /api/v1/products/{id}/special-prices` | özel fiyat | API/TS | Tam |
| `/panel/urunler/{id}/ozel-fiyat/{price}/durum` | `Products::toggleSpecialPrice` | `products.manage` | Ürün özel fiyatları | `POST /api/v1/products/{id}/special-prices/{price}/status` | durum | API/TS | Tam |
| `/panel/siparisler` | `SalesDocuments::index` | own/all | Satış | `GET /api/v1/sales-documents` | liste/filtre | order verify | Tam |
| `/panel/siparisler/yeni` | `SalesDocuments::create/store` | `orders.create` | Yeni satış | `POST /api/v1/sales-documents` | teklif/sipariş | order verify | Tam |
| `/panel/siparisler/fiyat` | `SalesDocuments::price` | `orders.create` | Yeni satış | `GET /api/v1/products/{id}/price` | sunucu fiyatı | order verify | Tam |
| `/panel/siparisler/{id}` | `SalesDocuments::show` | own/all/assigned | Satış ayrıntı | `GET /api/v1/sales-documents/{id}` | ayrıntı/geçmiş | order verify | Tam |
| `/panel/siparisler/{id}/duzenle` | `SalesDocuments::edit/update` | `orders.create` | Satış düzenleme | `PUT /api/v1/sales-documents/{id}` | düzenleme/stale/sunucu fiyatı | order verify/TS | Tam |
| `/panel/siparisler/{id}/gonder` | `SalesDocuments::submit` | `orders.create` | Satış ayrıntı | `POST /api/v1/sales-documents/{id}/submit` | taslağı gönderme | API/TS | Tam |
| `/panel/siparisler/{id}/kesinlestir` | `SalesDocuments::finalizeQuote` | `orders.create` | Satış ayrıntı | `POST /api/v1/sales-documents/{id}/finalize` | kesinleştirme | order verify | Tam |
| `/panel/siparisler/{id}/siparise-cevir` | `SalesDocuments::convert` | `orders.create` | Satış ayrıntı | `POST /api/v1/sales-documents/{id}/convert-to-order` | dönüşüm | order verify | Tam |
| `/panel/siparisler/{id}/surec` | `SalesDocuments::progress` | atanan/fulfill | Görevler | `POST /api/v1/tasks/{id}/status` | ayırma/hazırlama/sevk/teslim | inventory verify/TS | Tam |
| `/panel/siparisler/{id}/onayla` | `SalesDocuments::approve` | — | — | — | web controller bu eski akışı kaldırmış | kaynak inceleme | Uygulanamaz |
| `/panel/siparisler/{id}/reddet` | `SalesDocuments::reject` | — | — | — | web controller bu eski akışı kaldırmış | kaynak inceleme | Uygulanamaz |
| `/panel/siparisler/{id}/iptal` | `SalesDocuments::cancel` | sahip/yönetici | Satış ayrıntı | `POST /api/v1/sales-documents/{id}/cancel` | gerekçeli iptal/rezervasyon bırakma | API/TS | Tam |
| `/panel/stok` | `Inventory::index` | `stock.manage` | Stok ve depo | `GET /api/v1/inventory` | bakiye/hareket/depo | inventory verify | Tam |
| `/panel/stok/hareket` | `Inventory::storeMovement` | `stock.manage` | Stok ve depo | `POST /api/v1/inventory/movements` | giriş/çıkış/iade/transfer formu | inventory verify/TS | Tam |
| `/panel/stok/sayim` | `Inventory::storeCount` | `stock.count` | Stok ve depo | `POST /api/v1/inventory/counts` | sayım ve fark | inventory verify/TS | Tam |
| `/panel/stok/depo` | `Inventory::storeWarehouse` | `warehouses.manage` | Depolar | `POST /api/v1/warehouses` | ekleme | API | Tam |
| `/panel/stok/siparis/{id}/ayir` | `Inventory::reserveOrder` | `orders.fulfill` | Görev/sipariş | `POST /api/v1/orders/{id}/reserve` | ayırma | inventory verify | Tam |
| `/panel/stok/siparis/{id}/sevk` | `Inventory::shipOrder` | `orders.fulfill` | Görev/sipariş | `POST /api/v1/orders/{id}/ship` | sevk | inventory verify | Tam |
| `/panel/tedarikciler` | `Inventory::suppliers/storeSupplier` | purchases/suppliers | Tedarikçiler | `GET, POST /api/v1/suppliers` | liste/ekleme | API/TS | Tam |
| `/panel/tedarikciler/{id}/durum` | `Inventory::toggleSupplier` | `suppliers.manage` | Tedarikçi ayrıntı | `POST /api/v1/suppliers/{id}/status` | durum | API | Tam |
| `/panel/alislar` | `Inventory::purchases` | purchases | Alışlar | `GET /api/v1/purchases` | liste/filtre | API/TS | Tam |
| `/panel/alislar/yeni` | `Inventory::createPurchase/storePurchase` | `purchases.create` | Alış formu | `POST /api/v1/purchases` | ekleme/satırlar | API | Tam |
| `/panel/alislar/{id}` | `Inventory::showPurchase` | purchases | Alış ayrıntı | `GET /api/v1/purchases/{id}` | ayrıntı | API/TS | Tam |
| `/panel/alislar/{id}/mal-kabul` | `Inventory::receivePurchase` | `purchases.receive` | Alış ayrıntı | `POST /api/v1/purchases/{id}/receive` | kısmi/tam kabul | inventory verify | Tam |
| `/panel/primler` | `Commissions::index` | own/all/manage | Primler | `GET /api/v1/commissions` | kendi/tümü | commission verify | Tam |
| `/panel/primler/kural` | `Commissions::storeRule` | `commissions.manage` | Prim yönetimi | `POST /api/v1/commission-rules` | kural | API | Tam |
| `/panel/primler/hesapla` | `Commissions::calculate` | `commissions.manage` | Prim yönetimi | `POST /api/v1/commissions/calculate` | hesaplama | commission verify | Tam |
| `/panel/primler/donem` | `Commissions::storePeriod` | `commissions.manage` | Prim yönetimi | `POST /api/v1/commission-periods` | dönem kapatma | API | Tam |
| `/panel/primler/donem/{id}/ode` | `Commissions::payPeriod` | `commissions.manage` | Prim yönetimi | `POST /api/v1/commission-periods/{id}/pay` | ödendi | API | Tam |
| `/panel/raporlar` | `Reports::index` | `reports.view` | Raporlar | `GET /api/v1/reports` | tüm rapor bölümleri/filtre | report verify | Tam |
| `/panel/raporlar/disari-aktar/{section}/{format}` | `Reports::export` | `reports.view` | Raporlar | `GET /api/v1/reports/export/{section}/{format}` | kimlik doğrulamalı indirme ve paylaşma | report verify/TS/bundle | Tam |

## Kaynakta bulunmayan istekler

Web panelinde bağımsız finans/tahsilat sayfası, kategori CRUD sayfası, depo transfer sayfası, tedarikçi iadesi sayfası veya ayrı bildirim yönetim sayfası yoktur. Bunlar bu matrisin “web eşliği” kapsamına eklenmemiştir; API servisleri genişletilirken mevcut güvenli davranış korunacaktır. Production yayınlama ve imzalı APK, gerçek imzalama anahtarı ile açık yayın onayı gerektirir.
