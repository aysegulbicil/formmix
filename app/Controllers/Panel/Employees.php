<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\EmployeeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Models\UserModel;
use Throwable;

class Employees extends BaseController
{
    private const ROLES = [
        'sales_manager' => 'Satış Yöneticisi',
        'field_sales'   => 'Saha Personeli',
        'accounting'    => 'Muhasebe',
        'warehouse'     => 'Depo',
    ];

    public function index(): string
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('durum');
        $model  = new EmployeeModel();

        $model->select('employees.*, auth_identities.secret AS account_email')
            ->join('auth_identities', "auth_identities.user_id = employees.user_id AND auth_identities.type = 'email_password'", 'left');

        if ($search !== '') {
            $model->groupStart()
                ->like('employees.full_name', $search)
                ->orLike('employees.employee_code', $search)
                ->orLike('employees.phone', $search)
                ->orLike('auth_identities.secret', $search)
                ->groupEnd();
        }

        if ($status === 'aktif' || $status === 'pasif') {
            $model->where('employees.is_active', $status === 'aktif' ? 1 : 0);
        }

        $employees = $model->orderBy('employees.is_active', 'DESC')
            ->orderBy('employees.full_name', 'ASC')
            ->findAll();

        $roleMap = $this->roleMap(array_column($employees, 'user_id'));
        foreach ($employees as &$employee) {
            $employee['role']       = $roleMap[(int) ($employee['user_id'] ?? 0)] ?? null;
            $employee['role_title'] = self::ROLES[$employee['role']] ?? null;
        }

        $all = (new EmployeeModel())->findAll();

