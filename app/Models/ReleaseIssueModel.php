<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ReleaseIssueModel extends Model
{
    protected $table = 'release_issues';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = ['title', 'description', 'severity', 'status', 'resolution_note', 'reported_by_user_id', 'resolved_by_user_id', 'resolved_at'];
}
