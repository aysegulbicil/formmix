<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductSpecialPriceModel extends Model
{
    protected $table = 'product_special_prices';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'product_id', 'product_variant_id', 'customer_price_group_id', 'customer_id',
        'unit_price', 'currency', 'valid_from', 'valid_until', 'is_active', 'created_by_user_id',
    ];
    protected $validationRules = [
        'product_id' => 'required|integer',
        'unit_price' => 'required|decimal|greater_than_equal_to[0]',
        'currency' => 'required|exact_length[3]|alpha',
        'valid_from' => 'permit_empty|valid_date[Y-m-d H:i:s]',
        'valid_until' => 'permit_empty|valid_date[Y-m-d H:i:s]',
    ];
}
