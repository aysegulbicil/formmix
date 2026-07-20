<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\EmployeeModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Models\UserModel;
use RuntimeException;
use Throwable;

class ProvisionLocalAdmin extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:provision-local-admin';
    protected $description = 'Yerel geliştirme ortamı için owner yetkili mobil/web test hesabını hazırlar.';

    public function run(array $params): int
    {
        if (ENVIRONMENT !== 'development') {
            CLI::error('Bu komut yalnız development ortamında çalıştırılabilir.');

            return EXIT_ERROR;
        }

        $email = mb_strtolower(trim((string) (getenv('FORMMIX_LOCAL_ADMIN_EMAIL') ?: 'admin@formmix.local')));
        $password = (string) getenv('FORMMIX_LOCAL_ADMIN_PASSWORD');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('FORMMIX_LOCAL_ADMIN_EMAIL geçerli bir e-posta adresi olmalıdır.');

            return EXIT_ERROR;
        }

        if (mb_strlen($password) < 6) {
            CLI::error('FORMMIX_LOCAL_ADMIN_PASSWORD en az 6 karakter olmalıdır.');

            return EXIT_ERROR;
        }

        $db = db_connect();
        $db->transBegin();

        try {
            /** @var UserModel $users */
            $users = auth()->getProvider();
            $identity = $db->table('auth_identities')
                ->select('user_id')
                ->where('type', 'email_password')
                ->where('secret', $email)
                ->get()
                ->getRowArray();

            if ($identity === null) {
                $entity = $users->createNewUser([
                    'username' => 'admin_formmix_local',
                    'email' => $email,
                    'password' => $password,
                ]);

                if (! $users->save($entity)) {
                    throw new RuntimeException(implode(' ', $users->errors()));
                }

                $userId = (int) $users->getInsertID();
            } else {
                $userId = (int) $identity['user_id'];
                $entity = $users->findById($userId);

                if ($entity === null) {
                    throw new RuntimeException('E-posta kimliğiyle bağlantılı kullanıcı bulunamadı.');
                }

                $entity->password = $password;
                if (! $users->save($entity)) {
                    throw new RuntimeException(implode(' ', $users->errors()));
                }
            }

            $user = $users->findById($userId);
            if ($user === null) {
                throw new RuntimeException('Yerel yönetici hesabı yeniden yüklenemedi.');
            }

            $user->activate();
            $user->syncGroups('owner');

            $employees = new EmployeeModel();
            $employee = $employees->where('user_id', $userId)->first();
            $employeeData = [
                'user_id' => $userId,
                'employee_code' => 'ADM-LOCAL',
                'full_name' => 'Yerel Yönetici',
                'max_discount_percent' => 100,
                'can_collect_payment' => 0,
                'is_active' => 1,
            ];

            if ($employee === null) {
                if (! $employees->insert($employeeData)) {
                    throw new RuntimeException(implode(' ', $employees->errors()));
                }
            } elseif (! $employees->update((int) $employee['id'], $employeeData)) {
                throw new RuntimeException(implode(' ', $employees->errors()));
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Veritabanı işlemi tamamlanamadı.');
            }

            $db->transCommit();
            CLI::write("Yerel owner hesabı hazır: {$email}", 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            $db->transRollback();
            CLI::error($exception->getMessage());

            return EXIT_ERROR;
        }
    }
}
