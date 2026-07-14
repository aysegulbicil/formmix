<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index(): string
    {
        return view('panel/dashboard', [
            'title'     => 'Yönetim Paneli | FORMMIX',
            'pageTitle' => 'Genel bakış',
            'activeNav' => 'dashboard',
            'user'      => auth()->user(),
        ]);
    }
}
