<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\AuditLogger;
use App\Models\AuditLogModel;
use App\Models\EmployeeModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;
use Throwable;

class VerifyPermissions extends BaseCommand
{
    protected $group       = 'FORMMIX';
    protected $name        = 'formmix:verify-permissions';
    protected $description = 'Görevlerin izin verilen ve yasaklanan işlemlerini geçici kullanıcılarla doğrular.';

    public function run(array $params): int
    {
        $cases = [
            'owner' => [
                'allow' => ['panel.access', 'users.manage', 'settings.manage', 'employees.view', 'employees.manage', 'products.view', 'products.manage', 'products.view-cost', 'orders.create', 'orders.view-all', 'orders.approve', 'orders.approve-high', 'purchases.create', 'purchases.receive', 'suppliers.manage', 'warehouses.manage', 'stock.manage', 'stock.count', 'finance.manage', 'reports.view'],
                'deny'  => [],
            ],
            'sales_manager' => [
                'allow' => ['panel.access', 'employees.view', 'employees.manage', 'customers.assign', 'products.view', 'products.manage', 'orders.create', 'orders.view-all', 'orders.approve', 'reports.view'],
                'deny'  => ['products.view-cost', 'finance.manage', 'orders.approve-high', 'purchases.create', 'stock.manage', 'settings.manage'],
            ],
            'field_sales' => [
                'allow' => ['panel.access', 'customers.view-own', 'products.view', 'orders.create', 'collections.notify'],
                'deny'  => ['products.manage', 'products.view-cost', 'employees.view', 'employees.manage', 'customers.view-all', 'orders.view-all', 'orders.approve', 'purchases.manage', 'stock.manage', 'finance.manage', 'reports.view', 'settings.manage'],
            ],
            'accounting' => [
                'allow' => ['panel.access', 'finance.manage', 'products.view', 'products.view-cost', 'orders.view-all', 'purchases.manage', 'purchases.create', 'purchases.receive', 'suppliers.manage', 'commissions.view-all', 'reports.view'],
                'deny'  => ['products.manage', 'employees.manage', 'orders.create', 'orders.approve', 'customers.assign', 'warehouses.manage', 'stock.manage', 'stock.count', 'users.manage', 'settings.manage'],
            ],
            'warehouse' => [
                'allow' => ['panel.access', 'products.view', 'orders.view-all', 'stock.manage', 'stock.count', 'orders.fulfill', 'purchases.manage', 'purchases.receive'],
                'deny'  => ['products.manage', 'products.view-cost', 'employees.view', 'employees.manage', 'orders.create', 'orders.approve', 'purchases.create', 'suppliers.manage', 'warehouses.manage', 'finance.manage', 'customers.create', 'commissions.view-all', 'reports.view', 'settings.manage'],
            ],
        ];

        /** @var UserModel $users */
        $users  = auth()->getProvider();
        $failed = [];

        foreach ($cases as $group => $expectations) {
            $id = null;

            try {
                $suffix = bin2hex(random_bytes(5));
                $user   = $users->createNewUser([
                    'username' => "permission_test_{$group}_{$suffix}",
                    'email'    => "permission-test-{$group}-{$suffix}@formmix.local",
                    'password' => 'Q7!x9z',
                ]);

                if (! $users->save($user)) {
                    throw new \RuntimeException(implode(' ', $users->errors()));
                }

                $id   = (int) $users->getInsertID();
                $user = $users->findById($id);
                if ($user === null) {
                    throw new \RuntimeException('Oluşturulan kullanıcı hesabı yeniden yüklenemedi.');
                }
                $user->activate();
                $user->syncGroups($group);

                foreach ($expectations['allow'] as $permission) {
                    if (! $user->can($permission)) {
                        $failed[] = "{$group}: izin verilmesi gereken {$permission} reddedildi";
                    }
                }

                foreach ($expectations['deny'] as $permission) {
                    if ($user->can($permission)) {
                        $failed[] = "{$group}: reddedilmesi gereken {$permission} verildi";
                    }
                }
            } catch (Throwable $exception) {
                $failed[] = "{$group}: {$exception->getMessage()}";
            } finally {
                if ($id !== null) {
                    $users->delete($id, true);
                }
            }
        }

        $employeeId = null;
        try {
            $employeeCode = 'VERIFY-' . strtoupper(bin2hex(random_bytes(4)));
            $employees    = new EmployeeModel();
            $employeeId   = $employees->insert([
                'employee_code'        => $employeeCode,
                'full_name'            => 'Doğrulama Personeli',
                'max_discount_percent' => '0',
                'can_collect_payment'  => 0,
                'is_active'            => 1,
            ], true);

            if (! is_numeric($employeeId) || ! $employees->update($employeeId, [
                'id'            => (int) $employeeId,
                'employee_code' => $employeeCode,
            ])) {
                throw new \RuntimeException(implode(' ', $employees->errors()));
            }
        } catch (Throwable $exception) {
            $failed[] = 'Personel kayıt doğrulaması: ' . $exception->getMessage();
        } finally {
            if (is_numeric($employeeId)) {
                (new EmployeeModel())->delete((int) $employeeId, true);
            }
        }

        $auditMarker = 'verification-' . bin2hex(random_bytes(5));
        $auditLogs   = new AuditLogModel();

        try {
            $written = (new AuditLogger($auditLogs))->record(
                'verification',
                'system_check',
                $auditMarker,
                null,
                ['status' => 'ok'],
            );
            $auditRow = $auditLogs->where('record_id', $auditMarker)->first();

            if (! $written || $auditRow === null) {
                $failed[] = 'İşlem geçmişi kaydı yazılıp okunamadı';
            }
        } catch (Throwable $exception) {
            $failed[] = 'İşlem geçmişi: ' . $exception->getMessage();
        } finally {
            $auditLogs->where('record_id', $auditMarker)->delete();
        }

        if ($failed !== []) {
            foreach ($failed as $message) {
                CLI::error($message);
            }

            return EXIT_ERROR;
        }

        CLI::write('Bütün görev, yetki ve işlem geçmişi kontrolleri başarılı.', 'green');

        return EXIT_SUCCESS;
    }
}
