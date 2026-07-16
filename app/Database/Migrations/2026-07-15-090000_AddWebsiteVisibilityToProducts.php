<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWebsiteVisibilityToProducts extends Migration
{
    private const EXISTING_WEBSITE_PRODUCTS = [
        'FM-POLO', 'FM-ONLUK', 'FM-SWEAT', 'FM-PANT', 'FM-YELEK', 'FM-TSHIRT',
    ];

    public function up(): void
    {
        if (! $this->db->fieldExists('show_on_website', 'products')) {
            $this->forge->addColumn('products', [
                'show_on_website' => [
                    'type' => 'BOOLEAN',
                    'default' => false,
                    'after' => 'image_path',
                ],
            ]);
        }

        $this->db->table('products')
            ->whereIn('product_code', self::EXISTING_WEBSITE_PRODUCTS)
            ->update(['show_on_website' => 1]);
    }

    public function down(): void
    {
        if ($this->db->fieldExists('show_on_website', 'products')) {
            $this->forge->dropColumn('products', 'show_on_website');
        }
    }
}
