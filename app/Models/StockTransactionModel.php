<?php

namespace App\Models;

use CodeIgniter\Model;

class StockTransactionModel extends Model
{
    protected $table            = 'stock_transactions';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'warehouse_id',
        'product_id',
        'quantity',
        'transaction_type',
        'status',
        'transaction_source',
        'related_warehouse_id'
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'warehouse_id' => 'required|integer',
        'product_id' => 'required|integer',
        'quantity' => 'required|integer',
        'transaction_type' => 'required',
        'status' => 'required',
    ];
    protected $validationMessages = [
        'warehouse_id' => ['required' => 'Warehouse is required.'],
        'product_id' => ['required' => 'Product is required.'],
    ];

    /**
     * 📌 Join ke warehouse dan produk
     */
    public function getJoinedQuery()
{
    return $this->from($this->table)
        ->select("
            {$this->table}.*,
            w1.name AS warehouse_name,
            IFNULL(w2.name, w1.name) AS related_warehouse_name,
            p.nama_produk AS product_name
        ")
        ->join('warehouses w1', 'w1.id = stock_transactions.warehouse_id')
        ->join('warehouses w2', 'w2.id = stock_transactions.related_warehouse_id', 'left')
        ->join('products p', 'p.id = stock_transactions.product_id');
}

}
