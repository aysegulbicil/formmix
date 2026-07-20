<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Services\ProductPriceResolver;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

final class CatalogController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard('products.view')) return $blocked;
        [$page, $perPage] = $this->pagination();
        $model = (new ProductModel())->where('is_active', 1);
        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') $model->groupStart()->like('name', $q)->orLike('product_code', $q)->groupEnd();
        $total = (clone $model)->countAllResults(false);
        $rows = $model->orderBy('name')->findAll($perPage, ($page - 1) * $perPage);
        $variants = new ProductVariantModel();
        foreach ($rows as &$row) {
            if (! auth()->user()?->can('products.view-cost')) unset($row['cost_price']);
            $row['variants'] = $variants->where('product_id', $row['id'])->where('is_active', 1)->findAll();
            foreach ($row['variants'] as &$variant) if (! auth()->user()?->can('products.view-cost')) unset($variant['cost_price_override']);
        }
        return $this->ok($rows, ['page'=>$page, 'per_page'=>$perPage, 'total'=>$total, 'last_page'=>(int) ceil($total / $perPage)]);
    }

    public function show(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.view')) return $blocked;
        $row = (new ProductModel())->find($id);
        if (! $row) return $this->error('NOT_FOUND', 'Urun bulunamadi.', 404);
        if (! auth()->user()?->can('products.view-cost')) unset($row['cost_price']);
        $row['variants'] = (new ProductVariantModel())->where('product_id', $id)->where('is_active', 1)->findAll();
        foreach ($row['variants'] as &$variant) if (! auth()->user()?->can('products.view-cost')) unset($variant['cost_price_override']);
        return $this->ok($row);
    }

    public function price(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.view')) return $blocked;
        try {
            $price = (new ProductPriceResolver())->resolve((int) $this->request->getGet('customer_id'), $id, (int) $this->request->getGet('variant_id'));
            return $this->ok(['unit_price'=>(float)$price['unit_price'], 'tax_rate'=>(float)$price['tax_rate'], 'currency'=>$price['currency'], 'source'=>$price['price_source']]);
        } catch (Throwable $e) {
            return $this->error('PRICE_UNAVAILABLE', $e->getMessage(), 422);
        }
    }
}
