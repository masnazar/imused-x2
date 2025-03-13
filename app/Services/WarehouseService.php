<?php

namespace App\Services;

use App\Repositories\WarehouseRepository;
use CodeIgniter\Validation\Validation;
use Psr\Log\LoggerInterface;

class WarehouseService
{
    protected $warehouseRepo;
    protected $validation;
    protected $logger;

    public function __construct(WarehouseRepository $warehouseRepo, Validation $validation, LoggerInterface $logger)
    {
        $this->warehouseRepo = $warehouseRepo;
        $this->validation = $validation;
        $this->logger = $logger;
    }

    /**
     * 📌 Ambil semua warehouse
     */
    public function getAllWarehouses()
    {
        return $this->warehouseRepo->getAllWarehouses();
    }

    /**
     * 📌 Ambil warehouse berdasarkan ID
     */
    public function getWarehouseById($id)
    {
        return $this->warehouseRepo->getWarehouseById($id);
    }

    /**
     * 📌 Simpan warehouse baru
     */
    public function createWarehouse($data)
    {
        $rules = [
            'name' => 'required',
            'address' => 'required',
            'warehouse_type' => 'required|in_list[Internal,Third-Party]',
            'pic_name' => 'required',
            'pic_contact' => 'required',
        ];

        if (!$this->validation->setRules($rules)->run($data)) {
            return ['error' => $this->validation->getErrors()];
        }

        try {
            $this->warehouseRepo->insertWarehouse($data);
            $this->logger->info("✅ Warehouse berhasil ditambahkan: " . json_encode($data));
            return true;
        } catch (\Exception $e) {
            $this->logger->error("❌ Gagal menyimpan warehouse: " . $e->getMessage());
            return ['error' => 'Gagal menyimpan warehouse'];
        }
    }

    /**
     * 📌 Update warehouse berdasarkan ID
     */
    public function updateWarehouse($id, $data)
    {
        try {
            $this->warehouseRepo->updateWarehouse($id, $data);
            $this->logger->info("✅ Warehouse berhasil diperbarui: " . json_encode($data));
            return true;
        } catch (\Exception $e) {
            $this->logger->error("❌ Gagal memperbarui warehouse: " . $e->getMessage());
            return ['error' => 'Gagal memperbarui warehouse'];
        }
    }

    /**
     * 📌 Hapus warehouse berdasarkan ID
     */
    public function deleteWarehouse($id)
    {
        try {
            $this->warehouseRepo->deleteWarehouse($id);
            $this->logger->info("✅ Warehouse ID {$id} berhasil dihapus.");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("❌ Gagal menghapus warehouse: " . $e->getMessage());
            return ['error' => 'Gagal menghapus warehouse'];
        }
    }
}
