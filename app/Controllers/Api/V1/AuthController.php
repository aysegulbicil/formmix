<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\EmployeeModel;
use App\Models\MobileDeviceModel;
use CodeIgniter\I18n\Time;

class AuthController extends ApiController
{
    public function login()
    {
        $data = $this->input();
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $installationId = trim((string) ($data['installation_id'] ?? ''));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || $installationId === '') {
            return $this->error('VALIDATION_ERROR', 'E-posta, parola ve cihaz kimliği zorunludur.', 422);
        }

        $result = auth('session')->check(['email' => $email, 'password' => $password]);
        if (! $result->isOK()) return $this->error('INVALID_CREDENTIALS', 'E-posta veya parola hatalı.', 401);
        $user = $result->extraInfo();
        $employee = (new EmployeeModel())->where('user_id', $user->id)->where('is_active', 1)->first();
        if (! $employee || ! $user->can('panel.access')) return $this->error('ACCOUNT_INACTIVE', 'Bu hesap mobil panel kullanımına açık değil.', 403);

        foreach ($user->accessTokens() as $existing) {
            if ($existing->name === 'mobile:'.$installationId) $user->revokeAccessTokenBySecret($existing->secret);
        }
        $expires = Time::now()->addDays(30);
        $token = $user->generateAccessToken('mobile:'.$installationId, ['mobile'], $expires);

        $devices = new MobileDeviceModel();
        $device = $devices->where('user_id', $user->id)->where('installation_id', $installationId)->first();
        $deviceData = [
            'user_id' => $user->id, 'installation_id' => substr($installationId, 0, 80), 'platform' => 'android',
            'device_name' => substr(trim((string) ($data['device_name'] ?? 'Android')), 0, 120),
            'app_version' => substr(trim((string) ($data['app_version'] ?? '0.1.0')), 0, 30),
            'last_seen_at' => date('Y-m-d H:i:s'), 'revoked_at' => null,
        ];
        $device ? $devices->update($device['id'], $deviceData) : $devices->insert($deviceData);

        return $this->ok([
            'token' => $token->raw_token,
            'expires_at' => $expires->toDateTimeString(),
            'user' => $this->userResource($user, $employee),
        ]);
    }

    public function logout()
    {
        if ($failure = $this->guard()) return $failure;
        $header = $this->request->getHeaderLine('Authorization');
        $raw = preg_match('/^Bearer\s+(.+)$/i', $header, $m) ? trim($m[1]) : '';
        if ($raw !== '') auth()->user()->revokeAccessToken($raw);
        (new MobileDeviceModel())->where('user_id', auth()->id())->where('installation_id', $this->request->getHeaderLine('X-Device-ID'))->set(['revoked_at' => date('Y-m-d H:i:s'), 'push_token' => null])->update();
        return $this->ok(['logged_out' => true]);
    }

    public function me()
    {
        if ($failure = $this->guard()) return $failure;
        return $this->ok($this->userResource(auth()->user(), $this->employee()));
    }

    private function userResource($user, array $employee): array
    {
        $groups = $user->getGroups();
        $config = config('AuthGroups');
        $permissions = [];
        foreach (array_keys($config->permissions) as $permission) if ($user->can($permission)) $permissions[] = $permission;
        return [
            'id' => (int) $user->id, 'email' => $user->email, 'groups' => $groups, 'permissions' => $permissions,
            'employee' => ['id' => (int) $employee['id'], 'code' => $employee['employee_code'], 'name' => $employee['full_name'], 'max_discount_percent' => (float) $employee['max_discount_percent']],
        ];
    }
}
