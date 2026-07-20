import Constants from 'expo-constants';
import * as SecureStore from 'expo-secure-store';
import * as Crypto from 'expo-crypto';
import type { ApiErrorBody, Session } from './types';

const SESSION_KEY = 'formmix.session';
const INSTALLATION_KEY = 'formmix.installation-id';
export const API_BASE_URL = String(Constants.expoConfig?.extra?.apiBaseUrl ?? '').replace(/\/$/, '');

export class ApiError extends Error {
  constructor(public code: string, message: string, public status: number, public fields: Record<string, string> = {}) { super(message); }
}

export async function getInstallationId(): Promise<string> {
  const existing = await SecureStore.getItemAsync(INSTALLATION_KEY);
  if (existing) return existing;
  const id = Crypto.randomUUID();
  await SecureStore.setItemAsync(INSTALLATION_KEY, id);
  return id;
}

export async function readSession(): Promise<Session | null> {
  const raw = await SecureStore.getItemAsync(SESSION_KEY);
  if (!raw) return null;
  try { return JSON.parse(raw) as Session; } catch { return null; }
}

export async function writeSession(session: Session | null): Promise<void> {
  if (session) await SecureStore.setItemAsync(SESSION_KEY, JSON.stringify(session), { keychainAccessible: SecureStore.AFTER_FIRST_UNLOCK_THIS_DEVICE_ONLY });
  else await SecureStore.deleteItemAsync(SESSION_KEY);
}

export async function api<T>(path: string, init: RequestInit & { idempotencyKey?: string } = {}): Promise<T> {
  const session = await readSession();
  const installationId = await getInstallationId();
  const headers = new Headers(init.headers);
  headers.set('Accept', 'application/json');
  headers.set('Content-Type', 'application/json');
  headers.set('X-Device-ID', installationId);
  if (session?.token) headers.set('Authorization', `Bearer ${session.token}`);
  if (init.idempotencyKey) headers.set('Idempotency-Key', init.idempotencyKey);
  const response = await fetch(`${API_BASE_URL}${path}`, { ...init, headers });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = body as ApiErrorBody;
    if (response.status === 401) await writeSession(null);
    throw new ApiError(error.error?.code ?? 'HTTP_ERROR', error.error?.message ?? 'Sunucu istegi tamamlanamadi.', response.status, error.error?.fields);
  }
  return body as T;
}
