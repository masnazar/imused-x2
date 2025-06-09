<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk data provinsi
 */
class ProvinceModel extends Model
{
    protected $table            = 'provinces';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['name'];
    protected $useTimestamps    = false;
}
