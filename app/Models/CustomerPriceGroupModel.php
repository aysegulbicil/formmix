<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class CustomerPriceGroupModel extends Model
{
    protected $table = 'customer_price_groups';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'description', 'discount_percent', 'is_active'];
    protected $validationRules = [
        'id'               => 'permit_empty|is_natural_no_zero',
        'code' => 'required|max_length[30]|is_unique[customer_price_groups.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[120]',
        'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
    ];
}
