<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk data desa/kelurahan
 */
class VillageModel extends Model
{
    protected $table            = 'villages';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['district_id', 'name', 'postal_code'];
    protected $useTimestamps    = false;
}
