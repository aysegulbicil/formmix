<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerAssignmentModel extends Model
{
    protected $table = 'customer_assignments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'customer_id', 'employee_id', 'started_at', 'ended_at', 'reason', 'assigned_by_user_id', 'created_at',
    ];
}
