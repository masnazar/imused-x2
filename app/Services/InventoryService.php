<?php

namespace App\Services;

use App\Repositories\InventoryRepository;
use CodeIgniter\Database\Exceptions\DatabaseException;

class InventoryService
{
    protected $repo;

    public function __construct(InventoryRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * 📌 Proses update stok dengan transaction
     */
    public function updateStock($warehouseId, $productId, $quantity)
    {
        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $this->repo->updateStock($warehouseId, $productId, $quantity);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new DatabaseException('Gagal memperbarui stok.');
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }
}
