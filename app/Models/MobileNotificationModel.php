<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class MobileNotificationModel extends Model
{
    protected $table='mobile_notifications'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['user_id','notification_type','title','body','target_route','entity_type','entity_id','delivery_status','attempt_count','last_error','sent_at','read_at'];
}
