<?php namespace App\Models;

use CodeIgniter\Model;

class PlatformModel extends Model
{
    protected $table      = 'platforms';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'code', 'name', 'created_at', 'updated_at'
    ];

    // Kita set false karena timestamp kita isi manual di Service
    protected $useTimestamps = false;
}
