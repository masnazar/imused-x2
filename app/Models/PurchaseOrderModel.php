<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table            = 'purchase_orders';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['po_number', 'supplier_id', 'status'];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    public function getPurchaseOrders()
    {
        return $this->select('purchase_orders.*, suppliers.supplier_name')
            ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
            ->findAll();
    }

    public function updateStatus($purchaseOrderId)
    {
        // ... (kode sebelumnya)
    }

    public function getPurchaseOrderData($start, $length, $search)
{
    $builder = $this->db->table('purchase_orders po')
        ->select('po.*, s.supplier_name, 
            GROUP_CONCAT(CONCAT_WS("::", p.sku, p.nama_produk, pod.quantity, pod.unit_price) SEPARATOR "||") AS products')
        ->join('suppliers s', 's.id = po.supplier_id', 'left')
        ->join('purchase_order_details pod', 'pod.purchase_order_id = po.id', 'left')
        ->join('products p', 'p.id = pod.product_id', 'left')
        ->groupBy('po.id')
        ->where('po.deleted_at', null);  // Tambahkan filter deleted_at

    if ($search) {
        $builder->groupStart()
                ->like('po.po_number', $search)
                ->orLike('s.supplier_name', $search)
                ->orLike('p.nama_produk', $search)
                ->groupEnd();
    }

    return $builder->limit($length, $start)->get()->getResultArray();
}
    

    public function countAllPurchaseOrders()
    {
        return $this->countAll();
    }

    public function countFilteredPurchaseOrders($search)
    {
        $builder = $this->db->table('purchase_orders po')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->join('purchase_order_details pod', 'pod.purchase_order_id = po.id', 'left')
            ->join('products p', 'p.id = pod.product_id', 'left');

        if ($search) {
            $builder->groupStart()
                ->like('po.po_number', $search)
                ->orLike('s.supplier_name', $search)
                ->orLike('p.nama_produk', $search)
                ->groupEnd();
        }

        return $builder->countAllResults();
    }
}