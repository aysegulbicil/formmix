<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\CustomerModel;
use App\Models\EmployeeModel;
use App\Models\ProductModel;
use App\Models\ProductVariantModel;
use App\Models\SalesDocumentApprovalModel;
use App\Models\SalesDocumentItemModel;
use App\Models\SalesDocumentModel;
use App\Models\SalesDocumentStatusHistoryModel;
use App\Models\WarehouseModel;
use App\Services\ProductPriceResolver;
use App\Services\SalesDocumentCalculator;
use App\Services\StockService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;
use Throwable;

class SalesDocuments extends BaseController
{
    private const TYPES=['quote'=>'Teklif','order'=>'Sipariş'];
    private const STATUSES=['draft'=>'Taslak','pending_approval'=>'Onay bekliyor','approved'=>'Onaylandı','procurement_waiting'=>'Tedarik bekliyor','reserved'=>'Stok ayrıldı','partially_shipped'=>'Kısmi sevk','shipped'=>'Sevk edildi','delivered'=>'Teslim edildi','cancelled'=>'İptal edildi'];

    public function index(): string
    {
        $this->requireViewAccess(); $q=trim((string)$this->request->getGet('q')); $status=(string)$this->request->getGet('durum'); $employee=(int)$this->request->getGet('personel');
        $from=(string)$this->request->getGet('baslangic'); $until=(string)$this->request->getGet('bitis');
        $model=new SalesDocumentModel(); $model->select('sales_documents.*,customers.company_name,employees.full_name AS sales_employee_name')
            ->join('customers','customers.id=sales_documents.customer_id')->join('employees','employees.id=sales_documents.sales_employee_id','left');
        $this->applyVisibility($model);
        if($q!=='') $model->groupStart()->like('sales_documents.document_number',$q)->orLike('customers.company_name',$q)->groupEnd();
        if(isset(self::STATUSES[$status])) $model->where('sales_documents.status',$status);
        if($employee>0 && $this->canViewAll()) $model->where('sales_documents.sales_employee_id',$employee);
        if($from!=='') $model->where('sales_documents.created_at >=',$from.' 00:00:00'); if($until!=='') $model->where('sales_documents.created_at <=',$until.' 23:59:59');
        return view('panel/sales_documents/index',['title'=>'Teklif ve Siparişler | FORMMIX','pageTitle'=>'Teklif ve siparişler','activeNav'=>'orders','documents'=>$model->orderBy('sales_documents.created_at','DESC')->findAll(),'types'=>self::TYPES,'statuses'=>self::STATUSES,'employees'=>$this->activeEmployees(),'q'=>$q,'status'=>$status,'employee'=>$employee,'from'=>$from,'until'=>$until,'canCreate'=>$this->canCreate()]);
    }

    public function create(): string
    {
        $this->requireCreate(); $type=(string)$this->request->getGet('tur'); if(!isset(self::TYPES[$type])) $type='order';
        $customerId=(int)$this->request->getGet('musteri'); if($customerId>0) $this->visibleCustomer($customerId);
        return view('panel/sales_documents/form',$this->formData(null,$type,$customerId));
    }
    public function store(): RedirectResponse { $this->requireCreate(); return $this->persist(null); }
    public function edit(int $id): string { $doc=$this->visibleDocument($id); $this->requireEditable($doc); return view('panel/sales_documents/form',$this->formData($doc,$doc['document_type'],(int)$doc['customer_id'])); }
    public function update(int $id): RedirectResponse { $doc=$this->visibleDocument($id); $this->requireEditable($doc); return $this->persist($doc); }

