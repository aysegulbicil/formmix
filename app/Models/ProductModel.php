<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'category_id', 'product_code', 'name', 'description', 'tax_rate', 'cost_price',
        'list_price', 'currency', 'image_path', 'is_active', 'track_stock',
        'critical_stock_level', 'customization_mode', 'created_by_user_id',
    ];
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero',
        'product_code' => 'required|max_length[40]|is_unique[products.product_code,id,{id}]',
        'name' => 'required|min_length[2]|max_length[180]',
        'tax_rate' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        'cost_price' => 'required|decimal|greater_than_equal_to[0]',
        'list_price' => 'required|decimal|greater_than_equal_to[0]',
        'currency' => 'required|exact_length[3]|alpha',
        'critical_stock_level' => 'required|decimal|greater_than_equal_to[0]',
        'customization_mode' => 'required|in_list[plain_only,optional,custom_only]',
    ];
    protected $validationMessages = [
        'product_code' => ['required' => 'Ürün kodu zorunludur.', 'is_unique' => 'Bu ürün kodu zaten kullanılıyor.'],
        'name' => ['required' => 'Ürün adı zorunludur.', 'min_length' => 'Ürün adı en az 2 karakter olmalıdır.'],
        'tax_rate' => ['required' => 'Vergi oranı zorunludur.', 'less_than_equal_to' => 'Vergi oranı 100 değerini aşamaz.'],
        'cost_price' => ['required' => 'Alış fiyatı zorunludur.', 'greater_than_equal_to' => 'Alış fiyatı eksi olamaz.'],
        'list_price' => ['required' => 'Liste satış fiyatı zorunludur.', 'greater_than_equal_to' => 'Liste satış fiyatı eksi olamaz.'],
    ];
}
