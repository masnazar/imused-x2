<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel menus
 */
class MenuModel extends Model
{
    protected $table            = 'menus';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'name',
        'slug',
        'icon',
        'route',
        'parent_id',
        'is_active',
        'sort_order',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps    = true;
}
