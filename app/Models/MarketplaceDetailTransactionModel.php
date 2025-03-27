<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk marketplace_detail_transactions
 */
class MarketplaceDetailTransactionModel extends Model
{
    protected $table = 'marketplace_detail_transactions';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'transaction_id',
        'product_id',
        'quantity',
        'hpp',
        'total_hpp',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
}
