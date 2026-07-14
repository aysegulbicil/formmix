<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\ProductPriceResolver;
use App\Services\SalesDocumentCalculator;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
use Throwable;

class VerifyOrderFoundation extends BaseCommand
{
    protected $group='FORMMIX'; protected $name='formmix:verify-order-foundation';
    protected $description='Teklif ve sipariş veri temelini geçici kayıtlarla doğrular.';

    public function run(array $params): int
    {
        $db=db_connect(); $suffix=strtoupper(bin2hex(random_bytes(4))); $now=date('Y-m-d H:i:s'); $createdUserId=null;
        $user=$db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        if (!$user) {
            $users=auth()->getProvider();
            $entity=new User(['username'=>'order_test_'.strtolower($suffix),'email'=>'order-test-'.strtolower($suffix).'@formmix.local','password'=>bin2hex(random_bytes(16))]);
            if(!$users->save($entity)){ CLI::error(implode(' ',$users->errors())); return EXIT_ERROR; }
            $createdUserId=(int)$users->getInsertID(); $user=['id'=>$createdUserId];
        }
        $db->transBegin();
        try {
            $db->table('employees')->insert(['employee_code'=>'TEST-'.$suffix,'full_name'=>'Sipariş Test Personeli','max_discount_percent'=>5,'can_collect_payment'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now]); $employeeId=(int)$db->insertID();
            $db->table('customers')->insert(['customer_code'=>'TEST-MUS-'.$suffix,'company_name'=>'Sipariş Test Müşterisi','city'=>'İstanbul','district'=>'Test','status'=>'active','payment_term_days'=>30,'current_owner_employee_id'=>$employeeId,'created_by_user_id'=>(int)$user['id'],'created_at'=>$now,'updated_at'=>$now]); $customerId=(int)$db->insertID();
            $db->table('customer_assignments')->insert(['customer_id'=>$customerId,'employee_id'=>$employeeId,'started_at'=>$now,'reason'=>'Doğrulama','assigned_by_user_id'=>(int)$user['id'],'created_at'=>$now]);
            $db->table('product_categories')->insert(['code'=>'TEST-'.$suffix,'name'=>'Sipariş Test Kategorisi','is_active'=>1,'created_at'=>$now,'updated_at'=>$now]); $categoryId=(int)$db->insertID();
            $db->table('products')->insert(['category_id'=>$categoryId,'product_code'=>'TEST-URUN-'.$suffix,'name'=>'Sipariş Test Ürünü','tax_rate'=>20,'cost_price'=>40,'list_price'=>100,'currency'=>'TRY','is_active'=>1,'track_stock'=>1,'critical_stock_level'=>0,'customization_mode'=>'optional','created_by_user_id'=>(int)$user['id'],'created_at'=>$now,'updated_at'=>$now]); $productId=(int)$db->insertID();
            $db->table('product_variants')->insert(['product_id'=>$productId,'sku'=>'TEST-VAR-'.$suffix,'size'=>'M','color'=>'Lacivert','preparation_type'=>'plain','list_price_override'=>110,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now]); $variantId=(int)$db->insertID();
            $db->table('customer_price_groups')->insert(['code'=>'TEST-GRP-'.$suffix,'name'=>'Sipariş Test Grubu','discount_percent'=>0,'is_active'=>1,'created_at'=>$now,'updated_at'=>$now]); $groupId=(int)$db->insertID();
            $db->table('customer_price_group_members')->insert(['customer_price_group_id'=>$groupId,'customer_id'=>$customerId,'starts_at'=>$now,'assigned_by_user_id'=>(int)$user['id'],'created_at'=>$now]);

            foreach ([[null,$groupId,null,90],[null,$groupId,$variantId,80],[$customerId,null,null,70],[$customerId,null,$variantId,60]] as [$customer,$group,$variant,$price]) {
                $db->table('product_special_prices')->insert(['product_id'=>$productId,'product_variant_id'=>$variant,'customer_price_group_id'=>$group,'customer_id'=>$customer,'unit_price'=>$price,'currency'=>'TRY','is_active'=>1,'created_by_user_id'=>(int)$user['id'],'created_at'=>$now,'updated_at'=>$now]);
            }
            $resolved=(new ProductPriceResolver())->resolve($customerId,$productId,$variantId);
            $this->assert((float)$resolved['unit_price']===60.0 && $resolved['price_source']==='customer_variant','Özel fiyat önceliği yanlış.');
            $line=(new SalesDocumentCalculator())->calculateLine(3,60,10,20);
            $totals=(new SalesDocumentCalculator())->calculateDocument([$line]);
            $this->assert($line['discount_amount']===18.0 && $line['net_amount']===162.0 && $line['tax_amount']===32.4 && $totals['grand_total']===194.4,'İndirim, vergi veya belge toplamı yanlış.');

            $number='TEST-SIP-'.$suffix; $reference='test-client-'.$suffix;
            $db->table('sales_documents')->insert(['document_number'=>$number,'document_type'=>'order','customer_id'=>$customerId,'customer_owner_employee_id'=>$employeeId,'sales_employee_id'=>$employeeId,'created_by_user_id'=>(int)$user['id'],'status'=>'pending_approval','client_reference'=>$reference,'currency'=>'TRY','subtotal'=>$totals['subtotal'],'discount_total'=>$totals['discount_total'],'tax_total'=>$totals['tax_total'],'grand_total'=>$totals['grand_total'],'created_at'=>$now,'updated_at'=>$now]); $documentId=(int)$db->insertID();
            $db->table('sales_document_items')->insert(['sales_document_id'=>$documentId,'product_id'=>$productId,'product_variant_id'=>$variantId,'product_code_snapshot'=>'TEST-URUN-'.$suffix,'product_name_snapshot'=>'Sipariş Test Ürünü','variant_snapshot'=>'M / Lacivert / TEST-VAR-'.$suffix,'quantity'=>3,'unit_price'=>60,'discount_percent'=>10,'discount_amount'=>$line['discount_amount'],'net_amount'=>$line['net_amount'],'tax_rate'=>20,'tax_amount'=>$line['tax_amount'],'line_total'=>$line['line_total'],'created_at'=>$now,'updated_at'=>$now]);
            $db->table('sales_document_status_history')->insert(['sales_document_id'=>$documentId,'old_status'=>'draft','new_status'=>'pending_approval','reason'=>'Personel sınırı aşıldı','changed_by_user_id'=>(int)$user['id'],'created_at'=>$now]);
            $db->table('sales_document_approvals')->insert(['sales_document_id'=>$documentId,'approval_type'=>'discount_limit','requested_percent'=>10,'status'=>'pending','requested_by_user_id'=>(int)$user['id'],'created_at'=>$now,'updated_at'=>$now]);
            $item=$db->table('sales_document_items')->where('sales_document_id',$documentId)->get()->getRowArray();
            $this->assert($item && $item['product_name_snapshot']==='Sipariş Test Ürünü' && (float)$item['unit_price']===60.0,'Ürün/fiyat anlık kopyası yazılmadı.');
            $this->assert($db->table('sales_document_status_history')->where('sales_document_id',$documentId)->countAllResults()===1,'Durum geçmişi yazılmadı.');
            $this->assert($db->table('sales_document_approvals')->where(['sales_document_id'=>$documentId,'status'=>'pending'])->countAllResults()===1,'Yetki üstü indirim onaya düşmedi.');
            $this->assert((int)$db->table('sales_documents')->where('id',$documentId)->get()->getRow('customer_id')===$customerId,'Müşteri-sipariş ilişkisi yanlış.');
            $this->assert((int)$db->table('sales_documents')->where('id',$documentId)->get()->getRow('sales_employee_id')===$employeeId,'Sipariş-personel ilişkisi yanlış.');

            $duplicateNumber=$this->duplicateRejected($db,array_merge($db->table('sales_documents')->where('id',$documentId)->get()->getRowArray(),['id'=>null,'client_reference'=>'other-'.$suffix]),'document_number');
            $duplicateClient=$this->duplicateRejected($db,array_merge($db->table('sales_documents')->where('id',$documentId)->get()->getRowArray(),['id'=>null,'document_number'=>'OTHER-'.$suffix]),'client_reference');
            $this->assert($duplicateNumber && $duplicateClient,'document_number veya client_reference benzersizliği çalışmıyor.');
            CLI::write('Sipariş veri temeli doğrulandı: ilişkiler, fiyat önceliği, anlık kopyalar, hesaplar, geçmiş, onay ve benzersizlik.', 'green');
            return EXIT_SUCCESS;
        } catch (Throwable $e) { CLI::error($e->getMessage()); return EXIT_ERROR; }
        finally { $db->transRollback(); if($createdUserId!==null) auth()->getProvider()->delete($createdUserId,true); }
    }

    private function duplicateRejected($db,array $row,string $field): bool
    {
        unset($row['id']);
        try { return $db->table('sales_documents')->insert($row) === false; } catch (Throwable) { return true; }
    }
    private function assert(bool $condition,string $message): void { if(!$condition) throw new \RuntimeException($message); }
}
