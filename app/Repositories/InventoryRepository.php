<?php

namespace App\Repositories;

use App\Models\InventoryModel;

class InventoryRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new InventoryModel();
    }

    public function getTotalInventory()
    {
        return $this->model->getTotalInventory();
    }

    public function updateStock($warehouseId, $productId, $quantity)
    {
        return $this->model->updateStock($warehouseId, $productId, $quantity);
    }

    public function getStockByWarehouse($warehouseId, $productId)
    {
        return $this->model->getStockByWarehouse($warehouseId, $productId);
    }
}
