<?php

namespace App\Controllers;

/**
 * Ürünler sayfası.
 */
class Products extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'       => 'Ürünler | FORMMIX Kurumsal İş Kıyafetleri',
            'description' => 'Polo yaka iş kıyafeti, baskılı tişört, sweatshirt, yelek ve iş pantolonu. Logonuza özel baskılı kurumsal iş giyimi modellerini inceleyin.',
            'bodyClass'   => 'page-products',
            'products'    => site_data('products'),
        ];

        return view('pages/products', $data);
    }
}
