<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class ApiIdempotencyKeyModel extends Model
{
    protected $table='api_idempotency_keys'; protected $returnType='array'; protected $useTimestamps=false;
    protected $allowedFields=['user_id','idempotency_key','operation','resource_type','resource_id','response_status','response_body','created_at'];
}
