<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class CleanupAcceptanceData extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:cleanup-acceptance-data';
    protected $description = 'Yalnızca sabit KABUL-* manuel kabul testi verilerini temizler.';

    public function run(array $params): int
    {
        if (! CLI::getOption('confirm')) {
            CLI::error('Temizleme yapılmadı. Onay için --confirm kullanın.');
            return EXIT_ERROR;
        }
        $db = db_connect();
        $customer = $db->table('customers')->select('id')->where('customer_code', 'KABUL-MUS-001')->get()->getRowArray();
        if (! $customer) {
            CLI::write('Temizlenecek kabul testi verisi bulunamadı.', 'yellow');
            return EXIT_SUCCESS;
        }
        $employee = $db->table('employees')->select('id')->where('employee_code', 'KABUL-PER-001')->get()->getRowArray();
        $product = $db->table('products')->select('id')->where('product_code', 'KABUL-URUN-001')->get()->getRowArray();
        $category = $db->table('product_categories')->select('id')->where('code', 'KABUL-KAT')->get()->getRowArray();
        $supplier = $db->table('suppliers')->select('id')->where('supplier_code', 'KABUL-TED-001')->get()->getRowArray();
        $db->transBegin();
        try {
            $documents = array_column($db->table('sales_documents')->select('id')->like('document_number', 'KABUL-SIP-', 'after')->get()->getResultArray(), 'id');
            $variants = $product ? array_column($db->table('product_variants')->select('id')->where('product_id', $product['id'])->get()->getResultArray(), 'id') : [];
            if ($documents) {
                $db->table('commission_entries')->whereIn('sales_document_id', $documents)->delete();
                $db->table('sales_documents')->whereIn('id', $documents)->delete();
            }
            if ($employee) {
                $db->table('commission_rules')->where('employee_id', $employee['id'])->where('name', 'Kabul Test Satış Primi')->delete();
            }
            $purchase = $db->table('purchase_orders')->select('id')->where('order_number', 'KABUL-ALS-001')->get()->getRowArray();
            if ($purchase) {
                $db->table('purchase_orders')->where('id', $purchase['id'])->delete();
            }
            if ($variants) {
                $db->table('stock_movements')->whereIn('product_variant_id', $variants)->where('reference_type', 'acceptance_test')->delete();
                $db->table('stock_balances')->whereIn('product_variant_id', $variants)->delete();
            }
            $db->table('customers')->where('id', $customer['id'])->delete();
            if ($product) {
                $db->table('products')->where('id', $product['id'])->delete();
            }
            if ($category) {
                $db->table('product_categories')->where('id', $category['id'])->delete();
            }
            if ($supplier) {
                $db->table('suppliers')->where('id', $supplier['id'])->delete();
            }
            if ($employee) {
                $db->table('employees')->where('id', $employee['id'])->delete();
            }
            $db->transCommit();
            CLI::write('KABUL-* manuel kabul testi verileri temizlendi.', 'green');
            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            $db->transRollback();
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        }
    }
}
