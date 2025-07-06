<?php namespace App\Repositories;

use Config\Database;
use CodeIgniter\Database\BaseBuilder;

class BrandExpenseRepository
{
    protected $db;
    protected $table = 'brand_expenses';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Base builder + join ke brands, accounts, platforms, users
     */
    protected function getBaseBuilder(): BaseBuilder
    {
        return $this->db->table("{$this->table} be")
            ->select([
                'be.id', 'be.date', 'be.description',
                'a.id   AS account_id', 'a.code   AS coa_code',    'a.name   AS coa_name',
                'b.id   AS brand_id',   'b.brand_name',
                'p.id   AS platform_id','p.code   AS platform_code','p.name   AS platform_name',
                'be.type',
                'be.amount',
                'u.id   AS processed_by','u.name   AS processed_by_name',
                'be.created_at','be.updated_at',
            ])
            ->join('accounts  a','a.id = be.account_id','left')
            ->join('brands    b','b.id = be.brand_id','left')
            ->join('platforms p','p.id = be.platform_id','left')
            ->join('users     u','u.id = be.processed_by','left');
    }

    /**
     * DataTables serverSide: getPaginated
     */
    public function getPaginated(array $params): array
    {
        helper('periode');

        // 1) tanggal filter
        $start = $end = null;
        if (($params['jenis_filter'] ?? '') === 'periode' && ! empty($params['periode'])) {
            list($start,$end) = get_date_range_from_periode($params['periode']);
        } elseif (
            ($params['jenis_filter'] ?? '') === 'custom' &&
            ! empty($params['start_date']) &&
            ! empty($params['end_date'])
        ) {
            $start = $params['start_date'];
            $end   = $params['end_date'];
        }

        $builder = $this->getBaseBuilder();
        if ($start && $end) {
            $builder->where('be.date >=', $start)
                    ->where('be.date <=', $end);
        }

        // 2) global search
        if (! empty($params['search']['value'])) {
            $kw = trim($params['search']['value']);
            $builder->groupStart()
                ->like('b.brand_name', $kw)
                ->orLike('a.code',        $kw)
                ->orLike('be.description',$kw)
                ->orLike('p.name',        $kw)
                ->orLike('u.name',        $kw)
                ->groupEnd();
        }

        // 3) count filtered
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // 4) paging params
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $draw   = (int) ($params['draw']   ?? 1);

        // 5) fetch page
        $data = $builder
            ->orderBy('be.date','DESC')
            ->limit($length,$start)
            ->get()
            ->getResultArray();

        // 6) total all
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
            ->where('be.id',$id)
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
        return (bool) $this->db->table($this->table)
            ->where('id',$id)
            ->update($data);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('id',$id)
            ->delete();
    }
}
