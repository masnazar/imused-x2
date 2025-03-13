<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseOrderModel extends Model
{
    protected $table            = 'purchase_orders';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'po_number',
        'supplier_id',
        'status',
    ];
    protected $useTimestamps    = true;
    protected $useSoftDeletes   = true;

    /**
     * 📌 Ambil Purchase Order beserta detail suppliernya
     */
    public function getPurchaseOrders()
    {
        return $this->select('purchase_orders.*, suppliers.supplier_name')
                    ->join('suppliers', 'suppliers.id = purchase_orders.supplier_id')
                    ->findAll();
    }

    /**
     * 📌 Update status PO setelah barang diterima
     */
    public function updateStatus($purchaseOrderId)
    {
        $db = \Config\Database::connect();
        $details = $db->table('purchase_order_details')
                      ->where('purchase_order_id', $purchaseOrderId)
                      ->get()
                      ->getResultArray();

        $allComplete = true;
        $anyPartial = false;

        foreach ($details as $detail) {
            if ($detail['received_quantity'] < $detail['quantity']) {
                $allComplete = false;
            }
            if ($detail['received_quantity'] > 0 && $detail['received_quantity'] < $detail['quantity']) {
                $anyPartial = true;
            }
        }

        $status = $allComplete ? 'Complete' : ($anyPartial ? 'Partial' : 'Pending');

        return $this->update($purchaseOrderId, ['status' => $status]);
    }
}
