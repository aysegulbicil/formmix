<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\MobileNotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class DispatchMobileNotifications extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:mobile-notifications';
    protected $description = 'Bekleyen mobil bildirimleri gonderir ve basarisiz olanlari yeniden dener.';
    public function run(array $params): int { $result=(new MobileNotificationService())->dispatchPending((int)($params[0]??100)); CLI::write(sprintf('%d kayit islendi; %d gonderildi, %d basarisiz.',$result['processed'],$result['sent'],$result['failed'])); return $result['failed']>0?1:0; }
}
