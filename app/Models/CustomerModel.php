<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_code', 'company_name', 'official_title', 'email', 'city', 'district', 'address',
        'delivery_address', 'billing_address', 'tax_office', 'tax_number', 'tax_number_normalized',
        'status', 'payment_term_days', 'credit_limit', 'current_owner_employee_id', 'created_by_user_id',
        'last_activity_at',
    ];
}
