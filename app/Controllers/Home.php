<?php

namespace App\Controllers;

use App\Services\WebsiteProductCatalogService;

/**
 * Anasayfa.
 */
class Home extends BaseController
{
    public function index(): string
    {
        $products = (new WebsiteProductCatalogService())->all();

        $data = [
            'title'       => 'FORMMIX | Kurumsal Baskılı İş Kıyafetleri',
            'description' => site('defaultDescription'),
            'bodyClass'   => 'page-home',
            'values'      => site_data('values'),
            'sectors'     => site_data('sectors'),
            'campaigns'   => site_data('campaigns'),
            'process'     => site_data('process'),
            'featured'    => array_slice($products, 0, 4),
        ];

        return view('pages/home', $data);
    }
}