    public function show(int $id): string
    {
        $doc=$this->visibleDocument($id); $items=(new SalesDocumentItemModel())->where('sales_document_id',$id)->orderBy('id')->findAll();
        $history=(new SalesDocumentStatusHistoryModel())->where('sales_document_id',$id)->orderBy('created_at','DESC')->findAll();
        $approval=(new SalesDocumentApprovalModel())->where('sales_document_id',$id)->orderBy('id','DESC')->first();
        return view('panel/sales_documents/show',['title'=>$doc['document_number'].' | FORMMIX','pageTitle'=>$doc['document_number'],'activeNav'=>'orders','document'=>$doc,'items'=>$items,'history'=>$history,'approval'=>$approval,'types'=>self::TYPES,'statuses'=>self::STATUSES,'canEdit'=>$this->isEditable($doc),'canApprove'=>$this->canApprove($doc),'canCancel'=>$this->canCancel($doc),'canFulfill'=>($doc['document_type']==='order'&&(auth()->user()?->can('orders.fulfill')??false)),'warehouses'=>(new WarehouseModel())->where('is_active',1)->orderBy('name')->findAll(),'savedReference'=>(string)$this->request->getGet('kaydedildi')]);
    }

    public function price(): ResponseInterface
    {
        $this->requireCreate();
        try { $customer=$this->visibleCustomer((int)$this->request->getGet('musteri')); $price=(new ProductPriceResolver())->resolve((int)$customer['id'],(int)$this->request->getGet('urun'),(int)$this->request->getGet('varyant')); return $this->response->setJSON(['ok'=>true,'unit_price'=>$price['unit_price'],'tax_rate'=>$price['tax_rate'],'source'=>$price['price_source']]); }
        catch(Throwable $e){ return $this->response->setStatusCode(422)->setJSON(['ok'=>false,'message'=>$e->getMessage()]); }
    }

    public function submit(int $id): RedirectResponse
    {
        $doc=$this->visibleDocument($id); $this->requireEditable($doc);
        if($doc['document_type']==='order' && trim((string)$doc['delivery_address'])==='') return redirect()->back()->with('errors',['submit'=>'Sipariş gönderilmeden önce teslimat adresi zorunludur.']);
        $max=(float)((new SalesDocumentItemModel())->selectMax('discount_percent','max_discount')->where('sales_document_id',$id)->first()['max_discount']??0); $limit=$this->currentDiscountLimit(); $requiresApproval=$max>$limit||$max>15;
        $db=db_connect();$db->transBegin();try{(new SalesDocumentModel())->update($id,['status'=>'pending_approval']);$this->history($id,'draft','pending_approval','Onaya gönderildi');if($requiresApproval)(new SalesDocumentApprovalModel())->insert(['sales_document_id'=>$id,'approval_type'=>$max>15?'high_discount':'discount_limit','requested_percent'=>$max,'status'=>'pending','requested_by_user_id'=>auth()->id()]);if(!$db->transStatus())throw new RuntimeException('Belge onaya gönderilemedi.');$db->transCommit();}catch(Throwable $e){$db->transRollback();return redirect()->back()->with('errors',['submit'=>$e->getMessage()]);}
        (new AuditLogger())->record($doc['document_type'].'.submitted','sales_document',$id,['status'=>'draft'],['status'=>'pending_approval']);if($requiresApproval)(new AuditLogger())->record('order.discount_approval_requested','sales_document',$id,null,['requested_percent'=>$max,'employee_limit'=>$limit]);return redirect()->back()->with('message','Belge onaya gönderildi.');
    }

