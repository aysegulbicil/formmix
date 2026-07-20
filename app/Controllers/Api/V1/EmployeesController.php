<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\AuditLogger;
use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Models\UserModel;
use Throwable;

final class EmployeesController extends ApiController
{
    private const ROLES = ['sales_manager', 'field_sales', 'accounting', 'warehouse'];

    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard('employees.view')) return $blocked; [$page, $perPage] = $this->pagination(); $model = new EmployeeModel(); $model->select("employees.*,auth_identities.secret AS account_email,(SELECT `group` FROM auth_groups_users agu WHERE agu.user_id=employees.user_id LIMIT 1) AS role", false)->join('auth_identities', "auth_identities.user_id=employees.user_id AND auth_identities.type='email_password'", 'left');
        $q = trim((string) $this->request->getGet('q')); if ($q !== '') $model->groupStart()->like('employees.full_name', $q)->orLike('employees.employee_code', $q)->orLike('employees.phone', $q)->orLike('auth_identities.secret', $q)->groupEnd(); $active = $this->request->getGet('active'); if ($active === '1' || $active === '0') $model->where('employees.is_active', (int) $active); $total = $model->countAllResults(false); return $this->ok($model->orderBy('employees.is_active', 'DESC')->orderBy('employees.full_name')->findAll($perPage, ($page - 1) * $perPage), ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function show(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('employees.view')) return $blocked; $row = $this->find($id); if (! $row) return $this->error('NOT_FOUND', 'Personel bulunamadı.', 404); $row['role'] = $this->role((int) ($row['user_id'] ?? 0)); $db = db_connect(); $row['customers'] = $db->table('customers')->select('id,customer_code,company_name,status')->where('current_owner_employee_id', $id)->where('deleted_at', null)->orderBy('company_name')->limit(50)->get()->getResultArray(); $row['orders'] = $db->table('sales_documents')->select('id,document_number,document_type,status,grand_total,created_at')->where('sales_employee_id', $id)->where('deleted_at', null)->orderBy('created_at', 'DESC')->limit(50)->get()->getResultArray(); return $this->ok($row);
    }

    public function create(): ResponseInterface { if ($blocked = $this->guard('employees.manage')) return $blocked; return $this->persist(null); }
    public function update(int $id): ResponseInterface { if ($blocked = $this->guard('employees.manage')) return $blocked; $row = $this->find($id); return $row ? $this->persist($row) : $this->error('NOT_FOUND', 'Personel bulunamadı.', 404); }

    public function status(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('employees.manage')) return $blocked; $row = $this->find($id); if (! $row) return $this->error('NOT_FOUND', 'Personel bulunamadı.', 404); $active = array_key_exists('is_active', $this->input()) ? (bool) $this->input()['is_active'] : ! (bool) $row['is_active']; (new EmployeeModel())->update($id, ['is_active' => $active ? 1 : 0]); (new AuditLogger())->record($active ? 'employee.activated' : 'employee.deactivated', 'employee', $id, ['is_active' => (bool) $row['is_active']], ['is_active' => $active]); return $this->ok(['id' => $id, 'is_active' => $active]);
    }

    private function persist(?array $existing): ResponseInterface
    {
        $in = $this->input(); $id = $existing['id'] ?? null;
        if ($existing && (! isset($in['expected_updated_at']) || (string) $in['expected_updated_at'] !== (string) $existing['updated_at'])) return $this->error('STALE_RESOURCE', 'Personel başka bir oturumda güncellendi.', 409);
        $data = ['id' => $id, 'employee_code' => mb_strtoupper(trim((string) ($in['employee_code'] ?? ''))), 'full_name' => trim((string) ($in['full_name'] ?? '')), 'phone' => trim((string) ($in['phone'] ?? '')) ?: null, 'max_discount_percent' => $this->decimal($in['max_discount_percent'] ?? 0), 'can_collect_payment' => ! empty($in['can_collect_payment']) ? 1 : 0, 'is_active' => array_key_exists('is_active', $in) ? (! empty($in['is_active']) ? 1 : 0) : 1, 'user_id' => $existing['user_id'] ?? null];
        $account = (string) ($in['account_user_id'] ?? ''); $role = (string) ($in['role'] ?? ''); $email = strtolower(trim((string) ($in['login_email'] ?? ''))); $password = (string) ($in['login_password'] ?? '');
        if ($account !== '' && ! auth()->user()?->can('users.manage')) return $this->error('FORBIDDEN', 'Kullanıcı hesabı yönetme yetkiniz bulunmuyor.', 403);
        if ($account !== '' && ! in_array($role, self::ROLES, true)) return $this->error('VALIDATION_FAILED', 'Geçerli bir görev seçin.', 422, ['role' => 'Geçersiz görev.']);
        if ($account === 'new' && (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 6)) return $this->error('VALIDATION_FAILED', 'Geçerli e-posta ve en az 6 karakter parola girin.', 422, ['login_email' => 'E-posta geçersiz olabilir.', 'login_password' => 'En az 6 karakter olmalıdır.']);
        $db = db_connect(); $model = new EmployeeModel(); $users = model(setting('Auth.userProvider')); assert($users instanceof UserModel); $db->transBegin();
        try {
            if ($account === 'new') { $user = $users->createNewUser(['email' => $email, 'password' => $password]); $user->username = null; if (! $users->save($user)) throw new \RuntimeException(implode(' ', $users->errors())); $user = $users->findById($users->getInsertID()); if (! $user) throw new \RuntimeException('Kullanıcı hesabı oluşturulamadı.'); $user->activate(); $user->syncGroups($role); $data['user_id'] = (int) $user->id; }
            elseif (ctype_digit($account)) { $user = $users->findById((int) $account); if (! $user) throw new \RuntimeException('Kullanıcı hesabı bulunamadı.'); $linked = $model->where('user_id', (int) $account)->first(); if ($linked && (int) $linked['id'] !== (int) $id) throw new \RuntimeException('Kullanıcı hesabı başka personele bağlı.'); $user->syncGroups($role); if ($password !== '') { if (mb_strlen($password) < 6) throw new \RuntimeException('Yeni parola en az 6 karakter olmalıdır.'); $user->password = $password; if (! $users->save($user)) throw new \RuntimeException(implode(' ', $users->errors())); } $data['user_id'] = (int) $account; }
            elseif ($account === '' && array_key_exists('account_user_id', $in)) $data['user_id'] = null;
            if ($id === null) { unset($data['id']); if (! $model->insert($data)) throw new \RuntimeException(implode(' ', $model->errors())); $id = (int) $model->getInsertID(); } elseif (! $model->update($id, $data)) throw new \RuntimeException(implode(' ', $model->errors()));
            if (! $db->transStatus()) throw new \RuntimeException('Personel kaydedilemedi.'); $db->transCommit();
        } catch (Throwable $e) { $db->transRollback(); return $this->error('EMPLOYEE_SAVE_FAILED', $e->getMessage(), 422); }
        $fresh = $model->find($id); (new AuditLogger())->record($existing ? 'employee.updated' : 'employee.created', 'employee', $id, $existing ? $this->audit($existing) : null, $this->audit($fresh ?? [])); return $this->ok($fresh, [], $existing ? 200 : 201);
    }

    private function find(int $id): ?array { return (new EmployeeModel())->find($id); }
    private function role(int $userId): ?string { if ($userId <= 0) return null; $row = db_connect()->table('auth_groups_users')->select('group')->where('user_id', $userId)->get()->getRowArray(); return $row['group'] ?? null; }
    private function audit(array $row): array { return array_intersect_key($row, array_flip(['employee_code', 'full_name', 'phone', 'max_discount_percent', 'can_collect_payment', 'is_active', 'user_id'])); }
    private function decimal(mixed $value): float { $value = str_replace(',', '.', trim((string) $value)); return is_numeric($value) ? (float) $value : 0.0; }
}
