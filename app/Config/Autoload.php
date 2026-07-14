<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

/**
 * Otomatik yukleyici yapilandirmasi.
 *
 * PSR-4 ad alanlari Composer tarafindan da yonetilir. Burada ek olarak
 * her istekte yuklenecek yardimci (helper) dosyalari tanimlanir.
 */
class Autoload extends AutoloadConfig
{
    /**
     * Ad alani -> dizin eslemeleri.
     */
    public $psr4 = [
        APP_NAMESPACE => APPPATH, // App\
    ];

    /**
     * Sinif haritasi.
     */
    public $classmap = [];

    /**
     * Her zaman yuklenecek dosyalar.
     */
    public $files = [];

    /**
     * Her istekte otomatik yuklenecek yardimcilar.
     * - url:  base_url(), site_url(), uri_string() ...
     * - form: form acma/kapama, old() ile form degerlerini koruma
     * - site: WhatsApp/telefon linki, icerik (site_data), asset(), nav_active()
     */
    public $helpers = ['url', 'form', 'site', 'auth', 'setting'];
}