    public function approve(int $id): RedirectResponse
    {
        $doc=$this->visibleDocument($id); if(!$this->canApprove($doc)) throw PageNotFoundException::forPageNotFound();
        if((int)$doc['created_by_user_id']===(int)auth()->id()) return redirect()->back()->with('errors',['approve'=>'Belgeyi oluşturan kişi kendi belgesini onaylayamaz.']);
        if($doc['status']!=='pending_approval') return redirect()->back()->with('errors',['approve'=>'Yalnızca onay bekleyen belge onaylanabilir.']);
        if($doc['document_type']==='order' && trim((string)$doc['delivery_address'])==='') return redirect()->back()->with('errors',['approve'=>'Teslimat adresi olmayan sipariş onaylanamaz.']);
        $max=(float)((new SalesDocumentItemModel())->selectMax('discount_percent','max_discount')->where('sales_document_id',$id)->first()['max_discount']??0);
        if($max>15 && !(auth()->user()?->can('orders.approve-high')??false)) return redirect()->back()->with('errors',['approve'=>'%15 üzerindeki indirimi yalnızca işletme sahibi onaylayabilir.']);
        if($max>$this->currentDiscountLimit() && !(auth()->user()?->can('orders.approve-high')??false)) return redirect()->back()->with('errors',['approve'=>'Bu indirim oranı onaylayan personelin sınırını aşıyor.']);
        $db=db_connect(); $db->transBegin(); try { (new SalesDocumentModel())->update($id,['status'=>'approved','approved_by_user_id'=>auth()->id(),'approved_at'=>date('Y-m-d H:i:s')]); $this->history($id,'pending_approval','approved',trim((string)$this->request->getPost('reason'))?:'Onaylandı'); (new SalesDocumentApprovalModel())->where('sales_document_id',$id)->where('status','pending')->set(['status'=>'approved','decided_by_user_id'=>auth()->id(),'decision_note'=>trim((string)$this->request->getPost('reason'))?:null,'decided_at'=>date('Y-m-d H:i:s')])->update(); if(!$db->transStatus()) throw new RuntimeException('Onay kaydedilemedi.'); $db->transCommit(); } catch(Throwable $e){$db->transRollback(); return redirect()->back()->with('errors',['approve'=>$e->getMessage()]);}
        (new AuditLogger())->record($doc['document_type'].'.approved','sales_document',$id,['status'=>$doc['status']],['status'=>'approved']); return redirect()->back()->with('message','Belge onaylandı.');
    }

    public function reject(int $id): RedirectResponse
    {
        $doc=$this->visibleDocument($id); if(!$this->canApprove($doc) || $doc['status']!=='pending_approval') throw PageNotFoundException::forPageNotFound(); $reason=trim((string)$this->request->getPost('reason'));
        if($reason==='') return redirect()->back()->with('errors',['reject'=>'Ret nedeni zorunludur.']);
        $db=db_connect(); $db->transBegin(); try{(new SalesDocumentModel())->update($id,['status'=>'draft']);$this->history($id,'pending_approval','draft','Reddedildi: '.$reason);(new SalesDocumentApprovalModel())->where('sales_document_id',$id)->where('status','pending')->set(['status'=>'rejected','decided_by_user_id'=>auth()->id(),'decision_note'=>$reason,'decided_at'=>date('Y-m-d H:i:s')])->update();$db->transCommit();}catch(Throwable $e){$db->transRollback();return redirect()->back()->with('errors',['reject'=>$e->getMessage()]);}
        (new AuditLogger())->record($doc['document_type'].'.rejected','sales_document',$id,['status'=>'pending_approval'],['status'=>'draft','reason'=>$reason]); return redirect()->back()->with('message','Belge gerekçesiyle taslağa döndürüldü.');
    }

    public function cancel(int $id): RedirectResponse
    {
        $doc=$this->visibleDocument($id); if(!$this->canCancel($doc)) throw PageNotFoundException::forPageNotFound(); $reason=trim((string)$this->request->getPost('reason')); if($reason==='') return redirect()->back()->with('errors',['cancel'=>'İptal nedeni zorunludur.']);
        $old=$doc['status'];$db=db_connect();$db->transBegin();try{if($doc['document_type']==='order')(new StockService())->releaseDocumentReservations($id);(new SalesDocumentModel())->update($id,['status'=>'cancelled','cancelled_at'=>date('Y-m-d H:i:s'),'cancellation_reason'=>$reason]);$this->history($id,$old,'cancelled',$reason);if(!$db->transStatus())throw new RuntimeException('İptal kaydedilemedi.');$db->transCommit();}catch(Throwable $e){$db->transRollback();return redirect()->back()->with('errors',['cancel'=>$e->getMessage()]);}(new AuditLogger())->record($doc['document_type'].'.cancelled','sales_document',$id,['status'=>$old],['status'=>'cancelled','reason'=>$reason]);return redirect()->back()->with('message','Belge iptal edildi; ayrılmış stok serbest bırakıldı ve geçmiş korundu.');
    }

