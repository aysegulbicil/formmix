<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class PurchaseOrderItemModel extends Model
{
    protected $table='purchase_order_items'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['purchase_order_id','product_variant_id','sku_snapshot','product_name_snapshot','ordered_quantity','received_quantity','unit_cost'];
}
