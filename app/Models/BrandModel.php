<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table      = 'brands';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'supplier_id',
        'kode_brand',
        'brand_name',
        'primary_color',
        'secondary_color',
        'accent_color',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $useTimestamps = true;
}
