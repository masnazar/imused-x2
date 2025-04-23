<?php

namespace App\Models;

use CodeIgniter\Model;

class SoscomTransactionModel extends Model
{
    protected $table            = 'soscom_transactions';
    protected $primaryKey       = 'id';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'date', 'order_number', 'customer_id', 'brand_id', 'warehouse_id',
        'store_name', 'payment_method', 'cod_fee', 'total_qty', 'selling_price',
        'discount', 'admin_fee', 'net_revenue', 'gross_profit',
        'lead_source', 'order_type', 'platform', 'status',
        'processed_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
