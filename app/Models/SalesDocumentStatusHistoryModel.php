<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class SalesDocumentStatusHistoryModel extends Model
{
    protected $table='sales_document_status_history'; protected $returnType='array'; protected $useTimestamps=false;
    protected $allowedFields=['sales_document_id','old_status','new_status','reason','changed_by_user_id','created_at'];
}
