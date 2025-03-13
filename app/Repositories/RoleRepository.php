<?php

namespace App\Repositories;

use CodeIgniter\Model;

class RoleRepository extends Model
{
    protected $table      = 'roles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role_name', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    /**
     * 📌 Ambil semua roles
     */
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * 📌 Ambil role berdasarkan ID
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * 📌 Simpan role baru
     */
    public function insertRole($data)
    {
        return $this->insert($data);
    }

    /**
     * 📌 Update role berdasarkan ID
     */
    public function updateRole($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * 📌 Hapus role berdasarkan ID
     */
    public function deleteRole($id)
    {
        return $this->delete($id);
    }
}
