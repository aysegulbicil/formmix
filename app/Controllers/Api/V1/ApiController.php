<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;
use App\Models\ApiIdempotencyKeyModel;
use App\Models\EmployeeModel;
use App\Models\MobileDeviceModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

abstract class ApiController extends BaseController
{
    protected function input(): array
    {
        $data = $this->request->getJSON(true);
        return is_array($data) ? $data : [];
    }

    protected function ok(mixed $data, array $meta = [], int $status = 200): ResponseInterface
    {
        $body = ['data' => $data];
        if ($meta !== []) $body['meta'] = $meta;
        return $this->response->setStatusCode($status)->setJSON($body);
    }

    protected function error(string $code, string $message, int $status = 422, array $fields = []): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'error' => ['code' => $code, 'message' => $message, 'fields' => (object) $fields],
        ]);
    }

    protected function guard(?string $permission = null): ?ResponseInterface
    {
        $user = auth()->user();
        if (! $user) return $this->error('UNAUTHENTICATED', 'Oturum gerekli.', 401);
        if (! $user->can('panel.access')) return $this->error('FORBIDDEN', 'Panel erişiminiz bulunmuyor.', 403);

        $employee = (new EmployeeModel())->where('user_id', $user->id)->first();
        if (! $employee || ! (bool) $employee['is_active']) {
            return $this->error('ACCOUNT_INACTIVE', 'Personel hesabınız etkin değil.', 403);
        }

        $installationId = trim($this->request->getHeaderLine('X-Device-ID'));
        if ($installationId === '') return $this->error('DEVICE_REQUIRED', 'Cihaz kimliği eksik.', 400);
        $device = (new MobileDeviceModel())->where('user_id', $user->id)->where('installation_id', $installationId)->first();
        if (! $device || $device['revoked_at'] !== null) return $this->error('DEVICE_REVOKED', 'Bu cihazın erişimi kapatılmış.', 401);
        (new MobileDeviceModel())->update($device['id'], ['last_seen_at' => date('Y-m-d H:i:s')]);

        if ($permission !== null && ! $user->can($permission)) return $this->error('FORBIDDEN', 'Bu işlem için yetkiniz bulunmuyor.', 403);
        return null;
    }

    protected function employee(): array
    {
        $row = (new EmployeeModel())->where('user_id', auth()->id())->where('is_active', 1)->first();
        if (! $row) throw new RuntimeException('Etkin personel kaydı bulunamadı.');
        return $row;
    }

    protected function pagination(): array
    {
        return [max(1, (int) $this->request->getGet('page')), min(100, max(1, (int) ($this->request->getGet('per_page') ?: 25)))];
    }

    protected function replay(string $operation): ?ResponseInterface
    {
        $key = trim($this->request->getHeaderLine('Idempotency-Key'));
        if ($key === '') return null;
        $row = (new ApiIdempotencyKeyModel())->where('user_id', auth()->id())->where('idempotency_key', $key)->where('operation', $operation)->first();
        if (! $row) return null;
        $body = json_decode((string) $row['response_body'], true);
        return $this->response->setStatusCode((int) $row['response_status'])->setJSON(is_array($body) ? $body : ['data' => null]);
    }

    protected function remember(string $operation, string $resourceType, int|string $resourceId, array $body, int $status = 201): void
    {
        $key = trim($this->request->getHeaderLine('Idempotency-Key'));
        if ($key === '') return;
        (new ApiIdempotencyKeyModel())->insert([
            'user_id' => auth()->id(), 'idempotency_key' => substr($key, 0, 100), 'operation' => $operation,
            'resource_type' => $resourceType, 'resource_id' => (string) $resourceId, 'response_status' => $status,
            'response_body' => json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function notFound(string $message = 'Kayıt bulunamadı.'): never
    {
        throw PageNotFoundException::forPageNotFound($message);
    }
}
