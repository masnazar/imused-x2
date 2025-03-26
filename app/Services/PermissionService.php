<?php

namespace App\Services;

use App\Models\PermissionModel;
use App\Repositories\PermissionRepository;
use App\Models\RolePermissionModel;

/**
 * Service untuk mengelola operasi berkaitan dengan permission.
 */
class PermissionService
{
    protected $permissionModel;
    protected $permissionRepo;
    protected $rolePermissionModel;
    protected $db;

    /**
     * Konstruktor untuk inisialisasi model dan repository.
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->permissionModel = new PermissionModel();
        $this->permissionRepo = new PermissionRepository();
        $this->rolePermissionModel = new RolePermissionModel();
    }

    /**
     * Mengambil semua permission.
     *
     * @return array
     */
    public function getAllPermissions()
    {
        return $this->permissionModel->findAll();
    }

    /**
     * Membuat permission baru.
     *
     * @param array $data
     * @return int|false
     */
    public function createPermission($data)
    {
        return $this->permissionModel->insert($data);
    }

    /**
     * Mengambil data permission berdasarkan ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getPermissionById($id)
    {
        return $this->permissionModel->find($id);
    }

    /**
     * Memperbarui data permission berdasarkan ID.
     *
     * @param int   $id
     * @param array $data
     * @return bool
     */
    public function updatePermission($id, $data)
    {
        return $this->permissionModel->update($id, $data);
    }

    /**
     * Menghapus permission berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function deletePermission($id)
    {
        return $this->permissionModel->delete($id);
    }

    /**
     * Menghapus semua permission dari role tertentu.
     *
     * @param int $roleId
     * @return bool
     */
    public function removePermissionsFromRole($roleId)
    {
        return $this->rolePermissionModel->where('role_id', $roleId)->delete();
    }

    /**
     * Mengaitkan beberapa permission ke role.
     *
     * @param int   $roleId
     * @param array $permissions
     * @return bool
     */
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

    /**
     * Mengambil semua permission milik user berdasarkan userId.
     *
     * @param int $userId
     * @return array
     */
    public function getPermissionsByUserId($userId): array
    {
        return array_map('intval', array_column(
            $this->permissionRepo->getUserPermissions($userId),
            'permission_id'
        ));
    }
}
