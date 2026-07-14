<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class StockCountItemModel extends Model { protected $table='stock_count_items'; protected $returnType='array'; protected $allowedFields=['stock_count_id','product_variant_id','system_quantity','counted_quantity','difference_quantity','stock_movement_id','created_at']; }
