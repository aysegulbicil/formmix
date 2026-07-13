<?php

namespace App\Controllers;

/**
 * Katalog sayfası.
 */
class Catalog extends BaseController
{
    public function index(): string
    {
        // Katalog PDF'i public/assets/catalog/ altina eklendiginde otomatik aktif olur.
        $pdfRelative = 'assets/catalog/formmix-katalog.pdf';
        $pdfExists   = is_file(FCPATH . $pdfRelative);

        $data = [
            'title'       => 'Katalog | FORMMIX Ürün Kataloğu',
            'description' => 'FORMMIX kurumsal iş giyimi modellerini ve kampanyalarını içeren ürün kataloğumuzu görüntüleyin veya WhatsApp’tan isteyin.',
            'bodyClass'   => 'page-catalog',
            'pdfUrl'      => $pdfExists ? base_url($pdfRelative) : null,
        ];

        return view('pages/catalog', $data);
    }
}
