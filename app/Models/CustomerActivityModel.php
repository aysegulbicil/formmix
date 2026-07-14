<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerActivityModel extends Model
{
    protected $table = 'customer_activities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id', 'employee_id', 'activity_type', 'subject', 'notes', 'happened_at', 'next_action_at',
        'created_by_user_id',
    ];
}
