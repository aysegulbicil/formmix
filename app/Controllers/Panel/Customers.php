<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\CustomerActivityModel;
use App\Models\CustomerAssignmentModel;
use App\Models\CustomerContactModel;
use App\Models\CustomerModel;
use App\Models\EmployeeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

class Customers extends BaseController
{
    private const STATUSES = ['candidate' => 'Aday', 'active' => 'Aktif', 'passive' => 'Pasif'];
    private const ACTIVITIES = ['call' => 'Telefon', 'visit' => 'Ziyaret', 'note' => 'Not', 'follow_up' => 'Takip'];

    public function index(): string
    {
        $this->requireCustomerAccess();
        $search = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('durum');
        $owner  = (int) $this->request->getGet('sorumlu');
        $model  = new CustomerModel();

        $model->select('customers.*, employees.full_name AS owner_name, customer_contacts.full_name AS contact_name, customer_contacts.phone AS contact_phone')
            ->join('employees', 'employees.id = customers.current_owner_employee_id', 'left')
            ->join('customer_contacts', 'customer_contacts.customer_id = customers.id AND customer_contacts.is_primary = 1 AND customer_contacts.deleted_at IS NULL', 'left');

        if (! $this->canViewAll()) {
            $model->where('customers.current_owner_employee_id', $this->currentEmployeeId() ?? 0);
        }
        if ($search !== '') {
            $model->groupStart()->like('customers.company_name', $search)->orLike('customers.customer_code', $search)
                ->orLike('customer_contacts.full_name', $search)->orLike('customer_contacts.phone', $search)->groupEnd();
        }
        if (isset(self::STATUSES[$status])) {
            $model->where('customers.status', $status);
        }
        if ($owner > 0 && $this->canViewAll()) {
            $model->where('customers.current_owner_employee_id', $owner);
        }

        $customers = $model->orderBy('customers.updated_at', 'DESC')->findAll();
        $scope      = new CustomerModel();
        if (! $this->canViewAll()) {
            $scope->where('current_owner_employee_id', $this->currentEmployeeId() ?? 0);
        }
        $all = $scope->findAll();

        return view('panel/customers/index', [
            'title' => 'Müşteriler | FORMMIX', 'pageTitle' => 'Müşteriler', 'activeNav' => 'customers',
            'customers' => $customers, 'search' => $search, 'status' => $status, 'owner' => $owner,
            'statuses' => self::STATUSES, 'employees' => $this->activeEmployees(), 'canViewAll' => $this->canViewAll(),
            'stats' => ['total' => count($all), 'active' => count(array_filter($all, static fn ($x) => $x['status'] === 'active')), 'unassigned' => count(array_filter($all, static fn ($x) => empty($x['current_owner_employee_id'])))],
        ]);
    }

    public function create(): string
    {
        return view('panel/customers/form', $this->formData(null));
    }

    public function store(): RedirectResponse
    {
        return $this->persist(null);
    }

    public function show(int $id): string
    {
        $customer = $this->visibleCustomer($id);
        $contact  = (new CustomerContactModel())->where('customer_id', $id)->where('is_primary', 1)->first();
        $assignments = (new CustomerAssignmentModel())->select('customer_assignments.*, employees.full_name AS employee_name')
            ->join('employees', 'employees.id = customer_assignments.employee_id')->where('customer_id', $id)->orderBy('started_at', 'DESC')->findAll();
        $activities = (new CustomerActivityModel())->select('customer_activities.*, employees.full_name AS employee_name')
            ->join('employees', 'employees.id = customer_activities.employee_id', 'left')->where('customer_id', $id)->orderBy('happened_at', 'DESC')->findAll();

        return view('panel/customers/show', [
            'title' => $customer['company_name'] . ' | FORMMIX', 'pageTitle' => $customer['company_name'], 'activeNav' => 'customers',
            'customer' => $customer, 'contact' => $contact, 'assignments' => $assignments, 'activities' => $activities,
            'statuses' => self::STATUSES, 'activityTypes' => self::ACTIVITIES, 'employees' => $this->activeEmployees(),
            'canAssign' => auth()->user()?->can('customers.assign') ?? false, 'canAddActivity' => $this->canAddActivity($customer),
        ]);
    }

