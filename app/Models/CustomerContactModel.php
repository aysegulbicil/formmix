<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerContactModel extends Model
{
    protected $table = 'customer_contacts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id', 'full_name', 'job_title', 'phone', 'phone_normalized', 'email', 'is_primary', 'is_active',
    ];

    protected $validationRules = [
        'customer_id' => 'required|integer',
        'full_name'   => 'required|min_length[2]|max_length[150]',
        'phone'       => 'required|max_length[30]',
        'email'       => 'permit_empty|valid_email|max_length[190]',
    ];
}
