<?php
declare(strict_types=1);namespace App\Models;use CodeIgniter\Model;class CommissionRuleModel extends Model{protected $table='commission_rules';protected $returnType='array';protected $useTimestamps=true;protected $allowedFields=['name','base_type','rate_percent','employee_id','product_category_id','starts_on','ends_on','is_active','created_by_user_id'];}
