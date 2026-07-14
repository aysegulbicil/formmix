<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ReleaseReadinessItemModel extends Model
{
    protected $table = 'release_readiness_items';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['status', 'notes', 'checked_by_user_id', 'checked_at'];
}