    public function convert(int $id): RedirectResponse
    {
        $quote=$this->visibleDocument($id); $this->requireCreate(); if($quote['document_type']!=='quote'||$quote['status']!=='approved') return redirect()->back()->with('errors',['convert'=>'Yalnızca onaylı teklif siparişe çevrilebilir.']);
        $db=db_connect();$db->transBegin();try{$data=array_intersect_key($quote,array_flip(['customer_id','customer_owner_employee_id','sales_employee_id','created_by_user_id','currency','subtotal','discount_total','tax_total','grand_total','notes','delivery_address','requested_delivery_date']));$data+=['document_number'=>$this->newNumber('order'),'document_type'=>'order','source_quote_id'=>$id,'created_by_user_id'=>auth()->id(),'status'=>'draft','client_reference'=>'convert-'.bin2hex(random_bytes(16))];$model=new SalesDocumentModel();if(!$model->insert($data))throw new RuntimeException(implode(' ',$model->errors()));$newId=(int)$model->getInsertID();foreach((new SalesDocumentItemModel())->where('sales_document_id',$id)->findAll() as $item){unset($item['id'],$item['created_at'],$item['updated_at']);$item['sales_document_id']=$newId;(new SalesDocumentItemModel())->insert($item);}$this->history($newId,null,'draft','Onaylı '.$quote['document_number'].' teklifinden oluşturuldu');$db->transCommit();}catch(Throwable $e){$db->transRollback();return redirect()->back()->with('errors',['convert'=>$e->getMessage()]);}
        (new AuditLogger())->record('quote.converted','sales_document',$id,null,['order_id'=>$newId]); return redirect()->to(site_url('panel/siparisler/'.$newId))->with('message','Teklif bağlantısı korunarak sipariş taslağı oluşturuldu.');
    }

