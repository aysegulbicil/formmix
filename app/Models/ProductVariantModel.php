<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    protected $table = 'product_variants';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'product_id', 'sku', 'size', 'color', 'other_options', 'preparation_type',
        'customer_id', 'customization_note', 'cost_price_override', 'list_price_override',
        'critical_stock_level', 'is_active',
    ];
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'product_id' => 'required|integer',
        'sku' => 'required|max_length[80]|is_unique[product_variants.sku,id,{id}]',
        'preparation_type' => 'required|in_list[plain,customized]',
        'cost_price_override' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'list_price_override' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'critical_stock_level' => 'permit_empty|decimal|greater_than_equal_to[0]',
    ];
}
