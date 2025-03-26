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
    public function updateStock($warehouseId, $productId, $quantity, $userId = null, $note = null)
{
    try {
        $db = \Config\Database::connect();
        $db->transStart();

        $this->repo->updateStock($warehouseId, $productId, $quantity);

        $this->repo->logStock(
            $warehouseId,
            $productId,
            $userId,
            $quantity >= 0 ? 'in' : 'out', // ✅ Tipe log
            abs($quantity),
            $note
        );

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new DatabaseException('Gagal update dan log stok.');
        }

        return true;
    } catch (\Throwable $e) {
        log_message('error', $e->getMessage());
        return false;
    }
}

/**
 * 📌 Ambil total stok dari gudang tertentu (atau semua gudang)
 *
 * @param int|null $warehouseId
 * @return int
 */
public function getTotalStock(?int $warehouseId = null): int
{
    return $this->repo->getTotalStock($warehouseId);
}

public function getInventoryDatatable($start, $length, $search, $warehouseId = null)
{
    return $this->repo->getInventoryDatatable($start, $length, $search, $warehouseId);
}

public function countFilteredInventory($search, $warehouseId = null)
{
    return $this->repo->countFilteredInventory($search, $warehouseId);
}

public function countTotalInventory($warehouseId = null)
{
    return $this->repo->countTotalInventory($warehouseId);
}

/**
 * 📌 Tambah stok ke inventory + catat log
 */
public function increaseStock($warehouseId, $productId, $quantity, $note = null, $type = 'in')
{
    $this->repo->updateStock($warehouseId, $productId, $quantity); // menambahkan
    $this->repo->insertLog([
        'warehouse_id' => $warehouseId,
        'product_id'   => $productId,
        'user_id'      => user_id(), // helper custom
        'type'         => $type,
        'quantity'     => $quantity,
        'note'         => $note,
    ]);
}

/**
 * 📌 Kurangi stok dari inventory + catat log
 */
public function decreaseStock($warehouseId, $productId, $quantity, $note = null, $type = 'out')
{
    $this->repo->updateStock($warehouseId, $productId, -$quantity); // mengurangi
    $this->repo->insertLog([
        'warehouse_id' => $warehouseId,
        'product_id'   => $productId,
        'user_id'      => user_id(),
        'type'         => $type,
        'quantity'     => $quantity,
        'note'         => $note,
    ]);
}

}
