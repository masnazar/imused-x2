<?php namespace App\Models;

use CodeIgniter\Model;

class StoreModel extends Model
{
    protected $table         = 'stores';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'brand_id',
        'store_code',
        'store_name',
    ];

    // timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
