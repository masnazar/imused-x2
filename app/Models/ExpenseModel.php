<?php namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table         = 'expenses';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'date', 'account_id', 'brand_id', 'amount', 'description', 'processed_by', 'type',
        'created_at', 'updated_at', 'platform_id',
    ];
    protected $returnType    = 'array';
}