    private function persist(?array $document): RedirectResponse
    {
        $customer=$this->visibleCustomer((int)$this->request->getPost('customer_id')); $type=(string)$this->request->getPost('document_type'); if(!isset(self::TYPES[$type])) $type='order';
        $reference=trim((string)$this->request->getPost('client_reference')); if($reference==='') return redirect()->back()->withInput()->with('errors',['form'=>'Cihaz taslak kimliği eksik.']);
        if($document===null && ($existing=(new SalesDocumentModel())->where('client_reference',$reference)->first())) return redirect()->to(site_url('panel/siparisler/'.$existing['id']))->with('message','Bu cihaz taslağı daha önce merkeze gönderilmiş; ikinci kayıt oluşturulmadı.');
        $input=json_decode((string)$this->request->getPost('items_json'),true); if(!is_array($input)||$input===[]) return redirect()->back()->withInput()->with('errors',['items'=>'En az bir ürün ekleyin.']);
        try{$prepared=$this->prepareItems((int)$customer['id'],$input);}catch(Throwable $e){return redirect()->back()->withInput()->with('errors',['items'=>$e->getMessage()]);}
        $salesEmployee=$this->resolveSalesEmployee($customer); $intent=(string)$this->request->getPost('intent'); $status=$intent==='submit'?'pending_approval':'draft'; $maxDiscount=max(array_column($prepared['items'],'discount_percent')); $limit=$this->currentDiscountLimit(); $requiresApproval=$maxDiscount>$limit||$maxDiscount>15;
        $data=['document_number'=>$document['document_number']??$this->newNumber($type),'document_type'=>$type,'source_quote_id'=>$document['source_quote_id']??null,'customer_id'=>$customer['id'],'customer_owner_employee_id'=>$customer['current_owner_employee_id']?:null,'sales_employee_id'=>$salesEmployee,'created_by_user_id'=>$document['created_by_user_id']??auth()->id(),'approved_by_user_id'=>null,'status'=>$status,'client_reference'=>$reference,'currency'=>'TRY']+$prepared['totals']+['notes'=>trim((string)$this->request->getPost('notes'))?:null,'delivery_address'=>trim((string)$this->request->getPost('delivery_address'))?:null,'requested_delivery_date'=>$this->dateOrNull((string)$this->request->getPost('requested_delivery_date'))];
        if($status==='pending_approval' && $type==='order' && !$data['delivery_address']) return redirect()->back()->withInput()->with('errors',['delivery'=>'Sipariş onaya gönderilirken teslimat adresi zorunludur.']);
        $db=db_connect();$db->transBegin();$model=new SalesDocumentModel();try{if($document===null){if(!$model->insert($data))throw new RuntimeException(implode(' ',$model->errors()));$id=(int)$model->getInsertID();$oldStatus=null;}else{$id=(int)$document['id'];if(!$model->update($id,$data))throw new RuntimeException(implode(' ',$model->errors()));(new SalesDocumentItemModel())->where('sales_document_id',$id)->delete();$oldStatus=$document['status'];}(new SalesDocumentApprovalModel())->where('sales_document_id',$id)->where('status','pending')->delete();foreach($prepared['items'] as $item){$item['sales_document_id']=$id;if(!(new SalesDocumentItemModel())->insert($item))throw new RuntimeException('Sipariş satırı kaydedilemedi.');}$this->history($id,$oldStatus,$status,$status==='draft'?'Taslak kaydedildi':'Onaya gönderildi');if($status==='pending_approval'&&$requiresApproval)(new SalesDocumentApprovalModel())->insert(['sales_document_id'=>$id,'approval_type'=>$maxDiscount>15?'high_discount':'discount_limit','requested_percent'=>$maxDiscount,'status'=>'pending','requested_by_user_id'=>auth()->id()]);if(!$db->transStatus())throw new RuntimeException('Belge kaydedilemedi.');$db->transCommit();}catch(Throwable $e){$db->transRollback();return redirect()->back()->withInput()->with('errors',['form'=>$e->getMessage()]);}
        $action=$type.'.'.($document?'updated':'created');(new AuditLogger())->record($action,'sales_document',$id,$document,$data);if($status==='pending_approval')(new AuditLogger())->record($type.'.submitted','sales_document',$id,['status'=>$oldStatus],['status'=>'pending_approval']);if($status==='pending_approval'&&$requiresApproval)(new AuditLogger())->record('order.discount_approval_requested','sales_document',$id,null,['requested_percent'=>$maxDiscount,'employee_limit'=>$limit]);
        return redirect()->to(site_url('panel/siparisler/'.$id).'?kaydedildi='.rawurlencode($reference))->with('message',$status==='draft'?'Taslak merkeze kaydedildi.':'Belge onaya gönderildi.');
    }

