<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryLogModel extends Model
{
    protected $table            = 'inventory_logs';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'warehouse_id', 'product_id', 'user_id', 'type', 'quantity', 'note'
    ];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = false;

    /**
     * 📌 Ambil log berdasarkan produk & gudang
     */
    public function getLogsByProductWarehouse($productId, $warehouseId)
    {
        return $this->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
