<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Supplier
            ['permission_name' => 'view_supplier', 'alias' => 'Melihat daftar supplier'],
            ['permission_name' => 'create_supplier', 'alias' => 'Menambahkan supplier baru'],
            ['permission_name' => 'edit_supplier', 'alias' => 'Mengedit supplier'],
            ['permission_name' => 'delete_supplier', 'alias' => 'Menghapus supplier'],

            // Inventory
            ['permission_name' => 'view_inventory', 'alias' => 'Melihat daftar inventory'],
            ['permission_name' => 'update_inventory', 'alias' => 'Mengubah stok inventory'],
            ['permission_name' => 'view_inventory_logs', 'alias' => 'Melihat riwayat inventory'],

            // Permissions Management
            ['permission_name' => 'create_permissions', 'alias' => 'Membuat permission baru'],
            ['permission_name' => 'edit_permission', 'alias' => 'Mengedit permission'],
            ['permission_name' => 'delete_permission', 'alias' => 'Menghapus permission'],

            // Roles
            ['permission_name' => 'view_role', 'alias' => 'Melihat daftar role'],
            ['permission_name' => 'create_role', 'alias' => 'Menambahkan role baru'],
            ['permission_name' => 'edit_role', 'alias' => 'Mengedit role'],
            ['permission_name' => 'delete_role', 'alias' => 'Menghapus role'],

            // Brands
            ['permission_name' => 'view_brand', 'alias' => 'Melihat daftar brand'],
            ['permission_name' => 'create_brand', 'alias' => 'Menambahkan brand baru'],
            ['permission_name' => 'edit_brand', 'alias' => 'Mengedit brand'],
            ['permission_name' => 'delete_brand', 'alias' => 'Menghapus brand'],

            // Products
            ['permission_name' => 'view_product', 'alias' => 'Melihat daftar produk'],
            ['permission_name' => 'create_product', 'alias' => 'Menambahkan produk baru'],
            ['permission_name' => 'edit_product', 'alias' => 'Mengedit produk'],
            ['permission_name' => 'delete_product', 'alias' => 'Menghapus produk'],

            // Warehouse
            ['permission_name' => 'view_warehouse', 'alias' => 'Melihat daftar gudang'],
            ['permission_name' => 'create_warehouse', 'alias' => 'Menambahkan gudang baru'],
            ['permission_name' => 'edit_warehouse', 'alias' => 'Mengedit gudang'],
            ['permission_name' => 'delete_warehouse', 'alias' => 'Menghapus gudang'],

            // Purchase Order
            ['permission_name' => 'view_purchase_order', 'alias' => 'Melihat daftar purchase order'],
            ['permission_name' => 'create_purchase_order', 'alias' => 'Membuat purchase order'],
            ['permission_name' => 'edit_purchase_order', 'alias' => 'Mengedit purchase order'],
            ['permission_name' => 'delete_purchase_order', 'alias' => 'Menghapus purchase order'],
            ['permission_name' => 'receive_purchase_order', 'alias' => 'Menerima barang dari PO'],
            ['permission_name' => 'view_po_detail', 'alias' => 'Melihat detail purchase order'],

            // Settings
            ['permission_name' => 'view_settings', 'alias' => 'Melihat konfigurasi sistem'],
            ['permission_name' => 'edit_settings', 'alias' => 'Mengubah konfigurasi sistem'],
        ];

        foreach ($permissions as $permission) {
            $exists = $this->db->table('permissions')
                ->where('permission_name', $permission['permission_name'])
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('permissions')->insert($permission);
            }
        }
    }
}
