<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\SalesDocumentModel;
use App\Services\ReportService;

class Dashboard extends BaseController
{
    public function index(): string
    {
        $pendingOrderCount = (auth()->user()?->can('orders.fulfill') ?? false)
            ? (new SalesDocumentModel())->whereIn('status', ['approved', 'procurement_waiting', 'reserved', 'partially_shipped'])->countAllResults()
            : 0;
        $dashboardMetrics = (auth()->user()?->can('reports.view') ?? false)
            ? (new ReportService())->dashboard()
            : null;
        return view('panel/dashboard', [
            'title'     => 'Yönetim Paneli | FORMMIX',
            'pageTitle' => 'Genel bakış',
            'activeNav' => 'dashboard',
            'user'      => auth()->user(),
            'pendingOrderCount' => $pendingOrderCount,
            'dashboardMetrics' => $dashboardMetrics,
        ]);
    }
}
