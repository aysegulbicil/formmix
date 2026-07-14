<?php

declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\AuthGroups;

class VerifyReleaseReadiness extends BaseCommand
{
    protected $group = 'FORMMIX';
    protected $name = 'formmix:verify-release-readiness';
    protected $description = 'Adım 10 teknik ön koşullarını ve manuel kontrol durumunu doğrular.';

    public function run(array $params): int
    {
        $db = db_connect();
        $failures = [];
        $requiredTables = [
            'users', 'employees', 'customers', 'products', 'product_variants', 'sales_documents',
            'warehouses', 'stock_balances', 'commission_entries', 'release_readiness_items', 'release_issues',
        ];
        foreach ($requiredTables as $table) {
            if (! $db->tableExists($table)) {
                $failures[] = 'Eksik tablo: '.$table;
            }
        }
        if ($failures !== []) {
            return $this->failed($failures);
        }

        $items = $db->table('release_readiness_items')->select('code,status')->get()->getResultArray();
        if (count($items) !== 18 || count(array_unique(array_column($items, 'code'))) !== 18) {
            $failures[] = 'Yayına hazırlık kontrol listesi eksik veya mükerrer.';
        }
        $validStatuses = ['pending', 'passed', 'failed', 'not_applicable'];
        foreach ($items as $item) {
            if (! in_array($item['status'], $validStatuses, true)) {
                $failures[] = 'Geçersiz kontrol durumu: '.$item['code'];
            }
        }

        $groups = new AuthGroups();
        foreach (['owner', 'sales_manager', 'field_sales', 'accounting', 'warehouse'] as $role) {
            if (! isset($groups->groups[$role])) {
                $failures[] = 'Eksik görev: '.$role;
            }
        }
        foreach (['settings.manage', 'users.manage', 'reports.view', 'products.view-cost'] as $permission) {
            if (! isset($groups->permissions[$permission])) {
                $failures[] = 'Eksik kritik yetki: '.$permission;
            }
        }
        if (! is_writable(WRITEPATH)) {
            $failures[] = 'Uygulama yazılabilir dizinine yazamıyor.';
        }
        foreach ([ROOTPATH.'scripts/docker-backup.ps1', ROOTPATH.'scripts/docker-restore-test.ps1'] as $script) {
            if (! is_file($script)) {
                $failures[] = 'Yedek/geri dönüş betiği bulunamadı: '.basename($script);
            }
        }
        $baseUrl = (string) config('App')->baseURL;
        $host = (string) parse_url($baseUrl, PHP_URL_HOST);
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if (ENVIRONMENT === 'production' && ! $isLocalHost && ! str_starts_with($baseUrl, 'https://')) {
            $failures[] = 'Production ortamında HTTPS taban adresi zorunludur.';
        }
        $openCritical = $db->table('release_issues')->where('status', 'open')->whereIn('severity', ['critical', 'high'])->countAllResults();
        if ($openCritical > 0) {
            $failures[] = $openCritical.' açık kritik/yüksek deneme sorunu bulunuyor.';
        }
        $user = $db->table('users')->select('id')->orderBy('id')->get(1)->getRowArray();
        if (! $user) {
            $failures[] = 'Kontrol kaydı için kullanıcı bulunamadı.';
        } else {
            $first = $items[0];
            $db->transBegin();
            $db->table('release_readiness_items')->where('code', $first['code'])->update(['status' => 'passed', 'checked_by_user_id' => $user['id'], 'checked_at' => date('Y-m-d H:i:s')]);
            $db->table('release_issues')->insert(['title' => 'Geçici ön kontrol', 'description' => 'Yazma ve geri alma doğrulaması.', 'severity' => 'low', 'status' => 'open', 'reported_by_user_id' => $user['id'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            $written = $db->table('release_readiness_items')->where(['code' => $first['code'], 'status' => 'passed'])->countAllResults() === 1
                && $db->table('release_issues')->where('title', 'Geçici ön kontrol')->countAllResults() === 1;
            $db->transRollback();
            if (! $written) {
                $failures[] = 'Kontrol ve sorun kaydı yazma/geri alma denemesi başarısız.';
            }
        }
        if ($failures !== []) {
            return $this->failed($failures);
        }

        $counts = array_count_values(array_column($items, 'status'));
        CLI::write('Teknik yayına hazırlık ön kontrolü başarılı.', 'green');
        CLI::write(sprintf(
            'Manuel liste: %d başarılı, %d bekleyen, %d sorunlu, %d kapsam dışı.',
            $counts['passed'] ?? 0, $counts['pending'] ?? 0, $counts['failed'] ?? 0, $counts['not_applicable'] ?? 0,
        ));
        CLI::write('Bu komut sistemi canlıya yayınlamaz.', 'yellow');
        return EXIT_SUCCESS;
    }

    private function failed(array $messages): int
    {
        foreach ($messages as $message) {
            CLI::error($message);
        }
        return EXIT_ERROR;
    }
}
