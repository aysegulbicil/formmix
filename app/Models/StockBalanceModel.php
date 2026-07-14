<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class StockBalanceModel extends Model
{
    protected $table='stock_balances'; protected $returnType='array'; protected $useTimestamps=true; protected $createdField='';
    protected $allowedFields=['warehouse_id','product_variant_id','on_hand_quantity','reserved_quantity'];
}
