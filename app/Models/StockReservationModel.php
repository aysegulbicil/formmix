<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class StockReservationModel extends Model { protected $table='stock_reservations'; protected $returnType='array'; protected $useTimestamps=true; protected $allowedFields=['sales_document_item_id','warehouse_id','reserved_quantity','fulfilled_quantity','status','created_by_user_id']; }
