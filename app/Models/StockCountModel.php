<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class StockCountModel extends Model { protected $table='stock_counts'; protected $returnType='array'; protected $useTimestamps=true; protected $allowedFields=['count_number','warehouse_id','status','reason','counted_at','created_by_user_id']; }
