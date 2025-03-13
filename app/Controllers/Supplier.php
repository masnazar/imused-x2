<?php

namespace App\Controllers;

use App\Services\SupplierService;
use CodeIgniter\Controller;

class Supplier extends BaseController
{
    protected $supplierService;

    public function __construct()
    {
        $this->supplierService = new SupplierService();
    }

    public function index()
    {
        $data['suppliers'] = $this->supplierService->getAllSuppliers();
        return view('suppliers/index', $data);
    }

    public function create()
    {
        return view('suppliers/create');
    }

    public function store()
    {
        $result = $this->supplierService->createSupplier($this->request);

        if (!$result['status']) {
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        return redirect()->to('/suppliers')->with('success', $result['message']);
    }

    public function edit($id)
    {
        $data['supplier'] = $this->supplierService->getSupplierById($id);
        return view('suppliers/edit', $data);
    }

    public function update($id)
    {
        $result = $this->supplierService->updateSupplier($id, $this->request);

        if (!$result['status']) {
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }

        return redirect()->to('/suppliers')->with('success', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->supplierService->deleteSupplier($id);
        return redirect()->to('/suppliers')->with('success', $result['message']);
    }
}
