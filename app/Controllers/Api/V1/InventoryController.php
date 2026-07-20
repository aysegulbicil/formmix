<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\AuditLogger;
use App\Models\PurchaseOrderItemModel;
use App\Models\PurchaseOrderModel;
use App\Models\StockCountItemModel;
use App\Models\StockCountModel;
use App\Models\SupplierModel;
use App\Models\WarehouseModel;
use App\Services\StockService;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

final class InventoryController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard('stock.manage')) return $blocked;
        $warehouses = (new WarehouseModel())->where('is_active', 1)->orderBy('name')->findAll();
        $warehouseId = max(0, (int) $this->request->getGet('warehouse_id')) ?: (int) ($warehouses[0]['id'] ?? 0);
        $db = db_connect();
        $balances = $db->table('product_variants pv')->select('pv.id AS variant_id,pv.sku,pv.size,pv.color,p.name AS product_name,COALESCE(sb.on_hand_quantity,0) AS on_hand_quantity,COALESCE(sb.reserved_quantity,0) AS reserved_quantity,COALESCE(pv.critical_stock_level,p.critical_stock_level,0) AS threshold', false)->join('products p', 'p.id=pv.product_id')->join('stock_balances sb', 'sb.product_variant_id=pv.id AND sb.warehouse_id=' . $warehouseId, 'left')->where('pv.is_active', 1)->where('p.track_stock', 1)->where('p.is_active', 1)->orderBy('p.name')->orderBy('pv.sku')->get()->getResultArray();
        foreach ($balances as &$row) {
            foreach (['on_hand_quantity', 'reserved_quantity', 'threshold'] as $key) $row[$key] = (float) $row[$key];
            $row['available_quantity'] = $row['on_hand_quantity'] - $row['reserved_quantity'];
            $row['is_critical'] = $row['threshold'] > 0 && $row['available_quantity'] <= $row['threshold'];
        }
        $movements = $db->table('stock_movements sm')->select('sm.id,sm.movement_number,sm.movement_type,sm.quantity,sm.balance_after,sm.reason,sm.created_at,w.name AS warehouse_name,pv.sku,p.name AS product_name')->join('warehouses w', 'w.id=sm.warehouse_id')->join('product_variants pv', 'pv.id=sm.product_variant_id')->join('products p', 'p.id=pv.product_id')->orderBy('sm.created_at', 'DESC')->limit(100)->get()->getResultArray();
        return $this->ok(['warehouses' => $warehouses, 'warehouse_id' => $warehouseId, 'balances' => $balances, 'movements' => $movements]);
    }

    public function movement(): ResponseInterface
    {
        if ($blocked = $this->guard('stock.manage')) return $blocked;
        if ($replay = $this->replay('stock.movement')) return $replay;
        $in = $this->input(); $type = (string) ($in['movement_type'] ?? '');
        $quantity = $this->decimal($in['quantity'] ?? 0); $warehouse = (int) ($in['warehouse_id'] ?? 0); $variant = (int) ($in['product_variant_id'] ?? 0); $reason = trim((string) ($in['reason'] ?? ''));
        try {
            $service = new StockService((int) auth()->id());
            if (! in_array($type, ['transfer', 'manual_in', 'manual_out', 'customer_return', 'supplier_return'], true)) throw new RuntimeException('Geçersiz stok hareketi.');
            $result = $type === 'transfer'
                ? $service->transfer($warehouse, (int) ($in['target_warehouse_id'] ?? 0), $variant, $quantity, $reason)
                : $service->move($warehouse, $variant, $quantity * (in_array($type, ['manual_out', 'supplier_return'], true) ? -1 : 1), $type, $reason);
            $body = ['data' => ['id' => $result]]; $this->remember('stock.movement', 'product_variant', $variant, $body);
            (new AuditLogger())->record('stock.movement_created', 'product_variant', $variant, null, ['type' => $type, 'quantity' => $quantity, 'warehouse_id' => $warehouse, 'reason' => $reason]);
            return $this->ok(['id' => $result], [], 201);
        } catch (Throwable $e) { return $this->error('STOCK_OPERATION_FAILED', $e->getMessage(), 422); }
    }

    public function count(): ResponseInterface
    {
        if ($blocked = $this->guard('stock.count')) return $blocked;
        if ($replay = $this->replay('stock.count')) return $replay;
        $in = $this->input(); $warehouse = (int) ($in['warehouse_id'] ?? 0); $variant = (int) ($in['product_variant_id'] ?? 0); $counted = $this->decimal($in['counted_quantity'] ?? -1); $reason = trim((string) ($in['reason'] ?? ''));
        if ($counted < 0 || $reason === '') return $this->error('VALIDATION_FAILED', 'Sayım miktarı eksi olamaz ve gerekçe zorunludur.', 422, ['counted_quantity' => 'Geçersiz miktar.', 'reason' => 'Gerekçe zorunludur.']);
        $service = new StockService((int) auth()->id()); $balance = $service->balance($warehouse, $variant);
        if ($counted < (float) $balance['reserved_quantity']) return $this->error('RESERVED_STOCK_CONFLICT', 'Sayılan miktar ayrılmış stoktan az olamaz.', 409);
        $difference = $counted - (float) $balance['on_hand_quantity']; $db = db_connect(); $db->transBegin();
        try {
            $model = new StockCountModel(); $model->insert(['count_number' => $this->number('SAY'), 'warehouse_id' => $warehouse, 'status' => 'completed', 'reason' => $reason, 'counted_at' => date('Y-m-d H:i:s'), 'created_by_user_id' => auth()->id()]); $id = (int) $model->getInsertID();
            $movement = abs($difference) >= .0005 ? $service->move($warehouse, $variant, $difference, 'adjustment', 'Sayım farkı: ' . $reason, 'stock_count', $id) : null;
            (new StockCountItemModel())->insert(['stock_count_id' => $id, 'product_variant_id' => $variant, 'system_quantity' => $balance['on_hand_quantity'], 'counted_quantity' => $counted, 'difference_quantity' => $difference, 'stock_movement_id' => $movement, 'created_at' => date('Y-m-d H:i:s')]);
            if (! $db->transStatus()) throw new RuntimeException('Sayım kaydedilemedi.'); $db->transCommit();
            $body = ['data' => ['id' => $id, 'difference' => $difference]]; $this->remember('stock.count', 'stock_count', $id, $body); (new AuditLogger())->record('stock.count_completed', 'stock_count', $id, null, $body['data']);
            return $this->ok($body['data'], [], 201);
        } catch (Throwable $e) { $db->transRollback(); return $this->error('STOCK_COUNT_FAILED', $e->getMessage(), 422); }
    }

    public function createWarehouse(): ResponseInterface
    {
        if ($blocked = $this->guard('warehouses.manage')) return $blocked;
        $in = $this->input(); $data = ['code' => mb_strtoupper(trim((string) ($in['code'] ?? ''))), 'name' => trim((string) ($in['name'] ?? '')), 'address' => trim((string) ($in['address'] ?? '')) ?: null, 'is_active' => 1]; $model = new WarehouseModel();
        if (! $model->insert($data)) return $this->error('VALIDATION_FAILED', 'Depo kaydedilemedi.', 422, $model->errors());
        $id = (int) $model->getInsertID(); (new AuditLogger())->record('warehouse.created', 'warehouse', $id, null, $data); return $this->ok($model->find($id), [], 201);
    }

    public function reserve(int $id): ResponseInterface { if ($blocked = $this->guard('orders.fulfill')) return $blocked; try { return $this->ok((new StockService((int) auth()->id()))->reserveOrder($id, (int) ($this->input()['warehouse_id'] ?? 0))); } catch (Throwable $e) { return $this->error('RESERVATION_FAILED', $e->getMessage(), 422); } }
    public function ship(int $id): ResponseInterface { if ($blocked = $this->guard('orders.fulfill')) return $blocked; try { return $this->ok((new StockService((int) auth()->id()))->shipReserved($id, trim((string) ($this->input()['reason'] ?? '')))); } catch (Throwable $e) { return $this->error('SHIPMENT_FAILED', $e->getMessage(), 422); } }

    public function suppliers(): ResponseInterface
    {
        if ($blocked = $this->purchaseGuard()) return $blocked; [$page, $perPage] = $this->pagination(); $model = new SupplierModel(); $q = trim((string) $this->request->getGet('q')); if ($q !== '') $model->groupStart()->like('company_name', $q)->orLike('supplier_code', $q)->orLike('phone', $q)->groupEnd(); $total = $model->countAllResults(false); return $this->ok($model->orderBy('company_name')->findAll($perPage, ($page - 1) * $perPage), ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function createSupplier(): ResponseInterface
    {
        if ($blocked = $this->guard('suppliers.manage')) return $blocked; if ($replay = $this->replay('supplier.create')) return $replay; $in = $this->input(); $data = ['supplier_code' => mb_strtoupper(trim((string) ($in['supplier_code'] ?? ''))), 'company_name' => trim((string) ($in['company_name'] ?? '')), 'contact_name' => trim((string) ($in['contact_name'] ?? '')) ?: null, 'phone' => trim((string) ($in['phone'] ?? '')) ?: null, 'email' => trim((string) ($in['email'] ?? '')) ?: null, 'tax_number' => trim((string) ($in['tax_number'] ?? '')) ?: null, 'address' => trim((string) ($in['address'] ?? '')) ?: null, 'notes' => trim((string) ($in['notes'] ?? '')) ?: null, 'is_active' => 1, 'created_by_user_id' => auth()->id()]; $model = new SupplierModel(); if (! $model->insert($data)) return $this->error('VALIDATION_FAILED', 'Tedarikçi kaydedilemedi.', 422, $model->errors()); $id = (int) $model->getInsertID(); $body = ['data' => $model->find($id)]; $this->remember('supplier.create', 'supplier', $id, $body); (new AuditLogger())->record('supplier.created', 'supplier', $id, null, $data); return $this->ok($body['data'], [], 201);
    }

    public function supplierStatus(int $id): ResponseInterface { if ($blocked = $this->guard('suppliers.manage')) return $blocked; $model = new SupplierModel(); $row = $model->find($id); if (! $row) return $this->error('NOT_FOUND', 'Tedarikçi bulunamadı.', 404); $active = ! (bool) $row['is_active']; $model->update($id, ['is_active' => $active ? 1 : 0]); (new AuditLogger())->record('supplier.status_changed', 'supplier', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => $active]); return $this->ok(['id' => $id, 'is_active' => $active]); }

    public function purchases(): ResponseInterface
    {
        if ($blocked = $this->purchaseGuard()) return $blocked; [$page, $perPage] = $this->pagination(); $model = new PurchaseOrderModel(); $model->select('purchase_orders.*,suppliers.company_name,warehouses.name AS warehouse_name')->join('suppliers', 'suppliers.id=purchase_orders.supplier_id')->join('warehouses', 'warehouses.id=purchase_orders.warehouse_id'); $status = trim((string) $this->request->getGet('status')); if ($status !== '') $model->where('purchase_orders.status', $status); $total = $model->countAllResults(false); $rows = $model->orderBy('purchase_orders.created_at', 'DESC')->findAll($perPage, ($page - 1) * $perPage); if (! auth()->user()?->can('products.view-cost')) foreach ($rows as &$row) unset($row['subtotal']); return $this->ok($rows, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function showPurchase(int $id): ResponseInterface
    {
        if ($blocked = $this->purchaseGuard()) return $blocked; $order = (new PurchaseOrderModel())->select('purchase_orders.*,suppliers.company_name,warehouses.name AS warehouse_name')->join('suppliers', 'suppliers.id=purchase_orders.supplier_id')->join('warehouses', 'warehouses.id=purchase_orders.warehouse_id')->find($id); if (! $order) return $this->error('NOT_FOUND', 'Alış siparişi bulunamadı.', 404); $items = (new PurchaseOrderItemModel())->where('purchase_order_id', $id)->findAll(); if (! auth()->user()?->can('products.view-cost')) { unset($order['subtotal']); foreach ($items as &$item) unset($item['unit_cost']); } return $this->ok(['order' => $order, 'items' => $items]);
    }

    public function createPurchase(): ResponseInterface
    {
        if ($blocked = $this->guard('purchases.create')) return $blocked; if ($replay = $this->replay('purchase.create')) return $replay; $in = $this->input(); $supplier = (new SupplierModel())->where('is_active', 1)->find((int) ($in['supplier_id'] ?? 0)); $warehouse = (new WarehouseModel())->where('is_active', 1)->find((int) ($in['warehouse_id'] ?? 0)); $input = $in['items'] ?? null; if (! $supplier || ! $warehouse || ! is_array($input) || $input === []) return $this->error('VALIDATION_FAILED', 'Tedarikçi, depo ve en az bir ürün satırı zorunludur.', 422);
        $variants = array_column($this->variants(true), null, 'variant_id'); $items = []; $subtotal = 0.0; foreach ($input as $row) { $variantId = (int) ($row['product_variant_id'] ?? 0); $qty = $this->decimal($row['quantity'] ?? 0); $cost = $this->decimal($row['unit_cost'] ?? 0); if (! isset($variants[$variantId]) || $qty <= 0 || $cost < 0) return $this->error('VALIDATION_FAILED', 'Ürün satırlarından biri geçersiz.', 422, ['items' => 'Geçersiz satır.']); $variant = $variants[$variantId]; $items[] = ['product_variant_id' => $variantId, 'sku_snapshot' => $variant['sku'], 'product_name_snapshot' => $variant['product_name'], 'ordered_quantity' => $qty, 'received_quantity' => 0, 'unit_cost' => $cost]; $subtotal += round($qty * $cost, 2); }
        $db = db_connect(); $db->transBegin(); try { $model = new PurchaseOrderModel(); $data = ['order_number' => $this->number('ALS'), 'supplier_id' => $supplier['id'], 'warehouse_id' => $warehouse['id'], 'status' => 'ordered', 'order_date' => $this->dateOrToday((string) ($in['order_date'] ?? '')), 'expected_date' => $this->dateOrNull((string) ($in['expected_date'] ?? '')), 'currency' => 'TRY', 'subtotal' => $subtotal, 'notes' => trim((string) ($in['notes'] ?? '')) ?: null, 'created_by_user_id' => auth()->id()]; if (! $model->insert($data)) throw new RuntimeException(implode(' ', $model->errors())); $id = (int) $model->getInsertID(); foreach ($items as $item) { $item['purchase_order_id'] = $id; (new PurchaseOrderItemModel())->insert($item); } if (! $db->transStatus()) throw new RuntimeException('Alış siparişi kaydedilemedi.'); $db->transCommit(); $body = ['data' => ['id' => $id, 'order_number' => $data['order_number']]]; $this->remember('purchase.create', 'purchase_order', $id, $body); (new AuditLogger())->record('purchase.created', 'purchase_order', $id, null, $data); return $this->ok($body['data'], [], 201); } catch (Throwable $e) { $db->transRollback(); return $this->error('PURCHASE_CREATE_FAILED', $e->getMessage(), 422); }
    }

    public function receivePurchase(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('purchases.receive')) return $blocked; if ($replay = $this->replay('purchase.receive')) return $replay; $order = (new PurchaseOrderModel())->find($id); if (! $order || ! in_array($order['status'], ['ordered', 'partial'], true)) return $this->error('NOT_FOUND', 'Mal kabul edilebilir alış siparişi bulunamadı.', 404); $posted = $this->input()['receive'] ?? null; if (! is_array($posted)) return $this->error('VALIDATION_FAILED', 'Mal kabul miktarı girin.', 422, ['receive' => 'Zorunlu.']); $items = (new PurchaseOrderItemModel())->where('purchase_order_id', $id)->findAll(); $db = db_connect(); $db->transBegin(); $received = 0.0;
        try { foreach ($items as $item) { $qty = $this->decimal($posted[(string) $item['id']] ?? $posted[$item['id']] ?? 0); $remaining = (float) $item['ordered_quantity'] - (float) $item['received_quantity']; if ($qty < 0 || $qty > $remaining + .0001) throw new RuntimeException($item['sku_snapshot'] . ' için kabul miktarı kalan miktarı aşıyor.'); if ($qty <= 0) continue; (new StockService((int) auth()->id()))->move((int) $order['warehouse_id'], (int) $item['product_variant_id'], $qty, 'purchase_receipt', 'Alış mal kabulü ' . $order['order_number'], 'purchase_order', $id); (new PurchaseOrderItemModel())->update($item['id'], ['received_quantity' => (float) $item['received_quantity'] + $qty]); $received += $qty; } if ($received <= 0) throw new RuntimeException('En az bir ürün için kabul miktarı girin.'); $remainingRow = (new PurchaseOrderItemModel())->select('SUM(ordered_quantity-received_quantity) AS remaining', false)->where('purchase_order_id', $id)->first(); $status = (float) ($remainingRow['remaining'] ?? 0) > 0 ? 'partial' : 'received'; (new PurchaseOrderModel())->update($id, ['status' => $status]); if (! $db->transStatus()) throw new RuntimeException('Mal kabul kaydedilemedi.'); $db->transCommit(); $body = ['data' => ['received' => $received, 'status' => $status]]; $this->remember('purchase.receive', 'purchase_order', $id, $body); (new AuditLogger())->record('purchase.received', 'purchase_order', $id, ['status' => $order['status']], $body['data']); return $this->ok($body['data']); } catch (Throwable $e) { $db->transRollback(); return $this->error('PURCHASE_RECEIVE_FAILED', $e->getMessage(), 422); }
    }

    private function purchaseGuard(): ?ResponseInterface { if ($blocked = $this->guard()) return $blocked; $user = auth()->user(); return $user && ($user->can('purchases.manage') || $user->can('purchases.create') || $user->can('purchases.receive')) ? null : $this->error('FORBIDDEN', 'Alış işlemleri için yetkiniz bulunmuyor.', 403); }
    private function variants(bool $withCost): array { $select = 'pv.id AS variant_id,pv.sku,pv.size,pv.color,p.name AS product_name'; if ($withCost) $select .= ',COALESCE(pv.cost_price_override,p.cost_price) AS unit_cost'; return db_connect()->table('product_variants pv')->select($select, false)->join('products p', 'p.id=pv.product_id')->where('pv.is_active', 1)->where('p.is_active', 1)->orderBy('p.name')->get()->getResultArray(); }
    private function decimal(mixed $value): float { $value = str_replace(',', '.', trim((string) $value)); return is_numeric($value) ? round((float) $value, 3) : 0.0; }
    private function dateOrNull(string $value): ?string { return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null; }
    private function dateOrToday(string $value): string { return $this->dateOrNull($value) ?? date('Y-m-d'); }
    private function number(string $prefix): string { return $prefix . '-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))); }
}
