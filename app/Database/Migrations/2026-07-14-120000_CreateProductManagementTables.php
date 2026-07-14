<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductManagementTables extends Migration
{
    public function up(): void
    {
        $this->createCategories();
        $this->createProducts();
        $this->createVariants();
        $this->createCustomerPriceGroups();
        $this->createCustomerPriceGroupMembers();
        $this->createSpecialPrices();
        $this->seedCatalogProducts();
    }

    public function down(): void
    {
        $this->forge->dropTable('product_special_prices', true);
        $this->forge->dropTable('customer_price_group_members', true);
        $this->forge->dropTable('customer_price_groups', true);
        $this->forge->dropTable('product_variants', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('product_categories', true);
    }

    private function createCategories(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('name');
        $this->forge->createTable('product_categories', true);
    }

    private function createProducts(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'product_code' => ['type' => 'VARCHAR', 'constraint' => 40],
            'name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'description' => ['type' => 'TEXT', 'null' => true],
            'tax_rate' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 20],
            'cost_price' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
            'list_price' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'default' => 0],
            'currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'TRY'],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'track_stock' => ['type' => 'BOOLEAN', 'default' => true],
            'critical_stock_level' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'default' => 0],
            'customization_mode' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'optional'],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('product_code');
        $this->forge->addKey(['category_id', 'is_active']);
        $this->forge->addKey('name');
        $this->forge->addForeignKey('category_id', 'product_categories', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('products', true);
    }

    private function createVariants(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'sku' => ['type' => 'VARCHAR', 'constraint' => 80],
            'size' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'color' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'other_options' => ['type' => 'TEXT', 'null' => true],
            'preparation_type' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'plain'],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'customization_note' => ['type' => 'TEXT', 'null' => true],
            'cost_price_override' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true],
            'list_price_override' => ['type' => 'DECIMAL', 'constraint' => '15,4', 'null' => true],
            'critical_stock_level' => ['type' => 'DECIMAL', 'constraint' => '15,3', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('sku');
        $this->forge->addKey(['product_id', 'is_active']);
        $this->forge->addKey(['size', 'color']);
        $this->forge->addKey('customer_id');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('product_variants', true);
    }

    private function createCustomerPriceGroups(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 30],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'description' => ['type' => 'TEXT', 'null' => true],
            'discount_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->createTable('customer_price_groups', true);
    }

    private function createCustomerPriceGroupMembers(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_price_group_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'starts_at' => ['type' => 'DATETIME'],
            'ends_at' => ['type' => 'DATETIME', 'null' => true],
            'assigned_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'ends_at']);
        $this->forge->addKey(['customer_price_group_id', 'ends_at']);
        $this->forge->addForeignKey('customer_price_group_id', 'customer_price_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('customer_price_group_members', true);
    }

    private function createSpecialPrices(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'product_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'product_variant_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'customer_price_group_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'customer_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'null' => true],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '15,4'],
            'currency' => ['type' => 'CHAR', 'constraint' => 3, 'default' => 'TRY'],
            'valid_from' => ['type' => 'DATETIME', 'null' => true],
            'valid_until' => ['type' => 'DATETIME', 'null' => true],
            'is_active' => ['type' => 'BOOLEAN', 'default' => true],
            'created_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['product_id', 'is_active']);
        $this->forge->addKey(['customer_price_group_id', 'is_active']);
        $this->forge->addKey(['customer_id', 'is_active']);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_variant_id', 'product_variants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_price_group_id', 'customer_price_groups', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('product_special_prices', true);
    }

    private function seedCatalogProducts(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('product_categories')->insert([
            'code' => 'IS-GIYIMI', 'name' => 'Kurumsal İş Giyimi',
            'description' => 'FORMMIX katalog ürünleri.', 'is_active' => 1,
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $categoryId = (int) $this->db->insertID();

        $products = [
            ['FM-POLO', 'Polo Yaka İş Kıyafeti', 'Kurumsal ekipler için pike örgü, logoya özel baskılı veya nakışlı polo yaka tişört.', 'assets/images/product-polo.jpg'],
            ['FM-ONLUK', 'Önlük', 'Kafe, restoran, kuaför ve mutfak ekipleri için çapraz askılı kurumsal önlük.', 'assets/images/product-onluk.jpg'],
            ['FM-SWEAT', 'Sweatshirt', 'Soğuk ortamlar ve dış mekân ekipleri için kurumsal sweatshirt.', 'assets/images/product-sweatshirt.jpg'],
            ['FM-PANT', 'İş Pantolonu', 'Fabrika ve teknik ekipler için dayanıklı, cep detaylı iş pantolonu.', 'assets/images/product-pants.jpg'],
            ['FM-YELEK', 'Yelek', 'Saha, depo ve servis ekipleri için logolu kurumsal yelek.', 'assets/images/product-waistcoat.png'],
            ['FM-TSHIRT', 'Baskılı Tişört', 'Etkinlik ve günlük ekip kullanımı için pamuklu kurumsal tişört.', 'assets/images/product-tshirt.jpg'],
        ];

        foreach ($products as [$code, $name, $description, $image]) {
            $this->db->table('products')->insert([
                'category_id' => $categoryId,
                'product_code' => $code,
                'name' => $name,
                'description' => $description,
                'tax_rate' => 20,
                'cost_price' => 0,
                'list_price' => 0,
                'currency' => 'TRY',
                'image_path' => $image,
                'is_active' => 1,
                'track_stock' => 1,
                'critical_stock_level' => 0,
                'customization_mode' => 'optional',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $productId = (int) $this->db->insertID();

            if ($code === 'FM-POLO') {
                $this->seedPoloVariants($productId, $code, $now);
                continue;
            }

            $this->db->table('product_variants')->insert([
                'product_id' => $productId,
                'sku' => $code . '-STD',
                'other_options' => json_encode(['label' => 'Standart'], JSON_UNESCAPED_UNICODE),
                'preparation_type' => 'plain',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->db->table('customer_price_groups')->insert([
            'code' => 'STANDART', 'name' => 'Standart Müşteri',
            'description' => 'Özel fiyat tanımlanmamış müşteriler için başlangıç grubu.',
            'discount_percent' => 0, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedPoloVariants(int $productId, string $productCode, string $now): void
    {
        $sizes = ['S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];
        $colors = [
            'Lacivert' => 'LAC', 'Antrasit' => 'ANT', 'Beyaz' => 'BYZ', 'Siyah' => 'SYH',
            'Koyu Antrasit' => 'KANT', 'Açık Gri' => 'AGRI', 'Saks Mavisi' => 'SAKS',
            'Açık Mavi' => 'AMAV', 'Bordo' => 'BRD', 'Kırmızı' => 'KRM', 'Turuncu' => 'TRN',
            'Sarı' => 'SRI', 'Yeşil' => 'YSL', 'Şişe Yeşili' => 'SYSL', 'Haki' => 'HAK',
            'Mor' => 'MOR', 'Kahverengi' => 'KHV', 'Bej' => 'BEJ',
        ];
        $rows = [];
        foreach ($sizes as $size) {
            foreach ($colors as $color => $colorCode) {
                $rows[] = [
                    'product_id' => $productId,
                    'sku' => $productCode . '-' . $size . '-' . $colorCode,
                    'size' => $size,
                    'color' => $color,
                    'preparation_type' => 'plain',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        $this->db->table('product_variants')->insertBatch($rows);
    }
}
