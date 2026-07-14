<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\CustomerActivityModel;
use App\Models\CustomerAssignmentModel;
use App\Models\CustomerContactModel;
use App\Models\CustomerModel;
use App\Models\EmployeeModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

class VerifyCustomerFoundation extends BaseCommand
{
    protected $group       = 'FORMMIX';
    protected $name        = 'formmix:verify-customer-foundation';
    protected $description = 'Müşteri ve personel veri temelini geçici kayıtlarla doğrular.';

    public function run(array $params): int
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $suffix = strtoupper(bin2hex(random_bytes(4)));

            $employees = new EmployeeModel();
            $employeeId = $employees->insert([
                'employee_code' => "TEST-{$suffix}",
                'full_name' => 'Geçici Test Personeli',
                'max_discount_percent' => 0,
                'can_collect_payment' => false,
                'is_active' => true,
            ], true);

            $customers = new CustomerModel();
            $customerId = $customers->insert([
                'customer_code' => "M-TEST-{$suffix}",
                'company_name' => 'Geçici Test Müşterisi',
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'status' => 'candidate',
                'payment_term_days' => 30,
                'current_owner_employee_id' => $employeeId,
            ], true);

            $contacts = new CustomerContactModel();
            $contacts->insert([
                'customer_id' => $customerId,
                'full_name' => 'Geçici Yetkili',
                'phone' => '05000000000',
                'phone_normalized' => '905000000000',
                'is_primary' => true,
                'is_active' => true,
            ]);

            $assignments = new CustomerAssignmentModel();
            $assignments->insert([
                'customer_id' => $customerId,
                'employee_id' => $employeeId,
                'started_at' => date('Y-m-d H:i:s'),
                'reason' => 'İlk kaydı açan personel',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $activities = new CustomerActivityModel();
            $activities->insert([
                'customer_id' => $customerId,
                'employee_id' => $employeeId,
                'activity_type' => 'note',
                'subject' => 'Geçici temel kontrolü',
                'happened_at' => date('Y-m-d H:i:s'),
            ]);

            $customer = $customers->find($customerId);
            $contactCount = $contacts->where('customer_id', $customerId)->countAllResults();
            $assignmentCount = $assignments->where('customer_id', $customerId)->countAllResults();
            $activityCount = $activities->where('customer_id', $customerId)->countAllResults();

            if (
                $customer === null
                || (int) $customer['current_owner_employee_id'] !== (int) $employeeId
                || $contactCount !== 1
                || $assignmentCount !== 1
                || $activityCount !== 1
            ) {
                throw new RuntimeException('Geçici müşteri ilişkileri beklenen şekilde okunamadı.');
            }

            CLI::write('Müşteri, yetkili kişi, personel ataması ve görüşme kaydı kontrolleri başarılı.', 'green');

            return EXIT_SUCCESS;
        } catch (Throwable $exception) {
            CLI::error($exception->getMessage());

            return EXIT_ERROR;
        } finally {
            $db->transRollback();
        }
    }
}
