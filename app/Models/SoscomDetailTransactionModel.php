<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel soscom_detail_transactions
 */
class SoscomDetailTransactionModel extends Model
{
    protected $table            = 'soscom_detail_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'transaction_id',
        'product_id',
        'quantity',
        'hpp',
        'unit_selling_price',
        'total_hpp',
    ];
}
