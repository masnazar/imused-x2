<?php namespace App\Services;

use App\Repositories\StoreRepository;
use CodeIgniter\Database\Exceptions\DataException;

class StoreService
{
    protected StoreRepository $repo;

    public function __construct()
    {
        $this->repo = new StoreRepository();
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
        if (empty($data['brand_id']) || ! is_numeric($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (empty(trim($data['store_code']))) {
            throw new DataException('Kode toko wajib diisi.');
        }
        if (empty(trim($data['store_name']))) {
            throw new DataException('Nama toko wajib diisi.');
        }

        $insert = [
            'brand_id'   => (int)$data['brand_id'],
            'store_code' => trim($data['store_code']),
            'store_name' => trim($data['store_name']),
        ];

        return $this->repo->insert($insert);
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data['brand_id']) || ! is_numeric($data['brand_id'])) {
            throw new DataException('Brand wajib dipilih.');
        }
        if (empty(trim($data['store_code']))) {
            throw new DataException('Kode toko wajib diisi.');
        }
        if (empty(trim($data['store_name']))) {
            throw new DataException('Nama toko wajib diisi.');
        }

        $update = [
            'brand_id'   => (int)$data['brand_id'],
            'store_code' => trim($data['store_code']),
            'store_name' => trim($data['store_name']),
        ];

        return $this->repo->update($id, $update);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
