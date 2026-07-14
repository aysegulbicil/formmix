<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class SupplierModel extends Model
{
    protected $table='suppliers'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['supplier_code','company_name','contact_name','phone','email','tax_number','address','notes','is_active','created_by_user_id'];
    protected $validationRules=['id'=>'permit_empty|is_natural_no_zero','supplier_code'=>'required|max_length[40]|is_unique[suppliers.supplier_code,id,{id}]','company_name'=>'required|max_length[180]','email'=>'permit_empty|valid_email|max_length[190]','created_by_user_id'=>'required|integer'];
}
