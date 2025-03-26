<?php

namespace App\Services;

use App\Repositories\PurchaseOrderRepository;
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
     * 📌 Generate Nomor PO dengan format: PO/YY/BRN/MM-XXX
     */
    public function generatePoNumber($productId)
    {
        $year = date('y'); // Tahun 2 digit terakhir
        $month = date('n'); // Bulan dalam angka
        $monthRoman = $this->repo->convertToRoman($month); // Konversi ke Romawi

        // Ambil kode brand berdasarkan produk
        $brandCode = $this->repo->getBrandCodeByProduct($productId);
        if (!$brandCode) {
            throw new Exception("Kode brand tidak ditemukan untuk produk ini.");
        }

        // Ambil nomor terakhir dalam bulan berjalan
        $lastNumber = $this->repo->getLastPoNumber($year, $monthRoman, $brandCode);
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "PO/$year/$brandCode/$monthRoman-$newNumber";
    }


    /**
     * 📌 Simpan Purchase Order
     */
    public function createPurchaseOrder($data)
    {
        return $this->repo->create($data);
    }

    /**
     * 📌 Simpan Detail Purchase Order
     */
    public function createPurchaseOrderDetail($data)
    {
        return $this->repo->createDetail($data);
    }

    /**
     * 📌 Ambil ID PO terakhir yang disimpan
     */
    public function getLastInsertId()
    {
        return $this->repo->getLastInsertId();
    }

    /**
     * 📌 Hapus Purchase Order dengan Soft Delete
     */
    public function deletePurchaseOrder($id)
    {
        return $this->repo->delete($id);
    }

    public function getPurchaseOrderDetails($purchaseOrderId)
{
    return $this->repo->getPurchaseOrderDetails($purchaseOrderId);
}

public function getPurchaseOrderById($id)
{
    $purchaseOrder = $this->repo->findById($id);

    if (!$purchaseOrder) {
        return null;
    }

    // Tambahkan default value jika address kosong
    $purchaseOrder['supplier_address'] = $purchaseOrder['supplier_address'] ?? 'Alamat tidak tersedia';
    
    $purchaseOrder['products'] = $this->repo->getPurchaseOrderDetails($id);

    return $purchaseOrder;
}



public function processReceivePo($data)
{
    log_message('info', '📥 Menerima data penerimaan PO: ' . json_encode($data));

    if (!isset($data['warehouse_id']) || empty($data['warehouse_id'])) {
        log_message('error', '❌ Error di receive(): Gudang belum dipilih!');
        throw new \Exception("Gudang harus dipilih.");
    }

    if (!isset($data['products']) || empty($data['products'])) {
        log_message('error', '❌ Error di receive(): Data produk tidak ditemukan!');
        throw new \Exception("Data produk tidak ditemukan.");
    }

    $db = \Config\Database::connect();
    $db->transStart();

    try {
        foreach ($data['products'] as $product) {
            if (!isset($product['product_id'], $product['received_quantity'])) {
                throw new \Exception("Format data produk tidak valid.");
            }

            $this->repo->logPoReceipt(
                $data['purchase_order_id'],
                $product['product_id'],
                $data['warehouse_id'],
                $product['received_quantity']
            );

            $this->repo->updateReceivedQuantity(
                $data['purchase_order_id'],
                $product['product_id'],
                $product['received_quantity']
            );
        }

        $this->repo->updatePoStatus($data['purchase_order_id']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new \Exception("Gagal menyimpan penerimaan PO.");
        }

        log_message('info', '✅ Penerimaan PO berhasil diproses.');
        return true;

    } catch (\Exception $e) {
        $db->transRollback();
        log_message('error', '❌ Error di processReceivePo(): ' . $e->getMessage());
        throw new \Exception($e->getMessage());
    }
}


public function getAllWarehouses()
{
    return $this->repo->getAllWarehouses();
}

public function getPOStatistics()
{
    return [
        'total' => $this->repo->countAll(),
        'pending' => $this->repo->countByStatus('Pending'),
        'completed' => $this->repo->countByStatus('Completed'),
        'total_items' => $this->repo->getTotalItems()
    ];
}

}
