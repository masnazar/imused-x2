<?php

namespace App\Repositories;

use App\Models\SupplierModel;

class SupplierRepository
{
    protected $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    public function getAllSuppliers()
    {
        return $this->supplierModel->findAll();
    }

    public function getSupplierById($id)
    {
        return $this->supplierModel->find($id);
    }

    public function createSupplier($data)
    {
        return $this->supplierModel->insert($data);
    }

    public function updateSupplier($id, $data)
    {
        return $this->supplierModel->update($id, $data);
    }

    public function deleteSupplier($id)
    {
        return $this->supplierModel->delete($id);
    }
}
