<?php

namespace App\Repositories;

use App\Models\PurchaseOrderModel;
use Exception;

class PurchaseOrderRepository
{
    protected $model;

    public function __construct()
    {
        $this->model = new PurchaseOrderModel();
    }

    /**
     * 📌 Ambil semua Purchase Orders
     */
    public function getAllPurchaseOrders()
    {
        try {
            return $this->model->getPurchaseOrders();
        } catch (Exception $e) {
            log_message('error', '❌ Gagal mengambil daftar Purchase Orders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 📌 Ambil Purchase Order berdasarkan ID
     */
    public function findById($id)
    {
        try {
            return $this->model->find($id);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal mengambil Purchase Order ID ' . $id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 📌 Buat Purchase Order baru
     */
    public function create($data)
    {
        try {
            return $this->model->insert($data);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal membuat Purchase Order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Perbarui Purchase Order
     */
    public function update($id, $data)
    {
        try {
            return $this->model->update($id, $data);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal memperbarui Purchase Order ID ' . $id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Hapus Purchase Order (Soft Delete)
     */
    public function delete($id)
    {
        try {
            return $this->model->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal menghapus Purchase Order ID ' . $id . ': ' . $e->getMessage());
            return false;
        }
    }
}
