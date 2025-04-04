<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk marketplace_transactions
 */
class MarketplaceTransactionModel extends Model
{
    protected $table = 'marketplace_transactions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'brand_id',
        'date',
        'order_number',
        'tracking_number',
        'courier_id',
        'platform',
        'warehouse_id',
        'store_name',
        'total_qty',
        'selling_price',
        'hpp',
        'discount',
        'admin_fee',
        'net_revenue',
        'gross_profit',
        'status',
        'created_at',
        'updated_at',
        'processed_by',
    ];

    protected $useTimestamps = true;
}
