export type Employee = { id: number; code?: string; name: string; max_discount_percent: number };
export type SessionUser = { id: number; email: string; groups: string[]; permissions: string[]; employee: Employee };
export type Session = { token: string; expires_at: string; user: SessionUser };
export type ApiErrorBody = { error: { code: string; message: string; fields?: Record<string, string> } };
export type Customer = { id: number; customer_code: string; company_name: string; city: string; district: string; status: string; contact_name?: string; contact_phone?: string; updated_at: string };
export type ProductVariant = { id: number; sku: string; size?: string; color?: string; list_price_override?: number };
export type Product = { id: number; product_code: string; name: string; list_price: number; tax_rate: number; image_path?: string; is_active?: number; variants: ProductVariant[] };
