<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class UserGuide extends BaseController
{
    public function index(): string
    {
        $user = auth()->user();
        return view('panel/user_guide/index', [
            'title' => 'Kullanım Rehberi | FORMMIX', 'pageTitle' => 'Kullanım rehberi', 'activeNav' => 'user-guide',
            'showOwner' => $user?->can('settings.manage') ?? false,
            'showSales' => $user?->can('orders.approve') ?? false,
            'showField' => ($user?->can('orders.create') ?? false) && ! ($user?->can('orders.approve') ?? false),
            'showAccounting' => $user?->can('finance.manage') ?? false,
            'showWarehouse' => $user?->can('stock.manage') ?? false,
        ]);
    }
}
