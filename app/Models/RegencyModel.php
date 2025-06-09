<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk data kabupaten/kota
 */
class RegencyModel extends Model
{
    protected $table            = 'regencies';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['province_id', 'name'];
    protected $useTimestamps    = false;
}
