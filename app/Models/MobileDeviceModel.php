<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class MobileDeviceModel extends Model
{
    protected $table='mobile_devices'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['user_id','installation_id','platform','device_name','app_version','push_token','notifications_enabled','last_seen_at','revoked_at'];
}
