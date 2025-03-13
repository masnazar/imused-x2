<?php

namespace App\Services;

use App\Repositories\SupplierRepository;
use CodeIgniter\HTTP\RequestInterface;

class SupplierService
{
    protected $supplierRepo;
    protected $validation;

    public function __construct()
    {
        $this->supplierRepo = new SupplierRepository();
        $this->validation = \Config\Services::validation();
    }

    public function getAllSuppliers()
    {
        return $this->supplierRepo->getAllSuppliers();
    }

    public function getSupplierById($id)
    {
        return $this->supplierRepo->getSupplierById($id);
    }

    public function createSupplier(RequestInterface $request)
    {
        $data = $request->getPost();

        $rules = [
            'supplier_name' => 'required|min_length[3]',
            'supplier_address' => 'required',
            'supplier_pic_name' => 'required',
            'supplier_pic_contact' => 'required|numeric',
        ];

        if (!$this->validation->setRules($rules)->run($data)) {
            return [
                'status' => false,
                'errors' => $this->validation->getErrors(),
            ];
        }

        $this->supplierRepo->createSupplier($data);
        return ['status' => true, 'message' => 'Supplier berhasil ditambahkan.'];
    }

    public function updateSupplier($id, RequestInterface $request)
    {
        $data = $request->getPost();

        $rules = [
            'supplier_name' => 'required|min_length[3]',
            'supplier_address' => 'required',
            'supplier_pic_name' => 'required',
            'supplier_pic_contact' => 'required|numeric',
        ];

        if (!$this->validation->setRules($rules)->run($data)) {
            return [
                'status' => false,
                'errors' => $this->validation->getErrors(),
            ];
        }

        $this->supplierRepo->updateSupplier($id, $data);
        return ['status' => true, 'message' => 'Supplier berhasil diperbarui.'];
    }

    public function deleteSupplier($id)
    {
        $this->supplierRepo->deleteSupplier($id);
        return ['status' => true, 'message' => 'Supplier berhasil dihapus.'];
    }
}
