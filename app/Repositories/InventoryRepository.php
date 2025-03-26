<?php

namespace App\Repositories;

use App\Models\InventoryModel;
use App\Models\InventoryLogModel;

/**
 * Repository untuk mengelola data inventaris.
 */
class InventoryRepository
{
    protected $model;
    protected $logModel;
    protected $db;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect(); // Koneksi ke database
        $this->model = new InventoryModel();
        $this->logModel = new InventoryLogModel();
    }

    /**
     * Mengambil total inventaris.
     *
     * @return int
     */
    public function getTotalInventory()
    {
        return $this->model->getTotalInventory();
    }

    /**
     * Memperbarui stok produk di gudang tertentu.
     *
     * @param int $warehouseId
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStock($warehouseId, $productId, $quantity)
    {
        return $this->model->updateStock($warehouseId, $productId, $quantity);
    }

    /**
     * Mengambil stok produk berdasarkan gudang.
     *
     * @param int $warehouseId
     * @param int $productId
     * @return int
     */
    public function getStockByWarehouse($warehouseId, $productId)
    {
        return $this->model->getStockByWarehouse($warehouseId, $productId);
    }

    /**
     * Mencatat log stok.
     *
     * @param int $warehouseId
     * @param int $productId
     * @param int $userId
     * @param string $type
     * @param int $quantity
     * @param string|null $note
     * @return bool
     */
    public function logStock($warehouseId, $productId, $userId, $type, $quantity, $note = null)
    {
        return $this->logModel->insert([
            'warehouse_id' => $warehouseId,
            'product_id'   => $productId,
            'user_id'      => $userId,
            'type'         => $type,
            'quantity'     => $quantity,
            'note'         => $note,
        ]);
    }

    /**
     * Mengambil total stok dari gudang tertentu.
     *
     * @param int|null $warehouseId
     * @return int
     */
    public function getTotalStock(?int $warehouseId = null): int
    {
        $builder = $this->model->selectSum('stock');

        if (!is_null($warehouseId)) {
            $builder->where('warehouse_id', $warehouseId);
        }

        $result = $builder->first();

        return (int) ($result['stock'] ?? 0);
    }

    /**
     * Mengambil data inventaris untuk datatable.
     *
     * @param int $start
     * @param int $length
     * @param string|null $search
     * @param int|null $warehouseId
     * @return array
     */
    public function getInventoryDatatable($start, $length, $search, $warehouseId = null)
    {
        $builder = $this->model->builder()
            ->select('inventory.stock, products.sku, products.nama_produk, warehouses.name AS warehouse_name')
            ->join('products', 'products.id = inventory.product_id')
            ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
            ->where('inventory.deleted_at', null);

        if ($warehouseId) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('products.sku', $search)
                ->orLike('products.nama_produk', $search)
                ->orLike('warehouses.name', $search)
                ->groupEnd();
        }

        return $builder
            ->limit($length, $start)
            ->get()
            ->getResultArray();
    }

    /**
     * Menghitung total inventaris.
     *
     * @param int|null $warehouseId
     * @return int
     */
    public function countTotalInventory($warehouseId = null)
    {
        $builder = $this->model->builder()
            ->where('inventory.deleted_at', null);

        if ($warehouseId) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        return $builder->countAllResults();
    }

    /**
     * Menghitung jumlah inventaris yang difilter.
     *
     * @param string|null $search
     * @param int|null $warehouseId
     * @return int
     */
    public function countFilteredInventory($search, $warehouseId = null)
    {
        $builder = $this->model->builder()
            ->join('products', 'products.id = inventory.product_id')
            ->join('warehouses', 'warehouses.id = inventory.warehouse_id')
            ->where('inventory.deleted_at', null);

        if ($warehouseId) {
            $builder->where('inventory.warehouse_id', $warehouseId);
        }

        if ($search) {
            $builder->groupStart()
                ->like('products.sku', $search)
                ->orLike('products.nama_produk', $search)
                ->orLike('warehouses.name', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Menyisipkan log ke tabel inventory_logs.
     *
     * @param array $data
     * @return bool
     */
    public function insertLog(array $data)
    {
        return $this->db->table('inventory_logs')->insert($data);
    }

    public function getInventoryLogs($productId, $warehouseId)
{
    return $this->db->table('inventory_logs')
        ->select('inventory_logs.*, users.name AS user_name') // Jangan pakai alias
        ->join('users', 'users.id = inventory_logs.user_id', 'left') // Alias bikin masalah di array result
        ->where('inventory_logs.product_id', $productId)
        ->where('inventory_logs.warehouse_id', $warehouseId)
        ->orderBy('inventory_logs.created_at', 'DESC')
        ->get()
        ->getResultArray();
}



}
