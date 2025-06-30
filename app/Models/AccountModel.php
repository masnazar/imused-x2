<?php namespace App\Models;

use CodeIgniter\Model;

class AccountModel extends Model
{
    protected $table      = 'accounts';
    protected $primaryKey = 'id';
    protected $useTimestamps = false; // kita atur sendiri created_at/updated_at
    protected $allowedFields = [
        'code',
        'name',
        'type',
        'normal_balance',
        'parent_id',
        'created_at',
        'updated_at',
    ];
}
