const fs = require('node:fs');
const path = require('node:path');

const pluginRoot = path.dirname(require.resolve('@react-native/gradle-plugin/package.json'));
const settingsPath = path.join(pluginRoot, 'settings.gradle.kts');
const current = fs.readFileSync(settingsPath, 'utf8');
const oldDeclaration = 'org.gradle.toolchains.foojay-resolver-convention").version("0.5.0")';
const fixedDeclaration = 'org.gradle.toolchains.foojay-resolver-convention").version("1.0.0")';

if (current.includes(fixedDeclaration)) {
  process.stdout.write('React Native Gradle toolchain resolver is already compatible.\n');
} else if (!current.includes(oldDeclaration)) {
  throw new Error('Unsupported React Native Gradle plugin layout; toolchain resolver was not patched.');
} else {
  fs.writeFileSync(settingsPath, current.replace(oldDeclaration, fixedDeclaration));
  process.stdout.write('Patched React Native Gradle toolchain resolver for Gradle 9.\n');
}

const devLauncherRoot = path.dirname(require.resolve('expo-dev-launcher/package.json'));
const controllerPath = path.join(
  devLauncherRoot,
  'android',
  'src',
  'debug',
  'java',
  'expo',
  'modules',
  'devlauncher',
  'DevLauncherController.kt',
);
const controller = fs.readFileSync(controllerPath, 'utf8');
const unsafeCategoryCopy = 'intent.categories?.let {\n            categories.addAll(it)\n          }';
const safeCategoryCopy = 'intent.categories?.forEach(::addCategory)';

if (controller.includes(safeCategoryCopy)) {
  process.stdout.write('Expo development launcher category handling is already compatible.\n');
} else if (!controller.includes(unsafeCategoryCopy)) {
  throw new Error('Unsupported Expo development launcher layout; intent categories were not patched.');
} else {
  fs.writeFileSync(controllerPath, controller.replace(unsafeCategoryCopy, safeCategoryCopy));
  process.stdout.write('Patched Expo development launcher intent category handling.\n');
}
