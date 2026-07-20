# FORMMIX Mobile

Android-first Expo SDK 56 / React Native 0.85 uygulamasi. Uygulama mevcut CodeIgniter sistemine `/api/v1` uzerinden baglanir.

## Gereksinimler

- Node.js 22.13 veya yenisi
- JDK 17
- Android Studio, Android SDK 36 ve ADB
- Android 7/API 24 veya daha yeni cihaz/emulator

## Calistirma

1. `.env.example` dosyasini `.env` olarak kopyalayip API adresini ayarlayin.
2. `npm install` calistirin.
3. `npx expo prebuild --platform android` ile yerel Android projesini olusturun.
4. `npm run android` ile development build'i cihazda baslatin.

Emulatorden bilgisayardaki API icin `10.0.2.2`; fiziksel telefonda bilgisayarin yerel ag IP adresi kullanilir. Release profillerinde API mutlaka HTTPS olmalidir.

## APK

Kalici keystore Git disinda iki guvenli konumda saklanir. Yerel Gradle release build'i icin `android/gradle.properties` ve `android/app/build.gradle` imzalama bilgileri kurumun gizli ortam degiskenlerinden beslenmelidir. Her APK'da `version` ve Android `versionCode` artirilir.
