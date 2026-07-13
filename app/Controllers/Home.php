<?php

namespace App\Controllers;

/**
 * Anasayfa.
 */
class Home extends BaseController
{
    public function index(): string
    {
        $products = site_data('products');

        $data = [
            'title'       => 'FORMMIX | Kurumsal Baskılı İş Kıyafetleri',
            'description' => site('defaultDescription'),
            'bodyClass'   => 'page-home',
            'values'      => site_data('values'),
            'sectors'     => site_data('sectors'),
            'campaigns'   => site_data('campaigns'),
            'process'     => site_data('process'),
            'featured'    => array_values(array_filter($products, static fn ($p) => ! empty($p['home_featured']))),
        ];

        return view('pages/home', $data);
    }
}
