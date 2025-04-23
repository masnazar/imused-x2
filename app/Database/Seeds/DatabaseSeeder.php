<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder utama untuk menjalankan semua seeder lain
 */
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('App\Database\Seeds\RoleSeeder');
        $this->call('App\Database\Seeds\PermissionsSeeder');
    }
}
