<?php

namespace App\Services;

use App\Repositories\CourierRepository;

/**
 * Service untuk bisnis logic courier
 */
class CourierService
{
    protected $repository;

    public function __construct(CourierRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createCourier(array $data): bool
    {
        // Bisa validasi di sini dulu
        return $this->repository->insert($data);
    }
}
