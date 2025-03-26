<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table = 'audit_logs'; // Ensure this matches your database table name
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'model',
        'model_id',
        'activity', // Ensure this matches the column name in your database
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    protected $useTimestamps = false; // Disable automatic timestamps if not needed
}