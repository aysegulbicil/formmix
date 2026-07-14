# FORMMIX Docker Kullanımı

Uygulama varsayılan olarak `http://localhost:8082` adresinde çalışır. Bilgisayardaki 80 ve 81 portlarını kullanmaz.

## İlk çalıştırma

Proje klasöründe PowerShell açın:

```powershell
docker compose up -d --build
```

İlk kurulumda PHP/Apache ve MySQL imajları indirilir, uygulama hazırlanır ve veritabanı tabloları otomatik kurulur. Bu işlem sonraki açılışlardan daha uzun sürebilir.

Durumu kontrol edin:

```powershell
docker compose ps
```

Tarayıcı adresleri:

- Kurumsal site: `http://localhost:8082`
- Çalışan girişi: `http://localhost:8082/login`
- Yönetim paneli: `http://localhost:8082/panel`
- Yerel e-posta kutusu: `http://localhost:8025`

## İlk işletme sahibi hesabı

Gerçek e-posta adresinizi kullanarak aşağıdaki komutu çalıştırın:

```powershell
docker compose exec app php spark shield:user create -n yonetici -e yonetici@example.com -g owner
```

Komut şifreyi güvenli biçimde sorar. Şifreyi proje dosyalarına yazmayın.

## Günlük kullanım

Başlatmak için:

```powershell
docker compose up -d
```

Durdurmak için:

```powershell
docker compose down
```

Kayıtları görmek için:

```powershell
docker compose logs -f app
```

## Portu değiştirme

8082 de kullanılıyorsa örneğin 8083 ile başlatabilirsiniz:

```powershell
$env:FORMMIX_PORT = '8083'
docker compose up -d
```

Compose, uygulama adresini seçtiğiniz porta göre otomatik ayarlar; `.env.docker` dosyasını değiştirmeniz gerekmez.

Kod veya Docker dosyaları değiştiğinde imajı yenilemek için:

```powershell
docker compose up -d --build
```

## Veriler nerede tutulur?

- MySQL verileri `formmix_mysql_data` adlı Docker alanında tutulur.
- Oturum, kayıt ve yükleme dosyaları `formmix_writable` adlı Docker alanında tutulur.
- `docker compose down` verileri silmez.

Veri alanlarını silen komutlar günlük kullanımda çalıştırılmamalıdır.

## Veritabanı yedeği

Yedek almak için:

```powershell
.\scripts\docker-backup.ps1
```

Komut, tarihli SQL dosyasını `backups` klasörüne kaydeder. Yedeğin gerçekten geri yüklenebildiğini ayrı ve geçici bir veritabanında sınamak için:

```powershell
.\scripts\docker-restore-test.ps1 -BackupFile .\backups\YEDEK_DOSYASI.sql
```

Geri yükleme testi gerçek `formmix` veritabanına dokunmaz; yalnızca `formmix_restore_test` adlı geçici veritabanını kullanır ve test sonunda kaldırır.

## Yerel e-posta testi

Şifre bağlantıları ve diğer geliştirme e-postaları internete gönderilmez. Docker içindeki Mailpit servisi tarafından yakalanır ve aşağıdaki adreste gösterilir:

`http://localhost:8025`

## Sorun kontrolü

```powershell
docker compose ps
docker compose logs --tail=100 app
docker compose logs --tail=100 db
```

Uygulama başlamazsa önce MySQL servisinin `healthy`, ardından uygulama servisinin `healthy` olduğuna bakın.
