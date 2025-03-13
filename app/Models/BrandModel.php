<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandModel extends Model
{
    protected $table      = 'brands';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'supplier_id',
        'brand_name',
        'primary_color',
        'secondary_color',
        'accent_color',
    ];
    protected $useTimestamps = true;
}