        return view('panel/employees/index', [
            'title'     => 'Personel | FORMMIX',
            'pageTitle' => 'Personel',
            'activeNav' => 'employees',
            'employees' => $employees,
            'search'    => $search,
            'status'    => $status,
            'stats'     => [
                'total'  => count($all),
                'active' => count(array_filter($all, static fn (array $item): bool => (bool) $item['is_active'])),
                'linked' => count(array_filter($all, static fn (array $item): bool => ! empty($item['user_id']))),
            ],
        ]);
    }

    public function create(): string
    {
        return view('panel/employees/form', $this->formData(null));
    }

    public function store(): RedirectResponse
    {
        return $this->persist(null);
    }

    public function edit(int $id): string
    {
        return view('panel/employees/form', $this->formData($this->findEmployee($id)));
    }

    public function update(int $id): RedirectResponse
    {
        return $this->persist($this->findEmployee($id));
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $employee = $this->findEmployee($id);
        $newStatus = ! (bool) $employee['is_active'];
        $model = new EmployeeModel();

        if (! $model->update($id, ['is_active' => $newStatus ? 1 : 0])) {
            return redirect()->back()->with('errors', $model->errors());
        }

        (new AuditLogger())->record(
            $newStatus ? 'employee.activated' : 'employee.deactivated',
            'employee',
            $id,
            ['is_active' => (bool) $employee['is_active']],
            ['is_active' => $newStatus],
        );

        return redirect()->to(site_url('panel/personel'))
            ->with('message', $newStatus ? 'Personel yeniden etkinleştirildi.' : 'Personel pasif duruma alındı.');
    }

    private function persist(?array $employee): RedirectResponse
    {
        $id      = $employee['id'] ?? null;
        $account = (string) $this->request->getPost('account_user_id');
        $data    = [
            'id'                       => $id,
            'employee_code'            => strtoupper(trim((string) $this->request->getPost('employee_code'))),
            'full_name'                => trim((string) $this->request->getPost('full_name')),
            'phone'                    => trim((string) $this->request->getPost('phone')) ?: null,
            'max_discount_percent'     => str_replace(',', '.', (string) $this->request->getPost('max_discount_percent')),
            'can_collect_payment'      => $this->request->getPost('can_collect_payment') ? 1 : 0,
            'is_active'                => $this->request->getPost('is_active') ? 1 : 0,
            'user_id'                  => $employee['user_id'] ?? null,
        ];

        $canManageUsers = auth()->user()?->can('users.manage') ?? false;
        $role           = (string) $this->request->getPost('role');
        $email          = strtolower(trim((string) $this->request->getPost('login_email')));
        $password       = (string) $this->request->getPost('login_password');
        $errors         = [];

        if ($canManageUsers && $account === 'new') {
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['login_email'] = 'Geçerli bir e-posta adresi yazın.';
            }
            if (mb_strlen($password) < 12) {
                $errors['login_password'] = 'Parola en az 12 karakter olmalıdır.';
            }
        }
        if ($canManageUsers && $account !== '' && ! isset(self::ROLES[$role])) {
            $errors['role'] = 'Kullanıcı için geçerli bir görev seçin.';
        }
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $db    = db_connect();
        $model = new EmployeeModel();
        $users = model(setting('Auth.userProvider'));
        assert($users instanceof UserModel);
        $db->transBegin();

        try {
            if ($canManageUsers) {
                if ($account === 'new') {
                    $user = $users->createNewUser(['email' => $email, 'password' => $password]);
                    $user->username = null;
                    if (! $users->save($user)) {
                        throw new \RuntimeException(implode(' ', $users->errors()));
                    }
                    $user = $users->findById($users->getInsertID());
                    $user->activate()->syncGroups($role);
                    $data['user_id'] = (int) $user->id;
                } elseif (ctype_digit($account)) {
                    $user = $users->findById((int) $account);
                    if ($user === null) {
                        throw new \RuntimeException('Seçilen kullanıcı hesabı bulunamadı.');
                    }
                    $linked = $model->where('user_id', (int) $account)->first();
                    if ($linked && (int) $linked['id'] !== (int) $id) {
                        throw new \RuntimeException('Bu kullanıcı hesabı başka bir personele bağlı.');
                    }
                    $user->syncGroups($role);
                    if ($password !== '') {
                        if (mb_strlen($password) < 12) {
                            throw new \RuntimeException('Yeni parola en az 12 karakter olmalıdır.');
                        }
                        $user->password = $password;
                        $users->save($user);
                    }
                    $data['user_id'] = (int) $account;
                } elseif ($account === '') {
                    $data['user_id'] = null;
                }
            }

            if ($id === null) {
                unset($data['id']);
                if (! $model->insert($data)) {
                    throw new \RuntimeException(implode(' ', $model->errors()));
                }
                $id = (int) $model->getInsertID();
            } elseif (! $model->update($id, $data)) {
                throw new \RuntimeException(implode(' ', $model->errors()));
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Kayıt veritabanına yazılamadı.');
            }
            $db->transCommit();
        } catch (Throwable $exception) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('errors', ['form' => $exception->getMessage()]);
        }

        $fresh = (new EmployeeModel())->find($id);
        (new AuditLogger())->record(
            $employee === null ? 'employee.created' : 'employee.updated',
            'employee',
            $id,
            $employee === null ? null : $this->auditValues($employee),
            $this->auditValues($fresh ?? []),
        );

        return redirect()->to(site_url('panel/personel'))
            ->with('message', $employee === null ? 'Personel kaydı oluşturuldu.' : 'Personel bilgileri güncellendi.');
    }

    private function formData(?array $employee): array
    {
        $canManageUsers = auth()->user()?->can('users.manage') ?? false;
        $users          = [];
        $currentRole    = null;

        if ($canManageUsers) {
            $userModel = model(setting('Auth.userProvider'));
            assert($userModel instanceof UserModel);
            $linkedIds = array_map('intval', array_filter(array_column((new EmployeeModel())->findAll(), 'user_id')));
            foreach ($userModel->findAll() as $user) {
                if (! in_array((int) $user->id, $linkedIds, true) || (int) $user->id === (int) ($employee['user_id'] ?? 0)) {
                    $users[] = ['id' => (int) $user->id, 'email' => $user->email ?? ('Kullanıcı #' . $user->id)];
                }
            }
            if (! empty($employee['user_id'])) {
                $currentRole = $this->roleMap([(int) $employee['user_id']])[(int) $employee['user_id']] ?? null;
            }
        }

        return [
            'title'          => ($employee ? 'Personel Düzenle' : 'Yeni Personel') . ' | FORMMIX',
            'pageTitle'      => $employee ? 'Personel düzenle' : 'Yeni personel',
            'activeNav'      => 'employees',
            'employee'       => $employee,
            'users'          => $users,
            'roles'          => self::ROLES,
            'currentRole'    => $currentRole,
            'canManageUsers' => $canManageUsers,
        ];
    }

    private function findEmployee(int $id): array
    {
        $employee = (new EmployeeModel())->find($id);
        if ($employee === null) {
            throw PageNotFoundException::forPageNotFound('Personel kaydı bulunamadı.');
        }
        return $employee;
    }

    /** @param list<int|string|null> $userIds */
    private function roleMap(array $userIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $userIds)));
        if ($ids === []) {
            return [];
        }
        $rows = db_connect()->table('auth_groups_users')->select('user_id, group')->whereIn('user_id', $ids)->get()->getResultArray();
        $map = [];
        foreach ($rows as $row) {
            if (isset(self::ROLES[$row['group']])) {
                $map[(int) $row['user_id']] = $row['group'];
            }
        }
        return $map;
    }

    private function auditValues(array $employee): array
    {
        return array_intersect_key($employee, array_flip([
            'employee_code', 'full_name', 'phone', 'max_discount_percent',
            'can_collect_payment', 'is_active', 'user_id',
        ]));
    }
}
