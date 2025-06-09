<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $roles = [
            ['role_name' => 'Superuser', 'slug' => 'superuser', 'created_at' => $now],

            // Eksekutif
            ['role_name' => 'CEO', 'slug' => 'ceo', 'created_at' => $now],
            ['role_name' => 'Owner', 'slug' => 'owner', 'created_at' => $now],

            // Manager
            ['role_name' => 'Manager Operasional', 'slug' => 'manager-operasional', 'created_at' => $now],
            ['role_name' => 'Manager Marketing', 'slug' => 'manager-marketing', 'created_at' => $now],
            ['role_name' => 'Manager Finance', 'slug' => 'manager-finance', 'created_at' => $now],
            ['role_name' => 'Manager HR', 'slug' => 'manager-hr', 'created_at' => $now],

            // Supervisor
            ['role_name' => 'Supervisor Operasional', 'slug' => 'supervisor-operasional', 'created_at' => $now],
            ['role_name' => 'Supervisor Marketing', 'slug' => 'supervisor-marketing', 'created_at' => $now],

            // Head
            ['role_name' => 'Head Warehouse', 'slug' => 'head-warehouse', 'created_at' => $now],
            ['role_name' => 'Head Purchasing', 'slug' => 'head-purchasing', 'created_at' => $now],
            ['role_name' => 'Head Finance', 'slug' => 'head-finance', 'created_at' => $now],
            ['role_name' => 'Head CRM', 'slug' => 'head-crm', 'created_at' => $now],
            ['role_name' => 'Head Soscom', 'slug' => 'head-soscom', 'created_at' => $now],
            ['role_name' => 'Head Viral Marketing', 'slug' => 'head-viral-marketing', 'created_at' => $now],
            ['role_name' => 'Head Brand', 'slug' => 'head-brand', 'created_at' => $now],
            ['role_name' => 'Head HR', 'slug' => 'head-hr', 'created_at' => $now],
            ['role_name' => 'Head GA', 'slug' => 'head-ga', 'created_at' => $now],

            // Koordinator
            ['role_name' => 'Koordinator CRM', 'slug' => 'koordinator-crm', 'created_at' => $now],
            ['role_name' => 'Koordinator CS', 'slug' => 'koordinator-cs', 'created_at' => $now],
            ['role_name' => 'Koordinator Konten Kreator', 'slug' => 'koordinator-konten-kreator', 'created_at' => $now],

            // Staff
            ['role_name' => 'Staff Warehouse', 'slug' => 'staff-warehouse', 'created_at' => $now],
            ['role_name' => 'Staff Purchasing', 'slug' => 'staff-purchasing', 'created_at' => $now],
            ['role_name' => 'Staff External', 'slug' => 'staff-external', 'created_at' => $now],
            ['role_name' => 'Staff CRM', 'slug' => 'staff-crm', 'created_at' => $now],
            ['role_name' => 'Advertiser', 'slug' => 'advertiser', 'created_at' => $now],
            ['role_name' => 'Konten Kreator', 'slug' => 'konten-kreator', 'created_at' => $now],
            ['role_name' => 'Customer Service', 'slug' => 'customer-service', 'created_at' => $now],
            ['role_name' => 'Staff Accounting', 'slug' => 'staff-accounting', 'created_at' => $now],
            ['role_name' => 'Staff GA', 'slug' => 'staff-ga', 'created_at' => $now],
            ['role_name' => 'HR Staff', 'slug' => 'hr-staff', 'created_at' => $now],

            // Default Role
            ['role_name' => 'User Baru', 'slug' => 'user-baru', 'created_at' => $now],
        ];

        foreach ($roles as $role) {
            $this->db->table('roles')->insert($role);
        }

        echo "✅ Seeder Roles berhasil dijalankan!\n";
    }
}
