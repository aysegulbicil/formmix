<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;
use Throwable;

final class VerifyMobileFoundation extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:verify-mobile-foundation';
    protected $description = 'Mobil cihaz, bildirim, idempotency, surum ve audit veri temelini dogrular.';

    public function run(array $params): int
    {
        $db = db_connect();
        $user = $db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        if (! $user) { CLI::error('Dogrulama icin kullanici bulunamadi.'); return EXIT_ERROR; }
        $suffix = bin2hex(random_bytes(5)); $now = date('Y-m-d H:i:s'); $db->transBegin();
        try {
            $db->table('mobile_devices')->insert(['user_id'=>$user['id'],'installation_id'=>'verify-'.$suffix,'platform'=>'android','device_name'=>'Verify','notifications_enabled'=>0,'created_at'=>$now,'updated_at'=>$now]);
            $device = (int) $db->insertID();
            $db->table('mobile_notifications')->insert(['user_id'=>$user['id'],'notification_type'=>'verify','title'=>'Dogrulama','body'=>'Mobil outbox','delivery_status'=>'pending','attempt_count'=>0,'created_at'=>$now,'updated_at'=>$now]);
            $notification = (int) $db->insertID();
            $row = ['user_id'=>$user['id'],'idempotency_key'=>'verify-'.$suffix,'operation'=>'verify.create','resource_type'=>'verify','resource_id'=>'1','response_status'=>201,'response_body'=>'{"data":{"id":1}}','created_at'=>$now];
            $db->table('api_idempotency_keys')->insert($row); $duplicateBlocked = false;
            try { $duplicateBlocked = $db->table('api_idempotency_keys')->insert($row) === false; } catch (Throwable) { $duplicateBlocked = true; }
            $db->table('mobile_app_releases')->insert(['platform'=>'android','version_name'=>'0.0.1','version_code'=>999999,'minimum_version_code'=>1,'download_url'=>'https://example.invalid/formmix.apk','sha256'=>str_repeat('a',64),'is_active'=>0,'published_at'=>$now,'created_at'=>$now,'updated_at'=>$now]);
            if ($device < 1 || $notification < 1 || ! $duplicateBlocked) throw new RuntimeException('Mobil veri iliskileri veya idempotency benzersizligi dogrulanamadi.');
            foreach (['source','device_id'] as $field) if (! $db->fieldExists($field, 'audit_logs')) throw new RuntimeException('Audit alani eksik: '.$field);
            $db->transRollback(); CLI::write('Mobil cihaz, bildirim outbox, idempotency, surum ve audit temeli dogrulandi.', 'green'); return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $db->transRollback(); CLI::error($e->getMessage()); return EXIT_ERROR;
        }
    }
}
