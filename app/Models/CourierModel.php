<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk couriers
 */
class CourierModel extends Model
{
    protected $table = 'couriers';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'courier_name',
        'courier_code',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
}
