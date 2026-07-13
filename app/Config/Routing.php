<?php

namespace Config;

use CodeIgniter\Config\Routing as BaseRouting;

/**
 * Yonlendirme (routing) yapilandirmasi. (CodeIgniter 4.7 uyumlu)
 */
class Routing extends BaseRouting
{
    /**
     * Rota tanim dosyalari.
     *
     * @var list<string>
     */
    public array $routeFiles = [
        APPPATH . 'Config/Routes.php',
    ];

    /**
     * Varsayilan ad alani.
     */
    public string $defaultNamespace = 'App\Controllers';

    /**
     * Varsayilan kontrolcu.
     */
    public string $defaultController = 'Home';

    /**
     * Varsayilan metot.
     */
    public string $defaultMethod = 'index';

    /**
     * URI'lerdeki tireleri alt cizgiye cevir.
     */
    public bool $translateURIDashes = false;

    /**
     * 404 gecersiz kilma (override) ayari.
     */
    public ?string $override404 = null;

    /**
     * Otomatik rota kapali (guvenlik icin onerilir; rotalar Routes.php'de tanimli).
     */
    public bool $autoRoute = false;

    /**
     * Kontrolcu attribute'lari kullanilsin mi?
     */
    public bool $useControllerAttributes = true;

    /**
     * Rota onceliklendirme.
     */
    public bool $prioritize = false;

    /**
     * Tek parametreye birden cok segment.
     */
    public bool $multipleSegmentsOneParam = false;

    /**
     * Otomatik rota icin modul-ad alani eslemesi.
     *
     * @var array<string, string>
     */
    public array $moduleRoutes = [];

    /**
     * URI tirelerini CamelCase'e cevir (otomatik rota icin).
     */
    public bool $translateUriToCamelCase = true;
}
