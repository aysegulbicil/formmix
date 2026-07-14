# Teknik Kurulum Notları

Bu belge geliştirici ve sistem yöneticisi içindir. Gerçek şifreler bu dosyaya veya proje koduna yazılmaz.

## Kullanılan temel

- PHP 8.2
- CodeIgniter 4.7
- CodeIgniter Shield 1.3 kullanıcı giriş sistemi
- Yerel geliştirmede SQLite
- Canlı ortamda MySQL ve InnoDB

## Docker ile yerel çalışma

Docker kurulumu, proje kökündeki `DOCKER.md` dosyasında anlatılmıştır. Uygulama varsayılan olarak `http://localhost:8082` adresinde çalışır. Docker içindeki MySQL dışarıya port açmaz; yalnızca uygulama servisi tarafından erişilir.

```powershell
docker compose up -d --build
```

## Ortamlar

### Yerel geliştirme

Yerel `.env` dosyasında `CI_ENVIRONMENT = development` kullanılır. Veritabanı `writable/formmix.sqlite` dosyasıdır ve Git'e eklenmez.

XAMPP komut satırında SQLite3 uzantısı varsayılan olarak kapalıysa komutlar aşağıdaki biçimde çalıştırılır:

```powershell
C:\xampp\php\php.exe -d extension=sqlite3 spark migrate --all
```

### Canlı ortam

Canlı sunucuda `.env.example` dosyası örnek alınır. Gerçek alan adı, MySQL bilgileri ve e-posta bilgileri yalnızca sunucudaki `.env` dosyasına yazılır.

Canlı ortamda:

- `CI_ENVIRONMENT = production` olmalıdır.
- HTTPS zorunlu olmalıdır.
- MySQL tabloları InnoDB kullanmalıdır.
- Veritabanı kullanıcısına yalnızca gerekli yetkiler verilmelidir.
- `.env` ve yedek dosyaları internetten erişilebilir olmamalıdır.

## Tablo kurulumu

Yerel ortam:

```powershell
C:\xampp\php\php.exe -d extension=sqlite3 spark migrate --all
```

Canlı MySQL ortamı:

```powershell
C:\xampp\php\php.exe spark migrate --all
```

Kurulum durumunu görmek için:

```powershell
C:\xampp\php\php.exe -d extension=sqlite3 spark migrate:status
```

## İlk işletme sahibi hesabı

Şifre komut satırına açık biçimde yazılmaz. Aşağıdaki komut çalıştırılır ve şifre istendiğinde girilir:

```powershell
C:\xampp\php\php.exe -d extension=sqlite3 spark shield:user create -n yonetici -e yonetici@example.com -g owner
```

Gerçek e-posta adresi ve kullanıcı adı kullanılmalıdır. İlk girişten sonra şifre yenilenmelidir.

## Kullanıcı görevleri

- `owner`: İşletme sahibi
- `sales_manager`: Satış yöneticisi
- `field_sales`: Saha personeli
- `accounting`: Muhasebe
- `warehouse`: Depo

Kullanıcı görevi değiştirmek için resmî kullanıcı yönetim komutları kullanılır:

```powershell
C:\xampp\php\php.exe -d extension=sqlite3 spark shield:user addgroup -e kullanici@example.com -g field_sales
C:\xampp\php\php.exe -d extension=sqlite3 spark shield:user removegroup -e kullanici@example.com -g field_sales
```

## Güvenlik kararları

- Dışarıdan kullanıcı kaydı kapalıdır.
- Şifre en az 12 karakterdir.
- Panel hem giriş hem görev yetkisi ister.
- Form gönderimleri güvenlik koduyla korunur.
- Giriş sayfaları IP başına dakikada 10 istekle sınırlandırılır.
- Çıkış yalnızca güvenlik kodu taşıyan POST isteğiyle yapılır.
- Önemli değişiklikler `audit_logs` tablosuna yazılacaktır.

## Henüz tamamlanmayan kurulum işleri

- Canlı MySQL bağlantısı ve migration denemesi
- Deneme ortamı
- Günlük otomatik yedek
- Gerçek e-posta ile şifre yenileme denemesi
- İlk işletme sahibi hesabının gerçek bilgilerle oluşturulması
