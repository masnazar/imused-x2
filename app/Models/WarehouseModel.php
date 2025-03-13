<?php

namespace App\Models;

use CodeIgniter\Model;

class WarehouseModel extends Model
{
    protected $table            = 'warehouses';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'address', 'pic_name', 'pic_contact', 'warehouse_type'
    ];
    protected $useTimestamps = true;
}
