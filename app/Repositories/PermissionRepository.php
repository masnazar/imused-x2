<?php

namespace App\Repositories;

use App\Models\PermissionModel;

/**
 * Repository untuk menangani data Permission.
 */
class PermissionRepository
{
    /**
     * @var \CodeIgniter\Database\BaseConnection $db
     * Koneksi database.
     */
    protected $db;

    /**
     * @var PermissionModel $permissionModel
     * Model Permission.
     */
    protected $permissionModel;

    /**
     * Konstruktor untuk inisialisasi koneksi database dan PermissionModel.
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->permissionModel = new PermissionModel();
    }

    /**
     * Mengambil semua data permission.
     *
     * @return array
     */
    public function getAllPermissions()
    {
        return $this->permissionModel->findAll();
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
     * Membuat data permission baru.
     *
     * @param array $data
     * @return int|false
     */
    public function createPermission($data)
    {
        return $this->permissionModel->insert($data);
    }

    /**
     * Memperbarui data permission berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePermission($id, $data)
    {
        return $this->permissionModel->update($id, $data);
    }

    /**
     * Menghapus data permission berdasarkan ID.
     *
     * @param int $id
     * @return bool
     */
    public function deletePermission($id)
    {
        return $this->permissionModel->delete($id);
    }

    /**
     * Mengambil data permission berdasarkan role.
     *
     * @param int $roleId
     * @return array
     */
    public function getPermissionsByRole($roleId)
    {
        $query = $this->db->table('role_permissions')
            ->select('permission_id')
            ->where('role_id', $roleId)
            ->get();

        log_message('info', '🔍 SQL Query: ' . $this->db->getLastQuery());

        return $query->getResultArray();
    }

    /**
     * Mengambil data permission khusus user tertentu.
     *
     * @param int $userId
     * @return array
     */
    public function getUserPermissions($userId)
    {
        return $this->db
            ->table('user_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
    }
}
