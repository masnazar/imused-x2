<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Kosongkan dulu child table untuk menghindari FK constraint
        $db->table('menu_role_access')->delete(['menu_id >' => 0]);
        $db->table('menus')->delete(['id >' => 0]);

        // Isi data menu dengan slug yang unik
        $menus = [
            [
                'id' => 1,
                'name' => 'Dashboard',
                'slug' => 'dashboard',
                'icon' => 'ri-dashboard-line',
                'route' => 'dashboard',
                'parent_id' => null,
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Settings',
                'slug' => 'settings',
                'icon' => 'ri-settings-3-line',
                'route' => null,
                'parent_id' => null,
                'is_active' => 1,
                'sort_order' => 99,
            ],
            [
                'id' => 3,
                'name' => 'System Setting',
                'slug' => 'settings-system',
                'icon' => 'ri-user-settings-line',
                'route' => 'settings/system',
                'parent_id' => 2,
                'is_active' => 1,
                'sort_order' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Email Setting',
                'slug' => 'settings-email',
                'icon' => 'ri-mail-settings-line',
                'route' => 'settings/email',
                'parent_id' => 2,
                'is_active' => 1,
                'sort_order' => 2,
            ],
            [
                'id' => 5,
                'name' => 'Security Setting',
                'slug' => 'settings-security',
                'icon' => 'ri-shield-keyhole-line',
                'route' => 'settings/security',
                'parent_id' => 2,
                'is_active' => 1,
                'sort_order' => 3,
            ],
            [
                'id' => 6,
                'name' => 'Menu Access',
                'slug' => 'settings-menu',
                'icon' => 'ri-list-settings-line',
                'route' => 'settings/menu',
                'parent_id' => 2,
                'is_active' => 1,
                'sort_order' => 4,
            ],
        ];

        $db->table('menus')->insertBatch($menus);

        // Grant access ke role_id = 1 (Administrator)
        $access = array_map(fn($m) => [
            'menu_id' => $m['id'],
            'role_id' => 1
        ], $menus);

        $db->table('menu_role_access')->insertBatch($access);
    }
}
