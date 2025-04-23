<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name', 'phone_number', 'city', 'province', 'order_count',
        'ltv', 'first_order_date', 'last_order_date', 'segment',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
