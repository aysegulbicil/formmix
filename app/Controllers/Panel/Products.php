<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\CustomerModel;
use App\Models\CustomerPriceGroupModel;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;
use App\Models\ProductSpecialPriceModel;
use App\Models\ProductVariantModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use RuntimeException;
use Throwable;

class Products extends BaseController
{
    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $category = (string) $this->request->getGet('kategori');
        $status = (string) $this->request->getGet('durum');
        $model = new ProductModel();
        $model->select('products.*, product_categories.name AS category_name')
            ->join('product_categories', 'product_categories.id = products.category_id', 'left');
        if ($search !== '') {
            $model->groupStart()->like('products.name', $search)->orLike('products.product_code', $search)->groupEnd();
        }
        if (ctype_digit($category)) {
            $model->where('products.category_id', (int) $category);
        }
        if ($status === 'aktif' || $status === 'pasif') {
            $model->where('products.is_active', $status === 'aktif' ? 1 : 0);
        }
        $products = $model->orderBy('products.is_active', 'DESC')->orderBy('products.name')->findAll();
        $variants = new ProductVariantModel();
        foreach ($products as &$product) {
            $rows = $variants->select('size, color, preparation_type')->where('product_id', $product['id'])->where('is_active', 1)->findAll();
            $product['variant_count'] = count($rows);
            $product['size_count'] = count(array_unique(array_filter(array_column($rows, 'size'))));
            $product['color_count'] = count(array_unique(array_filter(array_column($rows, 'color'))));
            $product['has_customized'] = in_array('customized', array_column($rows, 'preparation_type'), true);
            if (! $this->canViewCost()) {
                unset($product['cost_price']);
            }
        }
        return view('panel/products/index', [
            'title' => 'Ürünler | FORMMIX', 'pageTitle' => 'Ürünler', 'activeNav' => 'products',
            'products' => $products, 'categories' => (new ProductCategoryModel())->orderBy('name')->findAll(),
            'search' => $search, 'category' => $category, 'status' => $status,
            'canManage' => auth()->user()?->can('products.manage') ?? false, 'canViewCost' => $this->canViewCost(),
        ]);
    }

    public function create(): string
    {
        return view('panel/products/form', $this->formData(null));
    }

    public function store(): RedirectResponse
    {
        return $this->persist(null);
    }

    public function edit(int $id): string
    {
        return view('panel/products/form', $this->formData($this->findProduct($id)));
    }

    public function update(int $id): RedirectResponse
    {
        return $this->persist($this->findProduct($id));
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $product = $this->findProduct($id);
        $active = ! (bool) $product['is_active'];
        (new ProductModel())->update($id, ['is_active' => $active ? 1 : 0]);
        (new AuditLogger())->record($active ? 'product.activated' : 'product.deactivated', 'product', $id, ['is_active' => (bool) $product['is_active']], ['is_active' => $active]);
        return redirect()->to(site_url('panel/urunler'))->with('message', $active ? 'Ürün satışa açıldı.' : 'Ürün satışa kapatıldı.');
    }

    public function bulkPriceUpdate(): RedirectResponse
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('product_ids')))));
        $rate = $this->decimal($this->request->getPost('change_percent'));
        if ($ids === [] || $rate === null || $rate < -100 || $rate > 1000) {
            return redirect()->back()->with('errors', ['form' => 'Ürünleri seçin ve -100 ile 1000 arasında geçerli bir değişim oranı yazın.']);
        }
        $model = new ProductModel();
        $db = db_connect();
        $db->transBegin();
        try {
            foreach ($ids as $id) {
                $product = $model->find($id);
                if ($product === null) {
                    continue;
                }
                $old = (float) $product['list_price'];
                $new = round(max(0, $old * (1 + ($rate / 100))), 4);
                if (! $model->update($id, ['list_price' => $new])) {
                    throw new RuntimeException(implode(' ', $model->errors()));
                }
                (new AuditLogger())->record('product.price_bulk_updated', 'product', $id, ['list_price' => $old], ['list_price' => $new, 'change_percent' => $rate]);
            }
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            return redirect()->back()->with('errors', ['form' => $exception->getMessage()]);
        }
        return redirect()->to(site_url('panel/urunler'))->with('message', count($ids) . ' ürünün liste fiyatı güncellendi.');
    }

    public function priceGroups(): string
    {
        return view('panel/products/price_groups', [
            'title' => 'Fiyat Grupları | FORMMIX', 'pageTitle' => 'Müşteri fiyat grupları', 'activeNav' => 'products',
            'groups' => (new CustomerPriceGroupModel())->orderBy('is_active', 'DESC')->orderBy('name')->findAll(),
        ]);
    }

    public function storePriceGroup(): RedirectResponse
    {
        $model = new CustomerPriceGroupModel();
        $data = [
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'discount_percent' => $this->decimal($this->request->getPost('discount_percent')) ?? 0,
            'is_active' => 1,
        ];
        if (! $model->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }
        $id = (int) $model->getInsertID();
        (new AuditLogger())->record('customer_price_group.created', 'customer_price_group', $id, null, $data);
        return redirect()->to(site_url('panel/urunler/fiyat-gruplari'))->with('message', 'Müşteri fiyat grubu oluşturuldu.');
    }

    public function togglePriceGroup(int $id): RedirectResponse
    {
        $model = new CustomerPriceGroupModel();
        $group = $model->find($id);
        if ($group === null) {
            throw PageNotFoundException::forPageNotFound('Fiyat grubu bulunamadı.');
        }
        $active = ! (bool) $group['is_active'];
        $model->update($id, ['is_active' => $active ? 1 : 0]);
        (new AuditLogger())->record('customer_price_group.status_updated', 'customer_price_group', $id, ['is_active' => (bool) $group['is_active']], ['is_active' => $active]);
        return redirect()->to(site_url('panel/urunler/fiyat-gruplari'))->with('message', 'Fiyat grubu durumu güncellendi.');
    }

    public function storeSpecialPrice(int $productId): RedirectResponse
    {
        $this->findProduct($productId);
        [$targetType, $targetIdValue] = array_pad(explode('|', (string) $this->request->getPost('target'), 2), 2, '');
        $targetId = ctype_digit($targetIdValue) ? (int) $targetIdValue : 0;
        $price = $this->decimal($this->request->getPost('unit_price'));
        if (! in_array($targetType, ['group', 'customer'], true) || $targetId < 1 || $price === null || $price < 0) {
            return redirect()->back()->with('errors', ['special_price' => 'Geçerli bir müşteri veya fiyat grubu ile fiyat yazın.']);
        }
        $from = $this->dateTime($this->request->getPost('valid_from'));
        $until = $this->dateTime($this->request->getPost('valid_until'));
        if ($from && $until && $until < $from) {
            return redirect()->back()->with('errors', ['special_price' => 'Bitiş tarihi başlangıç tarihinden önce olamaz.']);
        }
        $variantId = ctype_digit((string) $this->request->getPost('product_variant_id')) ? (int) $this->request->getPost('product_variant_id') : null;
        if ($variantId !== null && (new ProductVariantModel())->where('product_id', $productId)->find($variantId) === null) {
            return redirect()->back()->with('errors', ['special_price' => 'Seçilen varyant bu ürüne ait değil.']);
        }
        $targetExists = $targetType === 'group'
            ? (new CustomerPriceGroupModel())->find($targetId)
            : (new CustomerModel())->find($targetId);
        if ($targetExists === null) {
            return redirect()->back()->with('errors', ['special_price' => 'Seçilen müşteri veya fiyat grubu bulunamadı.']);
        }
        $data = [
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'customer_price_group_id' => $targetType === 'group' ? $targetId : null,
            'customer_id' => $targetType === 'customer' ? $targetId : null,
            'unit_price' => $price, 'currency' => 'TRY', 'valid_from' => $from, 'valid_until' => $until,
            'is_active' => 1, 'created_by_user_id' => auth()->id(),
        ];
        $model = new ProductSpecialPriceModel();
        if (! $model->insert($data)) {
            return redirect()->back()->with('errors', ['special_price' => implode(' ', $model->errors())]);
        }
        $id = (int) $model->getInsertID();
        (new AuditLogger())->record('product.special_price_created', 'product_special_price', $id, null, $data);
        return redirect()->to(site_url("panel/urunler/{$productId}/duzenle"))->with('message', 'Özel fiyat kaydedildi.');
    }

    public function toggleSpecialPrice(int $productId, int $priceId): RedirectResponse
    {
        $model = new ProductSpecialPriceModel();
        $price = $model->where('product_id', $productId)->find($priceId);
        if ($price === null) {
            throw PageNotFoundException::forPageNotFound('Özel fiyat bulunamadı.');
        }
        $active = ! (bool) $price['is_active'];
        $model->update($priceId, ['is_active' => $active ? 1 : 0]);
        (new AuditLogger())->record('product.special_price_status_updated', 'product_special_price', $priceId, ['is_active' => (bool) $price['is_active']], ['is_active' => $active]);
        return redirect()->to(site_url("panel/urunler/{$productId}/duzenle"))->with('message', 'Özel fiyat durumu güncellendi.');
    }

    private function persist(?array $product): RedirectResponse
    {
        $id = isset($product['id']) ? (int) $product['id'] : null;
        $db = db_connect();
        $db->transBegin();
        try {
            $categoryId = $this->resolveCategory();
        } catch (Throwable $exception) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', ['form' => $exception->getMessage()]);
        }
        $data = [
            'id' => $id, 'category_id' => $categoryId,
            'product_code' => strtoupper(trim((string) $this->request->getPost('product_code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')) ?: null,
            'tax_rate' => $this->decimal($this->request->getPost('tax_rate')),
            'list_price' => $this->decimal($this->request->getPost('list_price')),
            'currency' => 'TRY', 'image_path' => trim((string) $this->request->getPost('image_path')) ?: null,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'track_stock' => $this->request->getPost('track_stock') ? 1 : 0,
            'critical_stock_level' => $this->decimal($this->request->getPost('critical_stock_level')) ?? 0,
            'customization_mode' => (string) $this->request->getPost('customization_mode'),
            'created_by_user_id' => $product['created_by_user_id'] ?? auth()->id(),
            'cost_price' => $this->canViewCost() ? $this->decimal($this->request->getPost('cost_price')) : (float) ($product['cost_price'] ?? 0),
        ];
        $model = new ProductModel();
        try {
            if ($id === null) {
                unset($data['id']);
                if (! $model->insert($data)) {
                    throw new RuntimeException(implode(' ', $model->errors()));
                }
                $id = (int) $model->getInsertID();
            } elseif (! $model->update($id, $data)) {
                throw new RuntimeException(implode(' ', $model->errors()));
            }
            $this->insertVariantLines($id, (string) $this->request->getPost('variant_lines'));
            if ($db->transStatus() === false) {
                throw new RuntimeException('Ürün veritabanına yazılamadı.');
            }
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', ['form' => $exception->getMessage()]);
        }
        $fresh = (new ProductModel())->find($id);
        (new AuditLogger())->record($product === null ? 'product.created' : 'product.updated', 'product', $id, $product ? $this->auditValues($product) : null, $this->auditValues($fresh ?? []));
        return redirect()->to(site_url('panel/urunler'))->with('message', $product === null ? 'Ürün oluşturuldu.' : 'Ürün güncellendi.');
    }

    private function insertVariantLines(int $productId, string $lines): void
    {
        $model = new ProductVariantModel();
        foreach (preg_split('/\R/u', trim($lines)) ?: [] as $index => $line) {
            if (trim($line) === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            $sku = strtoupper($parts[0] ?? '');
            $row = [
                'product_id' => $productId, 'sku' => $sku,
                'size' => ($parts[1] ?? '') ?: null, 'color' => ($parts[2] ?? '') ?: null,
                'preparation_type' => mb_strtolower($parts[3] ?? 'baskısız') === 'özel' ? 'customized' : 'plain',
                'list_price_override' => $this->decimal($parts[4] ?? null), 'is_active' => 1,
            ];
            if ($sku === '' || ! $model->insert($row)) {
                throw new RuntimeException(($index + 1) . '. varyant satırı kaydedilemedi: ' . implode(' ', $model->errors()));
            }
        }
    }

    private function resolveCategory(): ?int
    {
        $newName = trim((string) $this->request->getPost('new_category_name'));
        if ($newName === '') {
            $value = (string) $this->request->getPost('category_id');
            return ctype_digit($value) ? (int) $value : null;
        }
        $model = new ProductCategoryModel();
        $code = $this->codeFromText($newName);
        $existing = $model->where('code', $code)->first();
        if ($existing) {
            return (int) $existing['id'];
        }
        if ($code === '' || ! $model->insert(['code' => $code, 'name' => $newName, 'is_active' => 1])) {
            throw new RuntimeException(implode(' ', $model->errors()) ?: 'Kategori oluşturulamadı.');
        }
        return (int) $model->getInsertID();
    }

    private function formData(?array $product): array
    {
        $variants = $product ? (new ProductVariantModel())->where('product_id', $product['id'])->orderBy('sku')->findAll() : [];
        $specialPrices = [];
        if ($product) {
            $specialPrices = (new ProductSpecialPriceModel())
                ->select('product_special_prices.*, customer_price_groups.name AS group_name, customers.company_name, product_variants.sku')
                ->join('customer_price_groups', 'customer_price_groups.id = product_special_prices.customer_price_group_id', 'left')
                ->join('customers', 'customers.id = product_special_prices.customer_id', 'left')
                ->join('product_variants', 'product_variants.id = product_special_prices.product_variant_id', 'left')
                ->where('product_special_prices.product_id', $product['id'])->orderBy('product_special_prices.created_at', 'DESC')->findAll();
        }
        return [
            'title' => ($product ? 'Ürün Düzenle' : 'Yeni Ürün') . ' | FORMMIX',
            'pageTitle' => $product ? 'Ürün düzenle' : 'Yeni ürün', 'activeNav' => 'products',
            'product' => $product, 'categories' => (new ProductCategoryModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'variants' => $variants, 'specialPrices' => $specialPrices,
            'priceGroups' => (new CustomerPriceGroupModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'customers' => (new CustomerModel())->whereIn('status', ['candidate', 'active'])->orderBy('company_name')->findAll(),
            'canViewCost' => $this->canViewCost(),
        ];
    }

    private function findProduct(int $id): array
    {
        $product = (new ProductModel())->find($id);
        if ($product === null) {
            throw PageNotFoundException::forPageNotFound('Ürün bulunamadı.');
        }
        return $product;
    }

    private function canViewCost(): bool
    {
        return auth()->user()?->can('products.view-cost') ?? false;
    }

    private function decimal(mixed $value): ?float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $normalized = str_contains($value, ',') ? str_replace(['.', ','], ['', '.'], $value) : $value;
        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function dateTime(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
    }

    private function codeFromText(string $text): string
    {
        $map = ['ç'=>'c','Ç'=>'C','ğ'=>'g','Ğ'=>'G','ı'=>'i','İ'=>'I','ö'=>'o','Ö'=>'O','ş'=>'s','Ş'=>'S','ü'=>'u','Ü'=>'U'];
        $code = preg_replace('/[^A-Z0-9]+/', '-', strtoupper(strtr($text, $map))) ?? '';
        return trim(substr($code, 0, 30), '-');
    }

    private function auditValues(array $product): array
    {
        return array_intersect_key($product, array_flip(['category_id', 'product_code', 'name', 'tax_rate', 'cost_price', 'list_price', 'currency', 'is_active', 'track_stock', 'critical_stock_level', 'customization_mode']));
    }
}
