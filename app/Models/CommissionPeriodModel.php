<?php
declare(strict_types=1);namespace App\Models;use CodeIgniter\Model;class CommissionPeriodModel extends Model{protected $table='commission_periods';protected $returnType='array';protected $useTimestamps=true;protected $allowedFields=['period_code','starts_on','ends_on','status','closed_by_user_id','closed_at','paid_at'];}
