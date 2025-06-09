<?php

namespace App\Services;

use App\Repositories\SoscomTeamRepository;

/**
 * Service Layer untuk Soscom Team
 */
class SoscomTeamService
{
    protected SoscomTeamRepository $repo;

    public function __construct()
    {
        $this->repo = new SoscomTeamRepository();
    }

    public function getAll(): array
    {
        return $this->repo->getAll();
    }

    public function createTeam(array $data): int
    {
        return $this->repo->create($data);
    }

    public function updateTeam(int $id, array $data): bool
    {
        return $this->repo->update($id, $data);
    }

    public function deleteTeam(int $id): bool
    {
        return $this->repo->delete($id);
    }

    public function getById(int $id): ?array
    {
        return $this->repo->find($id);
    }
}
