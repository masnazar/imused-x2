<?php

namespace App\Repositories;

use App\Models\PurchaseOrderModel;

class PurchaseOrderRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new PurchaseOrderModel();
    }

    public function getAllPurchaseOrders()
    {
        return $this->model->getPurchaseOrders();
    }

    public function findById($id)
    {
        return $this->model->find($id);
    }

    public function create($data)
    {
        return $this->model->insert($data);
    }

    public function update($id, $data)
    {
        return $this->model->update($id, $data);
    }

    public function delete($id)
    {
        return $this->model->delete($id);
    }
}
