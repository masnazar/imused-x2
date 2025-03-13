<?php

namespace App\Controllers;

use App\Services\WarehouseService;
use App\Repositories\WarehouseRepository;
use CodeIgniter\Controller;

class Warehouse extends Controller
{
    protected $warehouseService;

    public function __construct()
    {
        $this->warehouseService = new WarehouseService(
            new WarehouseRepository(),
            \Config\Services::validation(),
            \Config\Services::logger()
        );
    }

    /**
     * 📌 Menampilkan daftar warehouse
     */
    public function index()
    {
        $warehouses = $this->warehouseService->getAllWarehouses();
        return view('warehouse/index', ['warehouses' => $warehouses]);
    }

    /**
     * 📌 Menampilkan form tambah warehouse
     */
    public function create()
    {
        return view('warehouse/create');
    }

    /**
     * 📌 Proses simpan warehouse baru
     */
    public function store()
    {
        $postData = $this->request->getPost();
        $result = $this->warehouseService->createWarehouse($postData);

        if (isset($result['error'])) {
            return redirect()->back()->withInput()->with('error', implode(', ', $result['error']));
        }

        return redirect()->to('/warehouse')->with('success', '✅ Warehouse berhasil ditambahkan.');
    }

    /**
     * 📌 Menampilkan form edit warehouse
     */
    public function edit($id)
    {
        $data['warehouse'] = $this->warehouseService->getWarehouseById($id);
        return view('warehouse/edit', $data);
    }

    /**
     * 📌 Proses update warehouse
     */
    public function update($id)
    {
        $postData = $this->request->getPost();
        $this->warehouseService->updateWarehouse($id, $postData);
        return redirect()->to('/warehouse')->with('success', '✅ Warehouse berhasil diperbarui.');
    }

    /**
     * 📌 Hapus warehouse
     */
    public function delete($id)
    {
        $this->warehouseService->deleteWarehouse($id);
        return redirect()->to('/warehouse')->with('success', '✅ Warehouse berhasil dihapus.');
    }
}
