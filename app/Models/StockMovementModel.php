<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class StockMovementModel extends Model
{
    protected $table='stock_movements'; protected $returnType='array';
    protected $allowedFields=['movement_number','movement_type','warehouse_id','product_variant_id','quantity','balance_after','reason','reference_type','reference_id','paired_movement_id','created_by_user_id','created_at'];
}
