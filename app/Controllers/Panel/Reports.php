<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Services\ReportService;
use App\Services\SpreadsheetExporter;
use CodeIgniter\HTTP\ResponseInterface;

class Reports extends BaseController
{
    public function index(): string
    {
        $service = new ReportService();
        $filters = $service->filters($this->request->getGet());
        $canViewCost = auth()->user()?->can('products.view-cost') ?? false;

        return view('panel/reports/index', [
            'title' => 'Raporlar | FORMMIX',
            'pageTitle' => 'Raporlar',
            'activeNav' => 'reports',
            'filters' => $filters,
            'employees' => $service->employees(),
            'report' => $service->report($filters, $canViewCost),
            'canViewCost' => $canViewCost,
        ]);
    }

    public function export(string $section, string $format): ResponseInterface
    {
        if (! in_array($format, ['csv', 'xlsx'], true)) {
            return $this->response->setStatusCode(404);
        }
        $service = new ReportService();
        $filters = $service->filters($this->request->getGet());
        $canViewCost = auth()->user()?->can('products.view-cost') ?? false;
        try {
            $export = $service->export($section, $service->report($filters, $canViewCost), $canViewCost);
        } catch (\InvalidArgumentException) {
            return $this->response->setStatusCode(404);
        }
        $writer = new SpreadsheetExporter();
        $content = $format === 'csv'
            ? $writer->csv($export['headers'], $export['rows'])
            : $writer->xlsx($export['title'], $export['headers'], $export['rows']);
        $mime = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $filename = 'formmix-'.$section.'-'.$filters['from'].'-'.$filters['until'].'.'.$format;

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($content);
    }
}
