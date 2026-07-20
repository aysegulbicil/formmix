<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\AuditLogger;
use App\Models\CustomerModel;
use App\Models\EmployeeModel;
use App\Models\SalesDocumentItemModel;
use App\Models\SalesDocumentModel;
use App\Models\SalesDocumentStatusHistoryModel;
use App\Services\ProductPriceResolver;
use App\Services\SalesDocumentCalculator;
use App\Services\MobileNotificationService;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

final class SalesDocumentsController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked=$this->guard()) return $blocked;
        [$page,$perPage]=$this->pagination();
        $model=(new SalesDocumentModel())->select('sales_documents.*,customers.company_name')->join('customers','customers.id=sales_documents.customer_id');
        $this->scope($model);
        $q=trim((string)$this->request->getGet('q')); if($q!=='')$model->groupStart()->like('document_number',$q)->orLike('customers.company_name',$q)->groupEnd();
        $type=(string)$this->request->getGet('type'); if(in_array($type,['quote','order'],true))$model->where('document_type',$type);
        $status=(string)$this->request->getGet('status'); if($status!=='')$model->where('status',$status);
        $total=(clone$model)->countAllResults(false); $rows=$model->orderBy('sales_documents.created_at','DESC')->findAll($perPage,($page-1)*$perPage);
        return $this->ok($rows,['page'=>$page,'per_page'=>$perPage,'total'=>$total,'last_page'=>(int)ceil($total/$perPage)]);
    }

    public function show(int $id): ResponseInterface
    {
        if($blocked=$this->guard())return$blocked; $doc=$this->visible($id); if(!$doc)return$this->error('NOT_FOUND','Belge bulunamadi.',404);
        $doc['items']=(new SalesDocumentItemModel())->where('sales_document_id',$id)->orderBy('id')->findAll();
        $doc['history']=(new SalesDocumentStatusHistoryModel())->where('sales_document_id',$id)->orderBy('created_at','DESC')->findAll();
        return$this->ok($doc);
    }

    public function create(): ResponseInterface
    {
        if($blocked=$this->guard('orders.create'))return$blocked; if($replay=$this->replay('sales_document.create'))return$replay;
        if($this->request->getHeaderLine('Idempotency-Key')==='')return$this->error('IDEMPOTENCY_KEY_REQUIRED','Idempotency-Key zorunludur.',400);
        $input=$this->input(); $type=in_array(($input['document_type']??''),['quote','order'],true)?$input['document_type']:'order'; $intent=($input['intent']??'draft')==='submit'?'submit':'draft';
        $customer=$this->customer((int)($input['customer_id']??0)); if(!$customer)return$this->error('NOT_FOUND','Musteri bulunamadi.',404);
        try{$prepared=$this->items((int)$customer['id'],(array)($input['items']??[]));}catch(RuntimeException $e){if(str_starts_with($e->getMessage(),'PRICE_CHANGED|'))return$this->error('PRICE_CHANGED',substr($e->getMessage(),14),409);return$this->error('VALIDATION_FAILED',$e->getMessage(),422);}
        $salesEmployee=(int)($customer['current_owner_employee_id']?:$this->employee()['id']); $status=$type==='order'&&$intent==='submit'?'approved':'draft';
        try{$this->discount($prepared['items'],$salesEmployee);if($status==='approved')$this->validateOrder($input);}catch(RuntimeException $e){return$this->error('VALIDATION_FAILED',$e->getMessage(),422);}
        $now=date('Y-m-d H:i:s'); $data=['document_number'=>$this->number($type),'document_type'=>$type,'customer_id'=>$customer['id'],'customer_owner_employee_id'=>$customer['current_owner_employee_id']?:null,'sales_employee_id'=>$salesEmployee,'preparation_employee_id'=>$type==='order'?(int)($input['preparation_employee_id']??0)?:null:null,'design_employee_id'=>$type==='order'?(int)($input['design_employee_id']??0)?:null:null,'print_employee_id'=>$type==='order'?(int)($input['print_employee_id']??0)?:null:null,'created_by_user_id'=>auth()->id(),'approved_by_user_id'=>$status==='approved'?auth()->id():null,'status'=>$status,'client_reference'=>'mobile-'.$this->request->getHeaderLine('Idempotency-Key'),'currency'=>'TRY','approved_at'=>$status==='approved'?$now:null,'notes'=>trim((string)($input['notes']??''))?:null,'delivery_address'=>trim((string)($input['delivery_address']??''))?:null,'requested_delivery_date'=>$input['requested_delivery_date']??null]+$prepared['totals'];
        $db=db_connect();$db->transBegin();try{$model=new SalesDocumentModel();if(!$model->insert($data))throw new RuntimeException(implode(' ',$model->errors()));$id=(int)$model->getInsertID();foreach($prepared['items']as$item){$item['sales_document_id']=$id;if(!(new SalesDocumentItemModel())->insert($item))throw new RuntimeException('Belge satiri kaydedilemedi.');}$this->history($id,null,$status,$status==='draft'?'Mobil taslak kaydedildi':'Mobil siparis olusturuldu');if(!$db->transStatus())throw new RuntimeException('Belge kaydedilemedi.');$db->transCommit();}catch(Throwable$e){$db->transRollback();return$this->error('SAVE_FAILED',$e->getMessage(),422);}
        (new AuditLogger())->record($type.'.created','sales_document',$id,null,$data);
        if($status==='approved')foreach(['preparation_employee_id','design_employee_id','print_employee_id']as$field)(new MobileNotificationService())->queueForEmployee((int)$data[$field],'order.assignment','Yeni siparis gorevi',$data['document_number'].' numarali siparis size atandi.','/sales-documents/'.$id,'sales_document',$id);
        $body=['data'=>(new SalesDocumentModel())->find($id)];$this->remember('sales_document.create','sales_document',$id,$body);return$this->response->setStatusCode(201)->setJSON($body);
    }

    public function finalize(int $id): ResponseInterface
    {
        if($blocked=$this->guard('orders.create'))return$blocked;$doc=$this->visible($id);if(!$doc||$doc['document_type']!=='quote'||$doc['status']!=='draft'||(int)$doc['created_by_user_id']!==(int)auth()->id())return$this->error('INVALID_TRANSITION','Yalniz teklifi hazirlayan kullanici taslak teklifi kesinlestirebilir.',409);
        try{$this->discount((new SalesDocumentItemModel())->where('sales_document_id',$id)->findAll(),(int)$doc['sales_employee_id']);}catch(RuntimeException$e){return$this->error('DISCOUNT_LIMIT_EXCEEDED',$e->getMessage(),422);}
        (new SalesDocumentModel())->update($id,['status'=>'approved','approved_by_user_id'=>auth()->id(),'approved_at'=>date('Y-m-d H:i:s')]);$this->history($id,'draft','approved','Mobil teklif kesinlestirildi');(new AuditLogger())->record('quote.finalized','sales_document',$id,['status'=>'draft'],['status'=>'approved']);return$this->ok((new SalesDocumentModel())->find($id));
    }

    public function update(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('orders.create')) return $blocked; $doc = $this->visible($id); if (! $doc) return $this->error('NOT_FOUND', 'Belge bulunamadı.', 404); if ($doc['status'] !== 'draft') return $this->error('INVALID_TRANSITION', 'Yalnızca taslak belge düzenlenebilir.', 409); $input = $this->input(); if ((string) ($input['expected_updated_at'] ?? '') !== (string) $doc['updated_at']) return $this->error('STALE_RESOURCE', 'Belge başka bir oturumda güncellendi.', 409); $customer = $this->customer((int) ($input['customer_id'] ?? $doc['customer_id'])); if (! $customer) return $this->error('NOT_FOUND', 'Müşteri bulunamadı.', 404);
        try { $prepared = $this->items((int) $customer['id'], (array) ($input['items'] ?? [])); $this->discount($prepared['items'], (int) $doc['sales_employee_id']); } catch (RuntimeException $e) { if (str_starts_with($e->getMessage(), 'PRICE_CHANGED|')) return $this->error('PRICE_CHANGED', substr($e->getMessage(), 14), 409); return $this->error('VALIDATION_FAILED', $e->getMessage(), 422); }
        $data = ['customer_id' => $customer['id'], 'customer_owner_employee_id' => $customer['current_owner_employee_id'] ?: null, 'preparation_employee_id' => $doc['document_type'] === 'order' ? (int) ($input['preparation_employee_id'] ?? 0) ?: null : null, 'design_employee_id' => $doc['document_type'] === 'order' ? (int) ($input['design_employee_id'] ?? 0) ?: null : null, 'print_employee_id' => $doc['document_type'] === 'order' ? (int) ($input['print_employee_id'] ?? 0) ?: null : null, 'notes' => trim((string) ($input['notes'] ?? '')) ?: null, 'delivery_address' => trim((string) ($input['delivery_address'] ?? '')) ?: null, 'requested_delivery_date' => $input['requested_delivery_date'] ?? null] + $prepared['totals']; $db = db_connect(); $db->transBegin(); try { (new SalesDocumentModel())->update($id, $data); (new SalesDocumentItemModel())->where('sales_document_id', $id)->delete(); foreach ($prepared['items'] as $item) { $item['sales_document_id'] = $id; (new SalesDocumentItemModel())->insert($item); } if (! $db->transStatus()) throw new RuntimeException('Belge güncellenemedi.'); $db->transCommit(); } catch (Throwable $e) { $db->transRollback(); return $this->error('SAVE_FAILED', $e->getMessage(), 422); } (new AuditLogger())->record($doc['document_type'] . '.updated', 'sales_document', $id, $doc, $data); return $this->ok((new SalesDocumentModel())->find($id));
    }

    public function submit(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('orders.create')) return $blocked; $doc = $this->visible($id); if (! $doc || $doc['status'] !== 'draft') return $this->error('INVALID_TRANSITION', 'Yalnızca taslak belge gönderilebilir.', 409); if ($doc['document_type'] === 'quote') return $this->finalize($id); try { $this->validateOrder($doc); $this->discount((new SalesDocumentItemModel())->where('sales_document_id', $id)->findAll(), (int) $doc['sales_employee_id']); } catch (RuntimeException $e) { return $this->error('VALIDATION_FAILED', $e->getMessage(), 422); } $now = date('Y-m-d H:i:s'); (new SalesDocumentModel())->update($id, ['status' => 'approved', 'approved_by_user_id' => auth()->id(), 'approved_at' => $now]); $this->history($id, 'draft', 'approved', 'Mobil uygulamadan sipariş oluşturuldu'); (new AuditLogger())->record('order.submitted', 'sales_document', $id, ['status' => 'draft'], ['status' => 'approved']); return $this->ok((new SalesDocumentModel())->find($id));
    }

    public function cancel(int $id): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked; $doc = $this->visible($id); if (! $doc || in_array($doc['status'], ['cancelled', 'delivered'], true)) return $this->error('INVALID_TRANSITION', 'Belge iptal edilemez.', 409); $reason = trim((string) ($this->input()['reason'] ?? '')); if ($reason === '') return $this->error('VALIDATION_FAILED', 'İptal gerekçesi zorunludur.', 422, ['reason' => 'Zorunlu.']); if ($doc['document_type'] === 'order') (new \App\Services\StockService((int) auth()->id()))->releaseDocumentReservations($id); (new SalesDocumentModel())->update($id, ['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s'), 'cancellation_reason' => $reason]); $this->history($id, $doc['status'], 'cancelled', $reason); (new AuditLogger())->record('order.cancelled', 'sales_document', $id, ['status' => $doc['status']], ['status' => 'cancelled', 'reason' => $reason]); return $this->ok(['id' => $id, 'status' => 'cancelled']);
    }

    public function convert(int $id): ResponseInterface
    {
        if($blocked=$this->guard('orders.create'))return$blocked;if($replay=$this->replay('quote.convert'))return$replay;if($this->request->getHeaderLine('Idempotency-Key')==='')return$this->error('IDEMPOTENCY_KEY_REQUIRED','Idempotency-Key zorunludur.',400);
        $quote=$this->visible($id);if(!$quote||$quote['document_type']!=='quote'||$quote['status']!=='approved')return$this->error('INVALID_TRANSITION','Yalniz kesinlesmis teklif donusturulebilir.',409);
        $copy=array_intersect_key($quote,array_flip(['customer_id','customer_owner_employee_id','sales_employee_id','currency','subtotal','discount_total','tax_total','grand_total','notes','delivery_address','requested_delivery_date']));$copy+=['document_number'=>$this->number('order'),'document_type'=>'order','source_quote_id'=>$id,'created_by_user_id'=>auth()->id(),'status'=>'draft','client_reference'=>'mobile-'.$this->request->getHeaderLine('Idempotency-Key')];
        $db=db_connect();$db->transBegin();try{$m=new SalesDocumentModel();$m->insert($copy);$newId=(int)$m->getInsertID();foreach((new SalesDocumentItemModel())->where('sales_document_id',$id)->findAll()as$item){unset($item['id'],$item['created_at'],$item['updated_at']);$item['sales_document_id']=$newId;(new SalesDocumentItemModel())->insert($item);}$this->history($newId,null,'draft','Kesinlesmis tekliften mobil siparis taslagi');$db->transCommit();}catch(Throwable$e){$db->transRollback();return$this->error('SAVE_FAILED',$e->getMessage(),422);}$body=['data'=>(new SalesDocumentModel())->find($newId)];$this->remember('quote.convert','sales_document',$newId,$body);return$this->response->setStatusCode(201)->setJSON($body);
    }

    private function customer(int$id):?array{$row=(new CustomerModel())->find($id);if(!$row)return null;if(!auth()->user()?->can('customers.view-all')&&(int)($row['current_owner_employee_id']??0)!==(int)$this->employee()['id'])return null;return$row;}
    private function visible(int$id):?array{$m=(new SalesDocumentModel())->select('sales_documents.*,customers.company_name')->join('customers','customers.id=sales_documents.customer_id');$this->scope($m);return$m->where('sales_documents.id',$id)->first();}
    private function scope(SalesDocumentModel$m):void{if(!auth()->user()?->can('orders.view-all')){$e=$this->employee()['id'];$m->groupStart()->where('sales_employee_id',$e)->orWhere('preparation_employee_id',$e)->orWhere('design_employee_id',$e)->orWhere('print_employee_id',$e)->groupEnd();}}
    private function items(int$customerId,array$input):array{if($input===[])throw new RuntimeException('En az bir urun ekleyin.');$resolver=new ProductPriceResolver();$calc=new SalesDocumentCalculator();$items=[];$lines=[];foreach($input as$row){$p=$resolver->resolve($customerId,(int)($row['product_id']??0),(int)($row['product_variant_id']??0));$expected=isset($row['expected_unit_price'])?(float)$row['expected_unit_price']:null;if($expected!==null&&abs($expected-(float)$p['unit_price'])>.005)throw new RuntimeException('PRICE_CHANGED|Urun fiyati degisti. Yeni fiyat: '.$p['unit_price']);$qty=(float)($row['quantity']??0);$discount=(float)($row['discount_percent']??0);$line=$calc->calculateLine($qty,(float)$p['unit_price'],$discount,(float)$p['tax_rate']);$lines[]=$line;$items[]=['product_id'=>$p['product']['id'],'product_variant_id'=>$p['variant']['id'],'product_code_snapshot'=>$p['product']['product_code'],'product_name_snapshot'=>$p['product']['name'],'variant_snapshot'=>$p['variant_label'],'quantity'=>$qty,'unit_price'=>$p['unit_price'],'discount_percent'=>$discount,'discount_amount'=>$line['discount_amount'],'net_amount'=>$line['net_amount'],'tax_rate'=>$p['tax_rate'],'tax_amount'=>$line['tax_amount'],'line_total'=>$line['line_total']];}return['items'=>$items,'totals'=>$calc->calculateDocument($lines)];}
    private function discount(array$items,int$employeeId):void{$e=(new EmployeeModel())->find($employeeId);if(!$e)throw new RuntimeException('Satis personeli belirlenmelidir.');foreach($items as$item)if((float)$item['discount_percent']>(float)$e['max_discount_percent'])throw new RuntimeException($e['full_name'].' icin indirim siniri %'.$e['max_discount_percent'].'.');}
    private function validateOrder(array$i):void{if(trim((string)($i['delivery_address']??''))==='')throw new RuntimeException('Teslimat adresi zorunludur.');foreach(['preparation_employee_id','design_employee_id','print_employee_id']as$f)if((int)($i[$f]??0)<1)throw new RuntimeException('Hazirlama, tasarim ve baski personelleri secilmelidir.');}
    private function number(string$type):string{do{$n=($type==='quote'?'TEK':'SIP').'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(2)));}while((new SalesDocumentModel())->where('document_number',$n)->first());return$n;}
    private function history(int$id,?string$old,string$new,string$reason):void{(new SalesDocumentStatusHistoryModel())->insert(['sales_document_id'=>$id,'old_status'=>$old,'new_status'=>$new,'reason'=>$reason,'changed_by_user_id'=>auth()->id(),'created_at'=>date('Y-m-d H:i:s')]);}
}
