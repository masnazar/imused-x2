<?php

namespace App\Repositories;

use App\Models\SoscomTeamModel;

/**
 * Repository untuk Soscom Team
 */
class SoscomTeamRepository
{
    protected SoscomTeamModel $model;

    public function __construct()
    {
        $this->model = new SoscomTeamModel();
    }

    public function getAll(): array
    {
        return $this->model->orderBy('team_name', 'ASC')->findAll();
    }

    public function find(int $id): ?array
    {
        return $this->model->find($id);
    }

    public function create(array $data): int
    {
        $this->model->insert($data);
        return $this->model->getInsertID();
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}
