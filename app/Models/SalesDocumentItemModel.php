<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class SalesDocumentItemModel extends Model
{
    protected $table='sales_document_items'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['sales_document_id','product_id','product_variant_id','product_code_snapshot','product_name_snapshot','variant_snapshot','quantity','reserved_quantity','fulfilled_quantity','unit_price','discount_percent','discount_amount','net_amount','tax_rate','tax_amount','line_total'];
    protected $validationRules=['sales_document_id'=>'required|integer','product_id'=>'required|integer','product_variant_id'=>'required|integer','quantity'=>'required|decimal|greater_than[0]','unit_price'=>'required|decimal|greater_than[0]','discount_percent'=>'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]','tax_rate'=>'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]'];
}
