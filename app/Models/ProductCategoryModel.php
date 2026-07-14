<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductCategoryModel extends Model
{
    protected $table = 'product_categories';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = ['code', 'name', 'description', 'is_active'];
    protected $validationRules = [
        'id'   => 'permit_empty|is_natural_no_zero',
        'code' => 'required|max_length[30]|is_unique[product_categories.code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[120]',
    ];
}
