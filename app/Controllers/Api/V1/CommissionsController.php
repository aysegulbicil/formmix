<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Libraries\AuditLogger;
use App\Models\CommissionPeriodModel;
use App\Models\CommissionRuleModel;
use App\Services\CommissionService;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

final class CommissionsController extends ApiController
{
    public function index(): ResponseInterface
    {
        if ($blocked = $this->viewGuard()) return $blocked; [$page, $perPage] = $this->pagination(); $employee = $this->employee(); $db = db_connect();
        $query = $db->table('commission_entries ce')->select('ce.*,cr.name AS rule_name,sd.document_number,sd.created_at AS document_date,e.full_name')->join('commission_rules cr', 'cr.id=ce.commission_rule_id')->join('sales_documents sd', 'sd.id=ce.sales_document_id')->join('employees e', 'e.id=ce.sales_employee_id');
        if (! auth()->user()?->can('commissions.view-all') && ! auth()->user()?->can('commissions.manage')) $query->where('ce.sales_employee_id', $employee['id']);
        $total = $query->countAllResults(false); $entries = $query->orderBy('ce.created_at', 'DESC')->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $data = ['entries' => $entries]; if (auth()->user()?->can('commissions.manage')) { $data['rules'] = (new CommissionRuleModel())->orderBy('starts_on', 'DESC')->findAll(); $data['periods'] = (new CommissionPeriodModel())->orderBy('starts_on', 'DESC')->findAll(); }
        return $this->ok($data, ['page' => $page, 'per_page' => $perPage, 'total' => $total]);
    }

    public function createRule(): ResponseInterface
    {
        if ($blocked = $this->guard('commissions.manage')) return $blocked; $in = $this->input(); $rate = $this->decimal($in['rate_percent'] ?? 0); $type = (string) ($in['base_type'] ?? ''); $start = $this->date((string) ($in['starts_on'] ?? '')); $end = $this->date((string) ($in['ends_on'] ?? ''), true);
        if ($rate <= 0 || $rate > 100 || ! in_array($type, ['sales', 'profit'], true) || ! $start) return $this->error('VALIDATION_FAILED', 'Geçerli bir matrah, tarih ve %0-100 arası oran girin.', 422);
        $data = ['name' => trim((string) ($in['name'] ?? '')), 'base_type' => $type, 'rate_percent' => $rate, 'employee_id' => (int) ($in['employee_id'] ?? 0) ?: null, 'product_category_id' => (int) ($in['product_category_id'] ?? 0) ?: null, 'starts_on' => $start, 'ends_on' => $end, 'is_active' => 1, 'created_by_user_id' => auth()->id()]; $model = new CommissionRuleModel();
        if (! $model->insert($data)) return $this->error('VALIDATION_FAILED', 'Prim kuralı kaydedilemedi.', 422, $model->errors()); $id = (int) $model->getInsertID(); (new AuditLogger())->record('commission.rule_created', 'commission_rule', $id, null, $data); return $this->ok($model->find($id), [], 201);
    }

    public function calculate(): ResponseInterface { if ($blocked = $this->guard('commissions.manage')) return $blocked; try { return $this->ok(['created' => (new CommissionService())->calculate()]); } catch (Throwable $e) { return $this->error('COMMISSION_CALCULATION_FAILED', $e->getMessage(), 422); } }

    public function createPeriod(): ResponseInterface
    {
        if ($blocked = $this->guard('commissions.manage')) return $blocked; $in = $this->input(); $start = $this->date((string) ($in['starts_on'] ?? '')); $end = $this->date((string) ($in['ends_on'] ?? '')); if (! $start || ! $end || $end < $start) return $this->error('VALIDATION_FAILED', 'Geçerli dönem tarihleri girin.', 422);
        $model = new CommissionPeriodModel(); $code = 'PRM-' . str_replace('-', '', $start) . '-' . str_replace('-', '', $end); if (! $model->insert(['period_code' => $code, 'starts_on' => $start, 'ends_on' => $end, 'status' => 'closed', 'closed_by_user_id' => auth()->id(), 'closed_at' => date('Y-m-d H:i:s')])) return $this->error('VALIDATION_FAILED', 'Bu dönem daha önce kapatılmış olabilir.', 422, $model->errors()); $id = (int) $model->getInsertID(); db_connect()->query('UPDATE commission_entries SET commission_period_id = ? WHERE status = ? AND commission_period_id IS NULL AND sales_document_id IN (SELECT id FROM sales_documents WHERE created_at >= ? AND created_at <= ?)', [$id, 'earned', $start . ' 00:00:00', $end . ' 23:59:59']); (new AuditLogger())->record('commission.period_closed', 'commission_period', $id, null, ['starts_on' => $start, 'ends_on' => $end]); return $this->ok($model->find($id), [], 201);
    }

    public function payPeriod(int $id): ResponseInterface
    {
        if ($blocked = $this->guard('commissions.manage')) return $blocked; $model = new CommissionPeriodModel(); $period = $model->find($id); if (! $period || $period['status'] !== 'closed') return $this->error('NOT_FOUND', 'Ödenebilir prim dönemi bulunamadı.', 404); $now = date('Y-m-d H:i:s'); $model->update($id, ['status' => 'paid', 'paid_at' => $now]); db_connect()->table('commission_entries')->where('commission_period_id', $id)->where('status', 'earned')->update(['status' => 'paid', 'updated_at' => $now]); (new AuditLogger())->record('commission.period_paid', 'commission_period', $id, ['status' => 'closed'], ['status' => 'paid']); return $this->ok(['id' => $id, 'status' => 'paid', 'paid_at' => $now]);
    }

    private function viewGuard(): ?ResponseInterface { if ($blocked = $this->guard()) return $blocked; $u = auth()->user(); return $u && ($u->can('commissions.view-own') || $u->can('commissions.view-all') || $u->can('commissions.manage')) ? null : $this->error('FORBIDDEN', 'Primleri görüntüleme yetkiniz bulunmuyor.', 403); }
    private function decimal(mixed $value): float { $value = str_replace(',', '.', trim((string) $value)); return is_numeric($value) ? (float) $value : 0.0; }
    private function date(string $value, bool $nullable = false): ?string { if ($nullable && $value === '') return null; return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null; }
}
