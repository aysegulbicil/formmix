import type { ExpoConfig, ConfigContext } from 'expo/config';

export default ({ config }: ConfigContext): ExpoConfig => ({
  ...config,
  name: 'FORMMIX',
  slug: 'formmix-mobile',
  scheme: 'formmix',
  version: '0.1.0',
  orientation: 'portrait',
  userInterfaceStyle: 'light',
  icon: './assets/icon.png',
  plugins: [
    'expo-router',
    'expo-status-bar',
    'expo-asset',
    'expo-font',
    ['expo-splash-screen', { image: './assets/splash.png', imageWidth: 220, resizeMode: 'contain', backgroundColor: '#0B1F3A' }],
    'expo-secure-store',
    ['expo-sqlite', { useSQLCipher: true }],
    ['expo-notifications', { icon: './assets/notification-icon.png', color: '#F47A20' }],
    'expo-sharing',
    ['expo-build-properties', { android: { minSdkVersion: 24, compileSdkVersion: 36, targetSdkVersion: 36, usesCleartextTraffic: (process.env.APP_ENV ?? 'development') === 'development' } }],
  ],
  android: {
    package: 'com.formmix.mobile',
    versionCode: 1,
    adaptiveIcon: { foregroundImage: './assets/adaptive-icon.png', backgroundColor: '#0B1F3A' },
    allowBackup: false,
    ...(process.env.GOOGLE_SERVICES_JSON ? { googleServicesFile: process.env.GOOGLE_SERVICES_JSON } : {}),
  },
  extra: {
    apiBaseUrl: process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://10.0.2.2:8080/api/v1',
    appEnv: process.env.APP_ENV ?? 'development',
    easProjectId: process.env.EXPO_PUBLIC_EAS_PROJECT_ID ?? null,
  },
});
