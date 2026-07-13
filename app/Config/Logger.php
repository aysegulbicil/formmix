<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Log\Handlers\FileHandler;

/**
 * Loglama yapilandirmasi.
 */
class Logger extends BaseConfig
{
    /**
     * Log esigi:
     * 0 = kapali, 1 = emergency ... 9 = debug (hepsi).
     * 4 = error ve uzeri ciddiyetteki kayitlari tutar.
     */
    public int $threshold = 4;

    /**
     * Log tarih bicimi.
     */
    public string $dateFormat = 'Y-m-d H:i:s';

    /**
     * Log isleyicileri.
     */
    public array $handlers = [
        FileHandler::class => [
            'handles' => [
                'critical',
                'alert',
                'emergency',
                'debug',
                'error',
                'info',
                'notice',
                'warning',
            ],
            'fileExtension'   => '',
            'filePermissions' => 0644,
            'path'            => '',
        ],
    ];
}
