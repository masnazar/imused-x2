<?php

namespace App\Repositories;

use App\Models\PermissionModel;

class PermissionRepository
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

    public function getPermissionById($id)
    {
        return $this->permissionModel->find($id);
    }

    public function createPermission($data)
    {
        return $this->permissionModel->insert($data);
    }

    public function updatePermission($id, $data)
    {
        return $this->permissionModel->update($id, $data);
    }

    public function deletePermission($id)
    {
        return $this->permissionModel->delete($id);
    }

    public function getPermissionsByRole($roleId)
    {
        $query = $this->db->table('role_permissions')
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->get();

        log_message('info', '🔍 SQL Query: ' . $this->db->getLastQuery());

        return $query->getResultArray();
    }

}
