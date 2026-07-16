<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductModel;

class WebsiteProductCatalogService
{
    /**
     * @return list<array{name:string, short:string, image_url:string, highlight:bool, badge:string}>
     */
    public function all(): array
    {
        $rows = (new ProductModel())
            ->select('id, product_code, name, description, image_path')
            ->where('show_on_website', 1)
            ->orderBy('id', 'ASC')
            ->findAll();

        return array_map(static function (array $product): array {
            $description = trim(strip_tags((string) ($product['description'] ?? '')));
            $isHighlighted = (string) $product['product_code'] === 'FM-POLO';
            $imagePath = trim((string) ($product['image_path'] ?? ''));

            return [
                'name' => (string) $product['name'],
                'short' => $description !== ''
                    ? mb_strimwidth($description, 0, 180, '…', 'UTF-8')
                    : 'Kurumsal kullanım ve logonuza özel uygulama seçenekleri için teklif alın.',
                'image_url' => $imagePath !== ''
                    ? base_url(ltrim($imagePath, '/'))
                    : base_url('assets/images/product-tshirt.svg'),
                'highlight' => $isHighlighted,
                'badge' => $isHighlighted ? 'En Çok Tercih Edilen' : '',
            ];
        }, $rows);
    }
}
