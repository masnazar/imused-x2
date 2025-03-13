<?php

namespace App\Services;

use App\Repositories\RoleRepository;

class RoleService
{
    protected $roleRepo;

    public function __construct(RoleRepository $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    /**
     * 📌 Ambil semua roles
     */
    public function getAllRoles()
    {
        return $this->roleRepo->getAll();
    }

    /**
     * 📌 Ambil role berdasarkan ID
     */
    public function getRoleById($id)
    {
        return $this->roleRepo->findById($id);
    }

    /**
     * 📌 Simpan role baru
     */
    public function createRole($data)
    {
        return $this->roleRepo->insertRole($data);
    }

    /**
     * 📌 Update role berdasarkan ID
     */
    public function updateRole($id, $data)
    {
        return $this->roleRepo->updateRole($id, $data);
    }

    /**
     * 📌 Hapus role berdasarkan ID
     */
    public function deleteRole($id)
    {
        return $this->roleRepo->deleteRole($id);
    }
}
