import * as Crypto from 'expo-crypto';
import { api, ApiError } from './api';
import { db } from './database';

export type OutboxItem = { id: string; operation: string; method: string; path: string; payload: string; state: string; error_code?: string; error_message?: string; created_at: string; updated_at: string };

export async function queue(operation: string, method: string, path: string, payload: unknown): Promise<string> {
  const id = Crypto.randomUUID(); const now = new Date().toISOString(); const database = await db();
  await database.runAsync('INSERT INTO outbox (id,operation,method,path,payload,state,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', id, operation, method, path, JSON.stringify(payload), 'pending', now, now);
  return id;
}

export async function outboxItems(): Promise<OutboxItem[]> { return (await db()).getAllAsync<OutboxItem>('SELECT * FROM outbox ORDER BY created_at'); }

export async function acceptServerValues(id:string):Promise<void>{const database=await db();const item=await database.getFirstAsync<OutboxItem>('SELECT * FROM outbox WHERE id=?',id);if(!item)return;const payload=JSON.parse(item.payload)as {items?:Array<Record<string,unknown>>};for(const line of payload.items??[])delete line.expected_unit_price;await database.runAsync('UPDATE outbox SET payload=?,state=?,error_code=NULL,error_message=NULL,updated_at=? WHERE id=?',JSON.stringify(payload),'pending',new Date().toISOString(),id);}
export async function discardOutbox(id:string):Promise<void>{await(await db()).runAsync('DELETE FROM outbox WHERE id=?',id);}

export async function synchronize(): Promise<{ sent: number; blocked: number }> {
  const database = await db(); let sent = 0; let blocked = 0;
  for (const item of await outboxItems()) {
    try {
      await api(item.path, { method: item.method, body: item.payload, idempotencyKey: item.id });
      await database.runAsync('DELETE FROM outbox WHERE id=?', item.id); sent++;
    } catch (error) {
      const apiError = error instanceof ApiError ? error : new ApiError('NETWORK_ERROR', 'Baglanti kurulamadi.', 0);
      const resolvable = ['PRICE_CHANGED', 'DUPLICATE_CUSTOMER', 'STALE_RESOURCE'].includes(apiError.code);
      await database.runAsync('UPDATE outbox SET state=?,error_code=?,error_message=?,updated_at=? WHERE id=?', resolvable ? 'blocked' : 'pending', apiError.code, apiError.message, new Date().toISOString(), item.id);
      if (resolvable) blocked++; else break;
    }
  }
  return { sent, blocked };
}
