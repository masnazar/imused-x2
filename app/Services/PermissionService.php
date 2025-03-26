<?php

namespace App\Services;

use App\Models\PermissionModel;

class PermissionService
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
    }

    public function getAllPermissions()
    {
        return $this->permissionModel->findAll();
    }

    public function createPermission($data)
    {
        return $this->permissionModel->insert($data);
    }

    public function getPermissionById($id)
    {
        return $this->permissionModel->find($id);
    }

    public function updatePermission($id, $data)
    {
        return $this->permissionModel->update($id, $data);
    }

    public function deletePermission($id)
    {
        return $this->permissionModel->delete($id);
    }

    public function removePermissionsFromRole($roleId)
    {
        return $this->rolePermissionModel->where('role_id', $roleId)->delete();
    }

    public function assignPermissionsToRole($roleId, $permissions)
    {
        $data = [];
        foreach ($permissions as $permissionId) {
            $data[] = [
                'role_id'       => $roleId,
                'permission_id' => $permissionId
            ];
        }
        
        return $this->rolePermissionModel->insertBatch($data);
    }

    public function getPermissionsByUserId($userId): array
{
    return array_map('intval', array_column(
        $this->permissionRepo->getUserPermissions($userId),
        'permission_id'
    ));
}


}