    private function prepareItems(int $customerId,array $input): array
    {
        $resolver=new ProductPriceResolver();$calculator=new SalesDocumentCalculator();$items=[];$lines=[];
        foreach($input as $row){$quantity=$this->decimal($row['quantity']??null);$discount=$this->decimal($row['discount_percent']??0)??0;$resolved=$resolver->resolve($customerId,(int)($row['product_id']??0),(int)($row['product_variant_id']??0));if($resolved['currency']!=='TRY')throw new RuntimeException('Bu adımda yalnızca TRY fiyatlı ürünler aynı belgeye eklenebilir.');$calc=$calculator->calculateLine((float)$quantity,(float)$resolved['unit_price'],(float)$discount,(float)$resolved['tax_rate']);$lines[]=$calc;$items[]=['product_id'=>$resolved['product']['id'],'product_variant_id'=>$resolved['variant']['id'],'product_code_snapshot'=>$resolved['product']['product_code'],'product_name_snapshot'=>$resolved['product']['name'],'variant_snapshot'=>$resolved['variant_label'],'quantity'=>$quantity,'unit_price'=>$resolved['unit_price'],'discount_percent'=>$discount,'discount_amount'=>$calc['discount_amount'],'net_amount'=>$calc['net_amount'],'tax_rate'=>$resolved['tax_rate'],'tax_amount'=>$calc['tax_amount'],'line_total'=>$calc['line_total']];}
        return ['items'=>$items,'totals'=>$calculator->calculateDocument($lines)];
    }

    private function formData(?array $doc,string $type,int $customerId): array
    {
        $customerModel=new CustomerModel();if(!$this->canViewAll())$customerModel->where('current_owner_employee_id',$this->currentEmployeeId()??0);$customers=$customerModel->whereIn('status',['candidate','active'])->orderBy('company_name')->findAll();
        $products=(new ProductModel())->where('is_active',1)->orderBy('name')->findAll();$catalog=[];$variants=new ProductVariantModel();foreach($products as $product)foreach($variants->where('product_id',$product['id'])->where('is_active',1)->orderBy('size')->orderBy('color')->findAll() as $variant){if((float)($variant['list_price_override']??0)<=0&&(float)$product['list_price']<=0&&!$this->hasSpecialPrice((int)$product['id']))continue;$catalog[]=['product_id'=>(int)$product['id'],'variant_id'=>(int)$variant['id'],'name'=>$product['name'],'code'=>$product['product_code'],'sku'=>$variant['sku'],'variant'=>implode(' / ',array_filter([$variant['size'],$variant['color']])),'image'=>$product['image_path']?base_url($product['image_path']):''];}
        $items=$doc?(new SalesDocumentItemModel())->where('sales_document_id',$doc['id'])->findAll():[];
        return ['title'=>($doc?'Belgeyi düzenle':'Yeni '.mb_strtolower(self::TYPES[$type])).' | FORMMIX','pageTitle'=>$doc?'Belgeyi düzenle':'Yeni '.mb_strtolower(self::TYPES[$type]),'activeNav'=>'orders','document'=>$doc,'documentType'=>$type,'customerId'=>$customerId,'customers'=>$customers,'employees'=>$this->activeEmployees(),'canChooseEmployee'=>$this->canViewAll(),'catalog'=>$catalog,'items'=>$items,'clientReference'=>$doc['client_reference']??''];
    }

