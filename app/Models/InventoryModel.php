<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table            = 'inventory';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'warehouse_id',
        'product_id',
        'stock',
    ];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    /**
     * 📌 Ambil total stok semua gudang
     */
    public function getTotalInventory()
    {
        return $this->select('SUM(stock) as total_stock')->first();
    }

    /**
     * 📌 Update stok produk di warehouse tertentu
     */
    public function updateStock($warehouseId, $productId, $quantity)
    {
        $existingStock = $this->where('warehouse_id', $warehouseId)
                              ->where('product_id', $productId)
                              ->first();

        if ($existingStock) {
            return $this->update($existingStock['id'], [
                'stock' => $existingStock['stock'] + $quantity,
            ]);
        } else {
            return $this->insert([
                'warehouse_id' => $warehouseId,
                'product_id'   => $productId,
                'stock'        => $quantity,
            ]);
        }
    }

    /**
     * 📌 Ambil stok berdasarkan warehouse dan produk
     */
    public function getStockByWarehouse($warehouseId, $productId)
    {
        return $this->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->first();
    }
}
