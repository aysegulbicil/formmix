<?php

/*
 |--------------------------------------------------------------------------
 | Canli ortam (production)
 |--------------------------------------------------------------------------
 | Hatalar ekranda gosterilmez, writable/logs altina kaydedilir.
 */

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

/*
 | Hata ayiklama modu kapali
 */
defined('CI_DEBUG') || define('CI_DEBUG', false);
