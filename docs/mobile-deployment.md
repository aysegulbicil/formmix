# FORMMIX Android yayın rehberi

Bu belge ilk şirket içi Android APK'sının hazırlanması ve
`https://www.formmix.com.tr/mobil` adresinde yayınlanması içindir.

## Bir defalık hazırlık

1. JDK 17, Android Studio, Android SDK 36 ve ADB kurulur.
2. `mobile/.env.production.example`, `mobile/.env.production` adıyla kopyalanır.
3. Expo/EAS proje kimliği `EXPO_PUBLIC_EAS_PROJECT_ID` alanına yazılır.
4. Firebase Android uygulaması `com.formmix.mobile` kimliğiyle oluşturulur.
5. Firebase'den alınan `google-services.json` dosyasının yolu
   `GOOGLE_SERVICES_JSON` değişkenine yazılır. Dosya Git'e eklenmez.
6. Kalıcı Android keystore oluşturulur. Keystore ve parolaları Git'e eklenmez;
   iki ayrı güvenli konumda yedeklenir.

## Sunucu hazırlığı

1. Production veritabanı ve uygulama dosyalarının yedeği alınır.
2. Uygulama kodu bakım penceresinde sunucuya aktarılır.
3. Production `.env` dosyasında HTTPS taban adresi ve doğru veritabanı seçilir.
4. Migration çalıştırılır:

   ```powershell
   php spark migrate
   ```

5. Mobil temel doğrulaması çalıştırılır:

   ```powershell
   php spark formmix:verify-mobile
   ```

6. Bildirim outbox komutu zamanlanmış görevde dakikada bir çalıştırılır:

   ```powershell
   php spark formmix:dispatch-mobile-notifications
   ```

7. Web sunucusunda `.apk` dosyaları için
   `application/vnd.android.package-archive` MIME türü tanımlanır.

## APK üretimi

`mobile` klasöründe bağımlılıklar kurulduktan sonra production native projesi
üretilir ve imzalı release APK derlenir:

```powershell
npm ci
npx expo prebuild --platform android --clean
Set-Location android
.\gradlew.bat assembleRelease
```

APK genellikle
`mobile/android/app/build/outputs/apk/release/app-release.apk` konumundadır.
Her yayında uygulama sürümü ve Android `versionCode` artırılmalıdır.

## APK'yı `/mobil` sayfasında yayınlama

Ana proje klasöründe aşağıdaki komut çalıştırılır:

```powershell
php spark formmix:publish-apk "mobile/android/app/build/outputs/apk/release/app-release.apk" "1.0.0" 1 1 "İlk saha sürümü"
```

Komut APK'yı `public/downloads` klasörüne kopyalar, SHA-256 değerini hesaplar
ve aktif sürümü veritabanına kaydeder. Ardından aşağıdakiler doğrulanır:

- `https://www.formmix.com.tr/mobil` sayfası açılıyor.
- İndir düğmesi HTTPS üzerinden APK'yı indiriyor.
- Sayfadaki SHA-256 ile indirilen dosyanın özeti eşleşiyor.
- APK Android 7/API 24 ve güncel bir Android cihazda kuruluyor.
- Uygulama `https://www.formmix.com.tr/api/v1` adresine bağlanıyor.
- Giriş, çevrimdışı taslak, senkronizasyon, bildirim ve güncelleme kontrolü çalışıyor.

## Geri dönüş

Yeni APK sorunluysa son sağlam APK aynı yayın komutuyla daha yüksek bir
`versionCode` kullanılarak yeniden yayınlanır. Veritabanı geri dönüşü yalnız
migration değişikliği veri kaybına neden olmayacaksa uygulanır; aksi durumda
önce alınan production yedeği kullanılır. Keystore değiştirilmez; farklı
keystore ile imzalanmış APK mevcut uygulamanın üzerine kurulamaz.
