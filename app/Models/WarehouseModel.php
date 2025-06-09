<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk table warehouses
 */
class WarehouseModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'warehouses';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name', 'code', 'address', 'pic_name', 'pic_contact', 'warehouse_type', 'created_at', 'updated_at', 'deleted_at'];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = false;
}
