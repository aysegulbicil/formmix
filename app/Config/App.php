<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * Sitenin temel URL'i. .env icindeki app.baseURL bu degeri ezer.
     * Sonundaki "/" zorunludur.
     */
    public string $baseURL = 'http://localhost/frommix/public/';

    /**
     * Izin verilen alan adlari (baseURL disinda erisilecekler).
     */
    public array $allowedHostnames = [];

    /**
     * URL'lerde gorunecek index dosyasi. Temiz URL icin bos birakilir.
     */
    public string $indexPage = '';

    /**
     * URI'nin tespit yontemi.
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * URI'de izin verilen karakterler.
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * Varsayilan dil.
     */
    public string $defaultLocale = 'tr';

    /**
     * Tarayici diline gore otomatik dil secimi.
     */
    public bool $negotiateLocale = false;

    /**
     * Desteklenen diller.
     */
    public array $supportedLocales = ['tr'];

    /**
     * Uygulama saat dilimi.
     */
    public string $appTimezone = 'Europe/Istanbul';

    /**
     * Varsayilan karakter seti.
     */
    public string $charset = 'UTF-8';

    /**
     * Tum istekleri HTTPS'e zorla (canlida SSL varsa true yapin).
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * Guvenilen proxy IP'leri.
     */
    public array $proxyIPs = [];

    /**
     * Content Security Policy aktif mi?
     */
    public bool $CSPEnabled = false;
}
