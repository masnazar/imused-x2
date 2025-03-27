<?php

namespace App\Services;

use App\Repositories\MarketplaceTransactionRepository;

/**
 * Service untuk logika bisnis Marketplace Transaction
 */
class MarketplaceTransactionService
{
    protected $repository;

    public function __construct(MarketplaceTransactionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPaginatedData(array $filters)
    {
        return $this->repository->getTransactions($filters);
    }
}
