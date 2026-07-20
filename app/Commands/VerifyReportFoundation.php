<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\ReportService;
use App\Services\SpreadsheetExporter;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

class VerifyReportFoundation extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:verify-report-foundation';
    protected $description = 'Adım 9 rapor hesaplarını ve dışa aktarımı doğrular.';

    public function run(array $params): int
    {
        $db = db_connect();
        $user = $db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        $warehouse = $db->table('warehouses')->select('id')->where('deleted_at', null)->orderBy('id')->get(1)->getRowArray();
        if (! $user || ! $warehouse) {
            CLI::error('Doğrulama için kullanıcı ve depo bulunamadı.');
            return EXIT_ERROR;
        }
        $suffix = strtoupper(bin2hex(random_bytes(3)));
        $now = date('Y-m-d H:i:s');
        $db->transBegin();
        try {
            $db->table('employees')->insert(['employee_code' => 'RPR-'.$suffix, 'full_name' => 'Rapor Test', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $employee = (int) $db->insertID();
            $db->table('customers')->insert(['customer_code' => 'RPR-M-'.$suffix, 'company_name' => 'Rapor Müşteri', 'city' => 'İstanbul', 'district' => 'Test', 'status' => 'active', 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $customer = (int) $db->insertID();
            $db->table('product_categories')->insert(['code' => 'RPR-K-'.$suffix, 'name' => 'Rapor Kategori', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $category = (int) $db->insertID();
            $db->table('products')->insert(['category_id' => $category, 'product_code' => 'RPR-U-'.$suffix, 'name' => 'Rapor Ürün', 'tax_rate' => 20, 'cost_price' => 60, 'list_price' => 100, 'currency' => 'TRY', 'is_active' => 1, 'track_stock' => 1, 'critical_stock_level' => 6, 'customization_mode' => 'optional', 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $product = (int) $db->insertID();
            $db->table('product_variants')->insert(['product_id' => $product, 'sku' => 'RPR-V-'.$suffix, 'size' => 'L', 'color' => 'Lacivert', 'preparation_type' => 'plain', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $variant = (int) $db->insertID();
            $approved = $this->document($db, $suffix.'-A', $customer, $employee, (int) $user['id'], 'approved', $now, 200, 20, 36, 216);
            $pending = $this->document($db, $suffix.'-P', $customer, $employee, (int) $user['id'], 'draft', $now, 50, 0, 10, 60);
            foreach ([[$approved, 2, 180, 36, 216, 0], [$pending, 1, 50, 10, 60, 0]] as [$document, $quantity, $net, $tax, $total, $fulfilled]) {
                $db->table('sales_document_items')->insert(['sales_document_id' => $document, 'product_id' => $product, 'product_variant_id' => $variant, 'product_code_snapshot' => 'RPR-U-'.$suffix, 'product_name_snapshot' => 'Rapor Ürün', 'variant_snapshot' => 'L / Lacivert', 'quantity' => $quantity, 'reserved_quantity' => 0, 'fulfilled_quantity' => $fulfilled, 'unit_price' => 100, 'discount_percent' => $document === $approved ? 10 : 0, 'discount_amount' => $document === $approved ? 20 : 0, 'net_amount' => $net, 'tax_rate' => 20, 'tax_amount' => $tax, 'line_total' => $total, 'created_at' => $now, 'updated_at' => $now]);
            }
            $db->table('stock_balances')->insert(['warehouse_id' => $warehouse['id'], 'product_variant_id' => $variant, 'on_hand_quantity' => 8, 'reserved_quantity' => 2, 'updated_at' => $now]);
            $db->table('commission_rules')->insert(['name' => 'Rapor Prim', 'base_type' => 'sales', 'rate_percent' => 5, 'employee_id' => $employee, 'starts_on' => date('Y-m-01'), 'is_active' => 1, 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $rule = (int) $db->insertID();
            $db->table('commission_entries')->insert(['commission_rule_id' => $rule, 'sales_document_id' => $approved, 'sales_employee_id' => $employee, 'base_amount' => 180, 'rate_percent' => 5, 'commission_amount' => 9, 'status' => 'pending', 'calculation_snapshot' => '{}', 'created_at' => $now, 'updated_at' => $now]);

            $filters = ['from' => date('Y-m-d'), 'until' => date('Y-m-d'), 'employee_id' => $employee];
            $report = (new ReportService($db))->report($filters, true);
            $this->assert($report['summary']['order_count'] === 1 && $report['summary']['net_sales'] === 180.0, 'Net satış özeti yanlış.');
            $this->assert(count($report['employees']) === 1 && $report['employees'][0]['quantity'] === 2.0, 'Personel raporu yanlış.');
            $this->assert(count($report['products']) === 1 && $report['products'][0]['cost_total'] === 120.0 && $report['products'][0]['gross_profit'] === 60.0, 'Brüt kârlılık raporu yanlış.');
            $attentionStatuses = array_column($report['orders'], 'status');
            $this->assert(count($report['orders']) === 2 && in_array('draft', $attentionStatuses, true) && in_array('approved', $attentionStatuses, true), 'İlgilenilecek sipariş raporu yanlış.');
            $stock = array_values(array_filter($report['stock'], static fn ($row) => $row['sku'] === 'RPR-V-'.$suffix));
            $this->assert(count($stock) === 1 && $stock[0]['available'] === 6.0 && $stock[0]['is_critical'], 'Kritik stok raporu yanlış.');
            $this->assert(count($report['commissions']) === 1 && $report['commissions'][0]['commission_amount'] === 9.0, 'Prim özeti yanlış.');
            $dashboard = (new ReportService($db))->dashboard();
            $this->assert($dashboard['monthNetSales'] >= 180.0 && $dashboard['openOrderCount'] >= 1, 'Yönetim ana sayfası göstergeleri yanlış.');
            $export = (new ReportService($db))->export('urun', $report, true);
            $writer = new SpreadsheetExporter();
            $csv = $writer->csv($export['headers'], $export['rows']);
            $xlsx = $writer->xlsx($export['title'], $export['headers'], $export['rows']);
            $this->assert(str_contains($csv, 'Brüt kâr') && str_starts_with($xlsx, "PK\x03\x04"), 'CSV/XLSX dışa aktarımı yanlış.');
            $safeExport = (new ReportService($db))->export('urun', (new ReportService($db))->report($filters, false), false);
            $this->assert(! in_array('Maliyet', $safeExport['headers'], true), 'Maliyet yetkisiz dışa aktarımda göründü.');
            $db->transRollback();
            CLI::write('Satış, filtre, kârlılık, sipariş, stok, prim ve CSV/XLSX raporları doğrulandı.', 'green');
            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            $db->transRollback();
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }

    private function document($db, string $suffix, int $customer, int $employee, int $user, string $status, string $now, float $subtotal, float $discount, float $tax, float $total): int
    {
        $db->table('sales_documents')->insert(['document_number' => 'RPR-S-'.$suffix, 'document_type' => 'order', 'customer_id' => $customer, 'sales_employee_id' => $employee, 'created_by_user_id' => $user, 'status' => $status, 'client_reference' => 'rpr-'.strtolower($suffix), 'currency' => 'TRY', 'subtotal' => $subtotal, 'discount_total' => $discount, 'tax_total' => $tax, 'grand_total' => $total, 'created_at' => $now, 'updated_at' => $now]);
        return (int) $db->insertID();
    }

    private function assert(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }
}
