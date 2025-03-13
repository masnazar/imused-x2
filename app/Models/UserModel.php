<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'email', 'whatsapp', 'birth_date', 'gender',
        'role_id', 'profile_image', 'bio', 'password', 'reset_token',
        'remember_token', 'activation_code', 'is_active', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
}
