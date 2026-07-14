<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class SalesDocumentApprovalModel extends Model
{
    protected $table='sales_document_approvals'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['sales_document_id','approval_type','requested_percent','status','requested_by_user_id','decided_by_user_id','decision_note','decided_at'];
}
