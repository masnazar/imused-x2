<?php namespace App\Repositories;

use Config\Database;

class ExpenseRepository
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * DataTables pagination + search + join COA, Brand, Platform, User
     */
    public function getPaginated(array $params): array
    {
        $start  = (int) ($params['start']  ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $draw   = (int) ($params['draw']   ?? 1);

        // ─── Query utama ──────────────────────────────────────────────────────────
        $builder = $this->db->table('expenses e')
            ->select([
                'e.id',
                'e.date',
                'e.description',
                'e.account_id',
                'a.code        AS coa_code',
                'a.name        AS coa_name',
                'e.brand_id',
                'b.brand_name',
                'e.platform_id',
                'p.code        AS platform_code',
                'p.name        AS platform_name',
                'e.type',                       // Request / Debit Saldo Akun
                'u.name        AS processed_by',
                'e.amount',
                'e.created_at',
                'e.updated_at',
            ])
            ->join('accounts a',  'a.id = e.account_id',   'left')
            ->join('brands   b',  'b.id = e.brand_id',     'left')
            ->join('platforms p','p.id = e.platform_id',  'left')
            ->join('users    u',  'u.id = e.processed_by', 'left');

        // ─── Search global ───────────────────────────────────────────────────────
        if (! empty($params['search']['value'])) {
            $kw = trim($params['search']['value']);
            $builder->groupStart()
                ->like('e.description', $kw)
                ->orLike('a.code',         $kw)
                ->orLike('b.brand_name',   $kw)
                ->orLike('p.name',         $kw)
                ->orLike('u.name',         $kw)
                ->groupEnd();
        }

        // ─── Hitung filtered ────────────────────────────────────────────────────
        $clone = clone $builder;
        $recordsFiltered = $clone->countAllResults(false);

        // ─── Ambil data page ────────────────────────────────────────────────────
        $data = $builder
            ->orderBy('e.date', 'desc')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => (int) $this->db->table('expenses')->countAllResults(),
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ];
    }

    /**
     * Ambil satu expense (dipakai di form edit/view)
     */
    public function find(int $id): ?array
    {
        return $this->db->table('expenses e')
            ->select([
                'e.*',
                'a.code        AS coa_code',
                'a.name        AS coa_name',
                'b.brand_name',
                'p.code        AS platform_code',
                'p.name        AS platform_name',
                'u.name        AS processed_by',
            ])
            ->join('accounts a',   'a.id = e.account_id',    'left')
            ->join('brands   b',   'b.id = e.brand_id',      'left')
            ->join('platforms p',  'p.id = e.platform_id',   'left')
            ->join('users    u',   'u.id = e.processed_by',  'left')
            ->where('e.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Insert baru
     */
    public function insert(array $data): int
    {
        $this->db->table('expenses')->insert($data);
        return (int)$this->db->insertID();
    }

    /**
     * Update
     */
    public function update(int $id, array $data): bool
    {
        return (bool) $this->db->table('expenses')
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Hapus
     */
    public function delete(int $id): bool
    {
        return (bool) $this->db->table('expenses')
            ->where('id', $id)
            ->delete();
    }
}
