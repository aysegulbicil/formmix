<?php

declare(strict_types=1);

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ReportService
{
    private const SALES_STATUSES = ['approved', 'procurement_waiting', 'reserved', 'partially_shipped', 'shipped', 'delivered'];

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    public function filters(array $input): array
    {
        $today = date('Y-m-d');
        $from = $this->date((string) ($input['from'] ?? '')) ?? date('Y-m-01');
        $until = $this->date((string) ($input['until'] ?? '')) ?? $today;
        if ($from > $until) {
            [$from, $until] = [$until, $from];
        }

        return ['from' => $from, 'until' => $until, 'employee_id' => max(0, (int) ($input['employee_id'] ?? 0))];
    }

    public function report(array $filters, bool $canViewCost): array
    {
        return [
            'summary' => $this->salesSummary($filters),
            'periods' => $this->periodSummaries($filters['employee_id']),
            'daily' => $this->dailySales($filters),
            'employees' => $this->employeeSales($filters),
            'customers' => $this->customerSales($filters),
            'products' => $this->productSales($filters, $canViewCost),
            'orders' => $this->attentionOrders($filters),
            'stock' => $this->stock(),
            'commissions' => $this->commissions($filters),
        ];
    }

    public function dashboard(): array
    {
        $today = date('Y-m-d');
        $month = ['from' => date('Y-m-01'), 'until' => $today, 'employee_id' => 0];
        $summary = $this->salesSummary($month);
        $orders = $this->attentionOrders(['from' => '2000-01-01', 'until' => '2999-12-31', 'employee_id' => 0]);
        $stock = $this->stock();

        return [
            'monthNetSales' => $summary['net_sales'],
            'monthOrderCount' => $summary['order_count'],
            'pendingApprovalCount' => count(array_filter($orders, static fn ($row) => $row['status'] === 'pending_approval')),
            'procurementWaitingCount' => count(array_filter($orders, static fn ($row) => $row['status'] === 'procurement_waiting')),
            'partiallyShippedCount' => count(array_filter($orders, static fn ($row) => $row['status'] === 'partially_shipped')),
            'criticalStockCount' => count(array_filter($stock, static fn ($row) => $row['is_critical'])),
            'recentAttentionOrders' => array_slice($orders, 0, 6),
        ];
    }

    public function employees(): array
    {
        return $this->db->table('employees')->select('id,full_name,employee_code')->where('deleted_at', null)->where('is_active', 1)->orderBy('full_name')->get()->getResultArray();
    }

    public function export(string $section, array $data, bool $canViewCost): array
    {
        $definitions = [
            'personel' => ['Personel Satışları', ['Personel', 'Sipariş', 'Miktar', 'Net satış', 'Vergi', 'Genel toplam'], $data['employees'], ['employee_name', 'order_count', 'quantity', 'net_sales', 'tax_total', 'grand_total']],
            'musteri' => ['Müşteri Satışları', ['Müşteri', 'Sipariş', 'Miktar', 'Net satış', 'Vergi', 'Genel toplam'], $data['customers'], ['customer_name', 'order_count', 'quantity', 'net_sales', 'tax_total', 'grand_total']],
            'urun' => ['Ürün ve Varyant Satışları', ['Ürün', 'Varyant', 'SKU', 'Miktar', 'Net satış'], $data['products'], ['product_name', 'variant_name', 'sku', 'quantity', 'net_sales']],
            'siparis' => ['İlgilenilecek Siparişler', ['Belge', 'Müşteri', 'Personel', 'Durum', 'Tarih', 'Net satış', 'Kalan miktar'], $data['orders'], ['document_number', 'customer_name', 'employee_name', 'status_label', 'created_at', 'net_sales', 'remaining_quantity']],
            'stok' => ['Depo Stokları', ['Depo', 'Ürün', 'Varyant', 'SKU', 'Mevcut', 'Ayrılmış', 'Kullanılabilir', 'Kritik eşik', 'Durum'], $data['stock'], ['warehouse_name', 'product_name', 'variant_name', 'sku', 'on_hand', 'reserved', 'available', 'threshold', 'stock_status']],
            'prim' => ['Prim Özeti', ['Personel', 'Durum', 'Kayıt', 'Matrah', 'Prim'], $data['commissions'], ['employee_name', 'status_label', 'entry_count', 'base_amount', 'commission_amount']],
        ];
        if (! isset($definitions[$section])) {
            throw new \InvalidArgumentException('Bilinmeyen rapor bölümü.');
        }
        [$title, $headers, $rows, $keys] = $definitions[$section];
        if ($section === 'urun' && $canViewCost) {
            array_push($headers, 'Maliyet', 'Brüt kâr', 'Brüt marj %');
            array_push($keys, 'cost_total', 'gross_profit', 'gross_margin_percent');
        }

        return ['title' => $title, 'headers' => $headers, 'rows' => array_map(static function ($row) use ($keys) {
            return array_map(static fn ($key) => $row[$key] ?? '', $keys);
        }, $rows)];
    }

    public function salesSummary(array $filters): array
    {
        $row = $this->salesDocuments($filters)->select('COUNT(DISTINCT sd.id) AS order_count,SUM(sd.subtotal-sd.discount_total) AS net_sales,SUM(sd.tax_total) AS tax_total,SUM(sd.grand_total) AS grand_total', false)->get()->getRowArray() ?? [];
        return ['order_count' => (int) ($row['order_count'] ?? 0), 'net_sales' => round((float) ($row['net_sales'] ?? 0), 2), 'tax_total' => round((float) ($row['tax_total'] ?? 0), 2), 'grand_total' => round((float) ($row['grand_total'] ?? 0), 2)];
    }

    private function periodSummaries(int $employeeId): array
    {
        $today = date('Y-m-d');
        $monday = date('Y-m-d', strtotime('monday this week'));
        return [
            'daily' => $this->salesSummary(['from' => $today, 'until' => $today, 'employee_id' => $employeeId]),
            'weekly' => $this->salesSummary(['from' => $monday, 'until' => $today, 'employee_id' => $employeeId]),
            'monthly' => $this->salesSummary(['from' => date('Y-m-01'), 'until' => $today, 'employee_id' => $employeeId]),
        ];
    }

    private function dailySales(array $filters): array
    {
        $rows = $this->salesDocuments($filters)->select('DATE(sd.created_at) AS sale_date,COUNT(DISTINCT sd.id) AS order_count,SUM(sd.subtotal-sd.discount_total) AS net_sales,SUM(sd.grand_total) AS grand_total', false)->groupBy('DATE(sd.created_at)')->orderBy('sale_date')->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['order_count'] = (int) $row['order_count'];
            $row['net_sales'] = (float) $row['net_sales'];
            $row['grand_total'] = (float) $row['grand_total'];
        }
        return $rows;
    }

    private function employeeSales(array $filters): array
    {
        $rows = $this->salesDocuments($filters)->select("COALESCE(e.full_name,'Atanmamış') AS employee_name,COUNT(DISTINCT sd.id) AS order_count,SUM(sdi.quantity) AS quantity,SUM(sdi.net_amount) AS net_sales,SUM(sdi.tax_amount) AS tax_total,SUM(sdi.line_total) AS grand_total", false)->join('sales_document_items sdi', 'sdi.sales_document_id=sd.id')->join('employees e', 'e.id=sd.sales_employee_id', 'left')->groupBy('sd.sales_employee_id,e.full_name')->orderBy('net_sales', 'DESC')->get()->getResultArray();
        return $this->numbers($rows, ['quantity', 'net_sales', 'tax_total', 'grand_total'], ['order_count']);
    }

    private function customerSales(array $filters): array
    {
        $rows = $this->salesDocuments($filters)->select('c.company_name AS customer_name,COUNT(DISTINCT sd.id) AS order_count,SUM(sdi.quantity) AS quantity,SUM(sdi.net_amount) AS net_sales,SUM(sdi.tax_amount) AS tax_total,SUM(sdi.line_total) AS grand_total', false)->join('sales_document_items sdi', 'sdi.sales_document_id=sd.id')->join('customers c', 'c.id=sd.customer_id')->groupBy('sd.customer_id,c.company_name')->orderBy('net_sales', 'DESC')->get()->getResultArray();
        return $this->numbers($rows, ['quantity', 'net_sales', 'tax_total', 'grand_total'], ['order_count']);
    }

    private function productSales(array $filters, bool $canViewCost): array
    {
        $select = "sdi.product_name_snapshot AS product_name,sdi.variant_snapshot AS variant_name,pv.sku,SUM(sdi.quantity) AS quantity,SUM(sdi.net_amount) AS net_sales";
        if ($canViewCost) {
            $select .= ',SUM(sdi.quantity*COALESCE(pv.cost_price_override,p.cost_price,0)) AS cost_total';
        }
        $query = $this->salesDocuments($filters)->select($select, false)->join('sales_document_items sdi', 'sdi.sales_document_id=sd.id')->join('products p', 'p.id=sdi.product_id')->join('product_variants pv', 'pv.id=sdi.product_variant_id')->groupBy('sdi.product_id,sdi.product_variant_id,sdi.product_name_snapshot,sdi.variant_snapshot,pv.sku')->orderBy('net_sales', 'DESC');
        $rows = $this->numbers($query->get()->getResultArray(), ['quantity', 'net_sales', 'cost_total']);
        if ($canViewCost) {
            foreach ($rows as &$row) {
                $row['gross_profit'] = round($row['net_sales'] - $row['cost_total'], 2);
                $row['gross_margin_percent'] = $row['net_sales'] > 0 ? round($row['gross_profit'] / $row['net_sales'] * 100, 2) : 0.0;
            }
        }
        return $rows;
    }

    private function attentionOrders(array $filters): array
    {
        $labels = ['draft' => 'Taslak / bekleyen', 'pending_approval' => 'Onay bekliyor', 'procurement_waiting' => 'Tedarik bekliyor', 'partially_shipped' => 'Kısmi sevk'];
        $query = $this->db->table('sales_documents sd')->select("sd.document_number,c.company_name AS customer_name,COALESCE(e.full_name,'Atanmamış') AS employee_name,sd.status,sd.created_at,sd.subtotal-sd.discount_total AS net_sales,SUM(sdi.quantity-sdi.fulfilled_quantity) AS remaining_quantity", false)->join('customers c', 'c.id=sd.customer_id')->join('employees e', 'e.id=sd.sales_employee_id', 'left')->join('sales_document_items sdi', 'sdi.sales_document_id=sd.id')->where('sd.document_type', 'order')->whereIn('sd.status', array_keys($labels))->where('sd.deleted_at', null)->where('sd.created_at >=', $filters['from'].' 00:00:00')->where('sd.created_at <=', $filters['until'].' 23:59:59');
        if ($filters['employee_id'] > 0) {
            $query->where('sd.sales_employee_id', $filters['employee_id']);
        }
        $rows = $query->groupBy('sd.id,sd.document_number,c.company_name,e.full_name,sd.status,sd.created_at,sd.subtotal,sd.discount_total')->orderBy('sd.created_at', 'DESC')->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['status_label'] = $labels[$row['status']];
            $row['net_sales'] = (float) $row['net_sales'];
            $row['remaining_quantity'] = (float) $row['remaining_quantity'];
        }
        return $rows;
    }

    private function stock(): array
    {
        $rows = $this->db->table('warehouses w')->select("w.name AS warehouse_name,p.name AS product_name,pv.sku,pv.size,pv.color,COALESCE(sb.on_hand_quantity,0) AS on_hand,COALESCE(sb.reserved_quantity,0) AS reserved,COALESCE(sb.on_hand_quantity,0)-COALESCE(sb.reserved_quantity,0) AS available,COALESCE(pv.critical_stock_level,p.critical_stock_level,0) AS threshold", false)->join('products p', 'p.track_stock=1 AND p.deleted_at IS NULL')->join('product_variants pv', 'pv.product_id=p.id AND pv.is_active=1 AND pv.deleted_at IS NULL')->join('stock_balances sb', 'sb.warehouse_id=w.id AND sb.product_variant_id=pv.id', 'left')->where('w.is_active', 1)->where('w.deleted_at', null)->where('p.is_active', 1)->orderBy('w.name')->orderBy('p.name')->orderBy('pv.sku')->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['variant_name'] = trim((string) ($row['size'] ?? '').' '.(string) ($row['color'] ?? '')) ?: 'Standart';
            unset($row['size'], $row['color']);
            foreach (['on_hand', 'reserved', 'available', 'threshold'] as $key) {
                $row[$key] = (float) $row[$key];
            }
            $row['is_critical'] = $row['threshold'] > 0 && $row['available'] <= $row['threshold'];
            $row['stock_status'] = $row['is_critical'] ? 'Kritik' : 'Normal';
        }
        return $rows;
    }

    private function commissions(array $filters): array
    {
        $labels = ['pending' => 'Bekleyen', 'earned' => 'Hak edilmiş', 'paid' => 'Ödendi', 'reversed' => 'Ters kayıt'];
        $query = $this->db->table('commission_entries ce')->select('e.full_name AS employee_name,ce.status,COUNT(ce.id) AS entry_count,SUM(ce.base_amount) AS base_amount,SUM(ce.commission_amount) AS commission_amount', false)->join('employees e', 'e.id=ce.sales_employee_id')->join('sales_documents sd', 'sd.id=ce.sales_document_id')->where('sd.created_at >=', $filters['from'].' 00:00:00')->where('sd.created_at <=', $filters['until'].' 23:59:59');
        if ($filters['employee_id'] > 0) {
            $query->where('ce.sales_employee_id', $filters['employee_id']);
        }
        $rows = $query->groupBy('ce.sales_employee_id,e.full_name,ce.status')->orderBy('e.full_name')->orderBy('ce.status')->get()->getResultArray();
        foreach ($rows as &$row) {
            $row['status_label'] = $labels[$row['status']] ?? $row['status'];
        }
        return $this->numbers($rows, ['base_amount', 'commission_amount'], ['entry_count']);
    }

    private function salesDocuments(array $filters)
    {
        $query = $this->db->table('sales_documents sd')->where('sd.document_type', 'order')->whereIn('sd.status', self::SALES_STATUSES)->where('sd.deleted_at', null)->where('sd.created_at >=', $filters['from'].' 00:00:00')->where('sd.created_at <=', $filters['until'].' 23:59:59');
        if ($filters['employee_id'] > 0) {
            $query->where('sd.sales_employee_id', $filters['employee_id']);
        }
        return $query;
    }

    private function numbers(array $rows, array $floats, array $integers = []): array
    {
        foreach ($rows as &$row) {
            foreach ($floats as $key) {
                if (array_key_exists($key, $row)) {
                    $row[$key] = round((float) $row[$key], 2);
                }
            }
            foreach ($integers as $key) {
                $row[$key] = (int) ($row[$key] ?? 0);
            }
        }
        return $rows;
    }

    private function date(string $value): ?string
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $parsed && $parsed->format('Y-m-d') === $value ? $value : null;
    }
}
