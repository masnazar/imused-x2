<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;

class BrandRepository
{
    protected $db;
    protected $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('brands');
    }

    /**
     * 📌 Ambil semua brands dengan supplier
     */
    public function getAllBrands()
    {
        return $this->builder
            ->select('brands.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = brands.supplier_id')
            ->get()
            ->getResultArray();
    }

    /**
     * 📌 Ambil brand berdasarkan ID
     */
    public function getBrandById($id)
    {
        return $this->builder
            ->select('brands.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = brands.supplier_id')
            ->where('brands.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * 📌 Simpan brand baru
     */
    public function insertBrand($data)
    {
        return $this->builder->insert($data);
    }

    /**
     * 📌 Update brand berdasarkan ID
     */
    public function updateBrand($id, $data)
    {
        return $this->builder->where('id', $id)->update($data);
    }

    /**
     * 📌 Hapus brand berdasarkan ID
     */
    public function deleteBrand($id)
    {
        return $this->builder->where('id', $id)->delete();
    }
}
