<?php namespace App\Services;

use App\Repositories\CustomerServiceRepository;
use CodeIgniter\Database\Exceptions\DataException;

class CustomerService
{
    protected CustomerServiceRepository $repo;

    public function __construct()
    {
        $this->repo = new CustomerServiceRepository();
    }

    public function getPaginated(array $params): array
    {
        return $this->repo->getPaginated($params);
    }

    public function find(int $id): ?array
    {
        return $this->repo->find($id);
    }

    public function create(array $data): int
    {
        if (empty(trim($data['kode_cs']))) {
            throw new DataException('Kode CS wajib diisi.');
        }
        if (empty(trim($data['nama_cs']))) {
            throw new DataException('Nama CS wajib diisi.');
        }

        // siapkan insert
        $insert = [
            'kode_cs' => trim($data['kode_cs']),
            'nama_cs' => trim($data['nama_cs']),
        ];

        return $this->repo->insert($insert);
    }

    public function update(int $id, array $data): bool
    {
        if (empty(trim($data['kode_cs']))) {
            throw new DataException('Kode CS wajib diisi.');
        }
        if (empty(trim($data['nama_cs']))) {
            throw new DataException('Nama CS wajib diisi.');
        }

        $update = [
            'kode_cs' => trim($data['kode_cs']),
            'nama_cs' => trim($data['nama_cs']),
        ];

        return $this->repo->update($id, $update);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
