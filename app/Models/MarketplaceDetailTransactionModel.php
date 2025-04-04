<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk marketplace_detail_transactions
 */
class MarketplaceDetailTransactionModel extends Model
{
    protected $table = 'marketplace_detail_transaction';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_selling_price',
        'hpp',
        'total_hpp',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
}
