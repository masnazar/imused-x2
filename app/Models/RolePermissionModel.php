<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'role_permissions';
    protected $allowedFields = ['role_id', 'permission_id'];

    public function getPermissionsByRole($roleId)
    {
        return $this->where('role_id', $roleId)->findAll();
    }

    public function assignPermissions($roleId, $permissions)
    {
        // Hapus semua permissions lama
        $this->where('role_id', $roleId)->delete();

        // Tambahkan yang baru
        $data = [];
        foreach ($permissions as $permissionId) {
            $data[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ];
        }

        if (!empty($data)) {
            $this->insertBatch($data);
        }
    }
}
