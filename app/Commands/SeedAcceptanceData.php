<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

class SeedAcceptanceData extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:seed-acceptance-data';
    protected $description = 'Manuel kabul testi için ayırt edilebilir KABUL-* verileri oluşturur.';

    public function run(array $params): int
    {
        $db = db_connect();
        $existing = $db->table('customers')->where('customer_code', 'KABUL-MUS-001')->get()->getRowArray();
        if ($existing) {
            CLI::write('Kabul testi verileri zaten mevcut; ikinci kez oluşturulmadı.', 'yellow');
            $this->summary($db);
            return EXIT_SUCCESS;
        }
        $user = $db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        $warehouse = $db->table('warehouses')->select('id,name')->where('deleted_at', null)->where('is_active', 1)->orderBy('id')->get(1)->getRowArray();
        if (! $user || ! $warehouse) {
            CLI::error('Deneme verisi için en az bir kullanıcı ve etkin depo gereklidir.');
            return EXIT_ERROR;
        }

        $now = date('Y-m-d H:i:s');
        $db->transBegin();
        try {
            $db->table('employees')->insert(['employee_code' => 'KABUL-PER-001', 'full_name' => 'Kabul Test Satış Personeli', 'max_discount_percent' => 5, 'can_collect_payment' => 0, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $employee = (int) $db->insertID();
            $db->table('customers')->insert(['customer_code' => 'KABUL-MUS-001', 'company_name' => 'Kabul Test Müşterisi A.Ş.', 'official_title' => 'Kabul Test Müşterisi Anonim Şirketi', 'email' => 'kabul-musteri@example.test', 'city' => 'İstanbul', 'district' => 'Kadıköy', 'address' => 'Kabul testi adresi, gerçek teslimat yapılmaz.', 'delivery_address' => 'Kabul testi teslimat adresi, gerçek teslimat yapılmaz.', 'billing_address' => 'Kabul testi fatura adresi.', 'tax_office' => 'Test', 'tax_number' => '1111111111', 'tax_number_normalized' => '1111111111', 'status' => 'active', 'payment_term_days' => 30, 'credit_limit' => 50000, 'current_owner_employee_id' => $employee, 'created_by_user_id' => $user['id'], 'last_activity_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
            $customer = (int) $db->insertID();
            $db->table('customer_contacts')->insert(['customer_id' => $customer, 'full_name' => 'Kabul Test Yetkilisi', 'job_title' => 'Satın Alma Sorumlusu', 'phone' => '0500 000 00 01', 'phone_normalized' => '905000000001', 'email' => 'kabul-yetkili@example.test', 'is_primary' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $db->table('customer_assignments')->insert(['customer_id' => $customer, 'employee_id' => $employee, 'started_at' => $now, 'reason' => 'Manuel kabul testi başlangıç ataması', 'assigned_by_user_id' => $user['id'], 'created_at' => $now]);
            $db->table('customer_activities')->insert(['customer_id' => $customer, 'employee_id' => $employee, 'activity_type' => 'phone', 'subject' => 'Kabul testi ilk görüşmesi', 'notes' => 'Bu kayıt yalnızca manuel kabul testi içindir.', 'happened_at' => $now, 'next_action_at' => date('Y-m-d H:i:s', strtotime('+7 days')), 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);

            $db->table('product_categories')->insert(['code' => 'KABUL-KAT', 'name' => 'Kabul Test Ürünleri', 'description' => 'Yalnızca manuel kabul testinde kullanılan ürünler.', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $category = (int) $db->insertID();
            $db->table('products')->insert(['category_id' => $category, 'product_code' => 'KABUL-URUN-001', 'name' => 'Kabul Test Polo', 'description' => 'Gerçek satışa konu olmayan manuel kabul testi ürünü.', 'tax_rate' => 20, 'cost_price' => 70, 'list_price' => 120, 'currency' => 'TRY', 'is_active' => 1, 'track_stock' => 1, 'critical_stock_level' => 5, 'customization_mode' => 'optional', 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $product = (int) $db->insertID();
            $variants = [];
            foreach ([['KABUL-VAR-L-LAC', 'L', 'Lacivert', 15], ['KABUL-VAR-M-SYH', 'M', 'Siyah', 4]] as [$sku, $size, $color, $stock]) {
                $db->table('product_variants')->insert(['product_id' => $product, 'sku' => $sku, 'size' => $size, 'color' => $color, 'preparation_type' => 'plain', 'critical_stock_level' => 5, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now]);
                $variant = (int) $db->insertID();
                $variants[] = $variant;
                $db->table('stock_balances')->insert(['warehouse_id' => $warehouse['id'], 'product_variant_id' => $variant, 'on_hand_quantity' => $stock, 'reserved_quantity' => 0, 'updated_at' => $now]);
                $db->table('stock_movements')->insert(['movement_number' => 'KABUL-HRK-'.$variant, 'movement_type' => 'manual_in', 'warehouse_id' => $warehouse['id'], 'product_variant_id' => $variant, 'quantity' => $stock, 'balance_after' => $stock, 'reason' => 'Kabul testi başlangıç stoğu', 'reference_type' => 'acceptance_test', 'created_by_user_id' => $user['id'], 'created_at' => $now]);
            }

            $db->table('suppliers')->insert(['supplier_code' => 'KABUL-TED-001', 'company_name' => 'Kabul Test Tedarikçisi Ltd.', 'contact_name' => 'Test Tedarik Yetkilisi', 'phone' => '0500 000 00 02', 'email' => 'kabul-tedarikci@example.test', 'tax_number' => '2222222222', 'address' => 'Kabul testi tedarikçi adresi.', 'notes' => 'Gerçek tedarikçi değildir.', 'is_active' => 1, 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $supplier = (int) $db->insertID();
            $db->table('purchase_orders')->insert(['order_number' => 'KABUL-ALS-001', 'supplier_id' => $supplier, 'warehouse_id' => $warehouse['id'], 'status' => 'ordered', 'order_date' => date('Y-m-d'), 'expected_date' => date('Y-m-d', strtotime('+5 days')), 'currency' => 'TRY', 'subtotal' => 1400, 'notes' => 'Kısmi ve tam mal kabul testi için.', 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $purchase = (int) $db->insertID();
            $db->table('purchase_order_items')->insert(['purchase_order_id' => $purchase, 'product_variant_id' => $variants[0], 'sku_snapshot' => 'KABUL-VAR-L-LAC', 'product_name_snapshot' => 'Kabul Test Polo', 'ordered_quantity' => 20, 'received_quantity' => 0, 'unit_cost' => 70, 'created_at' => $now, 'updated_at' => $now]);

            $documents = [
                ['KABUL-SIP-ONAY', 'approved', 10, 10, 1080, 216, 1296, 0],
                ['KABUL-SIP-BEK', 'pending_approval', 5, 0, 600, 120, 720, 0],
                ['KABUL-SIP-TED', 'procurement_waiting', 8, 0, 960, 192, 1152, 0],
                ['KABUL-SIP-KISMI', 'partially_shipped', 6, 0, 720, 144, 864, 2],
                ['KABUL-SIP-SEVK', 'shipped', 3, 0, 360, 72, 432, 3],
            ];
            $documentIds = [];
            foreach ($documents as [$number, $status, $quantity, $discount, $net, $tax, $total, $fulfilled]) {
                $subtotal = $net + $discount;
                $db->table('sales_documents')->insert(['document_number' => $number, 'document_type' => 'order', 'customer_id' => $customer, 'customer_owner_employee_id' => $employee, 'sales_employee_id' => $employee, 'created_by_user_id' => $user['id'], 'approved_by_user_id' => in_array($status, ['approved', 'procurement_waiting', 'partially_shipped', 'shipped'], true) ? $user['id'] : null, 'status' => $status, 'client_reference' => strtolower($number), 'currency' => 'TRY', 'subtotal' => $subtotal, 'discount_total' => $discount, 'tax_total' => $tax, 'grand_total' => $total, 'notes' => 'KABUL TESTİ — gerçek sipariş değildir.', 'delivery_address' => 'Kabul testi teslimat adresi.', 'approved_at' => $status === 'pending_approval' ? null : $now, 'created_at' => $now, 'updated_at' => $now]);
                $document = (int) $db->insertID();
                $documentIds[$status] = $document;
                $db->table('sales_document_items')->insert(['sales_document_id' => $document, 'product_id' => $product, 'product_variant_id' => $variants[0], 'product_code_snapshot' => 'KABUL-URUN-001', 'product_name_snapshot' => 'Kabul Test Polo', 'variant_snapshot' => 'L / Lacivert', 'quantity' => $quantity, 'reserved_quantity' => 0, 'fulfilled_quantity' => $fulfilled, 'unit_price' => 120, 'discount_percent' => $discount > 0 ? 10 : 0, 'discount_amount' => $discount, 'net_amount' => $net, 'tax_rate' => 20, 'tax_amount' => $tax, 'line_total' => $total, 'created_at' => $now, 'updated_at' => $now]);
                $db->table('sales_document_status_history')->insert(['sales_document_id' => $document, 'old_status' => null, 'new_status' => $status, 'reason' => 'Kabul testi başlangıç durumu', 'changed_by_user_id' => $user['id'], 'created_at' => $now]);
            }

            $db->table('commission_rules')->insert(['name' => 'Kabul Test Satış Primi', 'base_type' => 'sales', 'rate_percent' => 3, 'employee_id' => $employee, 'product_category_id' => $category, 'starts_on' => date('Y-m-01'), 'is_active' => 1, 'created_by_user_id' => $user['id'], 'created_at' => $now, 'updated_at' => $now]);
            $rule = (int) $db->insertID();
            $db->table('commission_entries')->insert(['commission_rule_id' => $rule, 'sales_document_id' => $documentIds['shipped'], 'sales_employee_id' => $employee, 'base_amount' => 360, 'rate_percent' => 3, 'commission_amount' => 10.80, 'status' => 'earned', 'calculation_snapshot' => json_encode(['source' => 'acceptance_test', 'document' => 'KABUL-SIP-SEVK'], JSON_UNESCAPED_UNICODE), 'created_at' => $now, 'updated_at' => $now]);

            $db->table('release_readiness_items')->where('code', 'test-data')->update(['status' => 'passed', 'notes' => 'KABUL-* kodlu müşteri, ürün, stok, alış, sipariş ve prim verileri oluşturuldu.', 'checked_by_user_id' => $user['id'], 'checked_at' => $now, 'updated_at' => $now]);
            if (! $db->transStatus()) {
                throw new RuntimeException('Deneme verileri kaydedilemedi.');
            }
            $db->transCommit();
            CLI::write('Kabul testi verileri oluşturuldu.', 'green');
            $this->summary($db);
            CLI::write('Temizleme gerektiğinde: php spark formmix:cleanup-acceptance-data --confirm', 'yellow');
            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            $db->transRollback();
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }

    private function summary($db): void
    {
        CLI::write('Müşteri: KABUL-MUS-001');
        CLI::write('Ürün: KABUL-URUN-001 (2 varyant)');
        CLI::write('Alış: KABUL-ALS-001');
        CLI::write('Siparişler: KABUL-SIP-ONAY, KABUL-SIP-BEK, KABUL-SIP-TED, KABUL-SIP-KISMI, KABUL-SIP-SEVK');
        CLI::write('Kayıtlı kabul siparişi: '.$db->table('sales_documents')->like('document_number', 'KABUL-SIP-', 'after')->countAllResults());
    }
}
