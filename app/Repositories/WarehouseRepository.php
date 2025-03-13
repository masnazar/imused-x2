<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;

class WarehouseRepository
{
    protected $db;
    protected $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('warehouses');
    }

    /**
     * 📌 Ambil semua warehouse
     */
    public function getAllWarehouses()
    {
        return $this->builder->get()->getResultArray();
    }

    /**
     * 📌 Ambil warehouse berdasarkan ID
     */
    public function getWarehouseById($id)
    {
        return $this->builder->where('id', $id)->get()->getRowArray();
    }

    /**
     * 📌 Simpan warehouse baru
     */
    public function insertWarehouse($data)
    {
        return $this->builder->insert($data);
    }

    /**
     * 📌 Update warehouse berdasarkan ID
     */
    public function updateWarehouse($id, $data)
    {
        return $this->builder->where('id', $id)->update($data);
    }

    /**
     * 📌 Hapus warehouse berdasarkan ID
     */
    public function deleteWarehouse($id)
    {
        return $this->builder->where('id', $id)->delete();
    }
}