    private function visibleCustomer(int $id): array { $customer=(new CustomerModel())->find($id); if(!$customer||(!$this->canViewAll()&&(int)($customer['current_owner_employee_id']??0)!==(int)($this->currentEmployeeId()??0))) throw PageNotFoundException::forPageNotFound('Müşteri bulunamadı.'); return $customer; }
    private function visibleDocument(int $id): array { $model=new SalesDocumentModel();$model->select('sales_documents.*,customers.company_name,employees.full_name AS sales_employee_name')->join('customers','customers.id=sales_documents.customer_id')->join('employees','employees.id=sales_documents.sales_employee_id','left');$this->applyVisibility($model);$doc=$model->find($id);if(!$doc)throw PageNotFoundException::forPageNotFound('Belge bulunamadı.');return $doc; }
    private function applyVisibility(SalesDocumentModel $model): void { if(!$this->canViewAll())$model->where('sales_documents.sales_employee_id',$this->currentEmployeeId()??0);if((auth()->user()?->can('orders.fulfill')??false)&&!(auth()->user()?->can('orders.approve')??false))$model->where('sales_documents.document_type','order')->whereIn('sales_documents.status',['approved','procurement_waiting','reserved','partially_shipped','shipped','delivered']); }
    private function resolveSalesEmployee(array $customer): ?int { $requested=(int)$this->request->getPost('sales_employee_id');if($this->canViewAll()&&$requested>0&&(new EmployeeModel())->where('is_active',1)->find($requested))return $requested;return $customer['current_owner_employee_id']?(int)$customer['current_owner_employee_id']:$this->currentEmployeeId(); }
    private function changeStatus(array $doc,string $new,string $reason,string $audit): RedirectResponse { $old=$doc['status'];(new SalesDocumentModel())->update($doc['id'],['status'=>$new]);$this->history((int)$doc['id'],$old,$new,$reason);(new AuditLogger())->record($audit,'sales_document',$doc['id'],['status'=>$old],['status'=>$new]);return redirect()->back()->with('message','Belge onaya gönderildi.'); }
    private function history(int $id,?string $old,string $new,?string $reason): void {(new SalesDocumentStatusHistoryModel())->insert(['sales_document_id'=>$id,'old_status'=>$old,'new_status'=>$new,'reason'=>$reason,'changed_by_user_id'=>auth()->id(),'created_at'=>date('Y-m-d H:i:s')]);}
    private function currentEmployeeId(): ?int {$row=(new EmployeeModel())->where('user_id',auth()->id())->where('is_active',1)->first();return $row?(int)$row['id']:null;}
    private function currentDiscountLimit(): float {$row=(new EmployeeModel())->where('user_id',auth()->id())->where('is_active',1)->first();return $row?(float)$row['max_discount_percent']:((auth()->user()?->can('orders.approve-high')??false)?100:0);}
    private function canViewAll(): bool {return auth()->user()?->can('orders.view-all')??false;} private function canCreate(): bool{return auth()->user()?->can('orders.create')??false;}
    private function canApprove(array $doc): bool {return (auth()->user()?->can('orders.approve')??false)&&$doc['status']==='pending_approval';} private function canCancel(array $doc): bool {if(in_array($doc['status'],['shipped','delivered','cancelled'],true))return false;if(in_array($doc['status'],['reserved','procurement_waiting','partially_shipped'],true))return auth()->user()?->can('orders.approve-high')??false;return (auth()->user()?->can('orders.approve')??false)||($doc['status']==='draft'&&(int)$doc['created_by_user_id']===(int)auth()->id());}
    private function isEditable(array $doc): bool {return $doc['status']==='draft'&&($this->canViewAll()||(int)$doc['created_by_user_id']===(int)auth()->id());} private function requireEditable(array $doc): void {if(!$this->isEditable($doc)||!$this->canCreate())throw PageNotFoundException::forPageNotFound();}
    private function requireCreate(): void {if(!$this->canCreate())throw PageNotFoundException::forPageNotFound();} private function requireViewAccess(): void {if(!$this->canCreate()&&!$this->canViewAll()&&!(auth()->user()?->can('orders.fulfill')??false))throw PageNotFoundException::forPageNotFound();}
    private function activeEmployees(): array {return (new EmployeeModel())->where('is_active',1)->orderBy('full_name')->findAll();} private function hasSpecialPrice(int $id): bool{return db_connect()->table('product_special_prices')->where(['product_id'=>$id,'is_active'=>1,'deleted_at'=>null])->countAllResults()>0;}
    private function decimal(mixed $value): ?float {$value=str_replace(',','.',trim((string)$value));return $value!==''&&is_numeric($value)?(float)$value:null;} private function dateOrNull(string $value): ?string {return preg_match('/^\d{4}-\d{2}-\d{2}$/',$value)?$value:null;}
    private function newNumber(string $type): string {$prefix=$type==='quote'?'TEK':'SIP';do{$number=$prefix.'-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(3)));}while((new SalesDocumentModel())->where('document_number',$number)->first());return $number;}
}
