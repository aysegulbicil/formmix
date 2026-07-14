<?php

declare(strict_types=1);

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\ReleaseIssueModel;
use App\Models\ReleaseReadinessItemModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class ReleaseReadiness extends BaseController
{
    private const ITEM_STATUSES = ['pending' => 'Bekliyor', 'passed' => 'Başarılı', 'failed' => 'Sorunlu', 'not_applicable' => 'Kapsam dışı'];
    private const SEVERITIES = ['critical' => 'Kritik', 'high' => 'Yüksek', 'medium' => 'Orta', 'low' => 'Düşük'];
    private const MANDATORY_CODES = ['backup-final', 'restore-tested', 'production-config', 'no-critical-issues', 'written-approval'];

    public function index(): string
    {
        $items = (new ReleaseReadinessItemModel())->orderBy('sort_order')->findAll();
        $issues = (new ReleaseIssueModel())->orderBy('status')->orderBy('created_at', 'DESC')->findAll();
        $counts = array_fill_keys(array_keys(self::ITEM_STATUSES), 0);
        foreach ($items as $item) {
            $counts[$item['status']]++;
        }
        $openCritical = count(array_filter($issues, static fn ($issue) => in_array($issue['severity'], ['critical', 'high'], true) && $issue['status'] === 'open'));
        $mandatoryPassed = count(array_filter($items, static fn ($item) => in_array($item['code'], self::MANDATORY_CODES, true) && $item['status'] === 'passed')) === count(self::MANDATORY_CODES);
        $ready = $counts['pending'] === 0 && $counts['failed'] === 0 && $openCritical === 0 && $mandatoryPassed;

        return view('panel/release_readiness/index', [
            'title' => 'Yayına Hazırlık | FORMMIX', 'pageTitle' => 'Yayına hazırlık', 'activeNav' => 'release-readiness',
            'items' => $items, 'issues' => $issues, 'counts' => $counts, 'openCritical' => $openCritical,
            'ready' => $ready, 'itemStatuses' => self::ITEM_STATUSES, 'severities' => self::SEVERITIES,
        ]);
    }

    public function updateItem(int $id): RedirectResponse
    {
        $model = new ReleaseReadinessItemModel();
        $item = $model->find($id);
        if (! $item) {
            throw PageNotFoundException::forPageNotFound();
        }
        $status = (string) $this->request->getPost('status');
        $notes = trim((string) $this->request->getPost('notes'));
        if (! isset(self::ITEM_STATUSES[$status])) {
            return redirect()->back()->with('errors', ['checklist' => 'Geçerli bir kontrol durumu seçin.']);
        }
        if ($status === 'not_applicable' && in_array($item['code'], self::MANDATORY_CODES, true)) {
            return redirect()->back()->with('errors', ['checklist' => 'Yedek, geri dönüş, canlı ortam, kritik sorun ve yazılı onay maddeleri kapsam dışı bırakılamaz.']);
        }
        if (in_array($status, ['failed', 'not_applicable'], true) && $notes === '') {
            return redirect()->back()->with('errors', ['checklist' => 'Sorunlu veya kapsam dışı maddede açıklama zorunludur.']);
        }
        $data = ['status' => $status, 'notes' => $notes ?: null, 'checked_by_user_id' => auth()->id(), 'checked_at' => date('Y-m-d H:i:s')];
        $model->update($id, $data);
        (new AuditLogger())->record('release.check_updated', 'release_readiness_item', $id, ['status' => $item['status'], 'notes' => $item['notes']], $data);
        return redirect()->back()->with('message', 'Hazırlık kontrolü güncellendi.');
    }

    public function storeIssue(): RedirectResponse
    {
        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));
        $severity = (string) $this->request->getPost('severity');
        if (mb_strlen($title) < 3 || $description === '' || ! isset(self::SEVERITIES[$severity])) {
            return redirect()->back()->withInput()->with('errors', ['issue' => 'Sorun başlığı, açıklaması ve önem derecesi zorunludur.']);
        }
        $model = new ReleaseIssueModel();
        $model->insert(['title' => $title, 'description' => $description, 'severity' => $severity, 'status' => 'open', 'reported_by_user_id' => auth()->id()]);
        $id = (int) $model->getInsertID();
        (new AuditLogger())->record('release.issue_created', 'release_issue', $id, null, ['title' => $title, 'severity' => $severity]);
        return redirect()->back()->with('message', 'Deneme sorunu kaydedildi.');
    }

    public function resolveIssue(int $id): RedirectResponse
    {
        $model = new ReleaseIssueModel();
        $issue = $model->find($id);
        if (! $issue) {
            throw PageNotFoundException::forPageNotFound();
        }
        $note = trim((string) $this->request->getPost('resolution_note'));
        if ($note === '') {
            return redirect()->back()->with('errors', ['issue' => 'Sorunu kapatmak için çözüm ve tekrar test notu zorunludur.']);
        }
        $data = ['status' => 'resolved', 'resolution_note' => $note, 'resolved_by_user_id' => auth()->id(), 'resolved_at' => date('Y-m-d H:i:s')];
        $model->update($id, $data);
        (new AuditLogger())->record('release.issue_resolved', 'release_issue', $id, ['status' => $issue['status']], $data);
        return redirect()->back()->with('message', 'Sorun çözüldü olarak kapatıldı.');
    }
}