    public function edit(int $id): string
    {
        return view('panel/customers/form', $this->formData($this->visibleCustomer($id)));
    }

    public function update(int $id): RedirectResponse
    {
        return $this->persist($this->visibleCustomer($id));
    }

    public function duplicateCheck(): ResponseInterface
    {
        $phone = $this->normalizePhone((string) $this->request->getGet('telefon'));
        $tax   = $this->normalizeTax((string) $this->request->getGet('vergi'));
        $exclude = (int) $this->request->getGet('haric');
        $matches = $this->duplicates($phone, $tax, $exclude ?: null);
        $private = ! $this->canViewAll();

        return $this->response->setJSON([
            'duplicate' => $matches !== [],
            'message' => $matches === [] ? '' : ($private ? 'Bu telefon veya vergi numarasıyla kayıtlı ve atanmış bir müşteri bulunuyor.' : 'Benzer kayıt bulundu: ' . implode(', ', array_column($matches, 'company_name'))),
        ]);
    }

    public function assign(int $id): RedirectResponse
    {
        $customer = $this->visibleCustomer($id);
        $employeeId = (int) $this->request->getPost('employee_id');
        $reason = trim((string) $this->request->getPost('reason'));
        $employee = (new EmployeeModel())->where('is_active', 1)->find($employeeId);
        if ($employee === null) {
            return redirect()->back()->with('errors', ['assignment' => 'Etkin bir sorumlu personel seçin.']);
        }
        if ((int) ($customer['current_owner_employee_id'] ?? 0) === $employeeId) {
            return redirect()->back()->with('errors', ['assignment' => 'Seçilen personel zaten bu müşterinin sorumlusu.']);
        }
        if (! empty($customer['current_owner_employee_id']) && $reason === '') {
            return redirect()->back()->with('errors', ['assignment' => 'Müşteri devrinde neden zorunludur.']);
        }

        $db = db_connect(); $db->transBegin(); $now = date('Y-m-d H:i:s');
        try {
            (new CustomerAssignmentModel())->where('customer_id', $id)->where('ended_at', null)->set(['ended_at' => $now])->update();
            (new CustomerAssignmentModel())->insert(['customer_id' => $id, 'employee_id' => $employeeId, 'started_at' => $now, 'reason' => $reason ?: 'İlk sorumlu ataması', 'assigned_by_user_id' => auth()->id(), 'created_at' => $now]);
            (new CustomerModel())->update($id, ['current_owner_employee_id' => $employeeId]);
            if ($db->transStatus() === false) { throw new \RuntimeException('Atama kaydedilemedi.'); }
            $db->transCommit();
        } catch (Throwable $e) { $db->transRollback(); return redirect()->back()->with('errors', ['assignment' => $e->getMessage()]); }

        (new AuditLogger())->record('customer.assigned', 'customer', $id, ['employee_id' => $customer['current_owner_employee_id']], ['employee_id' => $employeeId, 'reason' => $reason]);
        return redirect()->to(site_url('panel/musteriler/' . $id))->with('message', 'Müşteri sorumlusu güncellendi.');
    }

    public function addActivity(int $id): RedirectResponse
    {
        $customer = $this->visibleCustomer($id);
        if (! $this->canAddActivity($customer)) { throw PageNotFoundException::forPageNotFound(); }
        $type = (string) $this->request->getPost('activity_type');
        $subject = trim((string) $this->request->getPost('subject'));
        if (! isset(self::ACTIVITIES[$type]) || $subject === '') {
            return redirect()->back()->with('errors', ['activity' => 'Görüşme türü ve konu zorunludur.']);
        }
        $data = ['customer_id' => $id, 'employee_id' => $this->currentEmployeeId(), 'activity_type' => $type, 'subject' => $subject,
            'notes' => trim((string) $this->request->getPost('notes')) ?: null, 'happened_at' => date('Y-m-d H:i:s'),
            'next_action_at' => $this->dateOrNull((string) $this->request->getPost('next_action_at')), 'created_by_user_id' => auth()->id()];
        $model = new CustomerActivityModel(); $activityId = $model->insert($data);
        (new CustomerModel())->update($id, ['last_activity_at' => $data['happened_at']]);
        (new AuditLogger())->record('customer.activity_created', 'customer_activity', $activityId, null, $data);
        return redirect()->to(site_url('panel/musteriler/' . $id))->with('message', 'Görüşme kaydı eklendi.');
    }

