<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class PurchaseOrderModel extends Model
{
    protected $table='purchase_orders'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['order_number','supplier_id','warehouse_id','status','order_date','expected_date','currency','subtotal','notes','created_by_user_id'];
    protected $validationRules=['id'=>'permit_empty|is_natural_no_zero','order_number'=>'required|max_length[40]|is_unique[purchase_orders.order_number,id,{id}]','supplier_id'=>'required|integer','warehouse_id'=>'required|integer','status'=>'required|in_list[ordered,partial,received,cancelled]','order_date'=>'required|valid_date[Y-m-d]','currency'=>'required|exact_length[3]'];
}
