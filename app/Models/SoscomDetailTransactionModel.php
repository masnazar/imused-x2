<?php

namespace App\Models;

use CodeIgniter\Model;

class SoscomDetailTransactionModel extends Model
{
    protected $table            = 'soscom_detail_transactions';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'transaction_id', 'product_id', 'quantity', 'hpp',
        'unit_selling_price', 'total_hpp', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
