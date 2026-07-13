<?php

/*
 |--------------------------------------------------------------------------
 | Gelistirme ortami (development)
 |--------------------------------------------------------------------------
 | Hatalar ekranda gosterilir. Canliya almadan once CI_ENVIRONMENT degerini
 | 'production' yapin.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

/*
 | Hata ayiklama modu
 */
defined('CI_DEBUG') || define('CI_DEBUG', true);
