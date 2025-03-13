<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Log\Logger;

class ProductService
{
    protected $productRepo;
    protected $validation;
    protected $logger;

    public function __construct(ProductRepository $productRepo, Validation $validation, Logger $logger)
    {
        $this->productRepo = $productRepo;
        $this->validation = $validation;
        $this->logger = $logger;
    }

    /**
     * 📌 Ambil semua produk
     */
    public function getAllProducts()
    {
        try {
            return $this->productRepo->getAllProducts();
        } catch (\Exception $e) {
            $this->logger->error("❌ Error saat mengambil produk: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 📌 Ambil produk berdasarkan ID
     */
    public function getProductById($id)
    {
        try {
            return $this->productRepo->getProductById($id);
        } catch (\Exception $e) {
            $this->logger->error("❌ Error mengambil produk ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 📌 Simpan produk baru
     */
    public function createProduct($data)
    {
        try {
            // Validasi SKU unik
            if (!$this->productRepo->isSkuUnique($data['sku'])) {
                return ['error' => 'SKU sudah digunakan. Gunakan SKU lain.'];
            }

            $result = $this->productRepo->insertProduct($data);

            // Audit Log
            log_message('info', "📌 [AUDIT] Produk baru ditambahkan: " . json_encode($data));

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("❌ Error menambahkan produk: " . $e->getMessage());
            return ['error' => 'Gagal menambahkan produk.'];
        }
    }

    /**
     * 📌 Update produk berdasarkan ID
     */
    public function updateProduct($id, $data)
    {
        try {
            $result = $this->productRepo->updateProduct($id, $data);

            // Audit Log
            log_message('info', "📌 [AUDIT] Produk ID {$id} diperbarui: " . json_encode($data));

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("❌ Error memperbarui produk ID {$id}: " . $e->getMessage());
            return ['error' => 'Gagal memperbarui produk.'];
        }
    }

    /**
     * 📌 Hapus produk berdasarkan ID
     */
    public function deleteProduct($id)
    {
        try {
            $result = $this->productRepo->deleteProduct($id);

            // Audit Log
            log_message('info', "📌 [AUDIT] Produk ID {$id} dihapus.");

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("❌ Error menghapus produk ID {$id}: " . $e->getMessage());
            return ['error' => 'Gagal menghapus produk.'];
        }
    }
}
