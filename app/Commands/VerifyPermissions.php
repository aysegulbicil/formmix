<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\AuditLogger;
use App\Models\AuditLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
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
                'allow' => ['panel.access', 'users.manage', 'employees.view', 'employees.manage', 'orders.approve-high', 'finance.manage'],
                'deny'  => [],
            ],
            'sales_manager' => [
                'allow' => ['panel.access', 'employees.view', 'employees.manage', 'customers.assign', 'orders.approve', 'reports.view'],
                'deny'  => ['finance.manage', 'orders.approve-high'],
            ],
            'field_sales' => [
                'allow' => ['panel.access', 'customers.view-own', 'orders.create', 'collections.notify'],
                'deny'  => ['employees.view', 'employees.manage', 'customers.view-all', 'orders.approve', 'finance.manage'],
            ],
            'accounting' => [
                'allow' => ['panel.access', 'finance.manage', 'products.view-cost', 'commissions.view-all'],
                'deny'  => ['employees.manage', 'orders.approve', 'customers.assign', 'users.manage'],
            ],
            'warehouse' => [
                'allow' => ['panel.access', 'stock.manage', 'orders.fulfill', 'purchases.manage'],
                'deny'  => ['employees.view', 'employees.manage', 'finance.manage', 'customers.create', 'commissions.view-all'],
            ],
        ];

        /** @var UserModel $users */
        $users  = auth()->getProvider();
        $failed = [];

        foreach ($cases as $group => $expectations) {
            $id = null;

            try {
                $suffix = bin2hex(random_bytes(5));
                $user   = new User([
                    'username' => "permission_test_{$group}_{$suffix}",
                    'email'    => "permission-test-{$group}-{$suffix}@formmix.local",
                    'password' => bin2hex(random_bytes(16)),
                ]);

                if (! $users->save($user)) {
                    throw new \RuntimeException(implode(' ', $users->errors()));
                }

                $id   = (int) $users->getInsertID();
                $user = $users->findById($id);
                $user->addGroup($group);

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
