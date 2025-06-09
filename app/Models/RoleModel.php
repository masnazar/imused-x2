<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data Role (Hak Akses)
 */
class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $useTimestamps    = true;

    /**
     * Field yang bisa diisi
     */
    protected $allowedFields    = [
        'role_name',
        'slug',
        'created_at',
        'updated_at'
    ];

    /**
     * Aturan validasi
     */
    protected $validationRules  = [
        'role_name' => 'required|min_length[3]',
        'slug'      => 'required|alpha_dash|is_unique[roles.slug,id,{id}]',
    ];

    protected $validationMessages = [
        'slug' => [
            'is_unique' => 'Slug sudah dipakai, gunakan yang lain.',
        ]
    ];
}
