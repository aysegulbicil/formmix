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
}
