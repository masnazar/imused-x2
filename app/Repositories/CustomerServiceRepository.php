<?php namespace App\Repositories;

use Config\Database;
use CodeIgniter\Database\BaseBuilder;

class CustomerServiceRepository
{
    protected $db;
    protected $table = 'customer_services';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    protected function getBaseBuilder(): BaseBuilder
    {
        return $this->db
            ->table("{$this->table} cs")
            ->select(['cs.id', 'cs.kode_cs', 'cs.nama_cs']);
    }

    /**
     * Server-side DataTables pagination + search
     */
    public function getPaginated(array $params): array
    {
        // ambil param DataTables
        $draw   = (int) ($params['draw']   ?? 1);
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $builder = $this->getBaseBuilder();

        // global search
        if ($search !== '') {
            $builder->groupStart()
                ->like('cs.kode_cs', $search)
                ->orLike('cs.nama_cs', $search)
                ->groupEnd();
        }

        // hitung filtered
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // ambil data page
        $data = $builder
            ->orderBy('cs.id','DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // total keseluruhan
        $recordsTotal = (int) $this->db
            ->table($this->table)
            ->countAllResults();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    public function find(int $id): ?array
    {
        return $this->getBaseBuilder()
            ->where('cs.id', $id)
            ->get()
            ->getRowArray() ?: null;
    }

    public function insert(array $data): int
    {
        $this->db->table($this->table)->insert($data);
        return (int) $this->db->insertID();
    }

    public function update(int $id, array $data): bool
    {
        return (bool) $this->db
            ->table($this->table)
            ->where('id', $id)
            ->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db
            ->table($this->table)
            ->where('id', $id)
            ->delete();
    }
}
