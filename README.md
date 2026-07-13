# FORMMIX — Kurumsal Web Sitesi (CodeIgniter 4)

Kurumsal baskılı iş kıyafetleri markası **FORMMIX** için CodeIgniter 4 + PHP 8 ile
geliştirilmiş, veritabanı gerektirmeyen, mobil uyumlu kurumsal tanıtım sitesi.

> **Marka mesajı:** “Ekibiniz markanızı temsil eder.”

---

## 1. Gereksinimler

- **PHP 8.1+** (XAMPP ile birlikte gelir)
- **Composer** ([getcomposer.org](https://getcomposer.org/download/))
- `intl`, `mbstring`, `json` PHP eklentileri (XAMPP'te genelde açıktır)

> Not: CodeIgniter 4 çekirdeği (`system/`) bu pakete dahil **değildir**; aşağıdaki
> tek komutla Composer indirir. Bu, çerçevenin standart ve doğru kurulum yöntemidir.

---

## 2. Kurulum (XAMPP)

Proje zaten `C:\xampp\htdocs\frommix` içinde. Bir terminal (CMD/PowerShell) açın:

```bash
cd C:\xampp\htdocs\frommix
composer install
```

Bu komut `vendor/` klasörünü ve CodeIgniter 4 çekirdeğini indirir.

`.env` dosyası hazır gelir. İçindeki adres alt-klasör kurulumuna göre ayarlıdır:

```
app.baseURL = 'http://localhost/frommix/public/'
```

Apache ve (gerekirse) MySQL'i XAMPP panelinden başlatın, ardından tarayıcıda açın:

**http://localhost/frommix/public/**

> `composer install` “lock file” uyarısı verirse `composer update` çalıştırın.

---

## 3. Temiz URL (önerilen): Sanal Host

`/public/` kısmını adresten kaldırmak ve en temiz URL'yi almak için bir sanal host
tanımlayın.

**a)** `C:\xampp\apache\conf\extra\httpd-vhosts.conf` dosyasına ekleyin:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/frommix/public"
    ServerName formmix.test
    <Directory "C:/xampp/htdocs/frommix/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**b)** `C:\Windows\System32\drivers\etc\hosts` dosyasına ekleyin:

```
127.0.0.1   formmix.test
```

**c)** `.env` içindeki adresi güncelleyin ve Apache'yi yeniden başlatın:

```
app.baseURL = 'http://formmix.test/'
```

Artık site **http://formmix.test/** adresinde, temiz URL'lerle açılır.

---

## 4. ⚠️ İletişim Bilgilerini Güncelleyin (önemli)

Tüm site (header, footer, butonlar, iletişim sayfası) iletişim bilgilerini **tek bir
dosyadan** alır:

**`app/Config/Site.php`**

Buradaki şu alanları gerçek bilgilerinizle değiştirin:

| Alan | Açıklama | Örnek |
|------|----------|-------|
| `$whatsapp` | WhatsApp numarası (başında **+ yok**, ülke koduyla) | `905320000000` |
| `$phoneDisplay` | Ekranda görünen telefon | `+90 532 000 00 00` |
| `$phoneDial` | Aranabilir telefon | `+905320000000` |
| `$email` | E-posta | `info@formmix.com` |

`$instagram` zaten ayarlı: https://www.instagram.com/formmix_/

> Şu an numaralar **placeholder**'dır (örn. `905000000000`). Sadece bu dosyayı
> güncellemeniz tüm WhatsApp ve telefon linklerini düzeltir.

---

## 5. İçerik Yönetimi (veritabanısız)

İçerik, `app/Data/` altındaki PHP dizilerinden yönetilir:

| Dosya | İçerik |
|-------|--------|
| `products.php` | Ürünler (polo, tişört, sweatshirt, yelek, pantolon) |
| `sectors.php` | Sektörel çözümler |
| `campaigns.php` | Kampanya / fiyat paketleri |
| `process.php` | Sipariş süreci adımları |
| `values.php` | Güven/kurumsallık vurgu kartları |

Yeni ürün/sektör eklemek = ilgili diziye bir satır eklemek. Görseller
`public/assets/images/` altındadır.

---

## 6. Katalog PDF'i

Katalog sayfası, PDF dosyasını otomatik algılar. PDF'iniz hazır olduğunda şu konuma,
şu adla koyun:

```
public/assets/catalog/formmix-katalog.pdf
```

Dosya konunca “Kataloğu Görüntüle / İndir” butonları otomatik aktif olur. Dosya
yoksa “Katalog Hazırlanıyor” durumu ve WhatsApp'tan isteme butonu gösterilir.

---

## 7. Sayfalar ve Rotalar

| Sayfa | URL |
|-------|-----|
| Anasayfa | `/` |
| Hakkımızda | `/hakkimizda` |
| Ürünler | `/urunler` |
| Katalog | `/katalog` |
| İletişim | `/iletisim` |

---

## 8. Klasör Yapısı

```
frommix/
├─ app/
│  ├─ Config/        Ayarlar + Site.php (marka/iletişim)
│  ├─ Controllers/   Home, About, Products, Catalog, Contact
│  ├─ Data/          İçerik dizileri (DB yerine)
│  ├─ Helpers/       site_helper.php (whatsapp_link, icon, nav_active...)
│  └─ Views/         layouts / partials / components / pages / errors
├─ public/
│  ├─ index.php, .htaccess
│  └─ assets/        css / js / images / catalog
├─ writable/         cache / logs / session
├─ composer.json, .env, spark
```

---

## 9. Notlar

- **Ortam:** `.env` içinde `CI_ENVIRONMENT = production` (canlı için doğru). Hata
  ayıklamak isterseniz geçici olarak `development` yapın; hatalar ekranda görünür.
- **İletişim formu:** Frontend hazır, sunucu tarafı doğrulama çalışıyor. Mail/CRM
  entegrasyonu için `app/Controllers/Contact.php` içindeki işaretli alanı doldurun
  (`Config/Email.php` yapılandırması ile).
- **CSRF:** Form güvenliği için `app/Config/Filters.php` içindeki `csrf` filtresini
  `globals → before` altında etkinleştirebilirsiniz (mail entegrasyonuyla birlikte
  önerilir).
- **Logo/görseller:** `public/assets/images/` altındaki SVG'ler markaya uygun
  placeholder'lardır; gerçek logo ve ürün fotoğraflarıyla değiştirebilirsiniz.

---

© FORMMIX
