<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

class VerifyProductFoundation extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:verify-product-foundation';
    protected $description = 'Ürün, varyant, fiyat ve katalog veri temelini doğrular.';

    public function run(array $params): int
    {
        $db = db_connect();
        try {
            $catalogCount = (new ProductModel())->whereIn('product_code', ['FM-POLO', 'FM-ONLUK', 'FM-SWEAT', 'FM-PANT', 'FM-YELEK', 'FM-TSHIRT'])->countAllResults();
            $polo = (new ProductModel())->where('product_code', 'FM-POLO')->first();
            $poloVariantCount = $polo ? (new ProductVariantModel())->where('product_id', $polo['id'])->countAllResults() : 0;
            if ($catalogCount !== 6 || $poloVariantCount !== 126) {
                throw new RuntimeException("Katalog aktarımı beklenenden farklı: {$catalogCount} ürün, {$poloVariantCount} polo varyantı.");
            }

            $db->transBegin();
            $suffix = strtoupper(bin2hex(random_bytes(4)));
            $categoryId = (new ProductCategoryModel())->insert(['code' => "TEST-{$suffix}", 'name' => 'Geçici Test Kategorisi', 'is_active' => 1], true);
            $productId = (new ProductModel())->insert([
                'category_id' => $categoryId, 'product_code' => "TEST-{$suffix}", 'name' => 'Geçici Test Ürünü',
                'tax_rate' => 20, 'cost_price' => 75.50, 'list_price' => 125.75, 'currency' => 'TRY',
                'is_active' => 1, 'track_stock' => 1, 'critical_stock_level' => 5, 'customization_mode' => 'optional',
            ], true);
            $variantId = (new ProductVariantModel())->insert([
                'product_id' => $productId, 'sku' => "TEST-{$suffix}-M-LAC", 'size' => 'M', 'color' => 'Lacivert',
                'preparation_type' => 'plain', 'is_active' => 1,
            ], true);
            $variant = (new ProductVariantModel())->find($variantId);
            if ($variant === null || (int) $variant['product_id'] !== (int) $productId || $variant['size'] !== 'M' || $variant['color'] !== 'Lacivert') {
                throw new RuntimeException('Geçici ürün-varyant ilişkisi okunamadı.');
            }
            CLI::write('6 katalog ürünü, 126 polo varyantı ve geçici ürün-fiyat ilişkisi başarılı.', 'green');
            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            CLI::error($exception->getMessage());
            return EXIT_ERROR;
        } finally {
            $db->transRollback();
        }
    }
}
