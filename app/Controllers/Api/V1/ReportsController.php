<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Services\ReportService;
use App\Services\SpreadsheetExporter;
use CodeIgniter\HTTP\ResponseInterface;
use InvalidArgumentException;

final class ReportsController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard('reports.view')) return $blocked;
        $service = new ReportService();
        $filters = $service->filters($this->request->getGet());
        return $this->ok($service->report($filters, auth()->user()?->can('products.view-cost') ?? false), ['filters' => $filters]);
    }

    public function export(string $section, string $format): ResponseInterface
    {
        if ($blocked = $this->guard('reports.view')) return $blocked;
        if (! in_array($format, ['csv', 'xlsx'], true)) return $this->error('NOT_FOUND', 'Dışa aktarma biçimi bulunamadı.', 404);
        $service = new ReportService(); $filters = $service->filters($this->request->getGet()); $canViewCost = auth()->user()?->can('products.view-cost') ?? false;
        try { $export = $service->export($section, $service->report($filters, $canViewCost), $canViewCost); }
        catch (InvalidArgumentException) { return $this->error('NOT_FOUND', 'Rapor bölümü bulunamadı.', 404); }
        $writer = new SpreadsheetExporter(); $content = $format === 'csv' ? $writer->csv($export['headers'], $export['rows']) : $writer->xlsx($export['title'], $export['headers'], $export['rows']);
        $mime = $format === 'csv' ? 'text/csv; charset=UTF-8' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        return $this->response->setHeader('Content-Type', $mime)->setHeader('Content-Disposition', 'attachment; filename="formmix-' . $section . '-' . $filters['from'] . '-' . $filters['until'] . '.' . $format . '"')->setHeader('X-Content-Type-Options', 'nosniff')->setBody($content);
    }
}
