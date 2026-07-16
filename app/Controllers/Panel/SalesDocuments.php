<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\CustomerModel;
use App\Models\EmployeeModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\SalesDocumentItemModel;
use App\Models\SalesDocumentModel;
use App\Models\SalesDocumentStatusHistoryModel;
use App\Models\WarehouseModel;
use App\Services\ProductPriceResolver;
use App\Services\SalesDocumentCalculator;
use App\Services\StockService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class SalesDocuments extends BaseController
{
    private const TYPES = [
        'quote' => 'Teklif',
        'order' => 'Siparis',
    ];

    private const STATUSES = [
        'draft' => 'Taslak',
        'pending_approval' => 'Onay bekliyor',
        'approved' => 'Siparis olusturuldu',
        'procurement_waiting' => 'Hazirlaniyor',
        'reserved' => 'Hazirlaniyor',
        'partially_shipped' => 'Kargoya verildi',
        'shipped' => 'Kargoya verildi',
        'delivered' => 'Ulaştı / teslim edildi',
        'cancelled' => 'Iptal edildi',
    ];

    public function index(): string
    {
        $this->requireViewAccess();
        $q = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('durum');
        $employee = (int) $this->request->getGet('personel');
        $from = (string) $this->request->getGet('baslangic');
        $until = (string) $this->request->getGet('bitis');

        $model = new SalesDocumentModel();
        $model->select('sales_documents.*,customers.company_name,employees.full_name AS sales_employee_name')
            ->join('customers', 'customers.id=sales_documents.customer_id')
            ->join('employees', 'employees.id=sales_documents.sales_employee_id', 'left');

        $this->applyVisibility($model);

        if ($q !== '') {
            $model->groupStart()
                ->like('sales_documents.document_number', $q)
                ->orLike('customers.company_name', $q)
                ->groupEnd();
        }

        if (isset(self::STATUSES[$status])) {
            $model->where('sales_documents.status', $status);
        }

        if ($employee > 0 && $this->canViewAll()) {
            $model->where('sales_documents.sales_employee_id', $employee);
        }

        if ($from !== '') {
            $model->where('sales_documents.created_at >=', $from.' 00:00:00');
        }

        if ($until !== '') {
            $model->where('sales_documents.created_at <=', $until.' 23:59:59');
        }

        return view('panel/sales_documents/index', [
            'title' => 'Teklif ve Siparisler | FORMMIX',
            'pageTitle' => 'Teklif ve siparisler',
            'activeNav' => 'orders',
            'documents' => $model->orderBy('sales_documents.created_at', 'DESC')->findAll(),
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'employees' => $this->activeEmployees(),
            'q' => $q,
            'status' => $status,
            'employee' => $employee,
            'from' => $from,
            'until' => $until,
            'canCreate' => $this->canCreate(),
        ]);
    }

    public function create(): string
    {
        $this->requireCreate();
        $type = (string) $this->request->getGet('tur');
        if (! isset(self::TYPES[$type])) {
            $type = 'order';
        }

        $customerId = (int) $this->request->getGet('musteri');
        if ($customerId > 0) {
            $this->visibleCustomer($customerId);
        }

        return view('panel/sales_documents/form', $this->formData(null, $type, $customerId));
    }

    public function store(): RedirectResponse
    {
        $this->requireCreate();

        return $this->persist(null);
    }

    public function edit(int $id): string
    {
        $doc = $this->visibleDocument($id);
        $this->requireEditable($doc);

        return view('panel/sales_documents/form', $this->formData($doc, $doc['document_type'], (int) $doc['customer_id']));
    }

    public function update(int $id): RedirectResponse
    {
        $doc = $this->visibleDocument($id);
        $this->requireEditable($doc);

        return $this->persist($doc);
    }

    public function show(int $id): string
    {
        $doc = $this->visibleDocument($id);
        $items = (new SalesDocumentItemModel())->where('sales_document_id', $id)->orderBy('id')->findAll();
        $history = (new SalesDocumentStatusHistoryModel())->where('sales_document_id', $id)->orderBy('created_at', 'DESC')->findAll();

        return view('panel/sales_documents/show', [
            'title' => $doc['document_number'].' | FORMMIX',
            'pageTitle' => $doc['document_number'],
            'activeNav' => 'orders',
            'document' => $doc,
            'items' => $items,
            'history' => $history,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'canEdit' => $this->isEditable($doc),
            'canCancel' => $this->canCancel($doc),
            'canFulfill' => ($doc['document_type'] === 'order' && (auth()->user()?->can('orders.fulfill') ?? false)),
            'warehouses' => (new WarehouseModel())->where('is_active', 1)->orderBy('name')->findAll(),
            'savedReference' => (string) $this->request->getGet('kaydedildi'),
            'primaryContact' => $this->primaryContact((int) $doc['customer_id']),
        ]);
    }

    public function price(): ResponseInterface
    {
        $this->requireCreate();

        try {
            $customer = $this->visibleCustomer((int) $this->request->getGet('musteri'));
            $price = (new ProductPriceResolver())->resolve((int) $customer['id'], (int) $this->request->getGet('urun'), (int) $this->request->getGet('varyant'));

            return $this->response->setJSON([
                'ok' => true,
                'unit_price' => $price['unit_price'],
                'tax_rate' => $price['tax_rate'],
                'source' => $price['price_source'],
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    public function submit(int $id): RedirectResponse
    {
        $doc = $this->visibleDocument($id);
        $this->requireEditable($doc);

        if ($doc['document_type'] !== 'order') {
            return redirect()->back()->with('errors', ['submit' => 'Bu islem yalnizca siparisler icin gecerlidir.']);
        }

        if (trim((string) $doc['delivery_address']) === '') {
            return redirect()->back()->with('errors', ['submit' => 'Siparis olusturulmadan once teslimat adresi zorunludur.']);
        }

        (new SalesDocumentModel())->update($id, [
            'status' => 'approved',
            'approved_by_user_id' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->history($id, $doc['status'], 'approved', 'Siparis olusturuldu');
        (new AuditLogger())->record('order.created', 'sales_document', $id, ['status' => $doc['status']], ['status' => 'approved']);

        return redirect()->back()->with('message', 'Siparis olusturuldu.');
    }

    public function progress(int $id): RedirectResponse|ResponseInterface
    {
        $doc = $this->visibleDocument($id);
        if ($doc['document_type'] !== 'order') {
            throw PageNotFoundException::forPageNotFound();
        }

        $action = (string) $this->request->getPost('action');
        $notify = (int) $this->request->getPost('notify') === 1;

        try {
            if ($action === 'preparing') {
                $this->require('orders.fulfill');
                $result = (new StockService())->reserveOrder($id, (int) $this->request->getPost('warehouse_id'));
                $doc['status'] = $result['status'];
                $flash = $result['missing'] > 0
                    ? 'Siparis hazirlik surecine alindi; eksik urunler tedarik bekliyor.'
                    : 'Siparis hazirlik surecine alindi.';
            } elseif ($action === 'shipped') {
                $this->require('orders.fulfill');
                $result = (new StockService())->shipReserved($id, trim((string) $this->request->getPost('reason')));
                $doc['status'] = $result['status'];
                $flash = $result['status'] === 'partially_shipped'
                    ? 'Kismi sevkiyat kaydedildi.'
                    : 'Siparis kargoya verildi.';
            } elseif ($action === 'delivered') {
                if (! in_array($doc['status'], ['shipped', 'partially_shipped'], true)) {
                    throw new RuntimeException('Teslim isareti icin once sevkiyat kaydi yapilmalidir.');
                }

                (new SalesDocumentModel())->update($id, ['status' => 'delivered']);
                $this->history($id, $doc['status'], 'delivered', 'Siparis teslim edildi');
                (new AuditLogger())->record('order.delivered', 'sales_document', $id, ['status' => $doc['status']], ['status' => 'delivered']);
                $doc['status'] = 'delivered';
                $flash = 'Siparis teslim edildi olarak isaretlendi.';
            } else {
                throw new RuntimeException('Gecersiz surec islemi.');
            }
        } catch (Throwable $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->back()->withInput()->with('errors', ['progress' => $e->getMessage()]);
        }

        if (! $notify) {
            return redirect()->back()->with('message', $flash);
        }

        $contact = $this->primaryContact((int) $doc['customer_id']);
        if (! $contact || trim((string) ($contact['phone_normalized'] ?? '')) === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(422)->setJSON([
                    'ok' => false,
                    'message' => 'Musterinin birincil WhatsApp telefonu bulunamadi.',
                ]);
            }

            return redirect()->back()->with('errors', ['progress' => 'Musterinin birincil WhatsApp telefonu bulunamadi.']);
        }

        $whatsappUrl = whatsapp_contact_link(
            (string) $contact['phone_normalized'],
            $this->orderWhatsappMessage($doc, $action)
        );

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => $flash,
                'whatsapp_url' => $whatsappUrl,
            ]);
        }

        return redirect()->back()
            ->with('message', $flash)
            ->with('whatsappUrl', $whatsappUrl);
    }

    public function approve(int $id): RedirectResponse
    {
        return redirect()->back()->with('errors', ['approve' => 'Siparis onay akisı kaldirildi. Yeni surec butonlarini kullanin.']);
    }

    public function reject(int $id): RedirectResponse
    {
        return redirect()->back()->with('errors', ['reject' => 'Siparis onay akisı kaldirildi.']);
    }

    public function cancel(int $id): RedirectResponse
    {
        $doc = $this->visibleDocument($id);
        if (! $this->canCancel($doc)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('errors', ['cancel' => 'Iptal nedeni zorunludur.']);
        }

        $old = $doc['status'];
        $db = db_connect();
        $db->transBegin();

        try {
            if ($doc['document_type'] === 'order') {
                (new StockService())->releaseDocumentReservations($id);
            }

            (new SalesDocumentModel())->update($id, [
                'status' => 'cancelled',
                'cancelled_at' => date('Y-m-d H:i:s'),
                'cancellation_reason' => $reason,
            ]);
            $this->history($id, $old, 'cancelled', $reason);

            if (! $db->transStatus()) {
                throw new RuntimeException('Iptal kaydedilemedi.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with('errors', ['cancel' => $e->getMessage()]);
        }

        (new AuditLogger())->record('order.cancelled', 'sales_document', $id, ['status' => $old], ['status' => 'cancelled', 'reason' => $reason]);

        return redirect()->back()->with('message', 'Siparis iptal edildi; ayrilmis stok serbest birakildi.');
    }

    public function convert(int $id): RedirectResponse
    {
        $quote = $this->visibleDocument($id);
        $this->requireCreate();
        if ($quote['document_type'] !== 'quote' || $quote['status'] !== 'approved') {
            return redirect()->back()->with('errors', ['convert' => 'Yalnizca onayli teklif siparise cevrilebilir.']);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $data = array_intersect_key($quote, array_flip([
                'customer_id', 'customer_owner_employee_id', 'sales_employee_id', 'created_by_user_id',
                'currency', 'subtotal', 'discount_total', 'tax_total', 'grand_total',
                'notes', 'delivery_address', 'requested_delivery_date',
            ]));
            $data += [
                'document_number' => $this->newNumber('order'),
                'document_type' => 'order',
                'source_quote_id' => $id,
                'created_by_user_id' => auth()->id(),
                'approved_by_user_id' => auth()->id(),
                'approved_at' => date('Y-m-d H:i:s'),
                'status' => 'approved',
                'client_reference' => 'convert-'.bin2hex(random_bytes(16)),
            ];

            $model = new SalesDocumentModel();
            if (! $model->insert($data)) {
                throw new RuntimeException(implode(' ', $model->errors()));
            }

            $newId = (int) $model->getInsertID();

            foreach ((new SalesDocumentItemModel())->where('sales_document_id', $id)->findAll() as $item) {
                unset($item['id'], $item['created_at'], $item['updated_at']);
                $item['sales_document_id'] = $newId;
                (new SalesDocumentItemModel())->insert($item);
            }

            $this->history($newId, null, 'approved', 'Onayli '.$quote['document_number'].' teklifinden siparis olusturuldu');
            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with('errors', ['convert' => $e->getMessage()]);
        }

        (new AuditLogger())->record('quote.converted', 'sales_document', $id, null, ['order_id' => $newId]);

        return redirect()->to(site_url('panel/siparisler/'.$newId))->with('message', 'Teklif baglantisi korunarak siparis olusturuldu.');
    }

    private function persist(?array $document): RedirectResponse
    {
        $customer = $this->visibleCustomer((int) $this->request->getPost('customer_id'));
        $type = (string) $this->request->getPost('document_type');
        if (! isset(self::TYPES[$type])) {
            $type = 'order';
        }

        $reference = trim((string) $this->request->getPost('client_reference'));
        if ($reference === '') {
            return redirect()->back()->withInput()->with('errors', ['form' => 'Cihaz taslak kimligi eksik.']);
        }

        if ($document === null && ($existing = (new SalesDocumentModel())->where('client_reference', $reference)->first())) {
            return redirect()->to(site_url('panel/siparisler/'.$existing['id']))->with('message', 'Bu cihaz taslagi daha once merkeze gonderilmis; ikinci kayit olusturulmadi.');
        }

        $input = json_decode((string) $this->request->getPost('items_json'), true);
        if (! is_array($input) || $input === []) {
            return redirect()->back()->withInput()->with('errors', ['items' => 'En az bir urun ekleyin.']);
        }

        try {
            $prepared = $this->prepareItems((int) $customer['id'], $input);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('errors', ['items' => $e->getMessage()]);
        }

        $salesEmployee = $this->resolveSalesEmployee($customer);
        $intent = (string) $this->request->getPost('intent');
        $status = $type === 'order' && $intent === 'submit' ? 'approved' : 'draft';
        $approvedAt = $status === 'approved' ? date('Y-m-d H:i:s') : null;

        $data = [
            'document_number' => $document['document_number'] ?? $this->newNumber($type),
            'document_type' => $type,
            'source_quote_id' => $document['source_quote_id'] ?? null,
            'customer_id' => $customer['id'],
            'customer_owner_employee_id' => $customer['current_owner_employee_id'] ?: null,
            'sales_employee_id' => $salesEmployee,
            'created_by_user_id' => $document['created_by_user_id'] ?? auth()->id(),
            'approved_by_user_id' => $status === 'approved' ? auth()->id() : null,
            'status' => $status,
            'client_reference' => $reference,
            'currency' => 'TRY',
            'approved_at' => $approvedAt,
        ] + $prepared['totals'] + [
            'notes' => trim((string) $this->request->getPost('notes')) ?: null,
            'delivery_address' => trim((string) $this->request->getPost('delivery_address')) ?: null,
            'requested_delivery_date' => $this->dateOrNull((string) $this->request->getPost('requested_delivery_date')),
        ];

        if ($status === 'approved' && ! $data['delivery_address']) {
            return redirect()->back()->withInput()->with('errors', ['delivery' => 'Siparis olusturulurken teslimat adresi zorunludur.']);
        }

        $db = db_connect();
        $db->transBegin();
        $model = new SalesDocumentModel();

        try {
            if ($document === null) {
                if (! $model->insert($data)) {
                    throw new RuntimeException(implode(' ', $model->errors()));
                }

                $id = (int) $model->getInsertID();
                $oldStatus = null;
            } else {
                $id = (int) $document['id'];
                if (! $model->update($id, $data)) {
                    throw new RuntimeException(implode(' ', $model->errors()));
                }

                (new SalesDocumentItemModel())->where('sales_document_id', $id)->delete();
                $oldStatus = $document['status'];
            }

            foreach ($prepared['items'] as $item) {
                $item['sales_document_id'] = $id;
                if (! (new SalesDocumentItemModel())->insert($item)) {
                    throw new RuntimeException('Siparis satiri kaydedilemedi.');
                }
            }

            $this->history($id, $oldStatus, $status, $status === 'draft' ? 'Taslak kaydedildi' : 'Siparis olusturuldu');

            if (! $db->transStatus()) {
                throw new RuntimeException('Belge kaydedilemedi.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', ['form' => $e->getMessage()]);
        }

        $action = $type.'.'.($document ? 'updated' : 'created');
        (new AuditLogger())->record($action, 'sales_document', $id, $document, $data);

        return redirect()->to(site_url('panel/siparisler/'.$id).'?kaydedildi='.rawurlencode($reference))
            ->with('message', $status === 'draft' ? 'Taslak merkeze kaydedildi.' : 'Siparis olusturuldu.');
    }

    private function prepareItems(int $customerId, array $input): array
    {
        $resolver = new ProductPriceResolver();
        $calculator = new SalesDocumentCalculator();
        $items = [];
        $lines = [];

        foreach ($input as $row) {
            $quantity = $this->decimal($row['quantity'] ?? null);
            $discount = $this->decimal($row['discount_percent'] ?? 0) ?? 0;
            $resolved = $resolver->resolve($customerId, (int) ($row['product_id'] ?? 0), (int) ($row['product_variant_id'] ?? 0));

            if ($resolved['currency'] !== 'TRY') {
                throw new RuntimeException('Bu adimda yalnizca TRY fiyatli urunler ayni belgeye eklenebilir.');
            }

            $calc = $calculator->calculateLine((float) $quantity, (float) $resolved['unit_price'], (float) $discount, (float) $resolved['tax_rate']);
            $lines[] = $calc;
            $items[] = [
                'product_id' => $resolved['product']['id'],
                'product_variant_id' => $resolved['variant']['id'],
                'product_code_snapshot' => $resolved['product']['product_code'],
                'product_name_snapshot' => $resolved['product']['name'],
                'variant_snapshot' => $resolved['variant_label'],
                'quantity' => $quantity,
                'unit_price' => $resolved['unit_price'],
                'discount_percent' => $discount,
                'discount_amount' => $calc['discount_amount'],
                'net_amount' => $calc['net_amount'],
                'tax_rate' => $resolved['tax_rate'],
                'tax_amount' => $calc['tax_amount'],
                'line_total' => $calc['line_total'],
            ];
        }

        return ['items' => $items, 'totals' => $calculator->calculateDocument($lines)];
    }

    private function formData(?array $doc, string $type, int $customerId): array
    {
        $customerModel = new CustomerModel();
        if (! $this->canViewAll()) {
            $customerModel->where('current_owner_employee_id', $this->currentEmployeeId() ?? 0);
        }

        $customers = $customerModel->whereIn('status', ['candidate', 'active'])->orderBy('company_name')->findAll();
        $products = (new ProductModel())->where('is_active', 1)->orderBy('name')->findAll();
        $catalog = [];
        $variants = new ProductVariantModel();

        foreach ($products as $product) {
            foreach ($variants->where('product_id', $product['id'])->where('is_active', 1)->orderBy('size')->orderBy('color')->findAll() as $variant) {
                if ((float) ($variant['list_price_override'] ?? 0) <= 0 && (float) $product['list_price'] <= 0 && ! $this->hasSpecialPrice((int) $product['id'])) {
                    continue;
                }

                $catalog[] = [
                    'product_id' => (int) $product['id'],
                    'variant_id' => (int) $variant['id'],
                    'name' => $product['name'],
                    'code' => $product['product_code'],
                    'sku' => $variant['sku'],
                    'variant' => implode(' / ', array_filter([$variant['size'], $variant['color']])),
                    'image' => $product['image_path'] ? base_url($product['image_path']) : '',
                ];
            }
        }

        $items = $doc ? (new SalesDocumentItemModel())->where('sales_document_id', $doc['id'])->findAll() : [];

        return [
            'title' => ($doc ? 'Belgeyi duzenle' : 'Yeni '.mb_strtolower(self::TYPES[$type])).' | FORMMIX',
            'pageTitle' => $doc ? 'Belgeyi duzenle' : 'Yeni '.mb_strtolower(self::TYPES[$type]),
            'activeNav' => 'orders',
            'document' => $doc,
            'documentType' => $type,
            'customerId' => $customerId,
            'customers' => $customers,
            'employees' => $this->activeEmployees(),
            'canChooseEmployee' => $this->canViewAll(),
            'catalog' => $catalog,
            'items' => $items,
            'clientReference' => $doc['client_reference'] ?? '',
        ];
    }

    private function visibleCustomer(int $id): array
    {
        $customer = (new CustomerModel())->find($id);
        if (! $customer || (! $this->canViewAll() && (int) ($customer['current_owner_employee_id'] ?? 0) !== (int) ($this->currentEmployeeId() ?? 0))) {
            throw PageNotFoundException::forPageNotFound('Musteri bulunamadi.');
        }

        return $customer;
    }

    private function visibleDocument(int $id): array
    {
        $model = new SalesDocumentModel();
        $model->select('sales_documents.*,customers.company_name,employees.full_name AS sales_employee_name')
            ->join('customers', 'customers.id=sales_documents.customer_id')
            ->join('employees', 'employees.id=sales_documents.sales_employee_id', 'left');
        $this->applyVisibility($model);
        $doc = $model->find($id);

        if (! $doc) {
            throw PageNotFoundException::forPageNotFound('Belge bulunamadi.');
        }

        return $doc;
    }

    private function applyVisibility(SalesDocumentModel $model): void
    {
        if (! $this->canViewAll()) {
            $model->where('sales_documents.sales_employee_id', $this->currentEmployeeId() ?? 0);
        }

        if ((auth()->user()?->can('orders.fulfill') ?? false) && ! (auth()->user()?->can('orders.approve') ?? false)) {
            $model->where('sales_documents.document_type', 'order')
                ->whereIn('sales_documents.status', ['approved', 'procurement_waiting', 'reserved', 'partially_shipped', 'shipped', 'delivered']);
        }
    }

    private function resolveSalesEmployee(array $customer): ?int
    {
        $requested = (int) $this->request->getPost('sales_employee_id');
        if ($this->canViewAll() && $requested > 0 && (new EmployeeModel())->where('is_active', 1)->find($requested)) {
            return $requested;
        }

        return $customer['current_owner_employee_id']
            ? (int) $customer['current_owner_employee_id']
            : $this->currentEmployeeId();
    }

    private function history(int $id, ?string $old, string $new, ?string $reason): void
    {
        (new SalesDocumentStatusHistoryModel())->insert([
            'sales_document_id' => $id,
            'old_status' => $old,
            'new_status' => $new,
            'reason' => $reason,
            'changed_by_user_id' => auth()->id(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function currentEmployeeId(): ?int
    {
        $row = (new EmployeeModel())->where('user_id', auth()->id())->where('is_active', 1)->first();

        return $row ? (int) $row['id'] : null;
    }

    private function canViewAll(): bool
    {
        return auth()->user()?->can('orders.view-all') ?? false;
    }

    private function canCreate(): bool
    {
        return auth()->user()?->can('orders.create') ?? false;
    }

    private function canCancel(array $doc): bool
    {
        if (in_array($doc['status'], ['partially_shipped', 'shipped', 'delivered', 'cancelled'], true)) {
            return false;
        }

        if (in_array($doc['status'], ['reserved', 'procurement_waiting', 'partially_shipped'], true)) {
            return auth()->user()?->can('orders.approve-high') ?? false;
        }

        return (auth()->user()?->can('orders.approve') ?? false)
            || ($doc['status'] === 'draft' && (int) $doc['created_by_user_id'] === (int) auth()->id())
            || ($doc['status'] === 'approved' && ($this->canCreate() || (auth()->user()?->can('orders.approve-high') ?? false)));
    }

    private function isEditable(array $doc): bool
    {
        return $doc['status'] === 'draft' && ($this->canViewAll() || (int) $doc['created_by_user_id'] === (int) auth()->id());
    }

    private function requireEditable(array $doc): void
    {
        if (! $this->isEditable($doc) || ! $this->canCreate()) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function requireCreate(): void
    {
        if (! $this->canCreate()) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function requireViewAccess(): void
    {
        if (! $this->canCreate() && ! $this->canViewAll() && ! (auth()->user()?->can('orders.fulfill') ?? false)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function activeEmployees(): array
    {
        return (new EmployeeModel())->where('is_active', 1)->orderBy('full_name')->findAll();
    }

    private function hasSpecialPrice(int $id): bool
    {
        return db_connect()->table('product_special_prices')
            ->where(['product_id' => $id, 'is_active' => 1, 'deleted_at' => null])
            ->countAllResults() > 0;
    }

    private function decimal(mixed $value): ?float
    {
        $value = str_replace(',', '.', trim((string) $value));

        return $value !== '' && is_numeric($value) ? (float) $value : null;
    }

    private function dateOrNull(string $value): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function newNumber(string $type): string
    {
        $prefix = $type === 'quote' ? 'TEK' : 'SIP';

        do {
            $number = $prefix.'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));
        } while ((new SalesDocumentModel())->where('document_number', $number)->first());

        return $number;
    }

    private function primaryContact(int $customerId): ?array
    {
        return db_connect()->table('customer_contacts')
            ->where('customer_id', $customerId)
            ->where('deleted_at', null)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id')
            ->get()
            ->getFirstRow('array');
    }

    private function orderWhatsappMessage(array $doc, string $action): string
    {
        $base = 'Merhaba, '.$doc['company_name'].' yetkilisi. '.$doc['document_number'].' numarali siparisinizle ilgili guncelleme paylasiyoruz.';

        return match ($action) {
            'preparing' => $base."\n\nSiparisiniz hazirlik surecine alinmistir.",
            'shipped' => $base."\n\nSiparisiniz kargoya / sevke verilmistir.",
            'delivered' => $base."\n\nSiparisiniz size ulasti / teslim edildi olarak kaydedilmistir.",
            default => $base,
        };
    }

    private function require(string $permission): void
    {
        if (! (auth()->user()?->can($permission) ?? false)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }
}
