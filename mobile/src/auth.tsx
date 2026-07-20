import { createContext, useContext, useEffect, useMemo, useState, type PropsWithChildren } from 'react';
import * as Device from 'expo-device';
import { api, getInstallationId, readSession, writeSession } from './api';
import { clearLocalData } from './database';
import type { Session } from './types';

type AuthContextValue = { session: Session | null; loading: boolean; login(email: string, password: string): Promise<void>; logout(): Promise<void> };
const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: PropsWithChildren) {
  const [session, setSession] = useState<Session | null>(null); const [loading, setLoading] = useState(true);
  useEffect(() => { readSession().then(setSession).finally(() => setLoading(false)); }, []);
  const value = useMemo<AuthContextValue>(() => ({
    session, loading,
    async login(email, password) {
      const result = await api<{ data: Session }>('/auth/login', { method: 'POST', body: JSON.stringify({ email, password, installation_id: await getInstallationId(), device_name: Device.modelName ?? 'Android', platform: 'android', app_version: '0.1.0' }) });
      await writeSession(result.data); setSession(result.data);
    },
    async logout() { try { await api('/auth/logout', { method: 'POST' }); } finally { await clearLocalData(); await writeSession(null); setSession(null); } },
  }), [session, loading]);
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue { const value=useContext(AuthContext); if(!value)throw new Error('AuthProvider eksik.'); return value; }
