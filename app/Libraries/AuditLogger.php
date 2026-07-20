<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\AuditLogModel;

class AuditLogger
{
    public function __construct(private readonly AuditLogModel $logs = new AuditLogModel())
    {
    }

    public function record(
        string $action,
        string $recordType,
        int|string|null $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): bool {
        $request = service('request');
        $user    = auth()->user();
        $ip      = method_exists($request, 'getIPAddress') ? $request->getIPAddress() : '127.0.0.1';
        $agent   = method_exists($request, 'getUserAgent')
            ? $request->getUserAgent()->getAgentString()
            : 'FORMMIX command';

        return $this->logs->insert([
            'user_id'     => $user?->id,
            'action'      => $action,
            'record_type' => $recordType,
            'record_id'   => $recordId === null ? null : (string) $recordId,
            'old_values'  => $oldValues === null ? null : json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'new_values'  => $newValues === null ? null : json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip_address'  => $ip,
            'user_agent'  => substr($agent, 0, 255),
            'source'      => is_cli() ? 'command' : (str_starts_with((string) $request->getPath(), 'api/') ? 'mobile' : 'web'),
            'device_id'   => method_exists($request, 'getHeaderLine') ? (trim($request->getHeaderLine('X-Device-ID')) ?: null) : null,
            'created_at'  => date('Y-m-d H:i:s'),
        ]) !== false;
    }
}
