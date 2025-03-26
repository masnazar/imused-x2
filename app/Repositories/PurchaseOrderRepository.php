<?php

namespace App\Repositories;

use App\Models\PurchaseOrderModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PurchaseOrderRepository
{
    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new PurchaseOrderModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * 📌 Ambil semua Purchase Orders
     */
    public function getAllPurchaseOrders()
    {
        return $this->model->getPurchaseOrders();
    }

    /**
     * 📌 Ambil kode brand berdasarkan produk yang dipilih
     */
    public function getBrandCodeByProduct($productId)
    {
        return $this->db->table('brands')
            ->select('brands.kode_brand')
            ->join('products', 'products.brand_id = brands.id', 'left')
            ->where('products.id', $productId)
            ->get()
            ->getRowArray()['kode_brand'] ?? null;
    }

    /**
     * 📌 Ambil nomor urutan terakhir dalam bulan berjalan
     */
    public function getLastPoNumber($year, $month, $brandCode)
    {
        $prefix = "PO/$year/$brandCode/$month-";

        $query = $this->db->table('purchase_orders')
            ->select("MAX(SUBSTRING_INDEX(po_number, '-', -1)) AS last_number")
            ->like('po_number', $prefix, 'after')
            ->get()
            ->getRowArray();

        return isset($query['last_number']) ? (int) $query['last_number'] : 0;
    }

    /**
     * 📌 Konversi angka bulan ke angka Romawi
     */
    public function convertToRoman($month)
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        return $romanMonths[$month] ?? null;
    }

    /**
     * 📌 Simpan Purchase Order
     */
    public function create($data)
    {
        $this->model->insert($data);
        return $this->model->getInsertID();
    }

    /**
     * 📌 Simpan Detail Purchase Order
     */
    public function createDetail($data)
    {
        return $this->db->table('purchase_order_details')->insert($data);
    }

    /**
     * 📌 Ambil ID PO terakhir yang disimpan
     */
    public function getLastInsertId()
    {
        return $this->model->getInsertID();
    }

    /**
     * 📌 Hapus Purchase Order (Soft Delete)
     */
    public function delete($id)
    {
        return $this->model->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
    }

    public function getPurchaseOrderDetails($purchaseOrderId)
{
    $db = \Config\Database::connect();

    return $db->table('purchase_order_details')
        ->select('purchase_order_details.*, products.nama_produk, products.sku')
        ->join('products', 'products.id = purchase_order_details.product_id')
        ->where('purchase_order_details.purchase_order_id', $purchaseOrderId)
        ->get()
        ->getResultArray();
}

public function getPurchaseOrderData($start, $length, $search)
{
    $builder = $this->db->table('purchase_orders po')
        ->select('po.*, s.supplier_name, 
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    "product_id", pod.product_id,
                    "nama_produk", p.nama_produk,
                    "quantity", pod.quantity,
                    "unit_price", pod.unit_price,
                    "total_price", pod.total_price
                )
            ) AS products') // 🔥 Ubah ke JSON_ARRAYAGG
        ->join('suppliers s', 's.id = po.supplier_id', 'left')
        ->join('purchase_order_details pod', 'pod.purchase_order_id = po.id', 'left')
        ->join('products p', 'p.id = pod.product_id', 'left')
        ->groupBy('po.id');

    if ($search) {
        $builder->groupStart()
                ->like('po.po_number', $search)
                ->orLike('s.supplier_name', $search)
                ->orLike('p.nama_produk', $search)
                ->groupEnd();
    }

    return $builder->limit($length, $start)->get()->getResultArray();
}

public function findById($id)
{
    return $this->db->table('purchase_orders po')
        ->select('po.*, s.supplier_name, s.supplier_address') // Tambahkan supplier_address
        ->join('suppliers s', 's.id = po.supplier_id', 'left')
        ->where('po.id', $id)
        ->get()
        ->getRowArray();
}


public function updateReceivedQuantity($poId, $productId, $receivedQty)
{
    $this->db->table('purchase_order_details')
        ->where('purchase_order_id', $poId)
        ->where('product_id', $productId)
        ->set('received_quantity', "received_quantity + $receivedQty", false)
        ->set('remaining_quantity', "remaining_quantity - $receivedQty", false)
        ->update();
}

public function logPoReceipt($poId, $productId, $warehouseId, $receivedQty)
{
    return $this->db->table('po_receipt_logs')->insert([
        'purchase_order_id' => $poId,
        'product_id' => $productId,
        'warehouse_id' => $warehouseId,
        'received_quantity' => $receivedQty,
    ]);
}


public function getAllWarehouses()
{
    return $this->db->table('warehouses')
        ->select('id, name')
        ->where('deleted_at', null)
        ->get()
        ->getResultArray();
}


public function updatePoStatus($poId)
{
    // Ambil total PO dan total yang sudah diterima
    $query = $this->db->table('purchase_order_details')
        ->select('SUM(quantity) as total_ordered, SUM(received_quantity) as total_received')
        ->where('purchase_order_id', $poId)
        ->get()
        ->getRowArray();

    if (!$query) {
        return false;
    }

    // Tentukan status berdasarkan jumlah penerimaan
    if ($query['total_received'] == 0) {
        $status = 'Pending';
    } elseif ($query['total_received'] < $query['total_ordered']) {
        $status = 'Partial';
    } else {
        $status = 'Completed';
    }

    // Update status PO
    return $this->db->table('purchase_orders')
        ->where('id', $poId)
        ->update(['status' => $status]);
}

public function countAll()
{
    return $this->db->table('purchase_orders')
        ->where('deleted_at', null)
        ->countAllResults();
}

public function countByStatus($status)
{
    return $this->db->table('purchase_orders')
        ->where('status', $status)
        ->where('deleted_at', null)
        ->countAllResults();
}

public function getTotalItems()
{
    $result = $this->db->table('purchase_order_details')
        ->selectSum('quantity')
        ->get()
        ->getRowArray();

    return $result['quantity'] ?? 0;
}
}
