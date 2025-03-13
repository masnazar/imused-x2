<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['role_name' => 'Administrator'],
            ['role_name' => 'User Baru'],
            ['role_name' => 'Manager'],
            ['role_name' => 'Supervisor'],
            ['role_name' => 'Head of Brand'],
            ['role_name' => 'Leader'],
            ['role_name' => 'Koordinator'],
            ['role_name' => 'HR'],
            ['role_name' => 'Business Partner'],
            ['role_name' => 'Finance'],
            ['role_name' => 'Accounting'],
            ['role_name' => 'CEO'],
            ['role_name' => 'Owner'],
            ['role_name' => 'IT Specialist']
        ];

        foreach ($roles as $role) {
            $this->db->table('roles')->insert($role);
        }

        echo "✅ Seeder Roles berhasil dijalankan!\n";
    }
}
