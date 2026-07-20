<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\ProductCategoryModel;
use App\Models\CustomerPriceGroupModel;
use App\Models\ProductSpecialPriceModel;
use App\Models\CustomerModel;
use App\Libraries\AuditLogger;
use App\Services\ProductPriceResolver;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

final class CatalogController extends ApiController
{
    public function categories(): ResponseInterface
    {
        if ($blocked = $this->guard('products.view')) return $blocked;
        return $this->ok((new ProductCategoryModel())->where('is_active', 1)->orderBy('name')->findAll());
    }

    public function createCategory(): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $input = $this->input(); $name = trim((string) ($input['name'] ?? '')); $code = mb_strtoupper(trim((string) ($input['code'] ?? '')));
        if ($name === '' || $code === '') return $this->error('VALIDATION_FAILED', 'Kategori kodu ve adı zorunludur.', 422);
        $model = new ProductCategoryModel(); if (! $model->insert(['code' => $code, 'name' => $name, 'description' => trim((string) ($input['description'] ?? '')) ?: null, 'is_active' => 1])) return $this->error('VALIDATION_FAILED', 'Kategori kaydedilemedi.', 422, $model->errors()); $id = (int) $model->getInsertID(); (new AuditLogger())->record('product_category.created', 'product_category', $id, null, ['code' => $code, 'name' => $name]); return $this->ok($model->find($id), [], 201);
    }
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard('products.view')) return $blocked;
        [$page, $perPage] = $this->pagination();
        $model = new ProductModel();
        if (! auth()->user()?->can('products.manage')) $model->where('is_active', 1);
        $active = $this->request->getGet('active');
        if (auth()->user()?->can('products.manage') && ($active === '0' || $active === '1')) $model->where('is_active', (int) $active);
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

    public function create(): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked;
        return $this->save(null);
    }

    public function update(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $row = (new ProductModel())->find($id); if (! $row) return $this->error('NOT_FOUND', 'Ürün bulunamadı.', 404);
        return $this->save($row);
    }

    public function status(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $model = new ProductModel(); $row = $model->find($id); if (! $row) return $this->error('NOT_FOUND', 'Ürün bulunamadı.', 404); $active = array_key_exists('is_active', $this->input()) ? (bool) $this->input()['is_active'] : ! (bool) $row['is_active']; $model->update($id, ['is_active' => $active ? 1 : 0]); (new AuditLogger())->record('product.status_updated', 'product', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => $active]); return $this->ok(['id' => $id, 'is_active' => $active]);
    }

    public function archive(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $model = new ProductModel(); $row = $model->find($id); if (! $row) return $this->error('NOT_FOUND', 'Ürün bulunamadı.', 404); db_connect()->table('product_variants')->where('product_id', $id)->update(['is_active' => 0]); $model->delete($id); (new AuditLogger())->record('product.archived', 'product', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => false, 'archived' => true]); return $this->ok(['id' => $id, 'archived' => true]);
    }

    public function bulkPrice(): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $input = $this->input(); $ids = array_values(array_unique(array_filter(array_map('intval', (array) ($input['product_ids'] ?? []))))); $rate = (float) ($input['change_percent'] ?? 0); if ($ids === [] || $rate < -100 || $rate > 1000) return $this->error('VALIDATION_FAILED', 'Ürünleri seçin ve -100 ile 1000 arasında oran girin.', 422); $model = new ProductModel(); $count = 0; foreach ($ids as $id) { $row = $model->find($id); if (! $row) continue; $new = round(max(0, (float) $row['list_price'] * (1 + $rate / 100)), 4); $model->update($id, ['list_price' => $new]); (new AuditLogger())->record('product.price_bulk_updated', 'product', $id, ['list_price' => (float) $row['list_price']], ['list_price' => $new, 'change_percent' => $rate]); $count++; } return $this->ok(['updated' => $count]);
    }

    public function priceGroups(): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; return $this->ok((new CustomerPriceGroupModel())->orderBy('is_active', 'DESC')->orderBy('name')->findAll());
    }

    public function createPriceGroup(): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $input = $this->input(); $data = ['code' => mb_strtoupper(trim((string) ($input['code'] ?? ''))), 'name' => trim((string) ($input['name'] ?? '')), 'description' => trim((string) ($input['description'] ?? '')) ?: null, 'discount_percent' => (float) ($input['discount_percent'] ?? 0), 'is_active' => 1]; $model = new CustomerPriceGroupModel(); if (! $model->insert($data)) return $this->error('VALIDATION_FAILED', 'Fiyat grubu kaydedilemedi.', 422, $model->errors()); $id = (int) $model->getInsertID(); (new AuditLogger())->record('customer_price_group.created', 'customer_price_group', $id, null, $data); return $this->ok($model->find($id), [], 201);
    }

    public function priceGroupStatus(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $model = new CustomerPriceGroupModel(); $row = $model->find($id); if (! $row) return $this->error('NOT_FOUND', 'Fiyat grubu bulunamadı.', 404); $active = ! (bool) $row['is_active']; $model->update($id, ['is_active' => $active ? 1 : 0]); (new AuditLogger())->record('customer_price_group.status_updated', 'customer_price_group', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => $active]); return $this->ok(['id' => $id, 'is_active' => $active]);
    }

    public function specialPrices(int $productId): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; if (! (new ProductModel())->find($productId)) return $this->error('NOT_FOUND', 'Ürün bulunamadı.', 404); $rows = (new ProductSpecialPriceModel())->select('product_special_prices.*,customer_price_groups.name AS group_name,customers.company_name,product_variants.sku')->join('customer_price_groups', 'customer_price_groups.id=product_special_prices.customer_price_group_id', 'left')->join('customers', 'customers.id=product_special_prices.customer_id', 'left')->join('product_variants', 'product_variants.id=product_special_prices.product_variant_id', 'left')->where('product_special_prices.product_id', $productId)->orderBy('product_special_prices.created_at', 'DESC')->findAll(); return $this->ok($rows);
    }

    public function createSpecialPrice(int $productId): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; if (! (new ProductModel())->find($productId)) return $this->error('NOT_FOUND', 'Ürün bulunamadı.', 404); $input = $this->input(); $target = (string) ($input['target_type'] ?? ''); $targetId = (int) ($input['target_id'] ?? 0); $price = (float) ($input['unit_price'] ?? -1); if (! in_array($target, ['group', 'customer'], true) || $targetId < 1 || $price < 0) return $this->error('VALIDATION_FAILED', 'Geçerli hedef ve fiyat girin.', 422); $exists = $target === 'group' ? (new CustomerPriceGroupModel())->find($targetId) : (new CustomerModel())->find($targetId); if (! $exists) return $this->error('NOT_FOUND', 'Fiyat hedefi bulunamadı.', 404); $data = ['product_id' => $productId, 'product_variant_id' => (int) ($input['product_variant_id'] ?? 0) ?: null, 'customer_price_group_id' => $target === 'group' ? $targetId : null, 'customer_id' => $target === 'customer' ? $targetId : null, 'unit_price' => $price, 'currency' => 'TRY', 'valid_from' => trim((string) ($input['valid_from'] ?? '')) ?: null, 'valid_until' => trim((string) ($input['valid_until'] ?? '')) ?: null, 'is_active' => 1, 'created_by_user_id' => auth()->id()]; $model = new ProductSpecialPriceModel(); if (! $model->insert($data)) return $this->error('VALIDATION_FAILED', 'Özel fiyat kaydedilemedi.', 422, $model->errors()); $id = (int) $model->getInsertID(); (new AuditLogger())->record('product.special_price_created', 'product_special_price', $id, null, $data); return $this->ok($model->find($id), [], 201);
    }

    public function specialPriceStatus(int $productId, int $id): ResponseInterface
    {
        if ($blocked = $this->guard('products.manage')) return $blocked; $model = new ProductSpecialPriceModel(); $row = $model->where('product_id', $productId)->find($id); if (! $row) return $this->error('NOT_FOUND', 'Özel fiyat bulunamadı.', 404); $active = ! (bool) $row['is_active']; $model->update($id, ['is_active' => $active ? 1 : 0]); (new AuditLogger())->record('product.special_price_status_updated', 'product_special_price', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => $active]); return $this->ok(['id' => $id, 'is_active' => $active]);
    }

    private function save(?array $existing): ResponseInterface
    {
        $input = $this->input(); if ($existing && (string) ($input['expected_updated_at'] ?? '') !== (string) $existing['updated_at']) return $this->error('STALE_RESOURCE', 'Ürün başka bir oturumda güncellendi.', 409); $canCost = auth()->user()?->can('products.view-cost') ?? false;
        $data = ['category_id' => (int) ($input['category_id'] ?? 0) ?: null, 'product_code' => mb_strtoupper(trim((string) ($input['product_code'] ?? ''))), 'name' => trim((string) ($input['name'] ?? '')), 'description' => trim((string) ($input['description'] ?? '')) ?: null, 'tax_rate' => (float) ($input['tax_rate'] ?? 0), 'list_price' => (float) ($input['list_price'] ?? 0), 'currency' => 'TRY', 'show_on_website' => ! empty($input['show_on_website']) ? 1 : 0, 'is_active' => array_key_exists('is_active', $input) ? (! empty($input['is_active']) ? 1 : 0) : 1, 'track_stock' => ! empty($input['track_stock']) ? 1 : 0, 'critical_stock_level' => (float) ($input['critical_stock_level'] ?? 0), 'customization_mode' => in_array(($input['customization_mode'] ?? ''), ['none', 'optional', 'required'], true) ? $input['customization_mode'] : 'none', 'created_by_user_id' => $existing['created_by_user_id'] ?? auth()->id(), 'cost_price' => $canCost ? (float) ($input['cost_price'] ?? 0) : (float) ($existing['cost_price'] ?? 0)];
        if ($data['product_code'] === '' || $data['name'] === '' || $data['list_price'] < 0 || $data['tax_rate'] < 0) return $this->error('VALIDATION_FAILED', 'Ürün kodu, adı ve geçerli fiyat bilgileri zorunludur.', 422);
        $db = db_connect(); $db->transBegin(); try { $model = new ProductModel(); if ($existing) { if (! $model->update($existing['id'], $data)) throw new \RuntimeException(implode(' ', $model->errors())); $id = (int) $existing['id']; } else { if (! $model->insert($data)) throw new \RuntimeException(implode(' ', $model->errors())); $id = (int) $model->getInsertID(); } $variants = new ProductVariantModel(); foreach ((array) ($input['variants'] ?? []) as $variant) { $row = ['product_id' => $id, 'sku' => mb_strtoupper(trim((string) ($variant['sku'] ?? ''))), 'size' => trim((string) ($variant['size'] ?? '')) ?: null, 'color' => trim((string) ($variant['color'] ?? '')) ?: null, 'preparation_type' => ($variant['preparation_type'] ?? '') === 'customized' ? 'customized' : 'plain', 'list_price_override' => ($variant['list_price_override'] ?? '') === '' ? null : (float) $variant['list_price_override'], 'cost_price_override' => $canCost && ($variant['cost_price_override'] ?? '') !== '' ? (float) $variant['cost_price_override'] : null, 'critical_stock_level' => ($variant['critical_stock_level'] ?? '') === '' ? null : (float) $variant['critical_stock_level'], 'is_active' => array_key_exists('is_active', $variant) ? (! empty($variant['is_active']) ? 1 : 0) : 1]; if ($row['sku'] === '') throw new \RuntimeException('Varyant SKU zorunludur.'); $variantId = (int) ($variant['id'] ?? 0); if ($variantId && $variants->where('product_id', $id)->find($variantId)) $variants->update($variantId, $row); elseif (! $variants->insert($row)) throw new \RuntimeException(implode(' ', $variants->errors())); } if (! $existing && $variants->where('product_id', $id)->countAllResults() === 0) $variants->insert(['product_id' => $id, 'sku' => substr($data['product_code'] . '-STD', 0, 80), 'preparation_type' => 'plain', 'is_active' => 1]); if (! $db->transStatus()) throw new \RuntimeException('Ürün kaydedilemedi.'); $db->transCommit(); } catch (Throwable $e) { $db->transRollback(); return $this->error('PRODUCT_SAVE_FAILED', $e->getMessage(), 422); }
        $fresh = (new ProductModel())->find($id); (new AuditLogger())->record($existing ? 'product.updated' : 'product.created', 'product', $id, $existing, $fresh); return $this->ok($fresh, [], $existing ? 200 : 201);
    }
}