    private function persist(?array $customer): RedirectResponse
    {
        $id = $customer['id'] ?? null;
        $phone = trim((string) $this->request->getPost('contact_phone'));
        $tax = trim((string) $this->request->getPost('tax_number'));
        if ($this->duplicates($this->normalizePhone($phone), $this->normalizeTax($tax), $id ? (int) $id : null) !== []) {
            return redirect()->back()->withInput()->with('errors', ['duplicate' => 'Bu telefon veya vergi numarasıyla benzer bir müşteri zaten kayıtlı.']);
        }
        $data = ['id' => $id, 'customer_code' => $customer['customer_code'] ?? $this->newCustomerCode(),
            'company_name' => trim((string) $this->request->getPost('company_name')), 'official_title' => trim((string) $this->request->getPost('official_title')) ?: null,
            'email' => strtolower(trim((string) $this->request->getPost('email'))) ?: null, 'city' => trim((string) $this->request->getPost('city')), 'district' => trim((string) $this->request->getPost('district')),
            'address' => trim((string) $this->request->getPost('address')) ?: null, 'delivery_address' => trim((string) $this->request->getPost('delivery_address')) ?: null,
            'billing_address' => trim((string) $this->request->getPost('billing_address')) ?: null, 'tax_office' => trim((string) $this->request->getPost('tax_office')) ?: null,
            'tax_number' => $tax ?: null, 'tax_number_normalized' => $this->normalizeTax($tax) ?: null, 'status' => isset(self::STATUSES[(string) $this->request->getPost('status')]) ? (string) $this->request->getPost('status') : 'candidate',
            'payment_term_days' => (int) $this->request->getPost('payment_term_days'), 'credit_limit' => str_replace(',', '.', trim((string) $this->request->getPost('credit_limit'))) ?: null,
            'current_owner_employee_id' => $customer['current_owner_employee_id'] ?? null, 'created_by_user_id' => $customer['created_by_user_id'] ?? auth()->id()];
        $contact = ['full_name' => trim((string) $this->request->getPost('contact_name')), 'job_title' => trim((string) $this->request->getPost('contact_job_title')) ?: null,
            'phone' => $phone, 'phone_normalized' => $this->normalizePhone($phone), 'email' => strtolower(trim((string) $this->request->getPost('contact_email'))) ?: null, 'is_primary' => 1, 'is_active' => 1];
        if ($contact['full_name'] === '' || $contact['phone_normalized'] === '') {
            return redirect()->back()->withInput()->with('errors', ['contact' => 'Yetkili kişi ve telefon zorunludur.']);
        }

        $requestedOwner = (int) $this->request->getPost('current_owner_employee_id');
        if ($id === null) {
            $ownEmployee = $this->currentEmployeeId();
            if (! $this->canViewAll() && $ownEmployee) { $data['current_owner_employee_id'] = $ownEmployee; }
            elseif (auth()->user()?->can('customers.assign') && $requestedOwner > 0) { $data['current_owner_employee_id'] = $requestedOwner; }
        }

        $db = db_connect(); $db->transBegin(); $model = new CustomerModel(); $contactModel = new CustomerContactModel();
        try {
            if ($id === null) { unset($data['id']); if (! $model->insert($data)) throw new \RuntimeException(implode(' ', $model->errors())); $id = (int) $model->getInsertID(); }
            elseif (! $model->update($id, $data)) throw new \RuntimeException(implode(' ', $model->errors()));
            $contact['customer_id'] = $id; $existingContact = $contactModel->where('customer_id', $id)->where('is_primary', 1)->first();
            if ($existingContact) { if (! $contactModel->update($existingContact['id'], $contact)) throw new \RuntimeException(implode(' ', $contactModel->errors())); }
            elseif (! $contactModel->insert($contact)) throw new \RuntimeException(implode(' ', $contactModel->errors()));
            if ($customer === null && ! empty($data['current_owner_employee_id'])) {
                (new CustomerAssignmentModel())->insert(['customer_id' => $id, 'employee_id' => $data['current_owner_employee_id'], 'started_at' => date('Y-m-d H:i:s'), 'reason' => 'İlk müşteri kaydı', 'assigned_by_user_id' => auth()->id(), 'created_at' => date('Y-m-d H:i:s')]);
            }
            if ($db->transStatus() === false) throw new \RuntimeException('Müşteri kaydı yazılamadı.'); $db->transCommit();
        } catch (Throwable $e) { $db->transRollback(); return redirect()->back()->withInput()->with('errors', ['form' => $e->getMessage()]); }
        $fresh = (new CustomerModel())->find($id); (new AuditLogger())->record($customer ? 'customer.updated' : 'customer.created', 'customer', $id, $customer, $fresh);
        return redirect()->to(site_url('panel/musteriler/' . $id))->with('message', $customer ? 'Müşteri bilgileri güncellendi.' : 'Müşteri kaydı oluşturuldu.');
    }

