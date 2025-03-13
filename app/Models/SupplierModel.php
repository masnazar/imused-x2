<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierModel extends Model
{
    protected $table = 'suppliers';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'supplier_name',
        'supplier_address',
        'supplier_pic_name',
        'supplier_pic_contact',
    ];

    protected $useTimestamps = true;
}
