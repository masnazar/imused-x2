<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;

class Inventory extends BaseController
{
    protected $service;

    /**
     * Konstruktor untuk menginisialisasi service Inventory.
     */
    public function __construct()
    {
        $this->service = new InventoryService(new InventoryRepository());
    }

    /**
     * Menampilkan halaman utama inventory.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function index()
    {
        $warehouseRepo = new \App\Repositories\WarehouseRepository();
        $warehouses = $warehouseRepo->getAllWarehouses();

        $warehouseId = $this->request->getGet('warehouse_id');
        $totalStock = $this->service->getTotalStock($warehouseId);

        return view('inventory/index', [
            'warehouses'   => $warehouses,
            'warehouse_id' => $warehouseId,
            'total_stock'  => $totalStock,
        ]);
    }

    /**
     * Mengembalikan data inventory dalam format JSON untuk datatable.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function datatable()
    {
        $request = service('request');

        $start = (int) $request->getGet('start');
        $length = (int) $request->getGet('length');
        $search = $request->getGet('search')['value'] ?? '';
        $warehouseId = $request->getGet('warehouse_id') ?? null;

        $data = $this->service->getInventoryDatatable($start, $length, $search, $warehouseId);
        $recordsTotal = $this->service->countTotalInventory($warehouseId);
        $recordsFiltered = $this->service->countFilteredInventory($search, $warehouseId);

        return $this->response->setJSON([
            'draw' => (int) $request->getGet('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Memperbarui stok produk di gudang tertentu.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function updateStock()
    {
        $warehouseId = $this->request->getPost('warehouse_id');
        $productId   = $this->request->getPost('product_id');
        $quantity    = $this->request->getPost('quantity');

        if ($this->service->updateStock($warehouseId, $productId, $quantity)) {
            return redirect()->to('/inventory')->with('success', 'Stok berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui stok.');
    }

    /**
     * Menampilkan log perubahan stok untuk produk tertentu di gudang tertentu.
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function logs()
{
    $productId = $this->request->getGet('product_id');
    $warehouseId = $this->request->getGet('warehouse_id');

    $products = model('App\Models\ProductModel')->findAll();
    $warehouses = model('App\Models\WarehouseModel')->findAll();
    $logs = [];

    if ($productId && $warehouseId) {
        $inventoryRepo = new \App\Repositories\InventoryRepository(); // 💥 Pakai repo!
        $logs = $inventoryRepo->getInventoryLogs($productId, $warehouseId);
    }

    return view('inventory/logs', [
        'products' => $products,
        'warehouses' => $warehouses,
        'logs' => $logs,
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
    ]);
}

}