    private function formData(?array $customer): array
    {
        $contact = $customer ? (new CustomerContactModel())->where('customer_id', $customer['id'])->where('is_primary', 1)->first() : null;
        return ['title' => ($customer ? 'Müşteri Düzenle' : 'Yeni Müşteri') . ' | FORMMIX', 'pageTitle' => $customer ? 'Müşteri düzenle' : 'Yeni müşteri', 'activeNav' => 'customers',
            'customer' => $customer, 'contact' => $contact, 'statuses' => self::STATUSES, 'employees' => $this->activeEmployees(), 'canAssign' => auth()->user()?->can('customers.assign') ?? false];
    }

    private function visibleCustomer(int $id): array
    {
        $this->requireCustomerAccess(); $customer = (new CustomerModel())->select('customers.*, employees.full_name AS owner_name')->join('employees', 'employees.id = customers.current_owner_employee_id', 'left')->find($id);
        if ($customer === null || (! $this->canViewAll() && (int) ($customer['current_owner_employee_id'] ?? 0) !== (int) ($this->currentEmployeeId() ?? 0))) throw PageNotFoundException::forPageNotFound('Müşteri bulunamadı.');
        return $customer;
    }

    private function duplicates(string $phone, string $tax, ?int $exclude): array
    {
        if ($phone === '' && $tax === '') return [];
        $builder = db_connect()->table('customers')->select('customers.id, customers.company_name')->distinct()->join('customer_contacts', 'customer_contacts.customer_id = customers.id AND customer_contacts.deleted_at IS NULL', 'left')->where('customers.deleted_at', null)->groupStart();
        if ($phone !== '') $builder->where('customer_contacts.phone_normalized', $phone);
        if ($tax !== '') { if ($phone !== '') $builder->orWhere('customers.tax_number_normalized', $tax); else $builder->where('customers.tax_number_normalized', $tax); }
        $builder->groupEnd(); if ($exclude) $builder->where('customers.id !=', $exclude); return $builder->get()->getResultArray();
    }

    private function activeEmployees(): array { return (new EmployeeModel())->where('is_active', 1)->orderBy('full_name')->findAll(); }
    private function currentEmployeeId(): ?int { $row = (new EmployeeModel())->where('user_id', auth()->id())->where('is_active', 1)->first(); return $row ? (int) $row['id'] : null; }
    private function canViewAll(): bool { return auth()->user()?->can('customers.view-all') ?? false; }
    private function requireCustomerAccess(): void { if (! $this->canViewAll() && ! (auth()->user()?->can('customers.view-own') ?? false)) throw PageNotFoundException::forPageNotFound(); }
    private function canAddActivity(array $customer): bool { return (auth()->user()?->can('visits.view-all') ?? false) || ((auth()->user()?->can('visits.manage-own') ?? false) && (int) ($customer['current_owner_employee_id'] ?? 0) === (int) ($this->currentEmployeeId() ?? 0)); }
    private function normalizePhone(string $value): string { return preg_replace('/\D+/', '', $value) ?? ''; }
    private function normalizeTax(string $value): string { return mb_strtoupper(preg_replace('/[^\pL\pN]+/u', '', $value) ?? ''); }
    private function dateOrNull(string $value): ?string { if ($value === '') return null; $time = strtotime($value); return $time ? date('Y-m-d H:i:s', $time) : null; }
    private function newCustomerCode(): string { do { $code = 'MUS-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2))); } while ((new CustomerModel())->where('customer_code', $code)->first()); return $code; }
}
