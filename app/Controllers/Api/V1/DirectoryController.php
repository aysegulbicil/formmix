<?php

declare(strict_types=1);

namespace App\Controllers\Api\V1;

use App\Models\EmployeeModel;
use CodeIgniter\HTTP\ResponseInterface;

final class DirectoryController extends ApiController
{
    public function employees(): ResponseInterface
    {
        if ($blocked = $this->guard()) return $blocked;
        if (! auth()->user()?->can('orders.create') && ! auth()->user()?->can('orders.fulfill') && ! auth()->user()?->can('employees.view')) {
            return $this->error('FORBIDDEN', 'Personel listesi icin yetkiniz bulunmuyor.', 403);
        }
        $rows=(new EmployeeModel())->select('id,employee_code,full_name')->where('is_active',1)->orderBy('full_name')->findAll();
        return $this->ok(array_map(static fn(array $row):array=>['id'=>(int)$row['id'],'code'=>$row['employee_code'],'name'=>$row['full_name']],$rows));
    }
}
