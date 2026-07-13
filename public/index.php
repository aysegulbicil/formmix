<?php

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * PHP SURUM KONTROLU
 *---------------------------------------------------------------
 */
$minPhpVersion = '8.1'; // 'spark' dosyasini da guncellemeyi unutmayin.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'CodeIgniter calismasi icin PHP surumunuz %s veya uzeri olmalidir. Mevcut surum: %s',
        $minPhpVersion,
        PHP_VERSION,
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * ON KONTROLCU DIZINI
 *---------------------------------------------------------------
 */
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Calisma dizinini on kontrolcunun bulundugu dizine sabitle
chdir(FCPATH);

/*
 *---------------------------------------------------------------
 * UYGULAMAYI BASLAT
 *---------------------------------------------------------------
 */
require FCPATH . '../app/Config/Paths.php';

$paths = new Paths();

require $paths->systemDirectory . '/Boot.php';

exit(Boot::bootWeb($paths));
