<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;

class ProductRepository
{
    protected $db;
    protected $builder;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('products');
    }

    /**
     * 📌 Ambil semua produk dengan relasi ke brand
     */
    public function getAllProducts()
    {
        try {
            return $this->builder
    ->select('
        products.id,
        products.nama_produk,
        products.sku,
        products.hpp,
        products.stock,
        products.total_nilai_stok,
        products.no_bpom,
        products.no_halal,
        products.lead_time_days,
        products.safety_stock,
        products.min_stock,
        products.max_stock,
        brands.brand_name
    ')
    ->join('brands', 'brands.id = products.brand_id', 'left')
    ->get()
    ->getResultArray();

        } catch (\Exception $e) {
            log_message('error', "❌ Error mengambil semua produk: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 📌 Ambil produk berdasarkan ID
     */
    public function getProductById($id)
    {
        try {
            return $this->builder
                ->select('products.*, brands.brand_name')
                ->join('brands', 'brands.id = products.brand_id', 'left')
                ->where('products.id', $id)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', "❌ Error mengambil produk ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 📌 Simpan produk baru
     */
    public function insertProduct($data)
    {
        try {
            $data['total_nilai_stok'] = (float) $data['hpp'] * (int) $data['stock'];
            return $this->builder->insert($data);
        } catch (\Exception $e) {
            log_message('error', "❌ Error menambahkan produk: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Update produk berdasarkan ID
     */
    public function updateProduct($id, $data)
    {
        try {
            return $this->builder->where('id', $id)->update($data);
        } catch (\Exception $e) {
            log_message('error', "❌ Error memperbarui produk ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Hapus produk berdasarkan ID
     */
    public function deleteProduct($id)
    {
        try {
            return $this->builder->where('id', $id)->delete();
        } catch (\Exception $e) {
            log_message('error', "❌ Error menghapus produk ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 📌 Validasi SKU unik
     */
    public function isSkuUnique($sku, $id = null)
    {
        try {
            $builder = $this->builder->where('sku', $sku);

            if ($id) {
                $builder->where('id !=', $id);
            }

            return $builder->countAllResults() === 0;
        } catch (\Exception $e) {
            log_message('error', "❌ Error mengecek SKU unik: " . $e->getMessage());
            return false;
        }
    }

    public function findById(int $id): ?array
{
    return $this->builder
        ->where('id', $id)
        ->get()
        ->getRowArray();
}

public function addStock(int $productId, int $qty): bool
{
    $product = $this->db->table('products')->where('id', $productId)->get()->getRow();

    if (!$product) {
        log_message('error', "[ProductRepo] Produk ID $productId tidak ditemukan");
        return false;
    }

    $newStock = (int) $product->stock + $qty;
    $stockValue = $newStock * (int) $product->hpp;

    return $this->db->table('products')
        ->where('id', $productId)
        ->update([
            'stock' => $newStock,
            'total_nilai_stok' => $stockValue,
        ]);
}

/**
 * Kurangi stok produk
 *
 * @param int $productId
 * @param int $quantity
 * @return bool
 */
public function reduceStock(int $productId, int $quantity): bool
{
    return $this->db->table('products')
        ->set('stock', 'stock - ' . (int) $quantity, false)
        ->where('id', $productId)
        ->update();
}


}
