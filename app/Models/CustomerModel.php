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

    protected $validationRules = [
        'id'               => 'permit_empty|is_natural_no_zero',
        'customer_code'    => 'required|max_length[30]|is_unique[customers.customer_code,id,{id}]',
        'company_name'     => 'required|min_length[2]|max_length[180]',
        'email'            => 'permit_empty|valid_email|max_length[190]',
        'city'             => 'required|max_length[100]',
        'district'         => 'required|max_length[100]',
        'payment_term_days'=> 'required|integer|greater_than_equal_to[0]|less_than_equal_to[365]',
        'credit_limit'     => 'permit_empty|decimal|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'company_name' => ['required' => 'Firma adı zorunludur.', 'min_length' => 'Firma adı en az 2 karakter olmalıdır.'],
        'email' => ['valid_email' => 'Geçerli bir e-posta adresi yazın.'],
        'city' => ['required' => 'İl zorunludur.'],
        'district' => ['required' => 'İlçe zorunludur.'],
        'payment_term_days' => ['required' => 'Vade günü zorunludur.', 'integer' => 'Vade günü tam sayı olmalıdır.'],
    ];
}
