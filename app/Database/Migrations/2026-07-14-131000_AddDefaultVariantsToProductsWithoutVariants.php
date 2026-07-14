<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultVariantsToProductsWithoutVariants extends Migration
{
    private const MARKER = '{"generated_default":true,"label":"Standart"}';

    public function up(): void
    {
        $products = $this->db->table('products p')
            ->select('p.id, p.product_code')
            ->join('product_variants v', 'v.product_id = p.id AND v.deleted_at IS NULL', 'left')
            ->where('p.deleted_at', null)
            ->where('v.id', null)
            ->get()->getResultArray();

        foreach ($products as $product) {
            $this->db->table('product_variants')->insert([
                'product_id' => $product['id'],
                'sku' => $this->availableSku((string) $product['product_code']),
                'other_options' => self::MARKER,
                'preparation_type' => 'plain',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('product_variants')->where('other_options', self::MARKER)->delete();
    }

    private function availableSku(string $productCode): string
    {
        $base = substr(strtoupper($productCode) . '-STD', 0, 72);
        $sku = $base;
        $counter = 2;
        while ($this->db->table('product_variants')->where('sku', $sku)->countAllResults() > 0) {
            $sku = substr($base, 0, 72) . '-' . $counter++;
        }
        return $sku;
    }
}
