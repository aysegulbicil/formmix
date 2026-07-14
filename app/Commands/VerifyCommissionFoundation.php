<?php
declare(strict_types=1);
namespace App\Commands;
use App\Services\CommissionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;
class VerifyCommissionFoundation extends BaseCommand
{
 protected $group='FORMMIX';protected $name='formmix:verify-commission-foundation';protected $description='Prim hesaplama temelini doğrular.';
 public function run(array $params):int
 {
  $db=db_connect();$user=$db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();if(!$user)return EXIT_ERROR;$s=strtoupper(bin2hex(random_bytes(3)));$now=date('Y-m-d H:i:s');$db->transBegin();
  try{
   $db->table('employees')->insert(['employee_code'=>'PRM-'.$s,'full_name'=>'Prim Test','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$emp=(int)$db->insertID();
   $db->table('customers')->insert(['customer_code'=>'PRM-M-'.$s,'company_name'=>'Prim Müşteri','city'=>'İstanbul','district'=>'Test','status'=>'active','created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$cus=(int)$db->insertID();
   $db->table('product_categories')->insert(['code'=>'PRM-K-'.$s,'name'=>'Prim Kategori','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$cat=(int)$db->insertID();
   $db->table('products')->insert(['category_id'=>$cat,'product_code'=>'PRM-U-'.$s,'name'=>'Prim Ürün','tax_rate'=>20,'cost_price'=>60,'list_price'=>100,'currency'=>'TRY','is_active'=>1,'track_stock'=>1,'customization_mode'=>'optional','created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$product=(int)$db->insertID();
   $db->table('product_variants')->insert(['product_id'=>$product,'sku'=>'PRM-V-'.$s,'preparation_type'=>'plain','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$variant=(int)$db->insertID();
   $db->table('sales_documents')->insert(['document_number'=>'PRM-S-'.$s,'document_type'=>'order','customer_id'=>$cus,'sales_employee_id'=>$emp,'created_by_user_id'=>$user['id'],'status'=>'approved','client_reference'=>'prm-'.$s,'currency'=>'TRY','subtotal'=>200,'grand_total'=>240,'created_at'=>$now,'updated_at'=>$now]);$doc=(int)$db->insertID();
   $db->table('sales_document_items')->insert(['sales_document_id'=>$doc,'product_id'=>$product,'product_variant_id'=>$variant,'product_code_snapshot'=>'PRM-U-'.$s,'product_name_snapshot'=>'Prim Ürün','variant_snapshot'=>'Standart','quantity'=>2,'unit_price'=>100,'net_amount'=>200,'tax_rate'=>20,'tax_amount'=>40,'line_total'=>240,'created_at'=>$now,'updated_at'=>$now]);
   foreach([['Satış primi','sales',5],['Kâr primi','profit',10]] as $r)$db->table('commission_rules')->insert(['name'=>$r[0],'base_type'=>$r[1],'rate_percent'=>$r[2],'employee_id'=>$emp,'starts_on'=>date('Y-m-01'),'is_active'=>1,'created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);
   $this->assert($db->transStatus(),'Test verisi eklenemedi.');
   $ruleCount=$db->table('commission_rules')->where('employee_id',$emp)->countAllResults();$docCount=$db->table('sales_documents')->where('sales_employee_id',$emp)->countAllResults();
   $count=(new CommissionService())->calculate();$rows=$db->table('commission_entries')->where('sales_document_id',$doc)->orderBy('commission_amount')->get()->getResultArray();$amounts=array_map('floatval',array_column($rows,'commission_amount'));
   $this->assert($count===2&&$amounts===[8.0,10.0],'Satış/kâr primi yanlış: '.json_encode([$ruleCount,$docCount,$count,$amounts]));
   $db->table('sales_documents')->where('id',$doc)->update(['status'=>'shipped']);(new CommissionService())->calculate();
   $this->assert($db->table('commission_entries')->where(['sales_document_id'=>$doc,'status'=>'earned'])->countAllResults()===2,'Hak ediş geçişi yanlış.');$this->assert($db->table('commission_entries')->where('sales_document_id',$doc)->countAllResults()===2,'Mükerrer prim oluştu.');
   $db->transRollback();CLI::write('Satış/kâr primi, hak ediş ve mükerrer kayıt engeli doğrulandı.','green');return EXIT_SUCCESS;
  }catch(Throwable $e){$db->transRollback();CLI::error($e->getMessage());return EXIT_ERROR;}
 }
 private function assert(bool $ok,string $message):void{if(!$ok)throw new RuntimeException($message);}
}
