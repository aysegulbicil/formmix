<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeModel;
use App\Models\MobileDeviceModel;
use App\Models\MobileNotificationModel;
use Throwable;

final class MobileNotificationService
{
    public function queueForEmployee(int $employeeId, string $type, string $title, string $body, ?string $route = null, ?string $entityType = null, int|string|null $entityId = null): ?int
    {
        $employee = (new EmployeeModel())->find($employeeId);
        if (! $employee || ! $employee['user_id']) return null;
        return (int) (new MobileNotificationModel())->insert(['user_id'=>$employee['user_id'],'notification_type'=>$type,'title'=>$title,'body'=>$body,'target_route'=>$route,'entity_type'=>$entityType,'entity_id'=>$entityId,'delivery_status'=>'pending','attempt_count'=>0], true);
    }

    public function dispatchPending(int $limit = 100): array
    {
        $model = new MobileNotificationModel(); $sent = 0; $failed = 0;
        $rows = $model->whereIn('delivery_status', ['pending', 'failed'])->where('attempt_count <', 5)->orderBy('created_at')->findAll(max(1, min(500, $limit)));
        foreach ($rows as $row) {
            $devices = (new MobileDeviceModel())->where('user_id', $row['user_id'])->where('notifications_enabled', 1)->where('revoked_at', null)->where('push_token !=', null)->findAll();
            if ($devices === []) { $model->update($row['id'], ['delivery_status'=>'in_app_only','last_error'=>null]); continue; }
            try {
                $messages = array_map(static fn(array $device): array => ['to'=>$device['push_token'],'sound'=>'default','title'=>$row['title'],'body'=>$row['body'],'data'=>['route'=>$row['target_route'],'entity_type'=>$row['entity_type'],'entity_id'=>$row['entity_id']]], $devices);
                $response = service('curlrequest')->post('https://exp.host/--/api/v2/push/send', ['headers'=>['Accept'=>'application/json','Content-Type'=>'application/json'],'json'=>$messages,'timeout'=>10]);
                if ($response->getStatusCode() >= 300) throw new \RuntimeException('Push servisi HTTP '.$response->getStatusCode().' dondurdu.');
                $model->update($row['id'], ['delivery_status'=>'sent','attempt_count'=>(int)$row['attempt_count']+1,'last_error'=>null,'sent_at'=>date('Y-m-d H:i:s')]); $sent++;
            } catch (Throwable $e) {
                $model->update($row['id'], ['delivery_status'=>'failed','attempt_count'=>(int)$row['attempt_count']+1,'last_error'=>mb_substr($e->getMessage(),0,500)]); $failed++;
            }
        }
        return ['sent'=>$sent,'failed'=>$failed,'processed'=>count($rows)];
    }
}
