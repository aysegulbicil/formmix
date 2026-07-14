<?php
declare(strict_types=1);
namespace App\Commands;
use App\Services\StockService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
use RuntimeException;
use Throwable;
class VerifyInventoryFoundation extends BaseCommand
{
    protected $group='FORMMIX';protected $name='formmix:verify-inventory-foundation';protected $description='Alış, stok, ayırma, sevkiyat, iade, sayım ve transfer kurallarını geçici kayıtlarla doğrular.';
    public function run(array $params):int
    {
        $db=db_connect();$suffix=strtoupper(bin2hex(random_bytes(4)));$now=date('Y-m-d H:i:s');$createdUser=null;$user=$db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        if(!$user){$provider=auth()->getProvider();$entity=new User(['username'=>'stock_test_'.strtolower($suffix),'email'=>'stock-test-'.strtolower($suffix).'@formmix.local','password'=>bin2hex(random_bytes(16))]);if(!$provider->save($entity)){CLI::error(implode(' ',$provider->errors()));return EXIT_ERROR;}$createdUser=(int)$provider->getInsertID();$user=['id'=>$createdUser];}
        $db->transBegin();
        try{
            $db->table('warehouses')->insert(['code'=>'TEST-A-'.$suffix,'name'=>'Test Ana Depo','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$warehouseA=(int)$db->insertID();$db->table('warehouses')->insert(['code'=>'TEST-B-'.$suffix,'name'=>'Test İkinci Depo','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$warehouseB=(int)$db->insertID();
            $db->table('suppliers')->insert(['supplier_code'=>'TEST-TED-'.$suffix,'company_name'=>'Stok Test Tedarikçisi','is_active'=>1,'created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$supplier=(int)$db->insertID();
            $db->table('employees')->insert(['employee_code'=>'TEST-STK-'.$suffix,'full_name'=>'Stok Test Personeli','max_discount_percent'=>5,'can_collect_payment'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$employee=(int)$db->insertID();
            $db->table('customers')->insert(['customer_code'=>'TEST-STK-MUS-'.$suffix,'company_name'=>'Stok Test Müşterisi','city'=>'İstanbul','district'=>'Test','status'=>'active','payment_term_days'=>30,'current_owner_employee_id'=>$employee,'created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$customer=(int)$db->insertID();
            $db->table('product_categories')->insert(['code'=>'TEST-STK-'.$suffix,'name'=>'Stok Test Kategorisi','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$category=(int)$db->insertID();
            $db->table('products')->insert(['category_id'=>$category,'product_code'=>'TEST-STK-URUN-'.$suffix,'name'=>'Stok Test Ürünü','tax_rate'=>20,'cost_price'=>40,'list_price'=>100,'currency'=>'TRY','is_active'=>1,'track_stock'=>1,'critical_stock_level'=>3,'customization_mode'=>'optional','created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$product=(int)$db->insertID();
            $db->table('product_variants')->insert(['product_id'=>$product,'sku'=>'TEST-STK-VAR-'.$suffix,'size'=>'M','color'=>'Lacivert','preparation_type'=>'plain','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]);$variant=(int)$db->insertID();
            $db->table('purchase_orders')->insert(['order_number'=>'TEST-ALS-'.$suffix,'supplier_id'=>$supplier,'warehouse_id'=>$warehouseA,'status'=>'partial','order_date'=>date('Y-m-d'),'currency'=>'TRY','subtotal'=>600,'created_by_user_id'=>$user['id'],'created_at'=>$now,'updated_at'=>$now]);$purchase=(int)$db->insertID();
            $db->table('purchase_order_items')->insert(['purchase_order_id'=>$purchase,'product_variant_id'=>$variant,'sku_snapshot'=>'TEST-STK-VAR-'.$suffix,'product_name_snapshot'=>'Stok Test Ürünü','ordered_quantity'=>15,'received_quantity'=>10,'unit_cost'=>40,'created_at'=>$now,'updated_at'=>$now]);
            $db->table('sales_documents')->insert(['document_number'=>'TEST-SIP-STK-'.$suffix,'document_type'=>'order','customer_id'=>$customer,'customer_owner_employee_id'=>$employee,'sales_employee_id'=>$employee,'created_by_user_id'=>$user['id'],'status'=>'approved','client_reference'=>'test-stock-'.$suffix,'currency'=>'TRY','subtotal'=>1200,'tax_total'=>240,'grand_total'=>1440,'created_at'=>$now,'updated_at'=>$now]);$document=(int)$db->insertID();
            $db->table('sales_document_items')->insert(['sales_document_id'=>$document,'product_id'=>$product,'product_variant_id'=>$variant,'product_code_snapshot'=>'TEST-STK-URUN-'.$suffix,'product_name_snapshot'=>'Stok Test Ürünü','variant_snapshot'=>'M / Lacivert','quantity'=>12,'reserved_quantity'=>0,'fulfilled_quantity'=>0,'unit_price'=>100,'discount_percent'=>0,'discount_amount'=>0,'net_amount'=>1200,'tax_rate'=>20,'tax_amount'=>240,'line_total'=>1440,'created_at'=>$now,'updated_at'=>$now]);
            $service=new StockService((int)$user['id']);$service->move($warehouseA,$variant,10,'purchase_receipt','İlk mal kabul','purchase_order',$purchase);$balance=$service->balance($warehouseA,$variant);$this->assert((float)$balance['on_hand_quantity']===10.0,'Mal kabul stoğu artırmadı.');
            $first=$service->reserveOrder($document,$warehouseA);$this->assert($first['reserved']===10.0&&$first['missing']===2.0&&$first['status']==='procurement_waiting','Kısmi ayırma/tedarik bekleme yanlış.');
            $service->move($warehouseA,$variant,5,'purchase_receipt','İkinci mal kabul','purchase_order',$purchase);$second=$service->reserveOrder($document,$warehouseA);$this->assert($second['reserved']===2.0&&$second['missing']===0.0&&$second['status']==='reserved','Eksik stok geldikten sonra ayırma yanlış.');
            $ship=$service->shipReserved($document,'Test sevkiyatı');$balance=$service->balance($warehouseA,$variant);$this->assert($ship['status']==='shipped'&&(float)$balance['on_hand_quantity']===3.0&&(float)$balance['reserved_quantity']===0.0,'Sevkiyat stoğu doğru azaltmadı.');
            $service->move($warehouseA,$variant,2,'customer_return','Kullanılabilir müşteri iadesi');$this->assert((float)$service->balance($warehouseA,$variant)['on_hand_quantity']===5.0,'İade stoğa girmedi.');
            try{$service->move($warehouseA,$variant,-6,'manual_out','Eksi stok denemesi');throw new RuntimeException('Eksi stok engellenmedi.');}catch(RuntimeException $e){if($e->getMessage()==='Eksi stok engellenmedi.')throw$e;}
            $service->move($warehouseA,$variant,-1,'adjustment','Sayım farkı','stock_count',1);$service->transfer($warehouseA,$warehouseB,$variant,2,'Depolar arası test');$a=$service->balance($warehouseA,$variant);$b=$service->balance($warehouseB,$variant);$this->assert((float)$a['on_hand_quantity']===2.0&&(float)$b['on_hand_quantity']===2.0,'Sayım düzeltmesi veya transfer bakiyesi yanlış.');
            $this->assert($db->table('stock_movements')->where('product_variant_id',$variant)->countAllResults()===8,'Beklenen stok hareketi geçmişi oluşmadı.');$db->transRollback();if($createdUser)$db->table('users')->where('id',$createdUser)->delete();CLI::write('Alış, mal kabul, kısmi ayırma, sevkiyat, iade, eksi stok, sayım ve transfer doğrulandı.','green');return EXIT_SUCCESS;
        }catch(Throwable $e){$db->transRollback();if($createdUser)$db->table('users')->where('id',$createdUser)->delete();CLI::error($e->getMessage());return EXIT_ERROR;}
    }
    private function assert(bool $condition,string $message):void{if(!$condition)throw new RuntimeException($message);}
}
