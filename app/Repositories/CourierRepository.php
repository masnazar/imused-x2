<?php

namespace App\Repositories;

use App\Models\CourierModel;

/**
 * Repository untuk handling query courier
 */
class CourierRepository
{
    protected $model;

    public function __construct(CourierModel $model)
    {
        $this->model = $model;
    }

    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }

    public function all()
    {
        return $this->model->findAll();
    }
}
