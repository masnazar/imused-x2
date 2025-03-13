<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'nama_produk',
        'brand_id',
        'sku',
        'hpp',
        'stock',
        'total_nilai_stok',
        'no_bpom',
        'no_halal',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $beforeInsert = ['calculateTotalStockValue'];
    protected $beforeUpdate = ['calculateTotalStockValue'];

    /**
     * 📌 Ambil semua produk dengan relasi ke brand
     */
    public function getAllProducts()
    {
        return $this->select('products.id, products.nama_produk, products.sku, products.hpp, products.stock, 
                              products.total_nilai_stok, products.no_bpom, products.no_halal, 
                              brands.brand_name')
                    ->join('brands', 'brands.id = products.brand_id', 'left')
                    ->findAll();
    }

    /**
     * 📌 Ambil produk berdasarkan ID
     */
    public function getProductById($id)
    {
        return $this->select('products.*, brands.brand_name')
                    ->join('brands', 'brands.id = products.brand_id', 'left')
                    ->where('products.id', $id)
                    ->first();
    }

    /**
     * 📌 Simpan produk baru
     */
    public function createProduct($data)
    {
        return $this->insert($data);
    }

    /**
     * 📌 Update produk berdasarkan ID
     */
    public function updateProduct($id, $data)
    {
        return $this->update($id, $data);
    }

    /**
     * 📌 Hapus produk berdasarkan ID
     */
    public function deleteProduct($id)
    {
        return $this->delete($id);
    }

    /**
     * 📌 Hitung total nilai stok
     */
    protected function calculateTotalStockValue(array $data)
    {
        if (!isset($data['data']['hpp']) || !isset($data['data']['stock'])) {
            $data['data']['total_nilai_stok'] = 0;
        } else {
            $data['data']['total_nilai_stok'] = (float) $data['data']['hpp'] * (int) $data['data']['stock'];
        }

        return $data;
    }

    /**
     * 📌 Validasi SKU harus unik
     */
    public function isSkuUnique($sku, $id = null)
    {
        $builder = $this->where('sku', $sku);

        if ($id) {
            $builder->where('id !=', $id);
        }

        return $builder->countAllResults() === 0;
    }
}