<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel soscom_transactions
 */
class SoscomTransactionModel extends Model
{
    protected $table            = 'soscom_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'date',
        'phone_number',
        'customer_name',
        'city',
        'province',
        'brand_id',
        'total_qty',
        'selling_price',
        'hpp',
        'payment_method',
        'cod_fee',
        'shipping_cost',
        'total_payment',
        'estimated_profit',
        'courier_id',
        'tracking_number',
        'shipping_status',
        'soscom_team_id',
        'processed_by',
        'channel'
    ];
}
