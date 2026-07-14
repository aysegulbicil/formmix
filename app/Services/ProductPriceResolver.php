<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductModel;
use App\Models\ProductSpecialPriceModel;
use App\Models\ProductVariantModel;
use RuntimeException;

class ProductPriceResolver
{
    public function resolve(int $customerId, int $productId, int $variantId, ?string $at = null): array
    {
        $at ??= date('Y-m-d H:i:s');
        $product = (new ProductModel())->where('is_active', 1)->find($productId);
        $variant = (new ProductVariantModel())->where('product_id', $productId)->where('is_active', 1)->find($variantId);
        if ($product === null || $variant === null) {
            throw new RuntimeException('Yalnızca satışa açık ürün ve varyantlar seçilebilir.');
        }
        if (!empty($variant['customer_id']) && (int)$variant['customer_id'] !== $customerId) {
            throw new RuntimeException('Müşteriye özel bu varyant başka bir müşteri için kullanılamaz.');
        }

        $groupId = $this->activeGroupId($customerId, $at);
        $candidates = [
            [$customerId, null, $variantId, 'customer_variant'],
            [$customerId, null, null, 'customer_product'],
            [null, $groupId, $variantId, 'group_variant'],
            [null, $groupId, null, 'group_product'],
        ];
        foreach ($candidates as [$customer, $group, $variantFilter, $source]) {
            if ($customer === null && $group === null) continue;
            $special = $this->specialPrice($productId, $customer, $group, $variantFilter, $at);
            if ($special !== null && (float) $special['unit_price'] > 0) {
                return $this->result($product, $variant, (float) $special['unit_price'], (string) $special['currency'], $source);
            }
        }

        $variantPrice = $variant['list_price_override'];
        if ($variantPrice !== null && (float) $variantPrice > 0) {
            return $this->result($product, $variant, (float) $variantPrice, (string) $product['currency'], 'variant_list');
        }
        if ((float) $product['list_price'] > 0) {
            return $this->result($product, $variant, (float) $product['list_price'], (string) $product['currency'], 'product_list');
        }
        throw new RuntimeException('Ürünün geçerli satış fiyatı bulunmuyor.');
    }

    private function activeGroupId(int $customerId, string $at): ?int
    {
        $row = db_connect()->table('customer_price_group_members m')->select('m.customer_price_group_id')
            ->join('customer_price_groups g', 'g.id=m.customer_price_group_id AND g.deleted_at IS NULL AND g.is_active=1')
            ->where('m.customer_id', $customerId)->where('m.starts_at <=', $at)
            ->groupStart()->where('m.ends_at', null)->orWhere('m.ends_at >=', $at)->groupEnd()
            ->orderBy('m.starts_at', 'DESC')->get(1)->getRowArray();
        return $row ? (int) $row['customer_price_group_id'] : null;
    }

    private function specialPrice(int $productId, ?int $customerId, ?int $groupId, ?int $variantId, string $at): ?array
    {
        $model = new ProductSpecialPriceModel();
        $model->where('product_id', $productId)->where('is_active', 1)
            ->groupStart()->where('valid_from', null)->orWhere('valid_from <=', $at)->groupEnd()
            ->groupStart()->where('valid_until', null)->orWhere('valid_until >=', $at)->groupEnd();
        $customerId === null ? $model->where('customer_id', null) : $model->where('customer_id', $customerId);
        $groupId === null ? $model->where('customer_price_group_id', null) : $model->where('customer_price_group_id', $groupId);
        $variantId === null ? $model->where('product_variant_id', null) : $model->where('product_variant_id', $variantId);
        return $model->orderBy('valid_from', 'DESC')->orderBy('id', 'DESC')->first();
    }

    private function result(array $product, array $variant, float $price, string $currency, string $source): array
    {
        $parts = array_filter([$variant['size'] ?? null, $variant['color'] ?? null, $variant['sku'] ?? null]);
        return ['product'=>$product,'variant'=>$variant,'unit_price'=>$price,'currency'=>$currency,'tax_rate'=>(float)$product['tax_rate'],'price_source'=>$source,'variant_label'=>implode(' / ', $parts)];
    }
}
