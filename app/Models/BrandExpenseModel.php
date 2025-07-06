<?php namespace App\Models;

use CodeIgniter\Model;

class BrandExpenseModel extends Model
{
    protected $table         = 'brand_expenses';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'date', 'account_id', 'brand_id', 'platform_id',
        'amount', 'description', 'processed_by', 'type',
        'created_at', 'updated_at',
    ];
    protected $returnType    = 'array';
    protected $useTimestamps = true;
}
