<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\MobileAppReleaseModel;
use App\Models\MobileDeviceModel;
use App\Models\MobileNotificationModel;
use App\Models\SalesDocumentModel;
use App\Services\ReportService;

class OverviewController extends ApiController
{
    public function bootstrap()
    {
        if ($failure = $this->guard()) return $failure;
        $user = auth()->user(); $employee = $this->employee();
        $orders = new SalesDocumentModel();
        if (! $user->can('orders.view-all')) {
            $orders->groupStart()->where('sales_employee_id', $employee['id'])->orWhere('preparation_employee_id', $employee['id'])->orWhere('design_employee_id', $employee['id'])->orWhere('print_employee_id', $employee['id'])->groupEnd();
        }
        $open = $orders->where('document_type', 'order')->whereIn('status', ['approved','procurement_waiting','reserved','partially_shipped'])->countAllResults();
        $metrics = $user->can('reports.view') ? (new ReportService())->dashboard() : null;
        $unread = (new MobileNotificationModel())->where('user_id', $user->id)->where('read_at', null)->countAllResults();
        return $this->ok([
            'employee' => ['id'=>(int)$employee['id'],'name'=>$employee['full_name'],'max_discount_percent'=>(float)$employee['max_discount_percent']],
            'open_order_count' => $open, 'unread_notification_count' => $unread, 'dashboard_metrics' => $metrics,
            'features' => ['offline_drafts'=>true,'push_notifications'=>true,'camera_uploads'=>false,'finance'=>false,'visits'=>false],
        ]);
    }

    public function device()
    {
        if ($failure = $this->guard()) return $failure;
        $row = (new MobileDeviceModel())->where('user_id', auth()->id())->where('installation_id', $this->request->getHeaderLine('X-Device-ID'))->first();
        return $this->ok($row ? array_diff_key($row, ['push_token'=>true]) : null);
    }

    public function pushToken()
    {
        if ($failure = $this->guard()) return $failure;
        $input = $this->input(); $token = trim((string) ($input['push_token'] ?? ''));
        $enabled = (bool) ($input['enabled'] ?? ($token !== ''));
        (new MobileDeviceModel())->where('user_id', auth()->id())->where('installation_id', $this->request->getHeaderLine('X-Device-ID'))->set(['push_token'=>$token?:null,'notifications_enabled'=>$enabled?1:0,'app_version'=>substr((string)($input['app_version']??''),0,30)?:null])->update();
        return $this->ok(['registered' => $token !== '', 'enabled' => $enabled]);
    }

    public function notifications()
    {
        if ($failure = $this->guard()) return $failure;
        [$page,$perPage]=$this->pagination(); $model=new MobileNotificationModel();
        $total=$model->where('user_id',auth()->id())->countAllResults(false);
        $rows=$model->where('user_id',auth()->id())->orderBy('created_at','DESC')->findAll($perPage,($page-1)*$perPage);
        return $this->ok($rows,['page'=>$page,'per_page'=>$perPage,'total'=>$total]);
    }

    public function readNotification(int $id)
    {
        if ($failure = $this->guard()) return $failure;
        $model=new MobileNotificationModel(); $row=$model->where('user_id',auth()->id())->find($id);
        if(!$row)return $this->error('NOT_FOUND','Bildirim bulunamadı.',404);
        $model->update($id,['read_at'=>date('Y-m-d H:i:s')]); return $this->ok(['read'=>true]);
    }

    public function release()
    {
        $row=(new MobileAppReleaseModel())->where('platform','android')->where('is_active',1)->orderBy('version_code','DESC')->first();
        return $this->ok($row);
    }
}
