<?php

namespace App\Services;

use App\Repositories\PurchaseOrderRepository;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Exception;

class PurchaseOrderService
{
    protected $repo;

    public function __construct(PurchaseOrderRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * 📌 Ambil semua Purchase Orders
     */
    public function getAllPurchaseOrders()
    {
        return $this->repo->getAllPurchaseOrders();
    }

    /**
     * 📌 Buat Purchase Order baru dengan transaksi database
     */
    public function createPurchaseOrder($data)
    {
        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $result = $this->repo->create($data);
            if (!$result) {
                throw new DatabaseException('Gagal membuat Purchase Order.');
            }

            log_message('info', '🟢 Purchase Order berhasil dibuat: ' . json_encode($data));

            $db->transComplete();
            return true;
        } catch (Exception $e) {
            $db->transRollback();
            log_message('error', '❌ Error saat membuat Purchase Order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Perbarui Purchase Order
     */
    public function updatePurchaseOrder($id, $data)
    {
        try {
            return $this->repo->update($id, $data);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal memperbarui Purchase Order ID ' . $id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Hapus Purchase Order dengan Soft Delete
     */
    public function deletePurchaseOrder($id)
    {
        try {
            return $this->repo->delete($id);
        } catch (Exception $e) {
            log_message('error', '❌ Gagal menghapus Purchase Order ID ' . $id . ': ' . $e->getMessage());
            return false;
        }
    }
}
