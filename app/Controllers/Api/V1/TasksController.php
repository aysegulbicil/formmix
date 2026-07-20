<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\SalesDocumentModel;
use App\Models\SalesDocumentStatusHistoryModel;
use App\Libraries\AuditLogger;
use App\Services\StockService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

final class TasksController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked; $employee = $this->employee(); [$page, $perPage] = $this->pagination(); $status = (string) $this->request->getGet('status');
        $query = db_connect()->table('sales_documents sd')->select('sd.id,sd.document_number,sd.status,sd.expected_delivery_date,sd.updated_at,c.company_name,CASE WHEN sd.preparation_employee_id=' . (int) $employee['id'] . " THEN 'preparation' WHEN sd.design_employee_id=" . (int) $employee['id'] . " THEN 'design' WHEN sd.print_employee_id=" . (int) $employee['id'] . " THEN 'print' ELSE 'fulfillment' END AS task_type", false)->join('customers c', 'c.id=sd.customer_id')->where('sd.document_type', 'order')->where('sd.deleted_at', null)->groupStart()->where('sd.preparation_employee_id', $employee['id'])->orWhere('sd.design_employee_id', $employee['id'])->orWhere('sd.print_employee_id', $employee['id']);
        if (auth()->user()?->can('orders.fulfill')) $query->orWhereIn('sd.status', ['approved', 'procurement_waiting', 'reserved', 'partially_shipped']);
        $query->groupEnd(); if ($status === 'completed') $query->whereIn('sd.status', ['shipped', 'delivered']); elseif ($status === 'pending') $query->whereNotIn('sd.status', ['shipped', 'delivered', 'cancelled']);
        $total = $query->countAllResults(false); return $this->ok($query->orderBy('sd.updated_at', 'DESC')->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray(), ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function status(int $id): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked; $employee = $this->employee(); $row = (new SalesDocumentModel())->find($id); if (! $row || $row['document_type'] !== 'order') return $this->error('NOT_FOUND', 'Görev bulunamadı.', 404); $assigned = in_array((int) $employee['id'], [(int) $row['preparation_employee_id'], (int) $row['design_employee_id'], (int) $row['print_employee_id']], true); if (! $assigned && ! auth()->user()?->can('orders.fulfill')) return $this->error('FORBIDDEN', 'Bu görev size atanmamış.', 403);
        $input = $this->input(); $action = (string) ($input['action'] ?? '');
        try {
            if ($action === 'preparing') { if (! auth()->user()?->can('orders.fulfill')) return $this->error('FORBIDDEN', 'Stok ayırma yetkiniz bulunmuyor.', 403); $result = (new StockService((int) auth()->id()))->reserveOrder($id, (int) ($input['warehouse_id'] ?? 0)); return $this->ok($result); }
            if ($action === 'shipped') { if (! auth()->user()?->can('orders.fulfill')) return $this->error('FORBIDDEN', 'Sevkiyat yetkiniz bulunmuyor.', 403); return $this->ok((new StockService((int) auth()->id()))->shipReserved($id, trim((string) ($input['reason'] ?? '')))); }
            if ($action === 'delivered') { if (! in_array($row['status'], ['shipped', 'partially_shipped'], true)) return $this->error('INVALID_TRANSITION', 'Teslim işareti için önce sevkiyat yapılmalıdır.', 422); (new SalesDocumentModel())->update($id, ['status' => 'delivered']); (new SalesDocumentStatusHistoryModel())->insert(['sales_document_id' => $id, 'old_status' => $row['status'], 'new_status' => 'delivered', 'reason' => 'Mobil görev ekranından teslim edildi', 'changed_by_user_id' => auth()->id(), 'created_at' => date('Y-m-d H:i:s')]); (new AuditLogger())->record('order.delivered', 'sales_document', $id, ['status' => $row['status']], ['status' => 'delivered']); return $this->ok(['status' => 'delivered']); }
            return $this->error('INVALID_TRANSITION', 'Geçersiz görev durumu.', 422);
        } catch (Throwable $e) { return $this->error('TASK_UPDATE_FAILED', $e->getMessage(), 422); }
    }
}
