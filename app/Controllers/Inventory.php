<?php

namespace App\Controllers;

use App\Services\InventoryService;
use App\Repositories\InventoryRepository;
use CodeIgniter\Controller;

class Inventory extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new InventoryService(new InventoryRepository());
    }

    public function index()
    {
        return view('inventory/index');
    }

    public function updateStock()
    {
        $warehouseId = $this->request->getPost('warehouse_id');
        $productId   = $this->request->getPost('product_id');
        $quantity    = $this->request->getPost('quantity');

        if ($this->service->updateStock($warehouseId, $productId, $quantity)) {
            return redirect()->to('/inventory')->with('swal_success', 'Stok berhasil diperbarui.');
        }

        return redirect()->back()->with('swal_error', 'Gagal memperbarui stok.');
    }
}
