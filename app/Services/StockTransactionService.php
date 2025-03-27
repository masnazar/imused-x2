<?php

namespace App\Services;

use App\Repositories\StockTransactionRepository;

class StockTransactionService
{
    protected $repo;

    public function __construct()
    {
        $this->repo = new StockTransactionRepository();
    }

    /**
     * Ambil semua warehouse
     */
    public function getAllWarehouses(): array
    {
        return $this->repo->getAllWarehouses();
    }

    public function getTransactions(
        string $search,
        int $start,
        int $length,
        string $orderColumn,
        string $orderDir,
        array $filters // Terima filter sebagai array
    ) {
        try {
            return $this->repo->getTransactions(
                $search,
                $start,
                $length,
                $orderColumn,
                $orderDir,
                $filters['warehouse_id'],
                $filters['transaction_type'],
                $filters['start_date'],
                $filters['end_date']
            );
        } catch (\Exception $e) {
            log_message('error', '[Service Error] ' . $e->getMessage());
            return ['data' => []];
        }
    }

    public function countAll()
    {
        return $this->repo->countAll();
    }

    public function countFiltered($search, array $filters)
    {
        try {
            return $this->repo->countFiltered(
                $search,
                $filters['warehouse_id'],
                $filters['transaction_type'],
                $filters['start_date'],
                $filters['end_date']
            );
        } catch (\Exception $e) {
            log_message('error', '[Service Error] ' . $e->getMessage());
            return 0;
        }
    }

    public function getLastQuery()
    {
        return $this->repo->getLastQuery();
    }

    public function getStatistik(array $filters)
{
    return $this->repo->getStatistik($filters);
}

}
