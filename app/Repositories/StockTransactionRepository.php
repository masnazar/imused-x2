<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseBuilder;

class StockTransactionRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getAllWarehouses(): array
    {
        return $this->db->table('warehouses')->select('id, name')->get()->getResultArray();
    }

    public function getTransactions(
        $search,
        $start,
        $length,
        $orderColumn,
        $orderDir,
        $warehouseId,
        $transactionType,
        $startDate,
        $endDate
    ) {
        try {
            $builder = $this->baseBuilder();
            if ($search) {
                $builder->groupStart()
                    ->like('products.nama_produk', $search)
                    ->orLike('w1.name', $search)
                    ->groupEnd();
            }

            if ($warehouseId) {
                $builder->where('stock_transactions.warehouse_id', $warehouseId);
            }

            if ($transactionType) {
                $builder->where('stock_transactions.transaction_type', $transactionType);
            }

            if ($startDate) {
                $builder->where('stock_transactions.created_at >=', $startDate . ' 00:00:00');
            }
            if ($endDate) {
                $builder->where('stock_transactions.created_at <=', $endDate . ' 23:59:59');
            }

            $data = $builder->orderBy($orderColumn, $orderDir)
                    ->limit($length, $start)
                    ->get()
                    ->getResultArray();

            log_message('debug', '🧾 Stock Query: ' . $builder->getCompiledSelect());

            return ['data' => $data];
        } catch (\Exception $e) {
            log_message('error', '[getTransactions] DB Error: ' . $e->getMessage());
            return ['data' => []];
        }
    }

    public function countAll()
    {
        return $this->db->table('stock_transactions')->countAllResults();
    }

    public function countFiltered($search, $warehouseId, $transactionType, $startDate, $endDate)
    {
        try {
            $builder = $this->baseBuilder();

            if ($search) {
                $builder->groupStart()
                    ->like('products.nama_produk', $search)
                    ->orLike('w1.name', $search)
                    ->groupEnd();
            }

            if ($warehouseId) {
                $builder->where('stock_transactions.warehouse_id', $warehouseId);
            }

            if ($transactionType) {
                $builder->where('stock_transactions.transaction_type', $transactionType);
            }

            if ($startDate) {
                $builder->where('stock_transactions.created_at >=', $startDate);
            }

            if ($endDate) {
                $builder->where('stock_transactions.created_at <=', $endDate);
            }

            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', '[countFiltered] DB Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function baseBuilder(): BaseBuilder
    {
        return $this->db->table('stock_transactions')
            ->select('
                stock_transactions.id,
                products.nama_produk AS product_name,
                w1.name AS warehouse_name,
                w2.name AS related_warehouse_name,
                stock_transactions.transaction_type,
                stock_transactions.quantity,
                stock_transactions.status,
                stock_transactions.transaction_source,
                stock_transactions.reference,
                stock_transactions.created_at
            ')
            ->join('products', 'products.id = stock_transactions.product_id')
            ->join('warehouses w1', 'w1.id = stock_transactions.warehouse_id')
            ->join('warehouses w2', 'w2.id = stock_transactions.related_warehouse_id', 'left');
    }

    public function getLastQuery()
    {
        return $this->db->getLastQuery();
    }

    public function getStatistik(array $filters): array
{
    $base = $this->db->table('stock_transactions');

    if (!empty($filters['warehouse_id'])) {
        $base->where('warehouse_id', $filters['warehouse_id']);
    }

    if (!empty($filters['transaction_type'])) {
        $base->where('transaction_type', $filters['transaction_type']);
    }

    if (!empty($filters['start_date'])) {
        $base->where('DATE(created_at) >=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $base->where('DATE(created_at) <=', $filters['end_date']);
    }

    // Total Inbound
    $inboundQty = clone $base;
    $totalInbound = (int)$inboundQty->selectSum('quantity')
        ->where('transaction_type', 'Inbound')
        ->get()
        ->getRow('quantity') ?? 0;

    // Total Outbound
    $outboundQty = clone $base;
    $totalOutbound = (int)$outboundQty->selectSum('quantity')
        ->where('transaction_type', 'Outbound')
        ->get()
        ->getRow('quantity') ?? 0;

    // Chart Data
    $chartQuery = clone $base;
    $chartData = $chartQuery->select("
            DATE(created_at) as date,
            SUM(CASE WHEN transaction_type = 'Inbound' THEN quantity ELSE 0 END) as inbound,
            SUM(CASE WHEN transaction_type = 'Outbound' THEN quantity ELSE 0 END) as outbound
        ")
        ->groupBy('DATE(created_at)')
        ->orderBy('DATE(created_at)', 'ASC')
        ->get()
        ->getResultArray();

    // Format Chart Data
    $labels = [];
    $inboundData = [];
    $outboundData = [];
    foreach ($chartData as $row) {
        $labels[] = $row['date'];
        $inboundData[] = $row['inbound'] ?? 0;
        $outboundData[] = $row['outbound'] ?? 0;
    }

    return [
        'total_inbound' => $totalInbound,
        'total_outbound' => $totalOutbound,
        'chart' => [
            'labels' => $labels,
            'inbound' => $inboundData,
            'outbound' => $outboundData
        ]
    ];
}

}
