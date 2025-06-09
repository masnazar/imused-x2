<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class MenuService
{
    protected $db;

    public function __construct(ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

   public function getAllAccessMatrix()
{
    $menus = $this->db->table('menus')
        ->select('id, name, slug')
        ->orderBy('sort_order', 'ASC')
        ->get()
        ->getResultArray();

    $roles = $this->db->table('roles')
        ->select('id, role_name, slug')
        ->get()
        ->getResultArray();

    $accessData = $this->db->table('menu_role_access')->get()->getResultArray();

    $accessMap = [];
    foreach ($accessData as $row) {
        $accessMap[$row['menu_id']][$row['role_id']] = true;
    }

    $result = [];
    foreach ($menus as $menu) {
        foreach ($roles as $role) {
            $result[] = [
                'menu_id'    => $menu['id'],
                'name'       => $menu['name'],         // ⬅️ ganti ke `name`
                'slug'       => $menu['slug'],         // ⬅️ ganti ke `slug`
                'role_id'    => $role['id'],
                'role_name'  => $role['role_name'],
                'has_access' => isset($accessMap[$menu['id']][$role['id']]),
            ];
        }
    }

    return $result;
}

/**
 * Update akses role terhadap menu tertentu.
 *
 * @param int $menuId
 * @param array $roleIds
 * @return void
 */
public function updateRoleAccess(int $menuId, array $roleIds): void
{
    // Hapus semua akses sebelumnya untuk menu ini
    $this->db->table('menu_role_access')->where('menu_id', $menuId)->delete();

    // Tambah ulang berdasarkan role yang dipilih
    foreach ($roleIds as $roleId) {
        $this->db->table('menu_role_access')->insert([
            'menu_id' => $menuId,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}


public function getSidebarMenusForRole($roleId)
{
    // Ambil semua menu yang bisa diakses oleh role ini
    $menus = $this->db->table('menus')
        ->select('menus.*')
        ->join('menu_role_access', 'menu_role_access.menu_id = menus.id')
        ->where('menu_role_access.role_id', $roleId)
        ->where('menus.is_active', 1)
        ->orderBy('menus.sort_order', 'ASC')
        ->get()
        ->getResultArray();

    // Atur struktur tree
    $menuTree = [];
    foreach ($menus as $menu) {
        if ($menu['parent_id'] === null) {
            $menu['children'] = [];
            $menuTree[$menu['id']] = $menu;
        }
    }

    foreach ($menus as $menu) {
        if ($menu['parent_id'] !== null && isset($menuTree[$menu['parent_id']])) {
            $menuTree[$menu['parent_id']]['children'][] = $menu;
        }
    }

    return $menuTree;
}


    /**
     * Toggle akses menu untuk role tertentu.
     */
    public function toggleAccess(int $menuId, int $roleId): void
    {
        $exists = $this->db->table('menu_role_access')
            ->where('menu_id', $menuId)
            ->where('role_id', $roleId)
            ->get()
            ->getRow();

        if ($exists) {
            $this->db->table('menu_role_access')
                ->where('menu_id', $menuId)
                ->where('role_id', $roleId)
                ->delete();
        } else {
            $this->db->table('menu_role_access')
                ->insert([
                    'menu_id' => $menuId,
                    'role_id' => $roleId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
        }
    }

    /**
     * Ambil semua menu aktif.
     */
    public function getAllMenus(): array
    {
        return $this->db->table('menus')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get()
            ->getResultArray();
    }

    /**
     * Ambil menu berdasarkan role
     */
    public function getMenusByRole(int $roleId): array
    {
        return $this->db->table('menus')
            ->select('menus.*')
            ->join('menu_role_access', 'menu_role_access.menu_id = menus.id')
            ->where('menu_role_access.role_id', $roleId)
            ->where('menus.is_active', 1)
            ->orderBy('menus.sort_order')
            ->get()
            ->getResultArray();
    }

public function updateFullAccessMatrix(array $matrix): void
{
    $this->db->table('menu_role_access')->truncate();

    foreach ($matrix as $menuId => $roleIds) {
        foreach ($roleIds as $roleId) {
            $this->db->table('menu_role_access')->insert([
                'menu_id' => $menuId,
                'role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}

public function getAllRoles(): array
{
    return $this->db->table('roles')
        ->select('id, role_name')
        ->get()
        ->getResultArray();
}

public function getAccessMap(): array
{
    $data = $this->db->table('menu_role_access')->get()->getResultArray();
    $map = [];
    foreach ($data as $row) {
        $map[$row['menu_id']][] = $row['role_id'];
    }
    return $map;
}


}
