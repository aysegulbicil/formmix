<?php

namespace App\Controllers;

/**
 * Hakkımızda sayfası.
 */
class About extends BaseController
{
    public function index(): string
    {
        $data = [
            'title'       => 'Hakkımızda | FORMMIX',
            'description' => 'FORMMIX, işletmelerin ekiplerini daha profesyonel ve güven veren bir görünüme kavuşturmak için kuruldu. Biz sadece kıyafet satmıyor; markanızın ilk izlenimini güçlendiriyoruz.',
            'bodyClass'   => 'page-about',
        ];

        return view('pages/about', $data);
    }
}
