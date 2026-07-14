<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DeactivateUnpricedCatalogProducts extends Migration
{
    private const CATALOG_CODES = ['FM-POLO', 'FM-ONLUK', 'FM-SWEAT', 'FM-PANT', 'FM-YELEK', 'FM-TSHIRT'];

    public function up(): void
    {
        $this->db->table('products')
            ->whereIn('product_code', self::CATALOG_CODES)
            ->where('list_price', 0)
            ->update(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function down(): void
    {
        $this->db->table('products')
            ->whereIn('product_code', self::CATALOG_CODES)
            ->where('list_price', 0)
            ->update(['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
