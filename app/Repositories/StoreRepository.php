<?php namespace App\Repositories;

use Config\Database;
use CodeIgniter\Database\BaseBuilder;

class StoreRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /** Base builder + join ke brands */
    protected function getBaseBuilder(): BaseBuilder
    {
        return $this->db->table('stores s')
            ->select([
                's.id', 's.store_code', 's.store_name',
                's.brand_id', 'b.brand_name',
                's.created_at','s.updated_at',
            ])
            ->join('brands b', 'b.id = s.brand_id', 'left');
    }

    /** Untuk DataTables server-side */
    public function getPaginated(array $params): array
    {
        $builder = $this->getBaseBuilder();

        // global search
        if (! empty($params['search']['value'])) {
            $kw = trim($params['search']['value']);
            $builder->groupStart()
                ->like('s.store_code', $kw)
                ->orLike('s.store_name', $kw)
                ->orLike('b.brand_name', $kw)
                ->groupEnd();
        }

        // hitung filtered
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // paging params
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $draw   = (int) ($params['draw']   ?? 1);

        // ambil data
        $data = $builder
            ->orderBy('s.id','DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // total tanpa filter
        $recordsTotal = (int) $this->db->table('stores')->countAllResults();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /** Cari satu record */
    public function find(int $id): ?array
    {
        $row = $this->getBaseBuilder()
            ->where('s.id', $id)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /** Insert baru */
    public function insert(array $data): int
    {
        $this->db->table('stores')->insert($data);
        return (int) $this->db->insertID();
    }

    /** Update existing */
    public function update(int $id, array $data): bool
    {
        return (bool) $this->db
            ->table('stores')
            ->where('id', $id)
            ->update($data);
    }

    /** Hapus */
    public function delete(int $id): bool
    {
        return (bool) $this->db
            ->table('stores')
            ->where('id', $id)
            ->delete();
    }
}
