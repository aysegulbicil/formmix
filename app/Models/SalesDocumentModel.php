<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class SalesDocumentModel extends Model
{
    protected $table='sales_documents'; protected $returnType='array'; protected $useSoftDeletes=true; protected $useTimestamps=true;
    protected $allowedFields=['document_number','document_type','source_quote_id','customer_id','customer_owner_employee_id','sales_employee_id','preparation_employee_id','design_employee_id','print_employee_id','created_by_user_id','approved_by_user_id','status','client_reference','currency','subtotal','discount_total','tax_total','grand_total','notes','delivery_address','requested_delivery_date','approved_at','cancelled_at','cancellation_reason'];
    protected $validationRules=['id'=>'permit_empty|is_natural_no_zero','document_number'=>'required|max_length[40]|is_unique[sales_documents.document_number,id,{id}]','document_type'=>'required|in_list[quote,order]','customer_id'=>'required|integer','created_by_user_id'=>'required|integer','status'=>'required|in_list[draft,pending_approval,approved,procurement_waiting,reserved,partially_shipped,shipped,delivered,cancelled]','client_reference'=>'required|max_length[80]|is_unique[sales_documents.client_reference,id,{id}]','currency'=>'required|exact_length[3]'];
}
