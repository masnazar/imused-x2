<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk data kecamatan
 */
class DistrictModel extends Model
{
    protected $table            = 'districts';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['regency_id', 'name'];
    protected $useTimestamps    = false;
}
