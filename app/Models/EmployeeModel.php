<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table            = 'employees';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'user_id',
        'employee_code',
        'full_name',
        'phone',
        'max_discount_percent',
        'can_collect_payment',
        'is_active',
    ];

    protected $validationRules = [
        'employee_code'       => 'required|max_length[30]|is_unique[employees.employee_code,id,{id}]',
        'full_name'           => 'required|min_length[2]|max_length[150]',
        'phone'               => 'permit_empty|max_length[30]',
        'max_discount_percent'=> 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];

    protected $validationMessages = [
        'employee_code' => [
            'required'  => 'Personel kodu zorunludur.',
            'is_unique' => 'Bu personel kodu zaten kullanılıyor.',
        ],
        'full_name' => [
            'required'   => 'Ad soyad zorunludur.',
            'min_length' => 'Ad soyad en az 2 karakter olmalıdır.',
        ],
        'max_discount_percent' => [
            'required'              => 'İndirim sınırı zorunludur.',
            'greater_than_equal_to' => 'İndirim sınırı 0 veya daha büyük olmalıdır.',
            'less_than_equal_to'    => 'İndirim sınırı 100 değerini aşamaz.',
        ],
    ];
}
