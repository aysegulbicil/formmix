import * as SQLite from 'expo-sqlite';
import * as SecureStore from 'expo-secure-store';
import * as Crypto from 'expo-crypto';

const DB_KEY = 'formmix.database-key';
let database: Promise<SQLite.SQLiteDatabase> | null = null;

async function encryptionKey(): Promise<string> {
  const saved = await SecureStore.getItemAsync(DB_KEY);
  if (saved) return saved;
  const key = Array.from(Crypto.getRandomBytes(32), (byte) => byte.toString(16).padStart(2, '0')).join('');
  await SecureStore.setItemAsync(DB_KEY, key, { keychainAccessible: SecureStore.AFTER_FIRST_UNLOCK_THIS_DEVICE_ONLY });
  return key;
}

export async function db(): Promise<SQLite.SQLiteDatabase> {
  database ??= (async () => {
    const instance = await SQLite.openDatabaseAsync('formmix.db');
    await instance.execAsync(`PRAGMA key = '${await encryptionKey()}';`);
    await instance.execAsync(`
      PRAGMA journal_mode = WAL;
      CREATE TABLE IF NOT EXISTS drafts (id TEXT PRIMARY KEY NOT NULL, type TEXT NOT NULL, title TEXT NOT NULL, payload TEXT NOT NULL, updated_at TEXT NOT NULL);
      CREATE TABLE IF NOT EXISTS outbox (id TEXT PRIMARY KEY NOT NULL, operation TEXT NOT NULL, method TEXT NOT NULL, path TEXT NOT NULL, payload TEXT NOT NULL, state TEXT NOT NULL DEFAULT 'pending', error_code TEXT, error_message TEXT, created_at TEXT NOT NULL, updated_at TEXT NOT NULL);
      CREATE TABLE IF NOT EXISTS cache (cache_key TEXT PRIMARY KEY NOT NULL, payload TEXT NOT NULL, updated_at TEXT NOT NULL);
    `);
    return instance;
  })();
  return database;
}

export async function clearLocalData(): Promise<void> {
  const instance = await db();
  await instance.execAsync('DELETE FROM drafts; DELETE FROM outbox; DELETE FROM cache;');
  await instance.closeAsync();
  database = null;
  await SQLite.deleteDatabaseAsync('formmix.db');
  await SecureStore.deleteItemAsync(DB_KEY);
}
