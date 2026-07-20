<?php
declare(strict_types=1);
namespace App\Models;
use CodeIgniter\Model;
class MobileAppReleaseModel extends Model
{
    protected $table='mobile_app_releases'; protected $returnType='array'; protected $useTimestamps=true;
    protected $allowedFields=['platform','version_name','version_code','minimum_version_code','download_url','sha256','release_notes','is_active','published_at'];
}
