<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\AuditLogger;
use App\Models\CustomerActivityModel;
use App\Models\CustomerAssignmentModel;
use App\Models\CustomerContactModel;
use App\Models\CustomerModel;
use App\Models\SalesDocumentModel;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

final class CustomersController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked;
        if (! auth()->user()?->can('customers.view-all') && ! auth()->user()?->can('customers.view-own')) {
            return $this->error('FORBIDDEN', 'Musteri erisiminiz bulunmuyor.', 403);
        }

        [$page, $perPage] = $this->pagination();
        $model = (new CustomerModel())
            ->select('customers.*,employees.full_name owner_name,customer_contacts.full_name contact_name,customer_contacts.phone contact_phone,customer_contacts.email contact_email')
            ->join('employees', 'employees.id=customers.current_owner_employee_id', 'left')
            ->join('customer_contacts', 'customer_contacts.customer_id=customers.id AND customer_contacts.is_primary=1 AND customer_contacts.deleted_at IS NULL', 'left');
        if (! auth()->user()?->can('customers.view-all')) $model->where('customers.current_owner_employee_id', $this->employee()['id']);
        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') $model->groupStart()->like('customers.company_name', $q)->orLike('customers.customer_code', $q)->orLike('customer_contacts.phone', $q)->groupEnd();
        $status = (string) $this->request->getGet('status');
        if (in_array($status, ['candidate', 'active', 'passive'], true)) $model->where('customers.status', $status);
        $total = (clone $model)->countAllResults(false);
        $rows = $model->orderBy('customers.updated_at', 'DESC')->findAll($perPage, ($page - 1) * $perPage);

        return $this->ok($rows, ['page'=>$page, 'per_page'=>$perPage, 'total'=>$total, 'last_page'=>(int) ceil($total / $perPage)]);
    }

    public function show(int $id): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked;
        $customer = $this->visible($id);
        if (! $customer) return $this->error('NOT_FOUND', 'Musteri bulunamadi.', 404);
        $customer['contacts'] = (new CustomerContactModel())->where('customer_id', $id)->where('is_active', 1)->findAll();
        $customer['activities'] = (new CustomerActivityModel())->where('customer_id', $id)->orderBy('happened_at', 'DESC')->findAll(100);
        $customer['sales_documents'] = (new SalesDocumentModel())->select('id,document_number,document_type,status,grand_total,created_at')->where('customer_id', $id)->orderBy('created_at', 'DESC')->findAll(50);
        return $this->ok($customer);
    }

    public function duplicateCheck(): ResponseInterface
    {
        if ($blocked = $this->guard('customers.create')) return $blocked;
        $input = $this->input();
        $matches = $this->duplicates($this->phone((string) ($input['phone'] ?? '')), $this->tax((string) ($input['tax_number'] ?? '')), (int) ($input['exclude_id'] ?? 0));
        return $this->ok(['duplicate'=>$matches !== [], 'matches'=>auth()->user()?->can('customers.view-all') ? $matches : []]);
    }

    public function create(): ResponseInterface
    {
        if ($blocked = $this->guard('customers.create')) return $blocked;
        if ($replay = $this->replay('customer.create')) return $replay;
        if ($this->request->getHeaderLine('Idempotency-Key') === '') return $this->error('IDEMPOTENCY_KEY_REQUIRED', 'Idempotency-Key zorunludur.', 400);
        return $this->save(null);
    }

    public function update(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('customers.create')) return $blocked;
        $customer = $this->visible($id);
        if (! $customer) return $this->error('NOT_FOUND', 'Musteri bulunamadi.', 404);
        return $this->save($customer);
    }

    public function activity(int $id): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked;
        if (! $this->visible($id)) return $this->error('NOT_FOUND', 'Musteri bulunamadi.', 404);
        if ($replay = $this->replay('customer.activity')) return $replay;
        if ($this->request->getHeaderLine('Idempotency-Key') === '') return $this->error('IDEMPOTENCY_KEY_REQUIRED', 'Idempotency-Key zorunludur.', 400);
        $input = $this->input();
        $type = (string) ($input['activity_type'] ?? '');
        $subject = trim((string) ($input['subject'] ?? ''));
        if (! in_array($type, ['call', 'visit', 'note', 'follow_up'], true) || $subject === '') return $this->error('VALIDATION_FAILED', 'Gorusme turu ve konu zorunludur.', 422);
        $data = ['customer_id'=>$id, 'employee_id'=>$this->employee()['id'], 'activity_type'=>$type, 'subject'=>$subject, 'notes'=>trim((string)($input['notes']??'')) ?: null, 'happened_at'=>date('Y-m-d H:i:s'), 'next_action_at'=>$this->date($input['next_action_at']??null), 'created_by_user_id'=>auth()->id()];
        $activityId = (int) (new CustomerActivityModel())->insert($data, true);
        (new CustomerModel())->update($id, ['last_activity_at'=>$data['happened_at']]);
        (new AuditLogger())->record('customer.activity_created', 'customer_activity', $activityId, null, $data);
        $body = ['data'=>['id'=>$activityId] + $data];
        $this->remember('customer.activity', 'customer_activity', $activityId, $body);
        return $this->response->setStatusCode(201)->setJSON($body);
    }

    public function assignment(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('customers.assign')) return $blocked;
        $customer = (new CustomerModel())->find($id);
        if (! $customer) return $this->error('NOT_FOUND', 'Müşteri bulunamadı.', 404);
        $input = $this->input(); $employeeId = (int) ($input['employee_id'] ?? 0); $reason = trim((string) ($input['reason'] ?? ''));
        $employee = (new \App\Models\EmployeeModel())->where('is_active', 1)->find($employeeId);
        if (! $employee || $reason === '') return $this->error('VALIDATION_FAILED', 'Etkin personel ve devir gerekçesi zorunludur.', 422, ['employee_id' => 'Etkin personel seçin.', 'reason' => 'Gerekçe zorunludur.']);
        $db = db_connect(); $db->transBegin();
        try {
            (new CustomerAssignmentModel())->where('customer_id', $id)->where('ended_at', null)->set(['ended_at' => date('Y-m-d H:i:s')])->update();
            (new CustomerAssignmentModel())->insert(['customer_id' => $id, 'employee_id' => $employeeId, 'started_at' => date('Y-m-d H:i:s'), 'reason' => $reason, 'assigned_by_user_id' => auth()->id(), 'created_at' => date('Y-m-d H:i:s')]);
            (new CustomerModel())->update($id, ['current_owner_employee_id' => $employeeId]);
            if (! $db->transStatus()) throw new RuntimeException('Müşteri devredilemedi.'); $db->transCommit();
        } catch (Throwable $e) { $db->transRollback(); return $this->error('ASSIGNMENT_FAILED', $e->getMessage(), 422); }
        (new AuditLogger())->record('customer.assigned', 'customer', $id, ['employee_id' => $customer['current_owner_employee_id'] ?? null], ['employee_id' => $employeeId, 'reason' => $reason]);
        return $this->ok(['id' => $id, 'employee_id' => $employeeId]);
    }

    private function save(?array $customer): ResponseInterface
    {
        $input = $this->input();
        if ($customer && ($input['expected_updated_at'] ?? '') !== $customer['updated_at']) return $this->error('STALE_RESOURCE', 'Kayit baska bir cihazda degistirilmis. Yenileyip tekrar deneyin.', 409);
        $phone = trim((string) ($input['contact_phone'] ?? ''));
        $tax = trim((string) ($input['tax_number'] ?? ''));
        if ($this->duplicates($this->phone($phone), $this->tax($tax), (int) ($customer['id'] ?? 0)) !== []) return $this->error('DUPLICATE_CUSTOMER', 'Bu telefon veya vergi numarasiyla benzer kayit var.', 409);
        $employee = $this->employee();
        $data = [
            'customer_code'=>$customer['customer_code'] ?? $this->newCode(), 'company_name'=>trim((string)($input['company_name']??'')),
            'official_title'=>trim((string)($input['official_title']??'')) ?: null, 'email'=>strtolower(trim((string)($input['email']??''))) ?: null,
            'city'=>trim((string)($input['city']??'')), 'district'=>trim((string)($input['district']??'')), 'address'=>trim((string)($input['address']??'')) ?: null,
            'delivery_address'=>trim((string)($input['delivery_address']??'')) ?: null, 'billing_address'=>trim((string)($input['billing_address']??'')) ?: null,
            'tax_office'=>trim((string)($input['tax_office']??'')) ?: null, 'tax_number'=>$tax ?: null, 'tax_number_normalized'=>$this->tax($tax) ?: null,
            'status'=>in_array(($input['status']??''), ['candidate','active','passive'], true) ? $input['status'] : 'candidate',
            'payment_term_days'=>(int)($input['payment_term_days']??0), 'credit_limit'=>(float)($input['credit_limit']??0),
            'current_owner_employee_id'=>$customer['current_owner_employee_id'] ?? $employee['id'], 'created_by_user_id'=>$customer['created_by_user_id'] ?? auth()->id(),
        ];
        $contact = ['full_name'=>trim((string)($input['contact_name']??'')), 'job_title'=>trim((string)($input['contact_job_title']??'')) ?: null, 'phone'=>$phone, 'phone_normalized'=>$this->phone($phone), 'email'=>strtolower(trim((string)($input['contact_email']??''))) ?: null, 'is_primary'=>1, 'is_active'=>1];
        if ($data['company_name'] === '' || $data['city'] === '' || $data['district'] === '' || $contact['full_name'] === '' || $contact['phone_normalized'] === '') return $this->error('VALIDATION_FAILED', 'Firma, il, ilce, yetkili ve telefon zorunludur.', 422);

        $db = db_connect(); $db->transBegin();
        try {
            $model = new CustomerModel();
            if ($customer) { if (! $model->update($customer['id'], $data)) throw new RuntimeException(implode(' ', $model->errors())); $id = (int) $customer['id']; }
            else { if (! $model->insert($data)) throw new RuntimeException(implode(' ', $model->errors())); $id = (int) $model->getInsertID(); }
            $contacts = new CustomerContactModel(); $old = $contacts->where('customer_id', $id)->where('is_primary', 1)->first(); $contact['customer_id'] = $id;
            $old ? $contacts->update($old['id'], $contact) : $contacts->insert($contact);
            if (! $customer) (new CustomerAssignmentModel())->insert(['customer_id'=>$id, 'employee_id'=>$data['current_owner_employee_id'], 'started_at'=>date('Y-m-d H:i:s'), 'reason'=>'Mobil musteri kaydi', 'assigned_by_user_id'=>auth()->id(), 'created_at'=>date('Y-m-d H:i:s')]);
            if (! $db->transStatus()) throw new RuntimeException('Musteri kaydedilemedi.');
            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            return $this->error('SAVE_FAILED', $e->getMessage(), 422);
        }
        $fresh = (new CustomerModel())->find($id);
        (new AuditLogger())->record($customer ? 'customer.updated' : 'customer.created', 'customer', $id, $customer, $fresh);
        $body = ['data'=>$fresh];
        if (! $customer) $this->remember('customer.create', 'customer', $id, $body);
        return $this->response->setStatusCode($customer ? 200 : 201)->setJSON($body);
    }

    private function visible(int $id): ?array { $row=(new CustomerModel())->find($id); if(!$row)return null; if(!auth()->user()?->can('customers.view-all') && (int)($row['current_owner_employee_id']??0)!==(int)$this->employee()['id'])return null; return $row; }
    private function phone(string $value): string { return preg_replace('/\D+/', '', $value) ?? ''; }
    private function tax(string $value): string { return mb_strtoupper(preg_replace('/[^\pL\pN]+/u', '', $value) ?? ''); }
    private function date(mixed $value): ?string { if (! $value) return null; $time=strtotime((string)$value); return $time ? date('Y-m-d H:i:s', $time) : null; }
    private function newCode(): string { do { $code='MUS-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(2))); } while ((new CustomerModel())->where('customer_code', $code)->first()); return $code; }
    private function duplicates(string $phone, string $tax, int $exclude=0): array
    {
        if ($phone === '' && $tax === '') return [];
        $builder=db_connect()->table('customers')->select('customers.id,customers.company_name')->distinct()->join('customer_contacts','customer_contacts.customer_id=customers.id AND customer_contacts.deleted_at IS NULL','left')->where('customers.deleted_at',null)->groupStart();
        if ($phone !== '') $builder->where('customer_contacts.phone_normalized', $phone);
        if ($tax !== '') $phone !== '' ? $builder->orWhere('customers.tax_number_normalized', $tax) : $builder->where('customers.tax_number_normalized', $tax);
        $builder->groupEnd(); if ($exclude) $builder->where('customers.id !=', $exclude);
        return $builder->get()->getResultArray();
    }
}
