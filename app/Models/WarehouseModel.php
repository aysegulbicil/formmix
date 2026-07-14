<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class WarehouseModel extends Model
{
    protected $table='warehouses'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['code','name','address','is_active'];
    protected $validationRules=['id'=>'permit_empty|is_natural_no_zero','code'=>'required|max_length[30]|is_unique[warehouses.code,id,{id}]','name'=>'required|max_length[120]'];
}
